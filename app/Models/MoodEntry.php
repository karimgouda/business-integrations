<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodEntry extends Model
{
    protected $fillable = ['user_id', 'mood', 'note', 'entry_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
