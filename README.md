# Estructura de ramas

## main
- Rama principal / producción.
- Solo se actualiza cuando una versión está lista para salir a producción.
- Debe estar siempre estable.

## dev
- Rama de desarrollo.
- Aquí se trabaja día a día.
- Se hacen las pruebas antes de pasar algo a `main`.

### Flujo de trabajo recomendado
1. Se trabaja en la rama `dev`.
2. Cuando una funcionalidad está lista y probada, se hace un **Pull Request** de `dev` → `main`.
3. Se revisa y se fusiona.
4. Se crea un **tag** de versión en `main`.
