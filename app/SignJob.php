<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SignJob extends Model
{
    protected $fillable = ["bduss_id", "job_id", "has_finished"];
}
