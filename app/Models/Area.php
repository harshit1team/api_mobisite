<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'display_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }
}
