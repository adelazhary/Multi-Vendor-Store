<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'slug',
        'cover_image',
        'logo_image',
        'status',
    ];
    public function products()
    {
        return $this->hasMany(product::class);
    }
}
