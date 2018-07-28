<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SignRecord extends Model
{
    protected $fillable = ['forum_id', 'sign_time', 'user_sign_rank', 'cont_sign_num', 'total_sign_num',
        'sign_bonus_point', 'level_name', 'levelup_score', 'has_signed', 'error_msg'];
}
