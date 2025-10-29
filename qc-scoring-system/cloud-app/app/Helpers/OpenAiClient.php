<?php
namespace App\Helpers;
use OpenAI;
use App\Models\Account\User;
use App\Models\Analisis\AnalisisPrompt;
use Illuminate\Support\Facades\Storage;


class OpenAiClient
{
     public $client;
     public $complianceCriteriaJson;
     public $agentBehaviorsJson;
     public $complianceCriteriaDescription;
     public $agentBehaviorsDescription;
     public function __construct(
     ) {
          $prompt = AnalisisPrompt::find(7);
          $this->client = OpenAI::client(env('OPEN_AI_KEY'));

          $promptScoring = (new KontakamiAI(new User))->scoringToPrompt($prompt->scoring, false);
          $this->complianceCriteriaJson = $promptScoring->json;
          $this->complianceCriteriaDescription = $promptScoring->description;


          $promptNonScoring = (new KontakamiAI(new User))->scoringToPrompt($prompt->non_scorings);
          $this->agentBehaviorsJson = $promptNonScoring->json;
          $this->agentBehaviorsDescription = $promptNonScoring->description;
     }

     private function transcribe()
     {
          $transcribe = $this->client->audio()->transcribe([
               'model' => 'whisper-1',
               'file' => fopen(Storage::path('copy1.gsm.wav'), 'r'),
               'response_format' => 'verbose_json',
               'timestamp_granularities' => ['segment', 'word']
          ]);

          return $transcribe;
     }

     private function analysis($transcribeText)
     {

          $promptAnalysis = 'System Instruction: Anda adalah seorang analis skrip panggilan yang ahli. Tugas Anda adalah menganalisis skrip layanan pelanggan yang diberikan, menentukan apakah skrip tersebut memenuhi kriteria kepatuhan tertentu, mendeteksi perilaku agen tertentu, membuat ringkasan interaksi.
HANYA berikan respons dalam format objek JSON. Objek JSON HARUS memiliki struktur berikut:
{
     "scoring": ' . json_encode($this->complianceCriteriaJson) . ',
     "non_scoring": ' . json_encode($this->agentBehaviorsJson) . ',
     "summary": {
          "attitude" : "Ringkasan sikap agent & kualitas pelayanan berdasarkan poin-poin evaluasi.",
          "recomendation" : "Rekomendasi perbaikan yang dapat ditindaklanjuti berdasarkan analisis."
     }
}

Definisi "scoring":
' . json_encode($this->complianceCriteriaDescription) . '

Definisi "non_scoring":
' . json_encode($this->agentBehaviorsDescription) . '

Instruksi untuk "summary":
Buat ringkasan singkat (maksimal 3-4 kalimat) pertimbangkan hasil "scoring" dan "non_scoring.

Intruksi untuk "non_scoring" bagian "sentence"
Berisikan nilai string, apabila "detected" bernilai false maka berikan alasan

Anda HARUS menyertakan SEMUA kunci (scoring, non_scoring, summary) dalam respons JSON Anda.

Analisis skrip berikut:
---
' . $transcribeText . '
---
HANYA kembalikan objek JSON seperti yang dijelaskan di atas, pastikan structure format json sama persis dengan json struktur diatas.';
          $analisis = $this->client->chat()->create([
               'model' => 'gpt-4o',
               'messages' => [
                    ['role' => 'user', 'content' => $promptAnalysis],
               ],
          ]);

          return $analisis;
     }

     private function scoring($output)
     {
          $prompt = 'Anda adalah seorang analis, anda ditugaskan untuk menganalisa berdasarkan masukkan yang diberikan
HANYA berikan respons dalam format objek JSON. Objek JSON HARUS memiliki struktur berikut:
{
	"attitude" : "string",
	"recomendation" : "string"
}

     Definisi "attitude":
' . $output['summary']['attitude'] . '

     Definisi "recomendation":
' . $output['summary']['recomendation'] . '

     Instruksi untuk "attitude":
Berikan ringkasan satu hingga dua kalimat mengenai sikap dan kualitas pelayanan seorang agen berdasarkan masukan berikut. Hasilkan HANYA ringkasan tersebut, tanpa kalimat pengantar atau frasa tambahan seperti "Berikut ringkasan..."

     Instruksi untuk "recomendation":
Berikan beberapa poin rekomendasi perbaikan yang konkret dan dapat ditindaklanjuti berdasarkan masukan berikut. Hasilkan HANYA poin-poin rekomendasi dalam format daftar atau paragraf singkat, tanpa kalimat pengantar atau frasa tambahan seperti "Berikut adalah rekomendasinya..."

HANYA kembalikan objek JSON seperti yang dijelaskan di atas, pastikan structure format json sama persis dengan json struktur diatas.';

          $scoring = $this->client->chat()->create([
               'model' => 'gpt-4o',
               'messages' => [
                    ['role' => 'user', 'content' => $prompt],
               ],
          ]);

          return $scoring;
     }

     private function getOutputJson($jsonString)
     {
          $jsonString = preg_replace('/^```json\s*/', '', $jsonString);  // Remove ```json + any whitespace after it
          $jsonString = preg_replace('/\s*```$/', '', $jsonString);   // Remove closing ``` + any whitespace before it
          $data = json_decode($jsonString, true);
          return $data;
     }
     public function run()
     {

          $transcribe = $this->transcribe();
          $analysis = $this->analysis($transcribe->text);

          $outputanalysis = $this->getOutputJson($analysis->choices[0]->message->content);
          $scoring = $this->scoring($outputanalysis);
          $outputScoring = $this->getOutputJson($scoring->choices[0]->message->content);


          return [
               'duration_transkrip' => $transcribe->duration,
               'total_token_analysis' => $analysis->usage->totalTokens,
               'total_token_scoring' => $scoring->usage->totalTokens,
               'outputanalysis' => $outputanalysis,
               'outputScoring' => $outputScoring,
          ];
     }
}