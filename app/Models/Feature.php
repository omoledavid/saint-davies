<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function plans()
    {
        return $this->belongsToMany(Plan::class)->withPivot('value')->withTimestamps();
    }
}
