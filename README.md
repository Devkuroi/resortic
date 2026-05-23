# RESORTIC – Plataforma de Gestión de Reservas Hoteleras

Sistema web desarrollado en **Laravel 11** + **MySQL** + **Bootstrap 5**.

## Requisitos

- PHP >= 8.2
- Composer
- MySQL 5.7+ o MariaDB 10.4+
- Laravel 11

---

## Instalación paso a paso

### 1. Crear proyecto Laravel

```bash
composer create-project laravel/laravel resortic
cd resortic
```

### 2. Copiar los archivos del proyecto

Reemplaza / copia los archivos entregados en sus rutas correspondientes:

```
app/
  Http/Controllers/
    AuthController.php
    AccountController.php
  Models/
    User.php

database/
  migrations/
    2024_01_01_000001_create_users_table.php
  seeders/
    DatabaseSeeder.php

resources/views/
  layouts/
    app.blade.php
  auth/
    login.blade.php
  accounts/
    index.blade.php
    create.blade.php
    edit.blade.php

routes/
  web.php
```

> **Importante:** reemplaza también `routes/web.php` con el archivo entregado.

---

### 3. Configurar base de datos

Crea la base de datos en MySQL:

```sql
CREATE DATABASE resortic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Copia el archivo de entorno:

```bash
cp .env.example .env
```

Edita `.env` con tus credenciales:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resortic
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

---

### 4. Generar clave de la aplicación

```bash
php artisan key:generate
```

---

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto crea la tabla `users` y registra 3 cuentas de prueba.

---

### 6. Iniciar servidor de desarrollo

```bash
php artisan serve
```

Abre en el navegador: **http://localhost:8000**

---

## Credenciales de prueba

| Rol           | Correo                     | Contraseña   |
|---------------|----------------------------|--------------|
| Administrador | admin@resortic.com         | admin123     |
| Hotel         | brisasdelmar@hotel.com     | hotel123     |
| Cliente       | ana.garcia@gmail.com       | cliente123   |

---

## Estructura del proyecto

```
app/Http/Controllers/
  AuthController.php     → Login y logout con sesión PHP
  AccountController.php  → CRUD completo de cuentas

app/Models/
  User.php               → Modelo con accessors para badges y etiquetas

database/migrations/
  *_create_users_table   → Tabla users: id, name, email, password, role, status

resources/views/
  layouts/app.blade.php  → Layout principal con navbar y sidebar
  auth/login.blade.php   → Formulario de inicio de sesión
  accounts/index.blade   → Listado con estadísticas, filtros y paginación
  accounts/create.blade  → Formulario de creación
  accounts/edit.blade    → Formulario de edición

routes/web.php           → Rutas: login, logout, resource accounts
```

---

## Funcionalidades implementadas

### Autenticación (RF3)
- Inicio de sesión con email y contraseña
- Verificación contra base de datos con `Hash::check()`
- Sesión PHP nativa (sin Sanctum ni Breeze)
- Cierre de sesión
- Protección de rutas: redirige a login si no hay sesión activa
- Bloqueo de cuentas inactivas

### CRUD de Cuentas (RF1, RF2)
- **Crear** cuenta con validación completa
- **Listar** con estadísticas, búsqueda y filtros por rol/estado
- **Editar** con cambio de contraseña opcional
- **Eliminar** con confirmación (protege que el admin no se elimine a sí mismo)
- Paginación de 10 registros por página
- Mensajes flash de éxito/error

### Roles del sistema
- `admin` → Administrador
- `hotel` → Hotel
- `client` → Cliente

---

## Próximos módulos a implementar

- RF4/RF5/RF6: Gestión de habitaciones
- RF7/RF8: Consulta de disponibilidad y reservas
- RF10: Pasarela de pagos
- RF11: Control contable
