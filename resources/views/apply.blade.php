<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Lamaran - AI ATS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h3 class="mb-4 text-primary">Lamar Pekerjaan</h3>

                        @if ($job)
                            <div class="alert alert-info">
                                <strong>Lowongan:</strong> {{ $job->title }}<br>
                                <small class="text-muted">{{ $job->department }}</small>
                            </div>

                            <form action="{{ route('apply.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="job_vacancy_id" value="{{ $job->id }}">

                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" placeholder="Budi Santoso"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="budi@email.com" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Upload CV (PDF Only)</label>
                                    <input type="file" name="resume" class="form-control" accept="application/pdf"
                                        required>
                                    <div class="form-text">Biarkan Gemini AI menganalisa CV kamu secara otomatis.</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Kirim Lamaran & Analisa AI</button>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                Belum ada lowongan tersedia. Masukkan data ke tabel <code>job_vacancies</code> dulu ya!
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
