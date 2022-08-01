<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $filable = [
        'firstname',
        'gender',
        'phone',
        'cni',
        'address',
        'category_id',
        'password',
        'account_state',
        'parent_id',
        'created_by',
        'role_id',
        'state',

    ];

    public function users()
    {

        return $this->hasMany(User::class);

    }
}
