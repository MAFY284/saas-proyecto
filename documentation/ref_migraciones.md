# Comandos Artisan para Migraciones en Laravel

## Crear una migración

### Crear una migración vacía

```bash
php artisan make:migration create_users_table
```

### Crear una migración para una nueva tabla

```bash
php artisan make:migration create_products_table --create=products
```

### Crear una migración para modificar una tabla existente

```bash
php artisan make:migration add_price_to_products_table --table=products
```

---

## Ejecutar migraciones

### Ejecutar todas las migraciones pendientes

```bash
php artisan migrate
```

### Ejecutar migraciones en producción sin solicitar confirmación

```bash
php artisan migrate --force
```

### Diferencia entre `migrate` y `migrate --force`

#### Comando normal

```bash
php artisan migrate
```

- Ejecuta las migraciones pendientes.
- En entornos de producción Laravel solicita confirmación antes de realizar cambios en la base de datos.
- Recomendado para ejecuciones manuales.

#### Comando forzado

```bash
php artisan migrate --force
```

- Ejecuta las mismas migraciones.
- Omite la confirmación de seguridad en producción.
- Ideal para despliegues automatizados (CI/CD, Docker, GitHub Actions, Jenkins, GitLab CI, etc.).

#### Ejemplo

Sin `--force`:

```text
APPLICATION IN PRODUCTION.

Do you really wish to run this command?
```

Con `--force`:

```bash
php artisan migrate --force
```

Las migraciones se ejecutan inmediatamente sin solicitar confirmación.

---

## Consultar estado de migraciones

```bash
php artisan migrate:status
```

Muestra:

- Migraciones ejecutadas.
- Migraciones pendientes.
- Número de lote (Batch).

---

## Revertir migraciones

### Revertir el último lote

```bash
php artisan migrate:rollback
```

### Revertir varios lotes

```bash
php artisan migrate:rollback --step=3
```

---

## Revertir todas las migraciones

```bash
php artisan migrate:reset
```

---

## Rehacer todas las migraciones

### Revertir y volver a ejecutar todas

```bash
php artisan migrate:refresh
```

### Incluyendo seeders

```bash
php artisan migrate:refresh --seed
```

---

## Reiniciar completamente la base de datos

### Eliminar todas las tablas y recrearlas

```bash
php artisan migrate:fresh
```

### Eliminar todas las tablas, recrearlas y cargar seeders

```bash
php artisan migrate:fresh --seed
```

> ⚠️ `migrate:fresh` elimina todas las tablas existentes. Utilízalo con precaución.

---

## Ejecutar una migración específica

```bash
php artisan migrate --path=database/migrations/2026_11_03_120000_create_products_table.php
```

---

## Visualizar SQL sin ejecutar

### Simular migraciones

```bash
php artisan migrate --pretend
```

Laravel mostrará las consultas SQL que ejecutaría sin modificar la base de datos.

---

# Comandos Relacionados

## Crear Modelo + Migración + Controlador Resource

```bash
php artisan make:model Product -mcr
```

Opciones:

| Opción | Descripción |
|----------|----------|
| `-m` | Crear migración |
| `-c` | Crear controlador |
| `-r` | Crear controlador Resource |

### Forma extendida

```bash
php artisan make:model Product --migration --controller --resource
```

---

# Uso con Docker

## Docker

```bash
docker exec -it laravel_app php artisan migrate
```

## Docker Compose

```bash
docker compose exec app php artisan migrate
```

## Laravel Sail

```bash
./vendor/bin/sail artisan migrate
```

---

# Flujo Recomendado en Desarrollo

### Crear migración

```bash
php artisan make:migration create_products_table
```

### Revisar código de la migración

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->timestamps();
});
```

### Ejecutar migraciones

```bash
php artisan migrate
```

### Verificar estado

```bash
php artisan migrate:status
```

---

# Resumen Rápido

| Acción | Comando |
|----------|----------|
| Crear migración | `php artisan make:migration nombre_migracion` |
| Ejecutar migraciones | `php artisan migrate` |
| Ejecutar en producción automática | `php artisan migrate --force` |
| Ver estado | `php artisan migrate:status` |
| Revertir último lote | `php artisan migrate:rollback` |
| Revertir varios lotes | `php artisan migrate:rollback --step=n` |
| Revertir todo | `php artisan migrate:reset` |
| Rehacer todas | `php artisan migrate:refresh` |
| Borrar y recrear tablas | `php artisan migrate:fresh` |
| Ejecutar seeders | `php artisan migrate:fresh --seed` |
| Simular SQL | `php artisan migrate --pretend` |