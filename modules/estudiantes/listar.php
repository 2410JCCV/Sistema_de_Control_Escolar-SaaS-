<?php
/**
 * Listar Estudiantes - Redirección a la nueva interfaz
 * Sistema de Control Escolar
 */

// Redirigir a la nueva interfaz
header('Location: index.php' . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit();
?>