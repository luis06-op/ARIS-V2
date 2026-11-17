<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class InicioController extends Controller
{
    public function __invoke(): View
    {
        return view('publico.inicio');
    }
}
