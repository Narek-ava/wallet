<?php

namespace App\Models\Cabinet;

use Illuminate\Database\Eloquent\Model;

class CUserTemporaryRegisterData extends Model
{
    protected $fillable = [
        'account_type',
        'email',
        'phone',
        'password_encrypted',
        'last_notified_at',
        'notifications_count',
    ];
}
