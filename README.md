# saas-proyecto

Repositorio del SaaS. Agrupa la aplicación web, el entorno para levantarla en local y la propuesta de arquitectura.

## Contenido

```
saas-proyecto/
├── project/          Aplicación Laravel
├── installation/     Entorno Docker (web, base de datos, phpMyAdmin)
├── documentation/    Propuesta de arquitectura (dos máquinas) y GitFlow
├── Documents/        Propuesta revisada (una máquina) y mapa de entidades
└── README.md
```

### `project/`

Aplicación web en **Laravel 13** (PHP 8.3+) con **Vite** y **Tailwind CSS 4**. Es el código que se desarrolla y se despliega.

| Carpeta | Qué hay |
| --- | --- |
| `app/` | Código de la aplicación: modelos, controladores HTTP y proveedores. |
| `routes/` | Rutas web (`web.php`) y comandos de consola (`console.php`). |
| `resources/` | Vistas Blade, CSS y JavaScript de entrada para Vite. |
| `database/` | Migraciones, seeders, factories y la base SQLite de desarrollo local. |
| `config/` | Configuración de la app, base de datos, sesión, colas, correo y caché. |
| `public/` | Punto de entrada de Apache (`index.php`) y assets compilados. |
| `tests/` | Pruebas unitarias y de funcionalidad (PHPUnit). |
| `storage/` | Logs, caché, sesiones y archivos subidos. |

`composer.json` y `package.json` definen las dependencias de PHP y de frontend. `vendor/` y `node_modules/` se generan al instalar; no forman parte del código fuente.

### `installation/`

Entorno local con Docker Compose. El servicio web monta `../project` en `/var/www/html`.

| Pieza | Qué hace |
| --- | --- |
| `docker-compose.yaml` | Define tres servicios: `web`, `db` y `phpmyadmin`. |
| `web/Dockerfile` | Imagen PHP 8.4 + Apache, con Composer y Node (por defecto 22) para Vite. |
| `web/docker-entrypoint.sh` | Al arrancar espera a MariaDB, crea `.env` si falta, alinea la conexión a MySQL, genera `APP_KEY`, corre migraciones y compila los assets si aún no existen. |
| `web/000-default.conf` | Virtual host de Apache con `mod_rewrite` hacia `public/`. |
| `.env` | `HOST_UID`, `HOST_GID` y `NODE_VERSION` para que los archivos del contenedor coincidan con el usuario del host. |

Puertos en el host:

- **8080** — aplicación (`http://localhost:8080`)
- **5173** — servidor de Vite
- **8081** — phpMyAdmin

La base de datos es **MariaDB 10.6**. El volumen `db_data` guarda los datos entre reinicios. En el primer arranque el entrypoint apunta Laravel a esa base (`laraveldb`).

Para levantarlo:

```bash
cd installation
docker compose up --build
```

### `documentation/`

- `Propuesta_Arquitectura_SaaS.docx` — propuesta de arquitectura con dos máquinas virtuales (producción y pruebas).
- `README.md` — el mismo GitFlow (`main`, `dev`, `feature/*`, `release/*`, `hotfix/*`).

### `Documents/`

- `Propuesta_Arquitectura_SaaS.docx` — propuesta de arquitectura revisada: una máquina virtual con contenedores de producción y de desarrollo.
- `mapa_entidades_saas.html` — mapa interactivo de entidades del SaaS.

## GitFlow

El desarrollo usa GitFlow: dos ramas permanentes, `main` y `dev`, y ramas temporales para cada cambio.

```
feature/* ──► dev ──► release/* ──► main (tag)
                              ▲         │
                              └── hotfix/* (vuelve también a dev)
```

### Ramas permanentes

#### `main`

Producción. Solo recibe merges de `release/*` y `hotfix/*`. Cada merge que sale a producción lleva un tag de versión (`v1.0.0`, `v1.0.1`).

#### `dev`

Integración. Aquí se juntan las funcionalidades terminadas. El día a día no se commitea directo en `dev`: entra por pull request desde una rama `feature/*`.

### Ramas temporales

| Rama | Sale de | Vuelve a | Cuándo |
| --- | --- | --- | --- |
| `feature/<nombre>` | `dev` | `dev` | Una funcionalidad o cambio nuevo. |
| `release/<versión>` | `dev` | `main` y `dev` | Preparar una versión: pruebas finales y ajustes menores. |
| `hotfix/<nombre>` | `main` | `main` y `dev` | Corregir un fallo que ya está en producción. |

Nombres en minúsculas y con guiones: `feature/login-usuarios`, `release/1.2.0`, `hotfix/error-pago`.

### Flujo de una funcionalidad

1. Partir de `dev` actualizada y crear `feature/<nombre>`.
2. Desarrollar y probar en esa rama.
3. Abrir un pull request hacia `dev`.
4. Revisar, fusionar y borrar la rama `feature/*`.

```bash
git checkout dev
git pull
git checkout -b feature/login-usuarios
# ... commits ...
git push -u origin feature/login-usuarios
```

### Flujo de una versión

1. Cuando `dev` tiene lo que va a salir, crear `release/<versión>` desde `dev`.
2. En esa rama solo entran correcciones y ajustes de la versión (versión en archivos, textos, bugs encontrados al probar). Nada de funcionalidades nuevas.
3. Pull request de `release/*` hacia `main`.
4. Al fusionar, crear el tag en `main` (`v1.2.0`).
5. Fusionar la misma `release/*` de vuelta en `dev` para que no se pierdan los ajustes.
6. Borrar la rama `release/*`.

### Flujo de un hotfix

1. Crear `hotfix/<nombre>` desde `main`.
2. Corregir y probar.
3. Pull request hacia `main`, fusionar y etiquetar (`v1.2.1`).
4. Fusionar el mismo hotfix en `dev` (o en la `release/*` abierta, si hay una en curso).
5. Borrar la rama `hotfix/*`.

### Reglas

- `main` se mantiene siempre desplegable.
- No se commitea directo en `main` ni en `dev`.
- Un pull request apunta a una sola rama: `feature` → `dev`, `release` o `hotfix` → `main`.
- La vuelta de `release` y `hotfix` hacia `dev` es un segundo merge, para que la integración no se quede atrás de producción.
