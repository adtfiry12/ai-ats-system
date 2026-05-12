<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\JobVacancy;
use App\Models\Application;
use Smalot\PdfParser\Parser;
use Google\Client;
use Google\Service\GenerativeLanguage;
use Google\Service\GenerativeLanguage\GenerateContentRequest;
use Google\Service\GenerativeLanguage\Content;
use Google\Service\GenerativeLanguage\Part;
use Illuminate\Support\Facades\Http;

class ApplicationController extends Controller
{
    // Fungsi ini cuma buat nampilin halaman form upload CV (buat ngetes)
    public function create()
    {
        // Misal kita ambil lowongan kerja pertama buat di-apply (kalau belum ada datanya, abaikan dulu)
        $job = JobVacancy::first();
        return view('apply', compact('job'));
    }

    // Ini fungsi utamanya: Nerima file, baca teks, nanya AI, dan simpan ke database
    public function store(Request $request)
    {
        // 1. Validasi Input User
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'job_vacancy_id' => 'required|exists:job_vacancies,id',
            'resume' => 'required|mimes:pdf|max:2048', // Wajib PDF, max 2MB
        ]);

        // 2. Simpan File Fisik PDF ke folder Storage (storage/app/resumes)
        $path = $request->file('resume')->store('resumes');

        // 3. Ekstrak Teks dari PDF (Pakai library smalot/pdfparser)
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile(storage_path('app/' . $path));
        $text = $pdf->getText();

        // 4. Tanya ke Gemini AI (Ngekstrak Skill)
        $extractedSkills = $this->extractSkillsWithGemini($text);

        // 5. Simpan Data Pelamar ke Database
        $candidate = Candidate::create([
            'name' => $request->name,
            'email' => $request->email,
            'resume_path' => $path,
            'extracted_skills' => json_encode($extractedSkills), // Simpan skill dalam format JSON
        ]);

        // 6. Hubungkan Pelamar dengan Lowongan (Tabel Application)
        Application::create([
            'job_vacancy_id' => $request->job_vacancy_id,
            'candidate_id' => $candidate->id,
            'status' => 'screening',
        ]);

        return response()->json([
            'message' => 'Lamaran berhasil disubmit dan dianalisa oleh AI!',
            'candidate' => $candidate
        ]);
    }

    // Fungsi khusus buat ngobrol sama Gemini API
    private function extractSkillsWithGemini($resumeText)
    {
        $apiKey = env('GEMINI_API_KEY');
        // Kita pakai model gemini-1.5-flash karena lebih cepat dan murah buat tugas begini
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        // Prompt (Instruksi) buat AI-nya
        $prompt = "Kamu adalah sistem HRD profesional. 
                   Tugas kamu: Ekstrak daftar SKILL TEKNIS (misal: PHP, Laravel, React, SQL, dll) dari teks resume berikut.
                   
                   Hanya kembalikan hasilnya dalam format array JSON yang rapi seperti contoh ini: [\"skill_1\", \"skill_2\"]. 
                   Jangan tambahkan kata-kata penjelasan apapun selain JSON tersebut.
                   
                   Teks Resume: \n\n" . substr($resumeText, 0, 3000); // Batasin teks biar nggak over limit

        // Kirim Request ke Google Gemini pakai cURL (lewat Laravel Http Client)
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        // Proses respon dari AI
        if ($response->successful()) {
            $data = $response->json();
            $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '[]';

            // Bersihin teks dari markdown code block (```json ... ```) yang kadang dikasih AI
            $cleanJson = trim($aiText, " \t\n\r\0\x0B`");
            $cleanJson = str_replace(['json', '```'], '', $cleanJson);

            // Ubah string JSON jadi Array PHP
            $skillsArray = json_decode(trim($cleanJson), true);

            // Kalau gagal parse JSON, kembalikan array kosong
            return is_array($skillsArray) ? $skillsArray : [];
        }

        // Kalau API gagal, kembalikan array kosong
        return [];
    }
}
