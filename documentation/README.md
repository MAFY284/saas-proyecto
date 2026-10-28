# Estructura de ramas y flujo de trabajo

El desarrollo sale de `main` y vuelve a `main`. No se trabaja sobre una rama `dev`.

## `main`

Producción. Solo contiene código estable y listo para desplegarse. Cuando corresponde, se crea un tag de versión sobre esta rama.

## Ramas de trabajo

Son ramas locales creadas a partir de `main`:

| Rama | Cuándo | Ejemplo |
| --- | --- | --- |
| `function/<nombre>` | Una funcionalidad nueva. | `function/agregar-login`, `function/modulo-red-social` |
| `fix/<nombre>` | Corregir un bug. | `fix/error-carga-imagenes`, `fix/validacion-formulario` |

Nombres en minúsculas y con guiones.

## Flujo de trabajo

1. Crear una rama local a partir de `main` (`function/...` o `fix/...`).
2. Desarrollar y probar en esa rama.
3. Fusionar hacia `main` cuando el cambio está listo, mediante pull request o merge.
4. Eliminar la rama local una vez fusionada.
5. Crear un tag de versión sobre `main` cuando corresponda.

```bash
git checkout main
git pull
git checkout -b function/agregar-login
# ... commits ...
git push -u origin function/agregar-login
```

La estrategia de ramas se irá refinando conforme avance el proyecto.
