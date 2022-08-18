<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Doctors;

class DocService extends Model
{
    use HasFactory;
    protected $table = 'doc_service';
    protected $fillable = [
        'doctor_id',
        'service_id',
    ];

    public function doctor_detail()
    {
        return $this->belongsTo(Doctors::class,'doctor_id','id');
    }
}
