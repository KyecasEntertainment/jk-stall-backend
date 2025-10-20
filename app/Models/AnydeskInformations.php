<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnydeskInformations extends Model
{
    protected $table = 'anydesk_informations';

    protected $fillable = [
        'anydesk_id',
        'pc_info',
    ];
}
