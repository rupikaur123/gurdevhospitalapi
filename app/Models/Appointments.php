<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Services;

class Appointments extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'appointment_date',
        'u_full_name',
        'u_email',
        'u_dob',
        'u_address',
        'u_phone_number',
        'comment',
        'status',
    ];

    public function service()
    {
        return $this->belongsTo(Services::class,'service_id','id');
    }
}
