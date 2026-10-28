# Estructura de ramas y flujo de trabajo

## Ramas principales

- **main**  
  Rama de producción. Solo contiene código estable y listo para desplegarse.

## Ramas de trabajo (locales)

Ya no se trabaja directamente sobre una rama `dev`.  
El desarrollo se realiza en **ramas locales** creadas a partir de `main`, usando la siguiente nomenclatura:

- `function/nombre-de-la-funcionalidad`  
  Ejemplo: `function/agregar-login`, `function/modulo-red-social`

- `fix/nombre-del-bug`  
  Ejemplo: `fix/error-carga-imagenes`, `fix/validacion-formulario`

### Flujo de trabajo

1. Se crea una rama local a partir de `main` (`function/...` o `fix/...`).
2. Se realiza el desarrollo y las pruebas en esa rama.
3. Cuando el cambio está listo, se fusiona hacia `main` (mediante Pull Request o merge).
4. Se elimina la rama local una vez fusionada.
5. Cuando corresponda, se crea un **tag de versión** sobre `main`.

> Nota: La estrategia de ramas se irá refinando conforme avance el proyecto.
