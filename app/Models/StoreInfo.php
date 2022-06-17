<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreInfo extends Model
{
    use HasFactory;

    protected $table = 'store_info';

    protected $fillable = [
        'store_hash',
        'access_token',
        'user_id',
        'user_email',
        'timezone'
    ];
}
