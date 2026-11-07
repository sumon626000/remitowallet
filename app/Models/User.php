<?php

namespace App\Models;

use App\Traits\Notify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Notify;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['last-seen-activity',];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('userRecord');
        });
    }


    public function getImageUrlAttribute()
    {
        return getFile($this->image_driver, $this->attributes['image']);
    }

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function funds()
    {
        return $this->hasMany(Fund::class)->latest()->where('status', '!=', 0);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class)->latest();
    }

    public function payout()
    {
        return $this->hasMany(Payout::class, 'user_id');
    }

    public function getLastSeenActivityAttribute(): bool
    {
        if (Cache::has('user-is-online-' . $this->id)) {
            return true;
        } else {
            return false;
        }
    }

    public function notifypermission()
    {
        return $this->morphOne(NotificationPermission::class, 'notifyable');
    }

    public function inAppNotification()
    {
        return $this->morphOne(InAppNotification::class, 'inAppNotificationable', 'in_app_notificationable_type', 'in_app_notificationable_id');
    }

    public function fireBaseToken()
    {
        return $this->morphMany(FireBaseToken::class, 'tokenable');
    }


    public function profilePicture($type = false): string
    {
        $type = config('demo.IS_DEMO') ;

        $status = $this->LastSeenActivity ? 'success' : 'warning';
        if (!$type && $this->image) {
            $url = getFile($this->image_driver, $this->image);
            return <<<HTML
            <div class="avatar avatar-sm avatar-circle">
                <img class="avatar-img" src="{$url}" alt="...">
                <span class="avatar-status avatar-sm-status avatar-status-{$status}"></span>
            </div>
        HTML;
        }

        $initial = mb_check_encoding($this->firstname, 'UTF-8') ? mb_substr(trim($this->firstname), 0, 1) : '';
        return <<<HTML
        <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
            <span class="avatar-initials">{$initial}</span>
            <span class="avatar-status avatar-sm-status avatar-status-{$status}"></span>
        </div>
    HTML;
    }


    public function sendPasswordResetNotification($token)
    {
        $this->mail($this, 'PASSWORD_RESET', $params = [
            'message' => '<a href="' . url('password/reset', $token) . '?email=' . $this->email . '" target="_blank">Click To Reset Password</a>'
        ]);
    }

    public function storeCurrency()
    {
        return $this->belongsTo(Currency::class, 'store_currency_id', 'id');
    }

    public function qrCurrency()
    {
        return $this->belongsTo(Currency::class, 'qr_currency_id', 'id');
    }


    public function getNameAttribute()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function getImage()
    {
        return getFile($this->image_driver, $this->image);
    }

    public function scopeFilterByState($query, $state)
    {
        return match ($state) {
            'active-users' => $query->whereStatus(1),
            'blocked-users' => $query->whereStatus(0),
            'email-unverified' => $query->where('email_verification', 0),
            'sms-unverified' => $query->where('sms_verification', 0),
            default => $query,
        };
    }

    public function qrLink(): string
    {
        return $this->qr_link ? route('public.qr.Payment', $this->qr_link) : 'javascript:void(0)';
    }

    public function getMobileAttribute()
    {
        return $this->phone_code .$this->phone;
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }



}
