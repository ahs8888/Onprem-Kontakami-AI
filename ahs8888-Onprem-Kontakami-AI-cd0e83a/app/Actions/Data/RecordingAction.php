<?php

namespace App\Actions\Data;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\RetryRecording;
use App\Models\Data\Setting;
use Illuminate\Http\Request;
use App\Models\Data\Recording;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessRecordingBatch;
use App\Jobs\UpdateRecordingStatus;
use App\Jobs\ProcessRecordingDetail;
use App\Models\Data\RecordingDetail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RecordingAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function read($id)
    {
        Recording::where("id", $id)->update([
            'is_read' => 1
        ]);
    }

    public function storeFolder(Request $request)
    {
        $recordingId = null;
        $injectId = $request->input('injectId');
        $requiresTicket = $request->input('requiresTicket', true); // Default to true

        DB::transaction(function () use ($request, &$recordingId, $injectId, $requiresTicket) {
            $token = Setting::where("key", "token")->first();
            $folderName = trim($request->input('folderName'));
            $files = $request->file('files');
            $paths = $request->input('relativePaths');

            if (!$injectId) {
                $originalName = $folderName;
                $counter = 1;
                while (Recording::where('folder_name', $folderName)->exists()) {
                    $folderName = $originalName . " ({$counter})";
                    $counter++;
                }

                $recording = Recording::create([
                    'folder_name' => $folderName,
                    'token' => $token?->value,
                    'injected_at' => Carbon::now()
                ]);
            } else {
                $recording = Recording::where("id", $injectId)->first();
                if (!$recording) {
                    throw new \Exception("Recording not found!");
                }

                if ($recording->status == 'Progress') {
                    throw new \Exception("Recording still in progress!");
                }

                $recording->update([
                    'injected_at' => Carbon::now()
                ]);
            }

            $result = [];

            $existDetails = RecordingDetail::where('recording_id', $recording->id)->count();

            foreach ($files as $index => $file) {
                $relativePath = $paths[$index] ?? '';
                $fileName = trim($file->getClientOriginalName());

                $continue = false;
                if ($injectId) {
                    $checkFile = RecordingDetail::where("name", $fileName)->where('recording_id', $recording->id)->first();
                    if (!$checkFile) $continue = true;
                } else {
                    $continue = true;
                }

                if ($continue) {
                    $fileNameStore = uniqid() . '.' . $file->getClientOriginalExtension();

                    $storedPath = $file->storeAs('uploads/' . $folderName, $fileNameStore, 'public');
                    $urlFile = \Storage::url($storedPath);

                    $upload = RecordingDetail::create([
                        'name' => $fileName,
                        'file' => $urlFile,
                        'recording_id' => $recording->id,
                        'sort' => $index + 1 + $existDetails,
                        'requires_ticket' => $requiresTicket,
                        'status' => $requiresTicket ? 'unlinked' : 'no_ticket_needed'
                    ]);
                }

            }

            $recordingId = $recording->id;
        });

        if ($recordingId) {
            UpdateRecordingStatus::dispatch($recordingId, "Progress");
            if (!$injectId) {
                ProcessRecordingBatch::dispatch($recordingId);
            } else {
                ProcessRecordingBatch::dispatch($recordingId, $injectId);
            }

            return $recordingId;
        }
    }


    public function deleteFolder(Request $request)
    {
        DB::transaction(function () use ($request) {
            $token = Setting::where("key", "token")->value("value");
            $recording = Recording::where("id", $request->id)->first();
            if ($token && $recording->clouds_uuid) {
                Http::withToken($token)->delete(config("services.clouds_url")."/api/external/v1/recording/".$recording->clouds_uuid);
            }

            Recording::where("id", $request->id)->delete();
        });
    }

    public function transcribeAudioGemini($storedPath)
    {
        $geminiKey = config("services.gemini_key");
        $modelApi = "gemini-2.5-flash";
        $audioContent = Storage::disk('public')->get($storedPath);
        if ($audioContent === false) {
            logger("Failed to fetch audio from URL.");
            return null;
        }

        if (Str::endsWith($storedPath, '.gsm')) {
            $mimeType = 'audio/x-gsm';
        } else {
            $mimeType = Storage::disk('public')->mimeType($storedPath) ?: 'application/octet-stream';
        }


        $data = base64_encode($audioContent);
        // $sizeInBytes = Storage::disk('public')->size($storedPath);
        // $sizeInMB = $sizeInBytes / 1024 / 1024;

        // if ($sizeInMB > 40) { // ~41MB base64 string
        //     return (object) [
        //         'json' => "File too large for inlineData (keep under ~40MB base64).",
        //         'status' => 'failed'
        //     ];
        // }

        $response = Http::timeout(520)->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $geminiKey,
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelApi}:generateContent", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                // 'text' => 'Transcribe this audio. Focus on accuracy and provide only the plain text of the transcription. Do not add any commentary or formatting other than the transcribed speech. Change the output to a conversational dialogue between "Agent" and "Customer" Each spoken line should begin with "Agent: " or "Customer: ". If the speaker cannot be identified exactly for a line, use "Unknown: ". Keep as much of the original script content as possible. Example "Agent: Good morning, how can I help you?\\nCustomer: Yes, I would like to ask about product X.\\nAgent: Sure, product X has benefits..."'
                                'text' => '
                                Transkripsikan file audio percakapan dua orang terlampir secara detail. Untuk setiap segmen ucapan, identifikasi pembicaranya (misalnya: Agent, Customer). Pada saat identifikasi pembicara terkait recording collection, tolong hasil transkripsi dipastikan secara benar sesuai rolenya, begitu juga untuk recording yang tellermarketing jgn sampai tertukat, karena recording collection saat ini hasilnya banyak yang tertukar antara role customer dan role agent, kalau bisa menentukan role nya mana saja itu coba di baca dulu semua isi percakapan baru setelah itu ditentukan mana role customer mana role agent. Dan jika ada suara pembicaraan yang timpang tindih tolong dipastikan jangan sampai tertukar role nya
                                Selain transkripsi, lakukan analisis sentimen/emosi dan durasi(timestamp) pembicaraan untuk setiap segmen ucapan. Sentimen/emosi ini harus diidentifikasi berdasarkan kombinasi dari konten verbal (kata-kata yang diucapkan) dan karakteristik akustik suara (nada, intonasi, volume, kecepatan bicara).
                                Klasifikasikan sentimen/emosi ke dalam kategori yang jelas seperti:
                                * Senang
                                * Sedih
                                * Marah
                                * Terkejut
                                * Jijik
                                * Takut
                                * Normal
                                * Kecewa
                                * Cemas

                                Jangan tambahkan komentar atau kalimat pembuka selain selain transkripsi pidato.

                                Format output dalam bentuk daftar kronologis, menampilkan:
                                -   Identifikasi Pembicara
                                -   Teks transkripsi
                                -   Sentimen/emosi yang teridentifikasi

                                Contoh format output yang diharapkan:

                                Agent: "Wah, saya sangat gembira hari ini!" [Sentimen: Senang]
                                Customer: [00:00]“Oh, benarkah? Saya justru merasa sedikit lesu." [Sentimen: Sedih]
                                Agent: [00:00]“Kenapa begitu? Ada apa?" [Sentimen: Netral / Khawatir]
                                '
                            ],
                            [
                                "inlineData" => [
                                    "mimeType" => $mimeType,
                                    "data" => $data,
                                ]
                            ]
                        ],
                    ],
                ],
            ]);

        if ($response->successful()) {
            return (object) [
                'json' => $response->json(),
                'status' => 'success'
            ];
        }
        return (object) [
            'json' => $response->json(),
            'status' => 'failed'
        ];
    }

    public function retry($id)
    {
        $recording = Recording::where("id", $id)->first();
        UpdateRecordingStatus::dispatch($recording->id, "Progress");
        RetryRecording::dispatch($recording->id);
    }
}
