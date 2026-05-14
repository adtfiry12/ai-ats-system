<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// WAJIB panggil namespace ini supaya attribute #[Fillable] dikenal
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['candidate_id', 'embedding'])]
class ResumeEmbedding extends Model
{
    /**
     * Karena kolom 'embedding' di Postgres itu tipe vector, 
     * kita cast ke array supaya gampang diolah di PHP.
     */
    protected $casts = [
        'embedding' => 'array',
    ];

    /**
     * Relasi ke model Candidate
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
