# saas-proyecto

Repositorio del SaaS. Agrupa la aplicación web, el entorno para levantarla en local y la propuesta de arquitectura.

## Contenido

```
saas-proyecto/
├── project/          Aplicación Laravel
├── installation/     Entorno Docker (web, base de datos, phpMyAdmin)
├── documentation/    Propuesta de arquitectura y GitFlow
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

- `Propuesta_Arquitectura_SaaS.docx` — propuesta de arquitectura del SaaS.
- `README.md` — GitFlow que se usa en el desarrollo (`main`, `dev`, `feature`, `release`, `hotfix`).
