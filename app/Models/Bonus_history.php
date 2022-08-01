<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus_history extends Model
{
    use HasFactory;

    protected $table = "bonus_history";
    protected $primaryKey = "id";

    protected $fillable = [
        'user_id',
        'message',
        'amount',
        'state'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
