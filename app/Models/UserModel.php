<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id_users';
    protected $allowedFields = ['email','password_hash','reset_token','reset_token_expires','nombre','id_roles','estado','created_at','updated_at'];
}
