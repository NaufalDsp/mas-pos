<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'price',
        'stock',
        'description',
        'image'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url']; // Automatically append to JSON

    // Relationship: Product belongs to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship: Product has many CartItems
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Relationship: Product has many TransactionItems
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Accessor untuk image URL
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image
                ? asset('storage/' . $this->image)
                : asset('images/default-product.png')
        );
    }
}
