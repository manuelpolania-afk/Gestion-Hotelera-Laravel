<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * Autenticación por token (Laravel Sanctum) para clientes móviles.
 * Cada login/registro emite un token Bearer que la app debe enviar en
 * la cabecera "Authorization: Bearer {token}".
 */
class AuthController extends Controller
{
    /**
     * Registra un nuevo cliente y devuelve su token de acceso.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'telefono' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $rolCliente = Role::firstOrCreate(
            ['nombre' => 'Cliente'],
            ['descripcion' => 'Usuario que realiza reservas']
        );

        $user = User::create([
            'name' => $data['name'],
            'documento' => $data['documento'],
            'telefono' => $data['telefono'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $rolCliente->id,
            'estado' => 'activo',
        ]);

        event(new Registered($user));

        return $this->respuestaConToken($user, $data['device_name'] ?? null, 201);
    }

    /**
     * Valida credenciales y devuelve un token de acceso.
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        return $this->respuestaConToken($user, $data['device_name'] ?? null);
    }

    /**
     * Devuelve el usuario autenticado.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('role'));
    }

    /**
     * Revoca el token con el que se hizo la petición.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Envía el correo con el enlace para restablecer la contraseña (flujo web).
     *
     * @throws ValidationException
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => __($status)]);
    }

    private function respuestaConToken(User $user, ?string $deviceName, int $status = 200): JsonResponse
    {
        $token = $user->createToken($deviceName ?: 'flutter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('role')),
        ], $status);
    }
}
