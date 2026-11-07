<?php

namespace App\Models;

use App\Traits\Notify;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, Notify;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'image',
        'image_driver',
        'phone',
        'address',
        'admin_access',
        'last_login',
        'status',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token'
    ];

    public function fireBaseToken()
    {
        return $this->morphMany(FireBaseToken::class, 'tokenable');
    }

    public function inAppNotification()
    {
        return $this->morphOne(InAppNotification::class, 'inAppNotificationable', 'in_app_notificationable_type', 'in_app_notificationable_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->mail($this, 'PASSWORD_RESET', $params = [
            'message' => '<a href="' . url('admin/password/reset', $token) . '?email=' . $this->email . '" target="_blank">Click To Reset Password</a>'
        ]);
    }

    public function profilePicture()
    {
        return getFile($this->image_driver, $this->image);
    }

    public function avatarImage(): string
    {
        $status = $this->LastSeenActivity ? 'success' : 'warning';
        if ($this->image) {
            $url = getFile($this->image_driver, $this->image);
            return <<<HTML
            <div class="avatar avatar-sm avatar-circle">
                <img class="avatar-img" src="{$url}" alt="...">
                <span class="avatar-status avatar-sm-status avatar-status-{$status}"></span>
            </div>
        HTML;
        }

        $initial = mb_check_encoding($this->name, 'UTF-8') ? mb_substr(trim($this->name), 0, 1) : '';
        return <<<HTML
        <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
            <span class="avatar-initials">{$initial}</span>
            <span class="avatar-status avatar-sm-status avatar-status-{$status}"></span>
        </div>
    HTML;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function activeRole()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')->where('status', 1);
    }


}
