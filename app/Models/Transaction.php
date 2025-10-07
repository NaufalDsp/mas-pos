<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    // Relationship: Transaction belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Transaction has many TransactionItems
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
