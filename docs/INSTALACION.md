# Instalación

Estos son los pasos que sigo para dejar el proyecto corriendo en un computador nuevo. Yo lo trabajo en Windows con Laragon, pero debería servir igual en otro entorno.

## 1. Lo que se necesita

- PHP **8.3 o más** con las extensiones normales de Laravel (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, etc.).
- Composer 2.
- Node.js 18 o más con npm (para compilar Tailwind y Alpine con Vite).
- **MySQL**. Yo uso el que trae [Laragon](https://laragon.org/), en `127.0.0.1:3306`.

Para revisar que todo esté instalado:

```bash
php -v
composer -V
node -v
npm -v
```

## 2. Clonar e instalar dependencias

```bash
git clone <url-del-repositorio> hotel-laravel
cd hotel-laravel
composer install
npm install
```

## 3. Configurar el .env

```bash
cp .env.example .env
php artisan key:generate
```

Después hay que poner los datos de la base de datos en el `.env`. El `.env.example` viene con `DB_CONNECTION=sqlite` porque así lo trae Laravel, pero yo trabajo con **MySQL**, entonces queda así:

```env
APP_NAME="Casa Hotel Mr Angel"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel-laravel
DB_USERNAME=root
DB_PASSWORD=
```

La base de datos hay que crearla vacía antes. Se puede hacer desde HeidiSQL o phpMyAdmin de Laragon, o por consola:

```bash
mysql -u root -e "CREATE DATABASE \`hotel-laravel\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Otras variables del `.env` que vale la pena conocer:

| Grupo | Variables | Para qué |
|---|---|---|
| App | `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE` | `APP_DEBUG=true` solo mientras desarrollo |
| Base de datos | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | uso `mysql` |
| Sesión | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120` | necesita la tabla `sessions`, que ya viene en las migraciones |
| Colas / caché | `QUEUE_CONNECTION=database`, `CACHE_STORE=database` | por ahora no uso colas para nada del hotel |
| Correo | `MAIL_MAILER=log` | los correos (verificación, cambio de contraseña) no se envían, quedan escritos en `storage/logs/laravel.log` |
| Frontend | `VITE_APP_NAME` | toma el valor de `APP_NAME` |

## 4. Migraciones y datos de prueba

```bash
php artisan migrate --seed
```

Con eso se crean todas las tablas (las explico en [ARQUITECTURA.md](ARQUITECTURA.md)) y se cargan unos datos para probar:

- 2 roles: `Administrador` y `Cliente`.
- 3 zonas: Norte, Sur y VIP.
- 4 habitaciones de ejemplo (una por zona y otra más en Norte).
- Un usuario administrador:

| Campo | Valor |
|---|---|
| Email | `test@example.com` |
| Password | `password` |
| Rol | Administrador |

Si quiero borrar todo y empezar de cero mientras desarrollo:

```bash
php artisan migrate:fresh --seed
```

Ojo que `migrate:fresh` **borra todas las tablas**, no hay que correrlo en una base con datos de verdad.

## 5. Imágenes

Las fotos de las habitaciones se guardan en el disco `public` (`storage/app/public/habitaciones`) y se ven gracias al enlace `public/storage`. Ese enlace se crea una sola vez:

```bash
php artisan storage:link
```

## 6. Compilar estilos y JS

```bash
npm run build     # para dejarlo compilado
# o mientras estoy trabajando, en otra terminal:
npm run dev       # Vite con recarga automática
```

Si cambio algo en `tailwind.config.js` o en `resources/css/app.css` tengo que volver a correr `npm run build` (o dejar `npm run dev` abierto).

## 7. Correr el proyecto

```bash
php artisan serve
```

Queda en `http://localhost:8000`. Con Laragon también se puede entrar por el virtualhost, que en mi caso es `http://hotel-laravel.test`.

## 8. Comandos que uso seguido

| Comando | Para qué |
|---|---|
| `composer dev` | levanta todo el entorno de desarrollo (servidor, colas y Vite) |
| `composer test` | limpia la caché de config y corre las pruebas (`php artisan test`) |
| `php artisan route:list` | ver todas las rutas |
| `php artisan tinker` | consola para probar modelos y consultas |
| `php artisan migrate:status` | ver qué migraciones ya corrieron |

## 9. Errores que me han salido

- **"SQLSTATE[HY000] [2002] Connection refused"**: MySQL no está prendido en Laragon, o `DB_HOST`/`DB_PORT` no coinciden.
- **No se ven las imágenes**: falta `php artisan storage:link`, o el enlace quedó apuntando a la carpeta vieja después de mover el proyecto (lo borro y lo vuelvo a crear).
- **Los estilos se ven dañados**: no he compilado los assets (`npm run build` o `npm run dev`).
- **"Class not found" después de clonar**: falta `composer install` o hay que correr `composer dump-autoload`.
