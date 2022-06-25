<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LatestNews extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'image_path',
        'date',
        'content',
        'status',
    ];
}
