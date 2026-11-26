<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsuarioController extends Controller
{
    /**
     * Lista de usuarios (solo lectura).
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::with('role')->orderBy('id')->get());
    }
}
