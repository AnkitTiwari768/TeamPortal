<?php

namespace App\Domain\QMS;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use App\Http\Api\V1\FileUpload\FileUpload;

class QueryAttachment extends Model
{
    use UUID;

    protected $table = 'qms_attachments';

    protected $fillable = [
        'id',
        'message_id',
        'file_upload_id',
    ];

    public function message()
    {
        return $this->belongsTo(QueryMessage::class, 'message_id');
    }

    public function fileUpload()
    {
        return $this->belongsTo(FileUpload::class, 'file_upload_id');
    }
}
