<?php
namespace App\Helpers;

use App\Models\Account\User;
use App\Models\Util\RequestAiLog;
use Illuminate\Support\Facades\Http;

class KontakamiAI
{
     public $apikey;
     public $apiUrl;
     public $complianceCriteriaJson;
     public $complianceCriteriaDescription;
     public $agentBehaviorsJson;
     public $agentBehaviorsDescription;
     public $version = 1;
     public $scoringParameter;
     public $nonScoringParameter;
     public $summaryParameter;

     public function __construct(
          public User $user
     ) {
          $model = config('services.AI.model');
          $this->apikey = config('services.AI.key');
          $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
     }

     public function version($version)
     {
          $this->version = $version;
     }
     public function scoring($scoring)
     {
          $this->scoringParameter = $this->parameterToPrompt($scoring);
          return $this;
     }


     public function nonScoring($nonScoring)
     {
          $this->nonScoringParameter = $this->parameterToPrompt($nonScoring, true);

          return $this;
     }


     public function summary($summary)
     {
          if ($this->version == 1) {
               $summary = [
                    [
                         'point' => @$summary['attitude']['name'],
                         'prompt' => @$summary['attitude']['prompt'],
                    ],
                    [
                         'point' => @$summary['recomendation']['name'],
                         'prompt' => @$summary['recomendation']['prompt'],
                    ]
               ];
          }
          $this->summaryParameter = collect($summary)->mapWithKeys(function ($prompt) {
               return [
                    str()->slug($prompt['point'],'_') => [
                         'type' => 'STRING',
                         'description' => $prompt['prompt'] .' Gunakan kutipan TANPA tanda petik dua. Jabarkan hasil secara jelas dan detail.',
                    ]
               ];
          })->toArray();
          return $this;
     }

     public function analysisV2($transcribe)
     {
          $userPrompt = 'Transkrip untuk dianalisis:
---
' . $transcribe . '
---
Mohon berikan analisis berdasarkan skema.';

          $systemInstruction = 'Anda adalah seorang analis ahli untuk panggilan layanan pelanggan.
Analisis transkrip berikut berdasarkan parameter dan prompt ringkasan yang diberikan.
Sajikan analisis Anda dalam format JSON terstruktur yang sesuai dengan skema yang disediakan.
1.  **sentiment**: Tentukan sentimen keseluruhan dari interaksi.
2.  **scoring**: Evaluasi setiap parameter yang diberikan berikan skor.
3.  **summaries**: Buat ringkasan yang diminta. Gunakan kutipan tanda petik, gunakan dalam kurung. Jabarkan hasil secara jelas dan detail.';
          if (count($this->nonScoringParameter)) {
               $systemInstruction .= '4.  **non_scoring**: Evaluasi setiap parameter yang diberikan berikan nilai true/false.';
          }
          $systemInstruction .= 'Semua output harus dalam Bahasa Indonesia.';


          $responseSchema = [
               'type' => 'OBJECT',
               'properties' => [
                    'sentiment' => [
                         'type' => 'STRING',
                         'description' => 'Sentimen keseluruhan dari kontak, dari 1 sampai 5: Sangat Positif, Positif, Netral, Negatif, Sangat Negatif.',
                         'enum' => [
                              "Sangat Negatif",
                              "Negatif",
                              "Netral",
                              "Positif",
                              "Sangat Positif"
                         ]
                    ],
                    'scoring' => [
                         'type' => 'OBJECT',
                         'properties' => $this->scoringParameter
                    ],
                    'summaries' => [
                         'type' => 'OBJECT',
                         'properties' => $this->summaryParameter
                    ]
               ],
               'required' => [
                    'sentiment',
                    'scoring',
                    'summaries'
               ]
          ];
          if (count($this->nonScoringParameter)) {
               $responseSchema['properties']['non_scoring'] = [
                    'type' => 'OBJECT',
                    'properties' => count($this->nonScoringParameter) ? $this->nonScoringParameter : (object) []
               ];
               $responseSchema['required'] = [
                    ...$responseSchema['required'],
                    'non_scoring'
               ];
          }



          $response = $this->sendRequest($userPrompt, $systemInstruction, $responseSchema, 'Recording Analysis');
          if (!$response) {
               return null;
          }
          $responseText = @$response['candidates'][0]['content']['parts'][0]['text'];
          $jsonString = str_replace(["\r", "\n"], '', $responseText);
          return [
               'output' => json_decode($jsonString),
               'token' => @$response['usageMetadata']['totalTokenCount'] ?: 0,
               'request_ai_id' => $response['request_ai_id']
          ];
     }

