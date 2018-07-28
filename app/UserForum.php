<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserForum extends Model
{
    protected $fillable = ["bduss_id", "forum_id", "forum_name", "level_id", "level_name", "cur_score"];
}
