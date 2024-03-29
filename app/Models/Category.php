<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function campagnes()
    {
        return $this->hasMany(Campaign::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