     public function agentScoringV2($props){
          $summary = collect($props['items'])->mapWithKeys(function ($item) {
               return [
                    $item['key'] => [
                         'type' => 'STRING',
                         'description' => 'Rangkum ringkasan dari .'.$item['result'] ,
                    ]
               ];
          })->toArray();
          $userPrompt = 'Mohon berikan analisis dalam satu ringkasan dari hasil gabungan summary berdasarkan skema.';

          $systemInstruction = 'Anda adalah seorang analis ahli untuk panggilan layanan pelanggan.
Analisis data berdasarkan parameter dan prompt ringkasan yang diberikan.
Sajikan analisis Anda dalam format JSON terstruktur yang sesuai dengan skema yang disediakan.
1.  **sentiment**: Tentukan rata rata sentimen keseluruhan dari summary interaksi.
2.  **summaries**: Buat satu ringkasan dari gabungan beberapa ringkasan skenario. Gunakan kutipan tanda petik, gunakan dalam kurung. Jabarkan hasil secara jelas dan detail.
Semua output harus dalam Bahasa Indonesia.';


          $responseSchema = [
               'type' => 'OBJECT',
               'properties' => [
                    'sentiment' => [
                         'type' => 'STRING',
                         'description' => 'Sentiman keseluruhan dari rangkum sentimen, '.implode(',',$props['sentiment']),
                         'enum' => [
                              "Sangat Negatif",
                              "Negatif",
                              "Netral",
                              "Positif",
                              "Sangat Positif"
                         ]
                    ],
                    'summaries' => [
                         'type' => 'OBJECT',
                         'properties' => $summary
                    ]
               ],
               'required' => [
                    'sentiment',
                    'summaries'
               ]
          ];

          $response = $this->sendRequest($userPrompt, $systemInstruction, $responseSchema, 'Recording Analysis');
          if (!$response) {
               return null;
          }
          $responseText = @$response['candidates'][0]['content']['parts'][0]['text'];
          $jsonString = str_replace(["\r", "\n"], '', $responseText);
          return [
               'output' => json_decode($jsonString),
               'token' => @$response['usageMetadata']['totalTokenCount'] ?: 0,
               'request_ai_id' => $response['request_ai_id']
          ];
     }

     /**
      * @deprecated using v2
      */
     public function analysis($transcribe)
     {
          $prompt = 'System Instruction:
Anda adalah seorang analis skrip panggilan yang sangat ahli.
Tugas Anda adalah:
- Menganalisis skrip layanan pelanggan yang diberikan.
- Menilai apakah skrip tersebut memenuhi kriteria kepatuhan tertentu (scoring).
- Mendeteksi perilaku-perilaku spesifik dari agen (non_scoring).
- Menyusun ringkasan mendetail pada bagian summary, dengan membedakan hasil scoring vs non_scoring.

Hanya berikan respons berupa satu objek JSON dengan struktur PERSIS seperti berikut:
{"scoring": ' . json_encode($this->complianceCriteriaJson) . ',"non_scoring": ' . json_encode($this->agentBehaviorsJson) . ',"summary": {"attitude": "Gunakan kutipan TANPA tanda petik dua. Jabarkan hasil secara jelas dan detail.","recomendation": "Gunakan kutipan TANPA tanda petik dua. Jabarkan hasil secara jelas dan detail."}}

Definisi "scoring":
' . json_encode($this->complianceCriteriaDescription) . '

Definisi "non_scoring":
' . json_encode($this->agentBehaviorsDescription) . '


Petunjuk "summary":
- Gunakan hasil scoring dan non_scoring sebagai data tambahan.
- Jabarkan perbandingan hasil "scoring” dan “non_scoring” secara jelas.
- Jangan pernah menggunakan tanda petik dua saat mengambil kutipan.
- Jangan tambahkan penjelasan, disclaimer, atau hasil lain di luar struktur JSON tersebut.

Petunjuk "summary" bagian "attitude"
' . json_encode($this->summary['attitude']) . '

Petunjuk "summary" bagian "recomendation"
' . json_encode($this->summary['recomendation']) . '

Petunjuk pengisian "non_scoring" bagian sentence:
- Berisi nilai string.
- Jika detected bernilai false, berikan alasan secara eksplisit TANPA tanda petik dua.

Instruksi tambahan:
- JANGAN menampilkan atau menulis apapun DI LUAR objek JSON di atas.
- Pastikan SEMUA kunci (scoring, non_scoring, summary) disertakan.
- Pastikan format JSON valid, struktur dan urutan sama persis dengan contoh di atas.
- Jangan menambah atau mengurangi field (misalnya menambah "recomendation" atau mengubah "scoring" menjadi object).


Skrip yang harus dianalisis:
---
' . $transcribe . '
---

HANYA kembalikan satu objek JSON VALID dan bisa di lakukan decode sesuai struktur di atas, tanpa tambahan apapun.';

          // $response = $this->sendRequest($prompt, 'Recording Analysis');
          // if (!$response) {
          //      return null;
          // }
          // $responseText = @$response['candidates'][0]['content']['parts'][0]['text'];
          // $jsonString = str_replace(["\r", "\n"], '', $responseText);
          // return [
          //      'output' => json_decode($jsonString),
          //      'token' => @$response['usageMetadata']['totalTokenCount'] ?: 0,
          //      'request_ai_id' => $response['request_ai_id']
          // ];
     }

