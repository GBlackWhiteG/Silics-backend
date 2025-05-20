<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['attachmentable_id', 'attachmentable_type', 'attachment_url', 'mime_type', 'original_filename'];

    public function attachmentable()
    {
        return $this->morphTo();
    }
}
