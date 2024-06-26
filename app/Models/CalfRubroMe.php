<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalfRubroMe extends Model
{
    use HasFactory;

    protected $fillable = 
    ['rubro_id',
     'valor',
     'mes',
     'año'
    ];
}
