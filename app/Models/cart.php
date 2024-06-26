<?php

namespace App\Models;

use App\Models\Scopes\cookieScope;
use App\Observers\CartObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[ObservedBy(CartObserver::class)]
class cart extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'options',
        'cookie_id',
    ];

    protected $casts = [
        'options' => 'array',
    ];
    public function product(){
        return $this->belongsTo(product::class);
    }
    public function scopeCookie($query, $cookie_id)
    {
        return $query->where('cookie_id', $cookie_id)->first();
    }
    public function getOptionsAttribute($value)
    {
        return json_decode($value);
    }
    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->product->price;
    }
    public function getTotalAttribute()
    {
        return $this->sum('subtotal');
    }
}
