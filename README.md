/**Sistema de Inventario con Seguimiento de Stock**/
Laravel 12 · Breeze · Spatie Roles/Permissions · AdminLTE · Bootstrap · PostgreSQL

Este proyecto es un Sistema de Inventario desarrollado con Laravel 12, usando Breeze para autenticación, Spatie para el control de roles/permisos y la plantilla administrativa AdminLTE.
La base de datos utilizada es PostgreSQL, ideal para sistemas con procesos simultáneos y registros de movimientos (kardex).
--------------------------------------------------------------
# Tecnologías Usadas
Componente	Descripción
Laravel 12	Framework Backend
Breeze	Autenticación básica
Spatie	Roles y permisos
AdminLTE 3	Panel administrativo
Bootstrap 5	Estilos del sistema
PostgreSQL	Base de datos
Composer / NPM	Dependencias

clonal el repositorio

ejecutar : composer install

configura Posgresql en .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE="sistema_inventario"
DB_USERNAME="db_user"
DB_PASSWORD="tu_password"

general la app_key
php artisan key:generate
