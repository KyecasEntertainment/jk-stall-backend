<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class StockBatches extends Model
{
    use HasFactory;

    protected $table = 'stock_batches';

    protected $fillable = [
        'batch_id',
        'product_id',
        'product_name',
        'entry_quantity',
        'original_cost',
        'selling_cost',
    ];

    protected $casts = [
    'received_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(ProductsList::class, 'product_id', 'product_id');
    }
}
