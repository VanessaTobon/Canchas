<?php
// Definir constantes de conexión si no están definidas
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'reserva_canchas');

try {
    // Configurar MySQLi para mostrar errores como excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Crear conexión
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Configurar codificación
    $conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("❌ Error de conexión a la base de datos: " . $e->getMessage());
    exit('⚠️ Error de conexión. Intenta más tarde.');
}
?>
