# GitFlow del proyecto

El desarrollo usa GitFlow con dos ramas permanentes, `main` y `dev`, y ramas temporales para cada cambio.

```
feature/* ──► dev ──► release/* ──► main (tag)
                              ▲         │
                              └── hotfix/* (vuelve también a dev)
```

## Ramas permanentes

### `main`

Producción. Solo recibe merges de `release/*` y `hotfix/*`. Cada merge que sale a producción lleva un tag de versión (`v1.0.0`, `v1.0.1`).

### `dev`

Integración. Aquí se juntan las funcionalidades terminadas. El día a día no se commitea directo en `dev`: entra por pull request desde una rama `feature/*`.

## Ramas temporales

| Rama | Sale de | Vuelve a | Cuándo |
| --- | --- | --- | --- |
| `feature/<nombre>` | `dev` | `dev` | Una funcionalidad o cambio nuevo. |
| `release/<versión>` | `dev` | `main` y `dev` | Preparar una versión: pruebas finales y ajustes menores. |
| `hotfix/<nombre>` | `main` | `main` y `dev` | Corregir un fallo que ya está en producción. |

Nombres en minúsculas y con guiones: `feature/login-usuarios`, `release/1.2.0`, `hotfix/error-pago`.

## Flujo de una funcionalidad

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

## Flujo de una versión

1. Cuando `dev` tiene lo que va a salir, crear `release/<versión>` desde `dev`.
2. En esa rama solo entran correcciones y ajustes de la versión (versión en archivos, textos, bugs encontrados al probar). Nada de funcionalidades nuevas.
3. Pull request de `release/*` hacia `main`.
4. Al fusionar, crear el tag en `main` (`v1.2.0`).
5. Fusionar la misma `release/*` de vuelta en `dev` para que no se pierdan los ajustes.
6. Borrar la rama `release/*`.

## Flujo de un hotfix

1. Crear `hotfix/<nombre>` desde `main`.
2. Corregir y probar.
3. Pull request hacia `main`, fusionar y etiquetar (`v1.2.1`).
4. Fusionar el mismo hotfix en `dev` (o en la `release/*` abierta, si hay una en curso).
5. Borrar la rama `hotfix/*`.

## Reglas

- `main` se mantiene siempre desplegable.
- No se commitea directo en `main` ni en `dev`.
- Un pull request apunta a una sola rama: `feature` → `dev`, `release` o `hotfix` → `main`.
- La vuelta de `release` y `hotfix` hacia `dev` es un segundo merge, para que la integración no se quede atrás de producción.
