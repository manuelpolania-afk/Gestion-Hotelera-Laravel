<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('role')->orderBy('id')->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }
}
