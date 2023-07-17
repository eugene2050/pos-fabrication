<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'action',
        'product',
        'image',
        'name',
        'date',
        'status',
        'description'
    ];
}