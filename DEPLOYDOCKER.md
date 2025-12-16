# Sistema Almacén (Laravel) — Guía de Deploy con Docker

Esta guía te explica paso a paso cómo desplegar el proyecto en Docker tanto localmente como en un servidor Linux (Ubuntu/Debian). Se basa en tener 4 archivos clave en la raíz del proyecto: `docker-compose.yml`, `Dockerfile`, `entrypoint.sh` y `nginx.conf`.

## Arquitectura Docker
- **sistema-almacen-laravel**: Contenedor PHP-FPM (Laravel) construido desde el `Dockerfile` (stage `app`).
- **sistema-almacen-web**: Contenedor Nginx construido desde el `Dockerfile` (stage `web`) que sirve `/public` y pasa PHP a `sistema-almacen-laravel:9000`.
- **sistema-almacen-db**: Contenedor MySQL 8 con volumen `db_data` para persistencia.

## Prerrequisitos
- Docker y Docker Compose Plugin ya instalados y configurados por el administrador del servidor.
- Tu usuario "grupo15" debe estar habilitado para ejecutar Docker sin sudo (pertenecer al grupo "docker").
- Acceso al servidor por SSH.

## Variables de entorno (docker-compose.yml)
En el servicio `sistema-almacen-laravel` ya están definidas:
- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=http://181.188.147.150` (ajústala si vas a otro host)
- `DB_HOST=sistema-almacen-db`, `DB_DATABASE=sistema_almacen`, `DB_USERNAME=sistema_user`, `DB_PASSWORD=sistema_pass`

Puedes añadir más variables (mail, servicios externos, etc.) en el bloque `environment` del compose.

## Deploy local (opcional)
1. Construye e inicia los contenedores:
   ```bash
   docker compose up -d --build
   ```
2. Abre la app:
   - http://localhost (usa el puerto 80 del host); si está ocupado, cambia el mapeo de puertos del servicio `sistema-almacen-web` (por ejemplo, `8080:80`).
3. Logs y estado:
   ```bash
   docker ps
   docker logs -f sistema-almacen-laravel
   docker logs -f sistema-almacen-web
   ```

## Deploy en servidor (Ubuntu/Debian)
1. Conéctate por SSH:
   ```bash
   ssh grupo15@181.188.147.150
   ```
2. Verifica que puedes usar Docker sin sudo:
   ```bash
   docker --version
   docker compose version
   docker ps
   ```
   - Si ves un error de permisos (por ejemplo: "permission denied" sobre `/var/run/docker.sock`), solicita al administrador que te agregue al grupo `docker`.
3. Copia el proyecto al servidor (desde tu PC):
   ```bash
   scp -r "c:/laragon/www/sistema-almacen" grupo15@181.188.147.150:/home/grupo15/
   ```
4. En el servidor, trabaja dentro de tu HOME:
   ```bash
   cd /home/grupo15/sistema-almacen
   ```
5. Ajusta variables de entorno si corresponde (por ejemplo `APP_URL` en `docker-compose.yml`).
6. Construye e inicia los contenedores:
   ```bash
   docker compose up -d --build
   ```
7. Abre la app en el navegador:
   - http://181.188.147.150
   - Si no carga, coordina con el administrador para validar que el firewall del servidor permita HTTP (puerto 80) y que Docker esté correctamente instalado. Al no tener privilegios de administrador, no podrás abrir puertos ni gestionar el firewall desde tu usuario.
   

## Post-deploy (automático en entrypoint)
El `entrypoint.sh` realiza:
- Preparación de `.env` si no existe
- Espera a la base de datos
- `php artisan migrate --force`
- `php artisan storage:link`
- Cacheo de configuración, rutas y vistas

Si necesitas sembrar datos:
```bash
docker compose exec sistema-almacen-laravel php artisan db:seed --force
```

## Mantenimiento
- Actualizar imágenes y contenedores:
  ```bash
  docker compose build && docker compose up -d
  ```
- Ver logs:
  ```bash
  docker logs -f sistema-almacen-laravel
  docker logs -f sistema-almacen-web
  docker logs -f sistema-almacen-db
  ```
- Detener la pila:
  ```bash
  docker compose down
  ```
- Backup de BD (ejemplo):
  ```bash
  docker exec -i sistema-almacen-db mysqldump -usistema_user -psistema_pass sistema_almacen > backup.sql
  ```

## Seguridad y buenas prácticas
- Cambia `DB_USERNAME` y `DB_PASSWORD` por credenciales robustas.
- No expongas MySQL públicamente (el compose no mapea puertos de DB por defecto).
- Para HTTPS y reglas de firewall, coordina con el administrador (al no tener sudo, no podrás configurar ufw ni certificados en el host).
- Considera usar HTTPS (reverse proxy o Nginx con TLS en el host/traefik).

## Archivos clave
- `docker-compose.yml`: define servicios `sistema-almacen-laravel`, `sistema-almacen-web`, `sistema-almacen-db`.
- `Dockerfile`: stages `node_builder`, `app` (PHP-FPM) y `web` (Nginx).
- `entrypoint.sh`: prepara la app y levanta PHP-FPM.
- `nginx.conf`: sirve `/public` y envía PHP a `sistema-almacen-laravel:9000`.

## Troubleshooting rápido
- Extensiones PHP faltantes: añade paquetes en el `Dockerfile` (se incluyeron `pdo_mysql`, `gd`, `zip`, `intl`, `opcache`, etc.).
- Permisos: verifica `storage` y `bootstrap/cache` (el entrypoint los ajusta).
- Caches: limpia si es necesario:
  ```bash
  docker compose exec sistema-almacen-laravel php artisan optimize:clear
  ```
- Conexión a DB: revisa credenciales y que el contenedor `sistema-almacen-db` esté arriba.



