<?php

// app/Models/ImagemEmMassa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagemEmMassa extends Model
{
    use HasFactory;

    protected $table = 'imagem_em_massa';
    protected $fillable = ['caminho'];
}
