<?php
namespace App\Repository\Account;

use App\Enum\UserStatus;
use App\Models\Account\User;

class UserRepository
{
     public function __construct(
          public $model = User::class
     ) {
     }

     public function findAllCompany()
     {
          return $this->model::where('status', UserStatus::Active)->select(['id','name','company'])->get();
     }

}