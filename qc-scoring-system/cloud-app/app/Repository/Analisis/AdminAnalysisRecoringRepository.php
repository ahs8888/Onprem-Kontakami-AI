<?php
namespace App\Repository\Analisis;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminAnalysisRecoringRepository
{
     public function datatable(Request $request, $limit = null)
     {
          /**
           * convert this query to  laravel eloquent or query builder
           * select u.name,u.company,r.*
           * from users u
           * join (
           * select id,user_id,created_at,folder,total_file as data,total_token as token,'vtt' as type from recordings
           * UNION
           * select id,user_id,created_at,foldername as folder,total_file as data,total_token as token,'rs' as type from analysis
           * UNION
           * select id,user_id,created_at,foldername as folder,total_data as data,total_token as token,'as' as type from agent_scorings
           * ) as r on r.user_id=u.id
           */
          $queryRecording = DB::table('recordings')
               ->select(['id', 'user_id', 'created_at', 'folder', 'total_file as data', 'total_token as token', DB::raw("'vtt' as type")]);

          $queryAnalysis = DB::table('analysis')
               ->select(['id', 'user_id', 'created_at', 'foldername as folder', 'total_file as data', 'total_token as token', DB::raw("'rs' as type")]);

          $queryAgentScoring = DB::table('agent_scorings')
               ->select(['id', 'user_id', 'created_at', 'foldername as folder', 'total_data as data', 'total_token as token', DB::raw("'as' as type")]);

          $unionQuery = $queryRecording->unionAll($queryAnalysis)->unionAll($queryAgentScoring);

          $query = DB::table(DB::raw("({$unionQuery->toSql()}) as r"))
               ->mergeBindings($unionQuery)
               ->join('users as u', 'r.user_id', '=', 'u.id')
               ->when($request->search, function ($query, $search) {
                    $query->whereAny([
                         'u.name',
                         'u.company',
                         'r.folder',
                         'u.email'
                    ], "LIKE", "%$search%");
               })
               ->when($request->filter, function ($query, $filter) {
                    $query->when(@$filter['date_start'], fn($query, $date) => $query->where('r.created_at', '>=', "{$date} 00:00:00"));
                    $query->when(@$filter['date_end'], fn($query, $date) => $query->where('r.created_at', '<=', "{$date} 23:59:59"));
                    $query->when(@$filter['types'], fn($query, $type) => $query->whereIn('r.type', explode(',',$type)));
               })
               ->select(['u.name', 'u.company', 'u.email', 'r.*'])
               ->latest('r.created_at');
          return $limit ? $query->paginate($limit) : $query->get();
     }
}