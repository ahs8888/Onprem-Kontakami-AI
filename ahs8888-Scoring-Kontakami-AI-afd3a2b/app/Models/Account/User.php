<?php

namespace App\Models\Account;

use App\Enum\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';


    protected $hidden = ['password'];

    protected $fileField = ['profile'];

    protected $casts = [
        'status' => UserStatus::class,
    ];
}
