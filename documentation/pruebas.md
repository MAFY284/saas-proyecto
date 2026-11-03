# Pruebas de la aplicación

Pruebas automáticas de la aplicación Laravel en `project/`. Comprueban el comportamiento visible (inicio, sesión y CRUD de usuarios) y la lógica del modelo `User`. No usan la base MariaDB del entorno Docker.

La descripción de cada prueba es el nombre del método. PHPUnit quita el prefijo `test_` y cambia los guiones bajos por espacios.

## Cómo ejecutarlas

Con el contenedor `laravel-web` en marcha, desde la raíz del repositorio:

```bash
docker exec -w /var/www/html laravel-web php artisan test
```

Una sola clase:

```bash
docker exec -w /var/www/html laravel-web php artisan test tests/Feature/UserManagementTest.php
```

Dentro del contenedor, en `/var/www/html`, basta con `php artisan test`.

## Qué se tuvo en cuenta

- Una prueba unitaria cubre lógica que no pasa por una ruta: iniciales, contraseña, estado activo y avatar.
- Una prueba de funcionalidad llama a la ruta como lo haría el navegador.
- Cada restricción del alta de usuario se envía sola. Si el dato es inválido, no se inserta ninguna fila.
- Los formularios no responden 422. Si la validación falla, Laravel redirige atrás y deja el mensaje en la sesión.
- La suite usa SQLite en memoria (`phpunit.xml`). No toca los usuarios reales.
- La migración que hace `ALTER TABLE ... MODIFY` es de MariaDB. En SQLite ese paso se omite para poder crear las tablas.
- Las vistas cargan Vite. En pruebas ese manifiesto no hace falta y queda desactivado en `tests/TestCase.php`.

## Unitarias

Archivo: `project/tests/Unit/UserTest.php`.

| Método | Qué comprueba |
| --- | --- |
| `test_las_iniciales_salen_del_nombre_visible` | «Ana Ruiz» produce `AR`. |
| `test_un_solo_nombre_deja_una_inicial` | Un nombre, aunque tenga espacios, produce una sola letra. |
| `test_sin_nombre_la_inicial_es_un_signo_de_interrogacion` | Un nombre vacío produce `?`. |
| `test_la_contrasena_se_guarda_hasheada` | El valor guardado no es el texto plano y `Hash::check` lo reconoce. |
| `test_is_active_se_lee_como_booleano` | `1` se lee como verdadero y `0` como falso. |
| `test_sin_avatar_no_hay_url` | Sin foto, `avatarUrl()` es nulo. |
| `test_actualizar_el_avatar_reemplaza_el_archivo_anterior` | La foto nueva queda en el disco `public` y la anterior se borra. |

`project/tests/Unit/ExampleTest.php` es el ejemplo de Laravel (`true` es `true`). No describe la aplicación.

## Inicio

Archivo: `project/tests/Feature/HomeTest.php`.

| Método | Qué comprueba |
| --- | --- |
| `test_el_inicio_indica_que_no_hay_sesion` | `/` muestra «No has iniciado sesión» y no muestra el menú Usuarios. |
| `test_el_inicio_saluda_al_usuario_autenticado` | Con sesión, `/` muestra el nombre, Usuarios y Cerrar sesión. |
| `test_la_ruta_dashboard_redirige_al_inicio` | `/dashboard` redirige a `/`. |

## Sesión y registro

Archivo: `project/tests/Feature/AuthenticationTest.php`.

| Método | Qué comprueba |
| --- | --- |
| `test_el_login_correcto_entra_y_vuelve_al_inicio` | Email y contraseña correctos inician sesión y redirigen a `/`. |
| `test_el_login_incorrecto_no_inicia_sesion` | Una contraseña mala vuelve al login, muestra el error y no autentica. |
| `test_cerrar_sesion_vuelve_al_mensaje_de_invitado` | `POST /logout` cierra la sesión y `/` vuelve al mensaje de invitado. |
| `test_el_registro_crea_el_usuario_e_inicia_sesion` | El registro parte el nombre en nombre y apellido, crea la fila e inicia sesión. |

La contraseña de los usuarios creados con `User::factory()` es `password`.

## Usuarios

Archivo: `project/tests/Feature/UserManagementTest.php`.

| Método | Qué comprueba |
| --- | --- |
| `test_un_invitado_no_entra_al_crud_de_usuarios` | `/users` y `/users/create` redirigen al login. |
| `test_se_puede_crear_un_usuario_con_los_campos_de_la_tabla` | El alta guarda nombre, apellido, nombre visible, email, teléfono, zona horaria, tipo, estado activo y contraseña hasheada. |
| `test_crear_usuario_rechaza_cada_restriccion_de_campo` | Cada dato inválido redirige al formulario, deja el error y no inserta una fila. |
| `test_editar_sin_password_conserva_la_contrasena_y_actualiza_los_demas_campos` | La contraseña vacía no cambia el hash. Teléfono y estado activo sí se actualizan. |
| `test_se_puede_eliminar_otro_usuario_pero_no_la_propia_cuenta` | Borrar la cuenta propia muestra error y la conserva. Borrar otro usuario lo elimina. |

### Restricciones del alta

Casos dentro de `test_crear_usuario_rechaza_cada_restriccion_de_campo`:

| Campo | Dato inválido | Mensaje esperado |
| --- | --- | --- |
| Nombre | Vacío | El nombre es obligatorio. |
| Apellido | Vacío | El apellido es obligatorio. |
| Nombre visible | Vacío | El nombre visible es obligatorio. |
| Nombre, apellido y nombre visible | Más de 255 caracteres | Error en ese campo. |
| Email | Vacío | El email es obligatorio. |
| Email | Texto que no es un correo | El email no es válido. |
| Email | Correo ya registrado | El email ya está en uso. |
| Teléfono | Más de 50 caracteres | El teléfono no puede superar 50 caracteres. |
| Zona horaria | Vacía | La zona horaria es obligatoria. |
| Zona horaria | Valor que no existe | La zona horaria no es válida. |
| Usuario activo | Ausente o un texto que no es booleano | Error en el campo. |
| Password | Vacío | El password es obligatorio. |
| Password | Menos de 8 caracteres | El password debe tener al menos 8 caracteres. |
| Tipo de usuario | Id que no existe | El tipo de usuario no es válido. |
| Idioma | Id que no existe | El idioma no es válido. |
| Foto | Un PDF | El archivo debe ser una imagen. |
| Foto | Imagen de más de 2 MB | La imagen no puede superar 2 MB. |

`project/tests/Feature/ExampleTest.php` solo comprueba que `/` responde 200. Es el ejemplo que trae Laravel.

## Qué no cubren

- El permiso de ejecución que Docker aplica a `artisan` y a los `.gitignore` de `storage/` y `bootstrap/cache`.
- El contenido de la base MariaDB local.
- Que la cookie de sesión lleve la marca `Secure`. En local la aplicación se sirve por HTTP y esa marca no está activada.
