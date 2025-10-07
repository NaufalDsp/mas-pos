<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationship: CartItem belongs to Cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Relationship: CartItem belongs to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper method: Get subtotal
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
