<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPermission extends Model
{
    use HasFactory;

    protected $fillable = ['notifyable_id', 'notifyable_type', 'template_email_key', 'template_sms_key', 'template_in_app_key', 'template_push_key'];

    public function notifyable()
    {
        return $this->morphTo(__FUNCTION__, 'notifyable_type', 'notifyable_id');
    }

    protected $casts = [
        'template_email_key' => 'array',
        'template_sms_key' => 'array',
        'template_in_app_key' => 'array',
        'template_push_key' => 'array'
    ];
}
