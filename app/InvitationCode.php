<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvitationCode extends Model
{
    protected $fillable = ['apply_user_id', 'used_user_id', 'code', 'has_used'];

    public function used_user() {
        return $this->hasOne("App\User", "id", "used_user_id");
    }

    public function apply_user() {
        return $this->hasOne("App\User", "id", "apply_user_id");
    }
}
