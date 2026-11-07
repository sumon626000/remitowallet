<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ticket', 'subject', 'status', 'last_reply'];

    protected $dates = ['last_reply'];

    public function getUsernameAttribute()
    {
        return $this->name;
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('ticketRecord');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages(){
        return $this->hasMany(SupportTicketMessage::class)->latest();
    }

    public function lastReply(){
        return $this->hasOne(SupportTicketMessage::class)->latest();
    }
    public function  getLastMessageAttribute(){
        return Str::limit($this->lastReply->message,40);
    }


    public function getStatus($type = null)
    {
        if ($this->status == 0) {
            return !$type ?
                '<span class="badge bg-soft-primary text-primary">
                        <span class="legend-indicator bg-primary"></span>' . trans('Open') . '
                    </span>'
                : 'Open' ;

        } elseif ($this->status == 1) {
            return !$type ?
                '<span class="badge bg-soft-info text-info">
                        <span class="legend-indicator bg-info"></span>' . trans('Answered') . '
                    </span>'
                : 'Answered';

        } elseif ($this->status == 2) {
            return !$type ?
                '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Replied') . '
                 </span>'
                : 'Replied';
        }
        else{
            return !$type ?
                '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Closed') . '
                  </span>'
                : 'Closed';
        }
    }

    public function scopeFilterByState($query, $state)
    {
        return match ($state) {
            'answered' => $query->whereStatus(1),
            'replied' => $query->whereStatus(2),
            'closed' => $query->whereStatus(3),
            default => $query,
        };
    }


}
