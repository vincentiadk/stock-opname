<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'location_id', 'user_id', 'location_rugs_id','location_shelf_id',
    ];
}
