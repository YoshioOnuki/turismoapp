# Plan de desarrollo — TurismoApp (caso "Zonas Turísticas")

Guía de trabajo del equipo. Resume cómo está armado el proyecto, las convenciones que seguimos y el orden en que construimos los requerimientos del ERS (Documento de Inicio de Proyecto, versión 1.1). Marquen las casillas a medida que avancen.

## 1. Stack y comandos

| Capa | Herramienta |
|---|---|
| Backend | PHP 8.3 y Laravel 13 |
| Interfaz | Livewire 4 (páginas multi-archivo en `resources/views/pages`), Flux UI edición gratuita y Tailwind CSS 4 |
| Base de datos | SQLite en desarrollo y pruebas; MySQL / MariaDB en producción |
| Pruebas | Pest 4 |
| Formato | Laravel Pint |

Comandos frecuentes:

| Comando | Para qué sirve |
|---|---|
| `composer run dev` | Levanta el servidor, Vite y los logs |
| `php artisan migrate:fresh --seed` | Recrea la base de datos con catálogos y datos de demostración |
| `php artisan test --compact` | Corre todas las pruebas |
| `vendor/bin/pint --dirty` | Formatea los archivos modificados |

Usuarios de demostración (todos con la clave `password`):

| Correo | Perfil |
|---|---|
| `admin@turismoapp.test` | Administrador MTC |
| `travelgroup@turismoapp.test` | Travel Group Perú |
| `turista@turismoapp.test` | Usuario final |

## 2. Convenciones

### 2.1 Arquitectura en capas

```
Componente Livewire  →  Service  →  Modelo Eloquent  →  Base de datos
(pantalla)              (lógica)     (tabla tb_*)
```

- **Componentes Livewire** (`resources/views/pages/...`): guardan el estado del formulario, validan la entrada con `rules()`, llaman a un service y convierten el resultado en mensajes o redirecciones. No consultan ni escriben la base de datos por su cuenta.
- **Services** (`app/Services`, uno por dominio: `AutenticacionService`, `ZonaTuristicaService`, ...): contienen las reglas de negocio. Se inyectan en el método del componente o del controlador y reciben datos ya validados, con tipos explícitos.
- **Excepciones de negocio** (`app/Exceptions/<Dominio>`): el service las lanza cuando una regla no se cumple (por ejemplo, `CredencialesInvalidasException`) y el componente las atrapa para mostrar el mensaje. Implementan `ShouldntReport`, porque son resultados esperados y no fallas del sistema.
- **Controladores**: solo para acciones HTTP que no son pantallas, como cerrar sesión.

### 2.2 Transacciones y manejo de errores

- Toda operación que **escribe** en la base de datos va dentro de `DB::transaction()` en el service. Los efectos secundarios (eventos, correos, notificaciones) se disparan después de confirmar la transacción.
- La validación va antes del `try`, con `$this->validate()`, para que Livewire muestre el error de cada campo.
- Cada llamada a un service desde un componente va dentro de `try/catch`:
  1. Primero se atrapan las excepciones de negocio, con un mensaje específico en el campo que corresponde.
  2. Al final, `catch (Throwable $excepcion)`: se registra con `report($excepcion)` y se muestra un mensaje general, sin detalles técnicos (RNF-04).

Ejemplo real (`resources/views/pages/auth/⚡iniciar-sesion/iniciar-sesion.php`):

```php
public function iniciarSesion(AutenticacionService $autenticacion): void
{
    $this->validate();

    try {
        $autenticacion->iniciarSesion($this->correo, $this->clave, $this->recordar, (string) request()->ip());
    } catch (CredencialesInvalidasException) {
        $this->addError('correo', __('auth.failed'));

        return;
    } catch (DemasiadosIntentosException $excepcion) {
        $this->addError('correo', __('auth.throttle', ['seconds' => $excepcion->segundos]));

        return;
    } catch (Throwable $excepcion) {
        report($excepcion);
        $this->addError('general', 'No pudimos iniciar tu sesión. Inténtalo de nuevo en unos minutos.');

        return;
    }

    $this->redirectIntended(route('inicio'), navigate: true);
}
```

### 2.3 Base de datos