       /**
      * @deprecated using v2
      */
     public function agentScoringSummary($summary)
     {
          $prompt = 'Anda adalah seorang analis, anda ditugaskan untuk menganalisa berdasarkan masukkan yang diberikan
HANYA berikan respons dalam format objek JSON. Objek JSON HARUS memiliki struktur berikut:
{
     "one" : "string",
     "two" : "string"
}

Definisi "one":
' . json_encode($summary['attitude']) . '. Gunakan kutipan TANPA tanda petik dua atau ubah kutipan tanda petik menjadi (...)


Definisi "two":
' . json_encode($summary['recomendation']) . ' Gunakan kutipan TANPA tanda petik dua atau ubah kutipan tanda petik menjadi (...)

Instruksi untuk "one":
' . json_encode($summary['attitude_prompt']) . ' . Gunakan kutipan TANPA tanda petik dua. Jabarkan hasil secara jelas dan detail.


Instruksi untuk "two":
' . json_encode($summary['recomendation_prompt']) . ' . Gunakan kutipan TANPA tanda petik dua. Jabarkan hasil secara jelas dan detail.

Petunjuk
Jangan pernah menggunakan tanda petik dua saat mengambil kutipan.
Jangan tambahkan penjelasan, disclaimer, atau hasil lain di luar struktur JSON tersebut.';


          // $response = $this->sendRequest($prompt, 'Agent Analysis');
          // if (!$response) {
          //      return null;
          // }
          // $responseText = @$response['candidates'][0]['content']['parts'][0]['text'];
          // $jsonString = str_replace(["\r", "\n"], '', $responseText);
          // return [
          //      'output' => json_decode($jsonString),
          //      'token' => @$response['usageMetadata']['totalTokenCount'] ?: 0,
          //      'request_ai_id' => $response['request_ai_id']
          // ];
     }
     public function profiling()
     {

     }

     private function sendRequest($userPrompt, $systemInstruction, $responseSchema, $type)
     {
          $response = Http::timeout(0)
               ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $this->apikey,
               ])->post($this->apiUrl, [
                         'systemInstruction' => [
                              'parts' => [
                                   'text' => $systemInstruction
                              ]
                         ],
                         'contents' => [
                              [
                                   'parts' => [
                                        ['text' => $userPrompt]
                                   ]
                              ]
                         ],
                         'generationConfig' => [
                              'responseMimeType' => 'application/json',
                              'responseSchema' => $responseSchema,
                         ]
                    ]);

          $requestLog = RequestAiLog::create([
               'user_id' => $this->user->id,
               'user_name' => $this->user->name,
               'type' => $type,
               'prompt' => $userPrompt,
               'prompt_v2' => [
                    'tools' => [
                         [
                              'function_declaration' => $responseSchema
                         ]
                    ],
                    'system_instruction' => $systemInstruction,
                    'user_prompt' => $userPrompt
               ],
               'output' => $response->json() ?: []
          ]);
          if ($response->successful()) {
               return [
                    ...$response->json(),
                    'request_ai_id' => $requestLog->id
               ];
          }
          return null;
     }

     public function parameterToPrompt($items, $nonScoring = false)
     {
          $description = collect($items)->mapWithKeys(function ($prompt) use ($nonScoring) {
               return [
                    str()->slug($prompt['name'], '_') => [
                         'type' => 'OBJECT',
                         'properties' => collect($prompt['items'])->mapWithKeys(function ($item) use ($nonScoring) {
                              $maxScore = intval(@$item['score_max']);
                              $minScore = intval($item['score']);
                              $score = $maxScore ? "{$minScore}-{$maxScore}" : "1-{$minScore}";
                              if ($minScore > $maxScore) {
                                   $score = "{$maxScore}-{$minScore}";
                              }

                              $properties = $nonScoring
                                   ? [
                                        'value' => [
                                             'type' => 'BOOLEAN',
                                             'description' => 'benar atau salah'
                                        ]
                                   ]
                                   : [
                                        'score' => [
                                             'type' => 'NUMBER',
                                             'description' => "skor berdasarkan format: {$score}"
                                        ]
                                   ];
                              return [
                                   str()->slug($item['point'], '_') => [
                                        'type' => 'OBJECT',
                                        'description' => $item['prompt'],
                                        'properties' => $properties,
                                        'required' => [
                                             $nonScoring ? 'value' : 'score'
                                        ]
                                   ]
                              ];
                         })->toArray()
                    ]
               ];
          })->toArray();


          return $description;
     }
}