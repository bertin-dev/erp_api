<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = "campaign";
    protected $fillable = [
        'category_id',
        'starting_date',
        'end_date',
        'discount',
    ];

    public function categorie()
    {
        return $this->belongsTo(Category::class);
    }


}
