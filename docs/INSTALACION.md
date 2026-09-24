# Instalación y puesta en marcha

## 1. Requisitos

- PHP **8.3+** con las extensiones habituales de Laravel (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, etc.).
- Composer 2.x.
- Node.js 18+ y npm (para compilar Tailwind/Alpine con Vite).
- Un servidor de base de datos **MySQL** (el entorno de desarrollo usa [Laragon](https://laragon.org/) en Windows, con MySQL en `127.0.0.1:3306`).

Verifica las versiones:

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

## 3. Variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` y configura la conexión a base de datos. El proyecto usa MySQL en desarrollo (el `.env.example` trae `DB_CONNECTION=sqlite` por defecto del scaffolding de Laravel, pero **este proyecto se ejecuta contra MySQL**):

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

Crea la base de datos vacía (por ejemplo desde HeidiSQL/phpMyAdmin de Laragon, o por CLI):

```bash
mysql -u root -e "CREATE DATABASE \`hotel-laravel\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Otras variables relevantes de `.env.example` (grupos, sin exponer valores sensibles):

| Grupo | Variables | Notas |
|---|---|---|
| App | `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE` | `APP_DEBUG=true` solo en desarrollo |
| Base de datos | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Este proyecto usa `mysql` |
| Sesión | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120` | Requiere tabla `sessions` (ya incluida en las migraciones) |
| Colas / caché | `QUEUE_CONNECTION=database`, `CACHE_STORE=database` | No se usan colas asíncronas en el dominio actual |
| Correo | `MAIL_MAILER=log` | Los correos (verificación, reset de password) se escriben en `storage/logs/laravel.log`, no se envían de verdad |
| Frontend | `VITE_APP_NAME` | Se sincroniza automáticamente con `APP_NAME` |

## 4. Base de datos: migraciones y datos semilla

```bash
php artisan migrate --seed
```

Esto crea todo el esquema (ver [ARQUITECTURA.md](ARQUITECTURA.md)) y carga:

- 2 roles: `Administrador`, `Cliente`.
- 3 zonas: Norte, Sur, VIP.
- 4 habitaciones de ejemplo (una por zona, más una extra en Norte).
- 1 usuario administrador de prueba:

| Campo | Valor |
|---|---|
| Email | `test@example.com` |
| Password | `password` |
| Rol | Administrador |

Si necesitas reiniciar la base de datos desde cero durante el desarrollo:

```bash
php artisan migrate:fresh --seed
```

> ⚠️ `migrate:fresh` **borra todas las tablas**. No usar contra una base de datos con información real.

## 5. Almacenamiento de imágenes

Las imágenes de las habitaciones se guardan en el disco `public` (`storage/app/public/habitaciones`) y se sirven a través del enlace simbólico `public/storage`. Crea el enlace (una sola vez por entorno):

```bash
php artisan storage:link
```

## 6. Compilar los assets (Tailwind / Alpine / Vite)

```bash
npm run build     # compilación de producción
# o, durante el desarrollo, en otra terminal:
npm run dev        # servidor de Vite con recarga en caliente
```

Si modificas `tailwind.config.js` o clases en `resources/css/app.css`, vuelve a ejecutar `npm run build` (o mantén `npm run dev` corriendo).

## 7. Levantar el servidor

```bash
php artisan serve
```

La app quedará disponible en `http://localhost:8000` (o el puerto que indique el comando). Con Laragon también puedes acceder directamente vía el virtualhost configurado (`http://hotel-laravel.test`, según tu configuración local).

## 8. Comandos útiles

| Comando | Uso |
|---|---|
| `composer dev` | Levanta el entorno de desarrollo (`php artisan dev`, incluye servidor, colas y Vite según configuración) |
| `composer test` | Limpia config de caché y corre la suite de PHPUnit (`php artisan test`) |
| `php artisan route:list` | Lista todas las rutas registradas |
| `php artisan tinker` | Consola interactiva para probar modelos/consultas |
| `php artisan migrate:status` | Ver qué migraciones se han ejecutado |

## 9. Problemas comunes

- **"SQLSTATE[HY000] [2002] Connection refused"**: el servicio MySQL de Laragon no está iniciado, o `DB_HOST`/`DB_PORT` no coinciden con tu configuración.
- **Las imágenes no se ven**: falta ejecutar `php artisan storage:link`, o el enlace apunta a una ruta antigua tras mover el proyecto (bórralo y vuelve a crearlo).
- **Estilos rotos / clases de Tailwind sin efecto**: falta compilar assets (`npm run build` o `npm run dev`).
- **"Class not found" tras clonar el repo**: falta `composer install` o el autoload está desactualizado (`composer dump-autoload`).
