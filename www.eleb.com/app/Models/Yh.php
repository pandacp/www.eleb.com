<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Yh extends   Authenticatable
{
    //
    protected $fillable=['username','password','tel','remember_token'];
}
