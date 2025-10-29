<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Query\Builder;

trait HasDatatable
{

     public function scopeSearch(Builder $query, $columns,$search = null)
     {
          $operator = config('database.default') == 'pgsql' ? 'ILIKE' : 'LIKE';
          return $query->when($search, function ($query, $search) use ($columns, $operator) {
               $query->whereAny($columns, $operator, "%$search%");
          });
     }

     public function scopeFilter(Builder $query, $filters,$scopes)
     {
          if ($filters = request('filter')) {
               foreach ($filters as $key => $filter) {
                    $scope = @$scopes[$key];
                    if ($scope && !is_null($filter)) {
                         $scope($query, $filter);
                    }
               }
          }
     }
}