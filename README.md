# Estarword API

Backend Laravel que expone CRUD de naves, listados dinámicos y gestión de mantenimientos con control de acceso por roles mediante Sanctum.

## Requisitos

- PHP 8.2+
- Composer
- curl y jq
- Opcional: definir `CLOUDINARY_URL` si quieres probar la subida de imágenes

## Puesta en marcha rápida

```bash
./setup.sh
```

El script instala dependencias, prepara `.env`, ejecuta `migrate:fresh --seed` y pregunta si deseas lanzar las pruebas y llamadas de verificación:

- Responde `s` para ejecutar los curl de comprobación, los tests (`MaintenanceTest`, `ApiCrudTest`)
- Pulsa Enter para omitir esa fase y quedarte sólo con la inicialización.

Comandos alternativos:

- `./setup.sh migrate` — prepara `.env` y ejecuta `migrate:fresh --seed`.
- `./setup.sh test` — inicializa, arranca servidor, ejecuta pruebas y lo detiene.
- `./setup.sh serve` / `./setup.sh stop` — arrancar o parar el servidor (`php artisan serve`).

Tras `setup.sh` o `migrate`, abre `http://127.0.0.1:8000` para usar la landing que muestra tokens demo y un formulario de subida a `/api/users/{id}/image`. Si necesitas hot reload del frontend (el socket de Vite que usa la welcome page), ejecuta en otra terminal:

```bash
npm run dev -- --host
```

Esto expone el servidor de Vite en `http://127.0.0.1:5173` y el cliente se conecta automáticamente a ese socket para HMR.

### Llamadas de ejemplo con curl

Suponiendo que ya tienes un token (por ejemplo el de admin), puedes probar los endpoints principales:

```bash
# Listar naves (viewer)
curl -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     http://127.0.0.1:8000/api/naves

# Crear nave (admin)
curl -X POST http://127.0.0.1:8000/api/naves \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"planet_id":1,"name":"Nave CLI","model":"CLI-1","crew":4,"passengers":10,"type":"cargo"}'

# Alta de mantenimiento (manager)
curl -X POST http://127.0.0.1:8000/api/naves/1/maintenances \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN_MANAGER" \
     -d '{"date_planned":"2026-01-01 10:00:00","description":"Chequeo CLI","cost":1200}'

# Subir imagen de usuario (admin o el propio usuario)
curl -X POST http://127.0.0.1:8000/api/users/1/image \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -F "image=@ruta/a/tu_imagen.png"
```

## Arquitectura y modelos

- `User` (`role`, `image_url`) — autenticado con Sanctum; roles `admin`, `manager`, `user`.
- `Ability` — tabla que mapea roles → habilidades (`admin:*`, `manager:maintainer,viewer`, `user:viewer`).
- `Spaceship` ↔︎ `Planet` (belongsTo). `Spaceship` tiene muchos `Maintenance` y relación many-to-many con `Pilot` vía `spaceship_pilots` (incluye fechas de asignación/salida).
- `Maintenance` — registra coste y fechas; método `calculateDurationCost()` con tests unitarios.
- `Pilot` (`image_url` con fallback local) — se asigna a naves mediante tabla pivote.

Los seeders crean 3 usuarios demo (roles admin/manager/user), planetas, naves, pilotos, mantenimientos y los tokens demo (`storage/app/private/demo_tokens.json`).

## Autenticación y tokens

Para obtener un token demo (o iniciar sesión manualmente) puedes llamar al endpoint de login:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/tokens \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

La respuesta incluye `token`, `abilities` y `role`. Usa el valor devuelto en la cabecera `Authorization: Bearer <token>`.

Habilidades disponibles:
- `viewer`: accesos de lectura.
- `maintainer`: crear/editar mantenimientos.
- `admin`: CRUD completo (incluye `viewer` y `maintainer`).

## Rutas principales y protecciones

Todas las rutas están bajo `auth:sanctum` salvo el login (`POST /api/auth/tokens`). Dentro se aplican grupos por habilidad:

| Habilidad | Rutas | Descripción |
|-----------|-------|-------------|
| `viewer` | `GET /api/naves`, `GET /api/naves/{id}`, `GET /api/naves/{id}/maintenances`, `GET /api/lists/*` | Lectura de naves, mantenimientos y listados dinámicos (naves sin piloto, pilotos activos, historial, mantenimientos entre fechas, etc.). |
| `maintainer` | `POST /api/naves/{id}/maintenances`, `PATCH /api/naves/{id}/maintenances/{maintenance}`, `POST /api/naves/{id}/pilots` | Crear y editar mantenimientos, asignar pilotos. |
| `admin` | `POST/PUT/PATCH/DELETE /api/naves`, `POST /api/pilots`, `DELETE /api/pilots/{id}`, `POST /api/users/{user}/image` | CRUD de naves, gestión de pilotos, subida de imágenes de usuarios (admins pueden actualizar cualquier usuario). |

Además, cualquier usuario autenticado puede subir su propia imagen (el controlador valida que sea el mismo usuario o un admin).

## Tests

- Unitarios: `MaintenanceTest` valida `calculateDurationCost`.
- De features: `ApiCrudTest` cubre registro de naves (admin), borrado de pilotos (admin) y actualización de mantenimientos (manager).
- `./setup.sh` (respuesta `s`) ejecuta ambos y resembrar para regenerar tokens demo.

## Flujo de uso sugerido

1. `./setup.sh` → responde `s` si quieres validar endpoints y dejar tokens listos.
2. Toma el token desde la landing o desde la consola.
3. Prueba los endpoints protegidos con curl o Postman usando las habilidades adecuadas.
4. Itera sobre el código; `php artisan migrate:fresh --seed` resetea la base y tokens cuando lo necesites.