- Tablas `tb_<entidad>` en singular. Cada campo empieza con el prefijo de tres letras de su tabla.
- Llave primaria `<prefijo>_codigo`. Llave foránea: prefijo propio + llave referenciada (`pre_usu_codigo`). Si una tabla referencia dos veces a otra, se agrega el rol (`hor_est_codigo_origen`, `hor_est_codigo_destino`).
- Fechas de auditoría `<prefijo>_fecha_creacion` y `<prefijo>_fecha_actualizacion` (constantes `CREATED_AT` y `UPDATED_AT` del modelo).
- Nada se borra físicamente: la baja es lógica con `<prefijo>_estado`.
- Los modelos declaran tabla y llave con `#[Table(name: 'tb_...', key: '..._codigo')]` y escriben las llaves de cada relación.
- Las tablas internas de Laravel (`sessions`, `cache`, `jobs`, `password_reset_tokens`, `migrations`, etc.) conservan sus nombres.
- Cada cambio de esquema es una migración nueva; no se editan migraciones que ya están en `main`.

| Tabla | Prefijo | Tabla | Prefijo |
|---|---|---|---|
| `tb_perfil` | `per_` | `tb_clima` | `cli_` |
| `tb_usuario` | `usu_` | `tb_horario` | `hor_` |
| `tb_categoria` | `cat_` | `tb_informe` | `inf_` |
| `tb_preferencia` | `pre_` | `tb_informe_categoria` | `ica_` |
| `tb_estacion` | `est_` | `tb_parametro` | `par_` |
| `tb_zona_turistica` | `zon_` | `tb_bitacora` | `bit_` |
| `tb_zona_imagen` | `zim_` | | |

### 2.4 Nombres

- El dominio va en español: modelos (`Usuario`, `ZonaTuristica`), services, métodos (`iniciarSesion`), rutas (`/iniciar-sesion`) y componentes (`pages::auth.registro`).
- Los casos de los enums van en TitleCase (`TipoPerfil::AdministradorMtc`).
- Las rutas que Laravel usa internamente conservan su nombre (`login`, `logout`); el resto va en español.

### 2.5 Interfaz

- Solo componentes de Flux UI edición gratuita (no Pro), con íconos de Heroicons.
- Textos en español (RNF-03), diseño responsive (RNF-01) y soporte de modo oscuro con variantes `dark:`.
- Cada pantalla es una página Livewire registrada con `Route::livewire()` y con título `#[Title('...')]`.

### 2.6 Pruebas

- Cada service y cada pantalla tienen pruebas en `tests/Feature` (Pest, nombres en español con `test('...')`).
- Casos mínimos por pantalla: flujo exitoso, validación, errores de negocio, error inesperado (service simulado que falla) y acceso sin permiso.
- Antes de cada commit: `vendor/bin/pint --dirty` y `php artisan test --compact` sin fallas.

### 2.7 Git

- Rama principal: `main`. Commits pequeños, en español y en imperativo ("Agrega...", "Corrige...").
- Recomendado: una rama por funcionalidad (por ejemplo, `feature/preferencias`) y un pull request hacia `main`.

### 2.8 Acceso por perfil (RF-03, RNF-12)

Cada perfil tiene su grupo de rutas en `routes/web.php`, protegido con `VerificarPerfil::permitir(...)`:

| Perfil | Prefijo | Nombres de ruta | Panel |
|---|---|---|---|
| Usuario final | `/turista` | `turista.*` | `turista.panel` |
| Travel Group Perú | `/travel-group` | `travel-group.*` | `travel-group.panel` |
| Administrador MTC | `/administracion` | `administracion.*` | `administracion.panel` |

- Un módulo nuevo se registra dentro del grupo de su perfil. Si lo usan varios perfiles, la ruta lleva `VerificarPerfil::permitir(TipoPerfil::TravelGroup, TipoPerfil::AdministradorMtc)`.
- Quien entra a la zona de otro perfil recibe un 403 ("Acceso denegado").
- Al iniciar sesión, cada usuario va al panel de su perfil (`TipoPerfil::rutaPanel()`).
- El panel muestra los módulos del perfil con `<x-tarjeta-modulo>`. Mientras un módulo no existe, la tarjeta dice "Próximamente"; cuando se construye, se le pasa `href` y la tarjeta se convierte en enlace.

## 3. Plan por etapas

Primero los RF de prioridad Alta (producto mínimo), luego los de prioridad Media y Baja.

### Etapa 0 — Base del proyecto

- [x] Repositorio en GitHub con la rama `main`.
- [x] Modelo de datos con prefijos (13 tablas), modelos, factories, seeders y pruebas.
- [x] ERS v1.1 alineado con el modelo de datos y el stack.
- [x] Zona horaria `America/Lima` y traducciones al español.

