<?php

declare(strict_types=1);

namespace App\Domain\QMS;

use App\Domain\QMS\Query;
use App\Domain\QMS\QueryMessage;
use App\Domain\QMS\QueryAttachment;
use App\Http\Api\V1\FileUpload\FileUploadService;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class QueryService
{
    public function __construct(private FileUploadService $fileUploadService) {}

    public function createQuery(QueryDTO $dto): Query
    {
        return DB::transaction(function () use ($dto) {
            $query = Query::create([
                'sender_id' => $dto->senderId,
                'sender_role_id' => $dto->senderRoleId,
                'receiver_id' => $dto->receiverId,
                'receiver_role_id' => $dto->receiverRoleId,
                'subject' => $dto->subject,
                'status' => 'open'
            ]);

            $message = $query->messages()->create([
                'sender_id' => $dto->senderId,
                'receiver_id' => $dto->receiverId,
                'receiver_role_id' => $dto->receiverRoleId,
                'message' => $dto->message
            ]);

            $this->handleAttachments($message, $dto->attachments);

            return $query;
        });
    }

    public function replyToQuery(string $queryId, string $senderId, string $messageContent, array $attachments = []): QueryMessage
    {
        return DB::transaction(function () use ($queryId, $senderId, $messageContent, $attachments) {
            $query = Query::findOrFail($queryId);
            
            // Re-open query if it was closed
            if ($query->status === 'closed') {
                $query->update(['status' => 'open']);
            }

            // Determine receiver based on sender
            $receiverId = null;
            $receiverRoleId = null;

            $userRoles = DB::table('user_roles')->where('user_id', $senderId)->pluck('role_id')->toArray();
            $isOriginalSender = ($senderId === $query->sender_id) || ($query->sender_role_id && in_array($query->sender_role_id, $userRoles));

            if ($isOriginalSender) {
                $receiverId = $query->receiver_id;
                $receiverRoleId = $query->receiver_role_id;
            } else {
                $receiverId = $query->sender_id;
                $receiverRoleId = $query->sender_role_id;
            }

            $message = $query->messages()->create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'receiver_role_id' => $receiverRoleId,
                'message' => $messageContent
            ]);

            if($message->sender_id != $query->sender_id){
                $batchId = DB::table('dy_queries')->where('query_id', $queryId)->value('batch_id');
                if ($batchId) {
                    // Standard query closure for batches
                    

                    // Special handling for Invoice Reupload Request
                    if ($query->subject === 'Invoice Reupload Request' && !empty($attachments)) {
                        DB::table('dy_batches')->where('id', $batchId)->update([
                        'is_query_open' => false
                    ]);
                        DB::table('dy_batches')->where('id', $batchId)->update([
                            'is_invoice_query_open' => false,
                            'is_invoice_uploaded' => true,
                        ]);

                        // Handle attachments for the batch as well
                        $this->handleAttachments($message, $attachments);
                        
                        // Copy the latest attachment to dy_attachments for the batch
                        $latestAttachment = QueryAttachment::where('message_id', $message->id)
                            ->join('file_uploads', 'qms_attachments.file_upload_id', '=', 'file_uploads.id')
                            ->orderBy('qms_attachments.created_at', 'desc')
                            ->select('file_uploads.file_system_name')
                            ->first();

                        if ($latestAttachment) {
                            DB::table('dy_attachments')->insert([
                                'id' => DB::raw('UUID()'),
                                'entity_type' => 'batch',
                                'entity_id' => $batchId,
                                'attachment_type_id' => DB::table('document_categories')->where('slug', 'invoices_upload')->value('id'),
                                'file_path' => $latestAttachment->file_system_name,
                                'uploaded_by' => $senderId,
                                'created_at' => now(),
                            ]);
                        }

                        // Close the query automatically
                        $query->update(['status' => 'closed']);
                        return $message; // Return early as we've already handled attachments
                    }
                }
            }

            $this->handleAttachments($message, $attachments);

            return $message;
        });
    }

    public function getQueriesForUser(string $userId)
    {
        $userRoles = \DB::table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        return Query::where('sender_id', $userId)
            ->orWhereIn('sender_role_id', $userRoles)
            ->orWhere('receiver_id', $userId)
            ->orWhereIn('receiver_role_id', $userRoles)
            ->with(['sender', 'receiver', 'senderRole', 'receiverRole'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getQueryDetails(string $queryId)
    {
        $query = Query::with([
            'sender', 
            'receiver', 
            'senderRole',
            'receiverRole',
            'messages.sender', 
            'messages.attachments.fileUpload'
        ])->findOrFail($queryId);

        $linkInfo = DB::table('dy_queries')
            ->leftJoin('dy_batches', 'dy_queries.batch_id', '=', 'dy_batches.id')
            ->leftJoin('claims', 'dy_queries.claim_id', '=', 'claims.id')
            ->where('dy_queries.query_id', $queryId)
            ->select('dy_batches.batch_number', 'claims.application_number','dy_batches.id as batch_id')
            ->first();

        if ($linkInfo) {
            $query->batch_number = $linkInfo->batch_number;
            $query->claim_number = $linkInfo->application_number;
            
            if ($linkInfo->batch_id) {
                $query->claim_slug = DB::table('dy_batches')
                    ->join('claim_types', 'claim_types.id', '=', 'dy_batches.claim_type_id')
                    ->where('dy_batches.id', $linkInfo->batch_id)
                    ->value('claim_types.slug');
            }
        }

        return $query;
    }

    private function handleAttachments(QueryMessage $message, array $files): void
    {
        foreach ($files as $file) {
            // Using a specific path for QMS attachments
            $fileUpload = $this->fileUploadService->uploadImage($file, 'qms_attachments');
            
            if ($fileUpload) {
                QueryAttachment::create([
                    'message_id' => $message->id,
                    'file_upload_id' => $fileUpload->id
                ]);
            }
        }
    }
    
    public function closeQuery(string $queryId): void
    {
        DB::transaction(function() use ($queryId) {
            Query::where('id', $queryId)->update(['status' => 'closed']);
            $batchId = DB::table('dy_queries')->where('query_id', $queryId)->value('batch_id');
            DB::table('dy_batches')->where('id', $batchId)->update([
                'is_query_open' => false
            ]);
        });
    }

    public function getAvailableReceivers(string $excludeUserId)
    {
        // For simplicity, returning all users except current. 
        // In a real system, this might be filtered by roles/permissions.
        return User::where('id', '!=', $excludeUserId)
            ->where('status', 1)
            ->get();
    }

    public function markAsRead(string $queryId, string $userId): void
    {
        $userRoles = DB::table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        QueryMessage::where('query_id', $queryId)
            ->whereNull('read_at')
            ->where(function ($q) use ($userId, $userRoles) {
                $q->where('receiver_id', $userId)
                    ->orWhereIn('receiver_role_id', $userRoles);
            })
            ->update(['read_at' => now()]);
    }

    public function getNewQueriesCountForUser(string $userId, ?string $claimSlug = null): int
    {
        $userRoles = DB::table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        // Simply count unread messages where the user (or their role) is the receiver
        $query = QueryMessage::whereNull('read_at')
            ->where(function ($q) use ($userId, $userRoles) {
                $q->where('receiver_id', $userId)
                    ->orWhereIn('receiver_role_id', $userRoles);
            });

        // Restrict to messages on queries raised against claims/batches of the
        // given claim type, matching the scoping used by QueryListQuery.
        if (!empty($claimSlug)) {
            $query->whereExists(function ($sub) use ($claimSlug) {
                $sub->select(DB::raw(1))
                    ->from('dy_queries')
                    ->leftJoin('claims', 'dy_queries.claim_id', '=', 'claims.id')
                    ->leftJoin('dy_batches', 'dy_queries.batch_id', '=', 'dy_batches.id')
                    ->leftJoin('claim_types as claim_claim_types', 'claims.claim_type_id', '=', 'claim_claim_types.id')
                    ->leftJoin('claim_types as batch_claim_types', 'dy_batches.claim_type_id', '=', 'batch_claim_types.id')
                    ->whereColumn('dy_queries.query_id', 'qms_messages.query_id')
                    ->where(function ($w) use ($claimSlug) {
                        $w->where('claim_claim_types.slug', $claimSlug)
                          ->orWhere('batch_claim_types.slug', $claimSlug);
                    });
            });
        }

        return $query->count();
    }

    public function getClaimSlug(string $batchId)
    {
        return DB::table('dy_batches')
            ->join('claim_types','claim_types.id', '=', 'dy_batches.claim_type_id')
            ->where('dy_batches.id', $batchId)
            ->value('claim_types.slug');
    }
}
