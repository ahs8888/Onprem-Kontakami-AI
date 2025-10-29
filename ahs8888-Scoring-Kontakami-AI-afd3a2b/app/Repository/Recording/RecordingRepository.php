<?php
namespace App\Repository\Recording;

use Illuminate\Http\Request;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\Recording\Recording;
use App\Models\Recording\RecordingFile;

class RecordingRepository extends BaseRepository
{
     public function __construct(
          public $model = Recording::class,
          public $file = RecordingFile::class,
     ) {

     }

     public function datatable(Request $request, $userID, $limit = null)
     {
          $query = $this->model::query()
               ->where('user_id', $userID)
               ->search(['folder'], $request->search)
               ->filter($request->filter, [
                    'date_start' => function ($query, $date) {
                         $query->where('created_at', '>=', "{$date} 00:00:00");
                    },
                    'date_end' => function ($query, $date) {
                         $query->where('created_at', '<=', "{$date} 23:59:59");
                    }
               ])
               ->latest('created_at');
          return $limit ? $query->paginate($limit) : $query->get();
     }

     public function datatableFiles(Request $request, $recordingId, $limit)
     {
          return $this->file::query()
               ->where('recording_id', $recordingId)
               ->search(['filename'], $request->search)
               ->select(['id', 'created_at', 'filename', 'transcribe'])
               ->paginate($limit);
     }

     public function findWhitFileConversation($uuid, $userId)
     {
          $recording = $this->findByUuid(
               $uuid,
               $userId,
               with: ['files:id,recording_id,created_at,filename,transcribe'],
               select: ['id', 'folder', 'total_file']
          );
          $recording->files = $recording->files->each(fn($row) => $row->append('conversations'));
          return $recording;
     }

     public function readById($id, $userID)
     {
          $this->model::query()
               ->where('user_id', $userID)
               ->where('id', $id)
               ->update([
                    'is_new' => false
               ]);
     }

     public function findAll($userId)
     {
          return $this->model::query()
               ->where('user_id', $userId)
               ->select(['id', 'folder'])
               ->get();
     }

     public function store(Request $request, $userId)
     {
          $recording = null;
          DB::transaction(function () use ($request, $userId, &$recording) {
               $now = now();
               $files = collect($request->get('files'));

               $recording = $this->model::create([
                    'user_id' => $userId,
                    'folder' => $request->folder,
                    'total_file' => $files->count(),
                    'total_token' => $files->sum('token')
               ]);

               $this->file::insert(
                    $files->map(fn($row) => [
                         'created_at' => $now,
                         'user_id' => $userId,
                         'recording_id' => $recording->id,
                         'filename' => $row['filename'],
                         'token' => $row['token'],
                         'size' => $row['size'],
                         'transcribe' => $row['transcribe'],
                    ])
                         ->toArray()
               );
          });

          return $recording;
     }

}