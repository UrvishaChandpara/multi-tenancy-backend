<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class super_admin extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guard = 'admin';
    
    protected $fillable = [
        'username',
        'password',
    ];

    public function getAuthIdentifierName()
    {
        return 'id';
    }
}
