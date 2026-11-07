<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UserKyc extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'kyc_id', 'kyc_type', 'kyc_info', 'status', 'reason'];

    protected $casts = [
        'kyc_info' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kyc()
    {
        return $this->belongsTo(Kyc::class, 'kyc_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('userKYCRecord');
        });
    }




    public function getStatus($type = null)
    {
        if ($this->status == 0) {
            return !$type ?
                '<span class="badge bg-soft-warning text-warning">
                        <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                    </span>'
                : 'Pending' ;

        } elseif ($this->status == 1) {
            return !$type ?
                '<span class="badge bg-soft-success text-success">
                        <span class="legend-indicator bg-success"></span>' . trans('Verified') . '
                    </span>'
                : 'Verified';

        } else {
            return !$type ?
                '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Rejected') . '
                 </span>'
                : 'Rejected';
        }
    }

    public function kycInfoShow()
    {
        $kycInfo = [];
        foreach ($this->kyc_info as $info) {
            if ($info->type == 'file') {
                $kycInfo[] = [
                    'name' => stringToTitle($info->field_name),
                    'value' => getFile($info->field_driver, $info->field_value),
                    'type' => $info->type
                ];
            } else {
                $kycInfo[] = [
                    'name' => stringToTitle($info->field_name),
                    'value' => $info->field_value,
                    'type' => $info->type
                ];
            }
        }
        return $kycInfo;
    }

}
