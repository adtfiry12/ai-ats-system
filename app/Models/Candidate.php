<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'email', 'phone', 'resume_path', 'extracted_skills'])]
class Candidate extends Model
{
    /**
     * Casting otomatis buat data JSON skill.
     */
    protected function casts(): array
    {
        return [
            'extracted_skills' => 'array',
        ];
    }
}
