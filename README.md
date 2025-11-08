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

## Arquitectura y modelos

- `User` (`