<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class UserAutoRegistration extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'lastname',
        'firstname',
        'gender',
        'phone',
        'cni',
        'address',
        'category_id',
        'password',
        'parent_id',
        'created_by',
        'role_id',
        'state',
        'nom_img_recto',
        'nom_img_verso'
    ];
    protected $table = 'user_registration';

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

}
