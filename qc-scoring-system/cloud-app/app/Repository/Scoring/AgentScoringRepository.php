<?php
namespace App\Repository\Scoring;

use Illuminate\Http\Request;
use App\Repository\BaseRepository;
use App\Models\Analisis\AgentScoring;
use App\Models\Analisis\AgentScoringItem;

class AgentScoringRepository extends BaseRepository
{
     public function __construct(
          public $model = AgentScoring::class,
          public $item = AgentScoringItem::class,
     ) {
     }

     public function datatable(Request $request, $userID, $limit = null)
     {
          $search = $request->search;
          $query = $this->model::query()
               ->where('user_id', $userID)
               ->whereAny(['foldername', 'analysis_name'], 'LIKE', "%{$search}%")
               ->filter($request->filter, [
                    'date_start' => function ($query, $date) {
                         $query->where('created_at', '>=', "{$date} 00:00:00");
                    },
                    'date_end' => function ($query, $date) {
                         $query->where('created_at', '<=', "{$date} 23:59:59");
                    },
                    'status' => function ($query, $status) {
                         $status = explode(',', $status);
                         if (!in_array('all', $status)) {
                              $query->whereIn('status', $status);
                         }
                    }
               ])
               ->select(['id', 'created_at', 'foldername', 'analysis_name', 'total_data', 'is_new', 'status'])
               ->latest();
          return $limit ? $query->paginate($limit) : $query->get();
     }

     public function datatableItem(Request $request, $scoringId, $limit)
     {
          return $this->item::query()
               ->where('agent_scoring_id', $scoringId)
               ->search(['spv', 'agent'],$request->search)
               ->select(['id', 'created_at', 'agent', 'spv', 'total_file', 'is_done', 'agent_scoring_id', 'avg_score','files'])
               ->filter($request->filter, [
                    'score' => function ($query, $score) {
                         $query->when($score == '50', fn($q) => $q->where('avg_score', '<=', 50));
                         $query->when($score == '75', fn($q) => $q->where('avg_score', '<=', 75));
                         $query->when($score == '100', fn($q) => $q->where('avg_score', '<=', 100));
                    }
               ])
               ->paginate($limit);
     }

     public function readByUuid($uuid, $userID)
     {
          $this->model::query()
               ->where('user_id', $userID)
               ->where('id', id_from_uuid($uuid))
               ->update([
                    'is_new' => false
               ]);
     }

     public function findScoring($scoringUuid, $itemUuid, $userId)
     {
          return $this->item::query()
               ->where('user_id', $userId)
               ->where('agent_scoring_id', id_from_uuid($scoringUuid))
               ->where('id', id_from_uuid($itemUuid))
               ->select(['id', 'agent', 'spv', 'scoring', 'avg_score', 'total_file', 'summary', 'files', 'version','sentiment','created_at'])
               ->first();
     }
}