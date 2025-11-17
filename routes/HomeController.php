<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Registro::count(),
            'ativos' => Registro::where('status', 'ativo')->count(),
            'inativos' => Registro::where('status', 'inativo')->count(),
            'estados' => Registro::distinct('estado')->count('estado'),
        ];

        return view('home', compact('stats'));
    }
}
