# TurismoApp — Caso "Zonas Turísticas"

Plataforma web de asesoría turística peatonal vinculada a estaciones ferroviarias. El turista marca sus preferencias, elige una estación y obtiene un informe consolidado con las zonas turísticas a las que puede llegar caminando (ruta de ida y vuelta, tiempo estimado y dificultad), el pronóstico del clima y los horarios, tiempos de viaje y precios del tren. Integra los datos de PeruRail, SENAMHI y Travel Group Perú.

El detalle de requerimientos, arquitectura y convenciones está en el ERS y en [PLAN_DE_DESARROLLO.md](PLAN_DE_DESARROLLO.md).

## Tecnologías

| Componente | Herramienta |
|---|---|
| Backend | PHP 8.3+ y Laravel 13 |
| Interfaz | Livewire 4, Flux UI (edición gratuita) y Tailwind CSS 4 |
| Mapas | Leaflet con OpenStreetMap |
| Informes PDF | barryvdh/laravel-dompdf |
| Base de datos | MySQL 8 / MariaDB en desarrollo y producción; SQLite en memoria solo para las pruebas automatizadas |
| Pruebas | Pest 4 (sobre PHPUnit) |

## Manual de instalación

### Requisitos

- PHP 8.3 o superior con las extensiones `pdo_mysql`, `mbstring`, `gd`, `intl` y `zip`.
- MySQL 8 o MariaDB.
- Composer 2.
- Node.js 20 o superior y npm.

### Instalación en desarrollo

Si su usuario de MySQL tiene contraseña, colóquela en `DB_PASSWORD` del archivo `.env` antes de migrar.

```bash
git clone <url-del-repositorio> turismoapp
cd turismoapp
composer install
npm install
cp .env.example .env
php artisan key:generate
mysql -u root -p -e "CREATE DATABASE turismoapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

La aplicación queda disponible en http://localhost:8000. Para trabajar con recarga automática de estilos use `composer run dev` en lugar de `php artisan serve`.

`php artisan migrate:fresh --seed` recrea la base de datos con los catálogos y los datos de demostración.

### Usuarios de demostración

Todos usan la contraseña `password`.

| Correo | Perfil |
|---|---|
| `turista@turismoapp.test` | Usuario final (turista) |
| `travelgroup@turismoapp.test` | Travel Group Perú |
| `admin@turismoapp.test` | Administrador MTC |

### Ver la base de datos

Con DBeaver, DataGrip, TablePlus o MySQL Workbench cree una conexión MySQL con los datos del `.env`:

| Dato | Valor |
|---|---|
| Host | `127.0.0.1` |
| Puerto | `3306` |
| Base de datos | `turismoapp` |
| Usuario / contraseña | `DB_USERNAME` / `DB_PASSWORD` |

El diagrama entidad-relación se genera desde la herramienta (DBeaver: pestaña *ER Diagram* de la base; DataGrip: *Diagrams → Show Diagram*). Desde la terminal:

```bash
mysql -u root -p turismoapp -e "SHOW TABLES"
php artisan db:table tb_zona_turistica
```

### Tareas programadas

La sincronización con PeruRail y SENAMHI (RF-13) y el respaldo diario de la base de datos (RNF-10) usan el programador de Laravel:

```bash
php artisan schedule:work
```

En un servidor se agrega una sola entrada de cron:

```cron
* * * * * cd /ruta/a/turismoapp && php artisan schedule:run >> /dev/null 2>&1
```

| Comando | Frecuencia | Qué hace |
|---|---|---|
| `php artisan sincronizacion:ejecutar` | Cada hora | Actualiza las fuentes cuya última sincronización superó la frecuencia configurada (24 h por defecto) |
| `php artisan respaldo:base-datos` | Diario, 02:00 | Genera un respaldo en `storage/app/private/respaldos` y conserva las últimas 7 copias |

### Producción (servidor Linux con Apache o Nginx)

1. Configure en `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, la conexión `DB_CONNECTION=mysql` con sus credenciales y las variables `MAIL_*` para la recuperación de contraseña (RF-04).
2. Ejecute `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `php artisan migrate --force --seed`, `php artisan storage:link` y `php artisan optimize`.
3. Apunte el sitio a la carpeta `public/`, active HTTPS con un certificado (RNF-13) y registre el cron del programador.
4. En producción los seeders solo cargan los catálogos; cree el primer administrador con `php artisan tinker` y asigne los demás perfiles desde el módulo de usuarios.

## Manual de uso

### Usuario final (turista)

1. **Crear cuenta** desde la página de inicio (o iniciar sesión).
2. **Planificar mi visita**: marque las categorías que le interesan (naturaleza, historia, aventura, gastronomía), elija la estación y pulse *Buscar zonas*. Se muestran el mapa, las zonas caminables con su ruta de ida y vuelta, el tiempo estimado y la dificultad, los trenes que llegan con su tiempo de viaje y precio, y el pronóstico del clima con su fecha de actualización.
3. **Guardar informe** y descargarlo en **PDF** o **HTML**. Los informes guardados quedan en *Mis informes*.

Si ninguna zona coincide, el sistema lo indica y sugiere ampliar los criterios. Si el SENAMHI no entregó un pronóstico vigente, se muestra el último disponible con un aviso.

### Travel Group Perú

- **Zonas turísticas**: registrar, editar, dar de baja o reactivar zonas con nombre, descripción, categoría, ubicación, estación, distancia a pie, dificultad e imágenes. Cada carga queda en la bitácora.
- **Estaciones**: consulta en modo lectura.
- **Zonas por estación**: reporte de estaciones con sus zonas asignadas.

### Administrador MTC

- **Usuarios**: alta, baja y cambio de perfil.
- **Parámetros y categorías**: distancia máxima caminable, frecuencia de sincronización, velocidad de caminata y catálogo de categorías.
- **Sincronización**: estado de cada fuente, sincronización manual y bitácora con fecha, fuente, registros y resultado.
- **Horarios y precios**: corrección de horarios y precios o registro de servicios adicionales.
- **Reporte de uso**: estaciones y categorías más consultadas.

## Pruebas

```bash
php artisan test --compact
vendor/bin/pint --dirty
```
