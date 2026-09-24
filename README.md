# Casa Hotel Mr Angel

Sistema de gestión hotelera desarrollado en **Laravel 13**. Permite a los huéspedes explorar habitaciones disponibles, reservar y pagar (mediante una pasarela de pago **simulada**, sin cobros reales), mientras que el personal administrativo gestiona zonas, habitaciones, reservas, pagos y usuarios desde un panel propio.

Proyecto académico — el módulo de pagos es una **simulación** pensada para fines educativos: no se integra con ninguna pasarela real (PSE, Stripe, etc.) y no procesa dinero de verdad.

## Índice de documentación

Este README da una visión general. Para el detalle de cada módulo, ver la carpeta [`docs/`](docs/):

| Documento | Contenido |
|---|---|
| [docs/INSTALACION.md](docs/INSTALACION.md) | Requisitos, instalación paso a paso, variables de entorno, comandos de desarrollo |
| [docs/ARQUITECTURA.md](docs/ARQUITECTURA.md) | Modelos, relaciones, esquema de base de datos completo |
| [docs/RUTAS.md](docs/RUTAS.md) | Listado completo de rutas web y de autenticación |
| [docs/ROLES_Y_PERMISOS.md](docs/ROLES_Y_PERMISOS.md) | Sistema de roles (Administrador / Cliente), middleware de acceso |
| [docs/MODULO_HABITACIONES.md](docs/MODULO_HABITACIONES.md) | Zonas, habitaciones, imágenes |
| [docs/MODULO_RESERVAS.md](docs/MODULO_RESERVAS.md) | Ciclo de vida de una reserva, validaciones, notificaciones |
| [docs/MODULO_PAGOS.md](docs/MODULO_PAGOS.md) | Cómo funciona el pago simulado, casos de prueba |
| [docs/TESTING.md](docs/TESTING.md) | Estado actual de las pruebas automatizadas |

## Características principales

- **Catálogo público** de habitaciones por zona, con imágenes y disponibilidad en tiempo real.
- **Reservas** con validación de capacidad, disponibilidad y solapamiento de fechas.
- **Pago simulado** (tarjeta / PSE / efectivo) con aprobación o rechazo automático, sin conexión a ninguna pasarela real.
- **Panel administrativo** para gestionar zonas, habitaciones (con galería de imágenes), reservas, usuarios y notificaciones internas.
- **Roles**: `Administrador` (acceso total al panel `/admin`) y `Cliente` (reserva y paga sus propias habitaciones).
- **Notificaciones internas** a los administradores cada vez que un cliente crea una reserva.

## Stack técnico

- **Backend**: PHP 8.3, Laravel 13, MySQL (vía Laragon).
- **Frontend**: Blade + Tailwind CSS (paleta y componentes propios, ver `resources/css/app.css`) + Alpine.js + Vite.
- **Autenticación**: Laravel Breeze (login, registro, verificación de email, recuperación de contraseña).

Ver [docs/ARQUITECTURA.md](docs/ARQUITECTURA.md) para el detalle completo de dependencias.

## Arranque rápido

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configura DB_* en .env (este proyecto usa MySQL por defecto en Laragon)
php artisan migrate --seed
php artisan storage:link
npm run build   # o `npm run dev` en otra terminal para desarrollo
php artisan serve
```

Instrucciones completas y solución de problemas comunes en [docs/INSTALACION.md](docs/INSTALACION.md).

### Usuario administrador de prueba

El seeder crea un administrador listo para iniciar sesión:

| Campo | Valor |
|---|---|
| Email | `test@example.com` |
| Password | `password` |
| Rol | Administrador |

## Estructura de carpetas relevante

```
app/
├── Http/
│   ├── Controllers/          # HomeController, HabitacionController, ZonaController,
│   │                          # ReservaController, PagoController, NotificacionController,
│   │                          # ImagenHabitacionController, Admin/UsuarioController, Auth/*
│   ├── Middleware/            # EnsureUserIsAdmin
│   └── Requests/               # StoreReservaRequest, Store/UpdateHabitacionRequest, ProfileUpdateRequest
└── Models/                     # User, Role, Zona, Habitacion, ImagenHabitacion,
                                 # Reserva, DetalleReserva, Pago, Notificacion
database/
├── migrations/                 # Esquema completo (ver docs/ARQUITECTURA.md)
└── seeders/                    # RoleSeeder, ZonaSeeder, HabitacionSeeder, DatabaseSeeder
resources/views/
├── home/, habitaciones/        # Catálogo público
├── reservas/, pagos/           # Flujo del cliente
├── admin/                      # Panel administrativo
└── components/, layouts/       # Layouts y componentes del design system
routes/
├── web.php                     # Rutas públicas, autenticadas y admin
└── auth.php                    # Rutas de Breeze (login, registro, password, verificación)
```

## Licencia

Proyecto académico, sin licencia comercial.
