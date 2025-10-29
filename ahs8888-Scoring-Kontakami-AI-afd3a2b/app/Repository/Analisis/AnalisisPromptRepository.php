<?php
namespace App\Repository\Analisis;

use Illuminate\Http\Request;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\Analisis\AnalisisPrompt;

class AnalisisPromptRepository extends BaseRepository
{
     public function __construct(
          public $model = AnalisisPrompt::class
     ) {
     }
     public function datatable(Request $request, $userID, $type, $limit, $isAdmin = false)
     {
          $search = $request->search;
          return $this->model::query()
               ->leftJoin(
                    DB::raw(
                         "(SELECT DISTINCT JSON_EXTRACT(data, '$.prompt_id') as prompt_id
            FROM process_logs 
            WHERE status = 'progress' AND type = 'recording-scoring') as u"
                    ),
                    'u.prompt_id',
                    '=',
                    'analisis_prompts.id'
               )
               ->join('users', 'users.id', 'analisis_prompts.user_id')
               ->filter($request->filter, [
                    'date_start' => function ($query, $date) {
                         $query->where('analisis_prompts.created_at', '>=', "{$date} 00:00:00");
                    },
                    'date_end' => function ($query, $date) {
                         $query->where('analisis_prompts.created_at', '<=', "{$date} 23:59:59");
                    }
               ])
               ->when(!$isAdmin, fn($query) => $query->where('analisis_prompts.user_id', $userID))
               ->when($isAdmin, fn($query) => $query->where('analisis_prompts.by_admin', true))
               ->whereIn('analisis_prompts.type', $type)
               ->whereAny(['analisis_prompts.name', 'analisis_prompts.type', 'users.company'], 'LIKE', "%{$search}%")
               ->select(['analisis_prompts.*', 'u.prompt_id as in_use', 'users.company'])
               ->latest('analisis_prompts.created_at')
               ->paginate($limit ?: 10);
     }

     public function store(Request $request, $type, $userID, $isAdmin = false)
     {
          $id = id_from_uuid($request->uuid);
          $this->model::updateOrCreate([
               'id' => $id,
               'user_id' => $userID,
          ], [
               'type' => $type,
               'name' => $request->name,
               'scorings' => $request->scorings,
               'non_scorings' => $request->non_scorings ?: [],
               'summaries' => $request->summary,
               'version' => 2,
               'by_admin' => $isAdmin
          ]);
     }

     public function findAll($userId, $types)
     {
          return $this->model::query()
               ->where('user_id', $userId)
               ->whereIn('type', $types)
               ->select(['id', 'name'])
               ->latest()
               ->get();
     }

     public function findFirstByUuid($uuid)
     {
          $find = $this->model::query()
               ->where('id', id_from_uuid($uuid))
               ->firstOrFail();
          $find->uuid = $uuid;
          return $find;
     }
}