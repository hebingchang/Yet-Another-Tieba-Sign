<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaiduAccount extends Model
{
    protected $fillable = ["user_id", "bduss", "baidu_id", "baidu_name", "available"];
}
