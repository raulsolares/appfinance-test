<?php
/**
 * Configuración de Base de Datos y Rutas Dinámicas
 */

// Parámetros de conexión (Cámbialos por tus datos de cPanel)
define('DB_HOST', 'localhost');
define('DB_NAME', 'kxussecn_finappdw');
define('DB_USER', 'kxussecn_dwfinappusr');
define('DB_PASS', 'yAKkbGkqY3v$W3lko!6SIgxr%');

// Detección dinámica de la URL base para portabilidad
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$base_dir = str_replace(basename($script), '', $script);
$root_url = $protocol . $host . $base_dir;

// Normalizar ROOT_URL para que siempre termine en / si es subcarpeta, o no si es raíz pública
define('ROOT_URL', rtrim($root_url, '/') . '/');

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // En producción podrías redirigir a una página de error o loguear
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
