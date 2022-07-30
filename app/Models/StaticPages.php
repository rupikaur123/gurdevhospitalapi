<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticPages extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'image_path',
        'meta_title',
        'meta_description',
        'meta_keyword',
        'content',
        'status',
    ];
}
