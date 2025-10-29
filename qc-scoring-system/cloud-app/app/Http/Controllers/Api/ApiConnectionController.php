<?php
namespace App\Http\Controllers\Api;

use App\Traits\JsonResponse;


class ApiConnectionController
{
     use JsonResponse;
     public function index()
     {
          return $this->message('success');
     }
}