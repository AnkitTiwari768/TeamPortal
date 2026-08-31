<?php

declare(strict_types=1);

namespace App\Domain\QMS;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Traits\Respond;
use Illuminate\Support\Facades\Validator;

class QueryController extends ClientController
{
    use Respond;

    public function __construct(private QueryService $service) {}

    public function index(Request $request, string $claimSlug = null)
    {
        if ($request->ajax()) {
            $queryList = app(QueryListQuery::class);
            return $this->success(data: $queryList->execute(AuthId(), $claimSlug ?: null));
        }

        $title = "Claim Queries";
        return view('qms.index', compact('title', 'claimSlug'));
    }

    public function create(): View
    {
        $users = $this->service->getAvailableReceivers(AuthId());
        $roles = \DB::table('roles')->where('status', 1)->get();
        $title = "Send New Query";
        return view('qms.create', compact('users', 'roles', 'title'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'receiver_role_id' => 'nullable|exists:roles,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if (!$request->receiver_id && !$request->receiver_role_id) {
            return redirect()->back()->withErrors(['receiver_id' => 'Please select either a user or a role.'])->withInput();
        }

        $senderRoleId = \DB::table('user_roles')->where('user_id', AuthId())->value('role_id');
        $dto = QueryDTO::fromRequest($request->all(), AuthId(), $senderRoleId);
        $this->service->createQuery($dto);

        return redirect()->route('qms.index')->with('success', 'Query sent successfully.');
    }

    public function show(string $id): View
    {
        $query = $this->service->getQueryDetails($id);
        
        $this->service->markAsRead($id, AuthId());
        
        $title = "View Query: " . $query->subject;
        return view('qms.show', compact('query', 'title'));
    }

    public function reply(Request $request, string $id): RedirectResponse
    {
        $query = $this->service->getQueryDetails($id);
        
        if (!$query->canUserReply(auth()->user())) {
            return redirect()->route('qms.show', $id)->with('error', 'You cannot take action on this query at this time (Read-Only).');
        }

        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $this->service->replyToQuery(
            $id, 
            AuthId(), 
            $request->message, 
            $request->file('attachments', [])
        );

        return redirect()->route('qms.show', $id)->with('success', 'Reply sent successfully.');
    }

    public function close(string $id): RedirectResponse
    {
        $query = $this->service->getQueryDetails($id);
        
        if (!$query->canUserClose(auth()->user())) {
            return redirect()->route('qms.show', $id)->with('error', 'You are not authorized to close this query.');
        }

        $this->service->closeQuery($id);
        return redirect()->route('qms.show', $id)->with('success', 'Query closed successfully.');
    }
}
