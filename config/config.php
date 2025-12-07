<?php
/**
 * Configuración general del sistema
 * Sistema de Control Escolar
 */

// Configuración de la sesión
session_start();

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); // Puerto estándar de MySQL, cambiar a 8080 si tu MySQL está configurado así
define('DB_NAME', 'sistema_escolar');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración del sistema
define('SITE_NAME', 'Sistema de Control Escolar');
// CAMBIO: URL actualizada de localhost:8080/sistema_escolar/ a https://tarea.site/
define('SITE_URL', 'https://tarea.site/');
define('ADMIN_EMAIL', 'admin@escuela.com');

// Configuración de archivos
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Configuración de paginación
define('RECORDS_PER_PAGE', 10);

// Incluir archivos necesarios
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/database.php';
?>

