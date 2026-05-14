<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['job_vacancy_id', 'candidate_id', 'ai_score', 'status'])]
class Application extends Model
{
    //
}
