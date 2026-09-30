<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $fillable = ['school_name', 'npsn', 'address', 'phone', 'email', 'principal_name', 'logo_path', 'logo_pemkab_path', 'logo_sekolah_path'];
}
