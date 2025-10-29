<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';

    protected $hidden = ['password'];
}
