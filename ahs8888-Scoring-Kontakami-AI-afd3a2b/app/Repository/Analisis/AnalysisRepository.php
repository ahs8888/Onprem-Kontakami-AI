<?php
namespace App\Repository\Analisis;

use App\Models\Analisis\AnalysisScoring;
use Illuminate\Http\Request;
use App\Models\Analisis\Analysis;
use App\Repository\BaseRepository;

class AnalysisRepository extends BaseRepository
{
     public function __construct(
          public $model = Analysis::class,
          public $scoring = AnalysisScoring::class,
     ) {
     }
     public function datatable(Request $request, $userID, $limit = null)
     {
          $search = $request->search;
          $query = $this->model::query()
               ->where('user_id', $userID)
               ->whereAny(['foldername', 'prompt_name', 'name'], 'LIKE', "%{$search}%")
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
               ->select(['id', 'created_at', 'foldername', 'prompt_name', 'total_file', 'is_new', 'status', 'name'])
               ->latest();
          return $limit ? $query->paginate($limit) : $query->get();
     }

     public function datatableItem(Request $request, $analisysId, $limit)
     {
          return $this->scoring::query()
               ->search(['filename'], $request->search)
               ->where('analysis_id', $analisysId)
               ->filter($request->filter, [
                    'score' => function ($query, $score) {
                         $query->when($score == '50', fn($q) => $q->where('avg_score', '<=', 50));
                         $query->when($score == '75', fn($q) => $q->where('avg_score', '<=', 75));
                         $query->when($score == '100', fn($q) => $q->where('avg_score', '<=', 100));
                    }
               ])
               ->select(['id', 'created_at', 'filename', 'analysis_id', 'transcribe', 'is_done', 'avg_score'])
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

     public function findWhitFileConversation($uuid, $userId)
     {
          $score = @request('filter')['score'];
          $recording = $this->findByUuid(
               $uuid,
               $userId,
               with: [
                    'scorings' => function ($query) use ($score) {
                         $query->select(['id', 'created_at', 'filename', 'analysis_id', 'transcribe', 'is_done', 'avg_score']);
                         $query->when($score == '50', fn($q) => $q->where('avg_score', '<=', 50));
                         $query->when($score == '75', fn($q) => $q->where('avg_score', '<=', 75));
                         $query->when($score == '100', fn($q) => $q->where('avg_score', '<=', 100));
                    }
               ],
               select: ['id', 'foldername', 'prompt_name', 'total_file', 'status']
          );
          $recording->scorings = $recording->scorings->each(fn($row) => $row->append('conversations'));
          $recording->uuid = $uuid;
          return $recording;
     }

     public function findScoring($analysisUuid, $scoringUuid, $userId)
     {
          return $this->scoring::query()
               ->where('user_id', $userId)
               ->where('analysis_id', id_from_uuid($analysisUuid))
               ->where('id', id_from_uuid($scoringUuid))
               ->select(['id', 'filename', 'file_size', 'scoring', 'avg_score', 'non_scoring', 'summary', 'transcribe','sentiment','version'])
               ->first()
                    ?->append('conversations');
     }

}