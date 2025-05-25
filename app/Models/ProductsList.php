<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsList extends Model
{
    protected $table = 'products_lists';

    protected $fillable = [
        'product_id',
        'product_name',
    ];

    public function stockBatches()
    {
        return $this->hasMany(StockBatches::class, 'product_id', 'product_id');
    }

    public function dailyStockActivities()
    {
        return $this->hasMany(DailyStockActivity::class, 'product_id', 'product_id');
    }
}
