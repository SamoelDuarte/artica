<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $table = 'config';
    protected $fillable = [
        'motoboy_fone',
        'status',
        'minuts',
        'chatbot',
        'resposta',
    ];

    protected $casts = [
        'status' => 'boolean',
        'chatbot' => 'boolean',
    ];

    public function getHoursAttribute()
    {
        return intdiv($this->minuts, 60);
    }

    public function getMinutesAttribute()
    {
        return $this->minuts % 60;
    }
}
