<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoFactorSetting extends Model
{
	use HasFactory;

	protected $fillable = ['user_id'];

	public function securityQuestion()
	{
		return $this->belongsTo(SecurityQuestion::class, 'security_question_id', 'id');
	}
}
