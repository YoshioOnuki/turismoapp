<?php

return [
    'conexion' => env('BACKUP_DB_CONNECTION'),
    'disco' => env('BACKUP_DISK', 'local'),
    'directorio' => env('BACKUP_DIRECTORY', 'respaldos'),
    'copias' => (int) env('BACKUP_RETENTION_COPIES', 7),
    'tiempo_limite' => (int) env('BACKUP_TIMEOUT', 300),
    'mysqldump' => env('MYSQLDUMP_BINARY', 'mysqldump'),
];
