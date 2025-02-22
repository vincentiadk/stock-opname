<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'location_id', 'location_name', 'user_id', 'location_rugs_id','location_rugs_name', 
        'location_shelf_name', 'location_shelf_id',
        'stockopname_id', 'stockopname_name'
    ];
    protected $guarded = [];
}
