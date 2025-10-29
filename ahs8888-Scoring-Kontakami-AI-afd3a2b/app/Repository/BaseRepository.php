<?php
namespace App\Repository;

class BaseRepository
{
     public $model;
     public function findByUuid($uuid, $userId, $with = [], $select = ['*'])
     {
          $find = $this->model::query()
               ->where('user_id', $userId)
               ->where('id', id_from_uuid($uuid))
               ->when($with, fn($query) => $query->with($with))
               ->select($select)
               ->firstOrFail();
          $find->uuid = $uuid;
          return $find;
     }

     public function findById($id, $userId, $with = [], $select = ['*'])
     {
          $find = $this->model::query()
               ->where('user_id', $userId)
               ->where('id', $id)
               ->when($with, fn($query) => $query->with($with))
               ->select($select)
               ->firstOrFail();
          return $find;
     }
     public function deleteByUuid($uuid, $userId)
     {
          $this->model::query()
               ->where('user_id', $userId)
               ->where('id', id_from_uuid($uuid))
               ->delete();
     }
}