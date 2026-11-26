<?php

namespace Tests\Feature\Api\V1;

use App\Models\Habitacion;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Role;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    private Zona $zona;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['nombre' => 'Administrador']);
        Role::create(['nombre' => 'Cliente']);
        $this->zona = Zona::create(['codigo_zona' => 'Z-1', 'descripcion' => 'Zona norte']);
    }

    private function crearUsuario(string $rol = 'Cliente'): User
    {
        return User::factory()->create([
            'role_id' => Role::where('nombre', $rol)->value('id'),
            'documento' => fake()->unique()->numerify('##########'),
        ]);
    }

    private function crearHabitacion(array $atributos = []): Habitacion
    {
        return Habitacion::create([
            'zona_id' => $this->zona->id,
            'nombre_habitacion' => 'Suite 101',
            'capacidad' => 2,
            'valor' => 150000,
            'estado' => 'Disponible',
            ...$atributos,
        ]);
    }

    private function crearReservaDe(User $cliente, Habitacion $habitacion, string $estado = 'Pendiente'): Reserva
    {
        $reserva = Reserva::create([
            'habitacion_id' => $habitacion->id,
            'fecha' => today(),
            'estado_reserva' => $estado,
            'sub_total' => 300000,
        ]);

        $reserva->detalle()->create([
            'user_id' => $cliente->id,
            'fecha_ingreso' => today()->addDays(5),
            'fecha_salida' => today()->addDays(7),
            'cantidad_personas' => 2,
        ]);

        return $reserva;
    }

    public function test_register_creates_client_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana Pérez',
            'documento' => '1234567890',
            'telefono' => '3001234567',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.rol', 'Cliente')
            ->assertJsonPath('user.es_admin', false);

        $this->withToken($response->json('token'))
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'ana@example.com');
    }

    public function test_login_rejects_wrong_password_and_accepts_valid_credentials(): void
    {
        $user = $this->crearUsuario();

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email', 'rol']]);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = $this->crearUsuario();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_portada_returns_every_room_per_zone_and_cheapest_available_price(): void
    {
        $this->crearHabitacion(['nombre_habitacion' => '101', 'valor' => 200000]);
        $this->crearHabitacion(['nombre_habitacion' => '102', 'valor' => 90000, 'estado' => 'Mantenimiento']);
        $this->crearHabitacion(['nombre_habitacion' => '103', 'valor' => 120000]);

        $this->getJson('/api/v1/portada')
            ->assertOk()
            ->assertJsonPath('data.precio_desde', 120000)
            ->assertJsonCount(1, 'data.zonas')
            ->assertJsonCount(3, 'data.zonas.0.habitaciones')
            ->assertJsonPath('data.zonas.0.habitaciones.1.estado', 'Mantenimiento');
    }

    public function test_catalog_lists_only_available_rooms_and_filters_by_zone(): void
    {
        $otraZona = Zona::create(['codigo_zona' => 'Z-2']);
        $disponible = $this->crearHabitacion();
        $this->crearHabitacion(['nombre_habitacion' => 'En obra', 'estado' => 'Mantenimiento']);
        $this->crearHabitacion(['nombre_habitacion' => 'Otra zona', 'zona_id' => $otraZona->id]);

        $this->getJson('/api/v1/habitaciones')->assertOk()->assertJsonCount(2, 'data');

        $this->getJson("/api/v1/habitaciones?zona_id={$this->zona->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $disponible->id)
            ->assertJsonPath('data.0.imagen_principal', null);
    }

    public function test_protected_routes_require_token(): void
    {
        $this->getJson('/api/v1/reservas')->assertUnauthorized();
    }

    public function test_client_creates_reservation_with_subtotal_and_admins_are_notified(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $habitacion = $this->crearHabitacion();
        Sanctum::actingAs($this->crearUsuario());

        $this->postJson('/api/v1/reservas', [
            'habitacion_id' => $habitacion->id,
            'fecha_ingreso' => today()->addDay()->toDateString(),
            'fecha_salida' => today()->addDays(4)->toDateString(),
            'cantidad_personas' => 2,
        ])
            ->assertCreated()
            ->assertJsonPath('data.estado_reserva', 'Pendiente')
            ->assertJsonPath('data.noches', 3)
            ->assertJsonPath('data.sub_total', 450000);

        $this->assertDatabaseHas(Notificacion::class, ['user_id' => $admin->id, 'leida' => false]);
    }

    public function test_reservation_is_rejected_when_dates_overlap_or_capacity_is_exceeded(): void
    {
        $habitacion = $this->crearHabitacion();
        $this->crearReservaDe($this->crearUsuario(), $habitacion);
        Sanctum::actingAs($this->crearUsuario());

        $this->postJson('/api/v1/reservas', [
            'habitacion_id' => $habitacion->id,
            'fecha_ingreso' => today()->addDays(6)->toDateString(),
            'fecha_salida' => today()->addDays(8)->toDateString(),
            'cantidad_personas' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('mensaje');

        $this->postJson('/api/v1/reservas', [
            'habitacion_id' => $habitacion->id,
            'fecha_ingreso' => today()->addDays(10)->toDateString(),
            'fecha_salida' => today()->addDays(12)->toDateString(),
            'cantidad_personas' => 5,
        ])->assertUnprocessable()->assertJsonValidationErrors('mensaje');

        $this->assertDatabaseCount('reservas', 1);
    }

    public function test_client_cannot_access_another_clients_reservation(): void
    {
        $reserva = $this->crearReservaDe($this->crearUsuario(), $this->crearHabitacion());
        Sanctum::actingAs($this->crearUsuario());

        $this->getJson("/api/v1/reservas/{$reserva->id}")->assertForbidden();
        $this->postJson("/api/v1/reservas/{$reserva->id}/cancelar")->assertForbidden();
        $this->postJson("/api/v1/reservas/{$reserva->id}/pago", ['metodo_pago' => 'PSE'])->assertForbidden();

        $this->assertSame('Pendiente', $reserva->fresh()->estado_reserva);
    }

    public function test_owner_can_cancel_pending_reservation_but_not_a_cancelled_one(): void
    {
        $cliente = $this->crearUsuario();
        $reserva = $this->crearReservaDe($cliente, $this->crearHabitacion());
        Sanctum::actingAs($cliente);

        $this->postJson("/api/v1/reservas/{$reserva->id}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.estado_reserva', 'Cancelada');

        $this->postJson("/api/v1/reservas/{$reserva->id}/cancelar")->assertUnprocessable();
    }

    public function test_card_ending_in_0000_is_rejected_and_reservation_stays_pending(): void
    {
        $cliente = $this->crearUsuario();
        $reserva = $this->crearReservaDe($cliente, $this->crearHabitacion());
        Sanctum::actingAs($cliente);

        $this->postJson("/api/v1/reservas/{$reserva->id}/pago", [
            'metodo_pago' => 'Tarjeta',
            'titular' => 'Ana Pérez',
            'numero_tarjeta' => '4111 1111 1111 0000',
            'fecha_expiracion' => today()->addYear()->format('m/y'),
            'cvv' => '123',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('pago.estado_pago', 'Rechazado')
            ->assertJsonPath('pago.tarjeta_enmascarada', '**** **** **** 0000');

        $this->assertSame('Pendiente', $reserva->fresh()->estado_reserva);
    }

    public function test_invoice_requires_an_approved_payment(): void
    {
        $cliente = $this->crearUsuario();
        $reserva = $this->crearReservaDe($cliente, $this->crearHabitacion(), 'Confirmada');
        Sanctum::actingAs($cliente);

        $this->getJson("/api/v1/reservas/{$reserva->id}/factura")->assertNotFound();

        Pago::create([
            'reserva_id' => $reserva->id,
            'metodo_pago' => 'PSE',
            'estado_pago' => 'Aprobado',
            'monto' => 300000,
            'referencia' => 'PAY-TEST0001',
            'fecha_pago' => now(),
        ]);

        $this->getJson("/api/v1/reservas/{$reserva->id}/factura")
            ->assertOk()
            ->assertJsonPath('data.pago.referencia', 'PAY-TEST0001')
            ->assertJsonPath('data.desglose.noches', 2)
            ->assertJsonPath('data.desglose.valor_noche', 150000);
    }

    public function test_admin_routes_are_forbidden_for_clients(): void
    {
        Sanctum::actingAs($this->crearUsuario());

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
        $this->postJson('/api/v1/admin/zonas', ['codigo_zona' => 'Z-9'])->assertForbidden();

        $this->assertDatabaseMissing(Zona::class, ['codigo_zona' => 'Z-9']);
    }

    public function test_admin_can_change_reservation_status(): void
    {
        $reserva = $this->crearReservaDe($this->crearUsuario(), $this->crearHabitacion());
        Sanctum::actingAs($this->crearUsuario('Administrador'));

        $this->patchJson("/api/v1/admin/reservas/{$reserva->id}/estado", ['estado_reserva' => 'Confirmada'])
            ->assertOk()
            ->assertJsonPath('data.estado_reserva', 'Confirmada');

        $this->patchJson("/api/v1/admin/reservas/{$reserva->id}/estado", ['estado_reserva' => 'Inventado'])
            ->assertUnprocessable();
    }

    public function test_admin_cannot_delete_zone_with_rooms(): void
    {
        $this->crearHabitacion();
        Sanctum::actingAs($this->crearUsuario('Administrador'));

        $this->deleteJson("/api/v1/admin/zonas/{$this->zona->id}")->assertConflict();

        $this->assertModelExists($this->zona);
    }

    public function test_admin_room_delete_marks_it_out_of_service(): void
    {
        $habitacion = $this->crearHabitacion();
        Sanctum::actingAs($this->crearUsuario('Administrador'));

        $this->deleteJson("/api/v1/admin/habitaciones/{$habitacion->id}")
            ->assertOk()
            ->assertJsonPath('data.estado', 'Fuera de servicio');

        $this->assertModelExists($habitacion);
    }
}