### Etapa 1 — Usuarios y accesos (RF-01 a RF-04)

- [x] RF-01: registro de turistas con correo y contraseña (`AutenticacionService::registrar`).
- [x] RF-02: inicio y cierre de sesión, con bloqueo de un minuto tras cinco intentos fallidos.
- [x] RF-03: acceso por perfil con `VerificarPerfil` y un panel para cada perfil con sus módulos (ver 2.8).
- [x] RF-04: recuperación de contraseña por correo, con solicitud segura, enlace temporal y formulario para definir una clave nueva. Cada entorno debe proporcionar sus credenciales `MAIL_*`; en desarrollo se usa el mailer `log`.

### Etapa 2 — Integración de datos (RF-11 a RF-15)

- [x] Un contrato por fuente (PeruRail y SENAMHI) con una implementación simulada, para conectar la real después sin tocar los módulos (RNF-16).
- [x] `SincronizacionService`: actualiza estaciones, horarios y clima con `updateOrCreate` por sus claves externas, dentro de transacciones independientes por fuente, y registra cada ejecución en `tb_bitacora` (RF-14).
- [x] Tarea programada diaria (RF-13), con la frecuencia tomada de `tb_parametro`.
- [x] Sincronización manual para el administrador (RF-15).
- [x] Si una fuente no responde, se conserva el último dato y se muestra su fecha (RNF-09).

### Etapa 3 — Administración (RF-16 a RF-20)

- [x] CRUD de zonas turísticas para Travel Group Perú, con imágenes y baja lógica (RF-16, RF-17).
- [x] Listado de estaciones en solo lectura para Travel Group Perú (RF-18).
- [x] Gestión de usuarios para el administrador: alta, baja y cambio de perfil (RF-19).
- [x] Parámetros generales y catálogo de categorías (RF-20).
- [x] Autorización por perfil con policies (RNF-12).

### Etapa 4 — Consulta del turista (RF-05 a RF-10)

- [x] Preferencias turísticas del usuario (RF-05).
- [x] Selección de una estación activa (RF-06).
- [x] Zonas filtradas por preferencias y por la distancia máxima caminable (RF-07), con aviso cuando no hay coincidencias (RF-09).
- [ ] Ruta de ida y vuelta: distancia total, tiempo según `velocidad_caminata` y dificultad (RF-08), con mapa en Leaflet.
- [ ] Trenes que llegan a la estación, con horario y precio (`Estacion::horariosDeLlegada`).
- [ ] Historial de búsquedas (RF-10).

### Etapa 5 — Informes (RF-21 a RF-24)

- [ ] Informe consolidado en pantalla, guardado en `tb_informe` (RF-21).
- [ ] Exportación a PDF y HTML (RF-22). Requiere aprobar una librería de PDF antes de instalarla.
- [ ] Reporte de estaciones y zonas asignadas para Travel Group Perú (RF-23).
- [ ] Reporte de uso para el MTC: estaciones y categorías más consultadas (RF-24).

### Etapa 6 — Calidad y entrega

- [ ] Revisar los RNF: tiempos de respuesta (RNF-05, RNF-07), HTTPS (RNF-13) y respaldo diario con siete copias (RNF-10).
- [ ] Plan de pruebas en el formato del curso, con los casos PR-xx del ERS.
- [ ] Cálculo de costos en el formato del curso.
- [ ] Manual de usuario y manual de instalación.
- [ ] Despliegue en un servidor Linux (Apache o Nginx) con MySQL.

## 4. Decisiones tomadas

- El informe lista los trenes que **llegan** a la estación seleccionada; los horarios son los mismos para todos los usuarios.
- La ruta peatonal usa la distancia que registra Travel Group Perú, así que no hace falta un servicio externo de rutas.
- Hasta donde sabemos, PeruRail y SENAMHI no publican una API abierta con estos datos, por eso las fuentes se simulan detrás de un contrato.
- Solo los turistas se registran solos; los perfiles de Travel Group Perú y del MTC los asigna el administrador.
- Toda dependencia nueva (librería de PDF, paquetes de mapas, etc.) se aprueba en equipo antes de instalarla.

## 5. Definición de terminado

Una funcionalidad está terminada cuando:

- cumple su RF y los RNF relacionados;
- su lógica está en un service, con transacciones en las escrituras;
- el componente maneja los errores con `try/catch` y muestra mensajes claros en español;
- tiene pruebas y toda la suite pasa;
- Pint no reporta cambios;
- el ERS sigue siendo coherente con lo construido.
