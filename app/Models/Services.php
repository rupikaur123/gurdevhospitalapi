<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'alies_name',
        'image',
        'image_path',
        'meta_title',
        'meta_description',
        'meta_keyword',
        'banner_image',
        'status',
    ];

    public function DocService()
    {
        return $this->hasMany(DocService::class,'id','service_id');
    }
}
