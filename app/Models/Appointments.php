<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'appointment_date',
        'u_first_name',
        'u_last_name',
        'u_email',
        'u_dob',
        'u_address',
        'u_phone_number',
        'comment',
        'status',
    ];
}
