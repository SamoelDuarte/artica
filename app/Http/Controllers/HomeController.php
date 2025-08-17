<?php

namespace App\Http\Controllers;

use App\Models\Sorteio;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Retornar para a view com os dados
        return view('home');
    }

}
