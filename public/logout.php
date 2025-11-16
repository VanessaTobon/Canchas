<?php
// Asegurar que la sesión esté activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir todos los datos de sesión
$_SESSION = [];
session_unset();
session_destroy();

// Redirigir al inicio
header("Location: ../public/index.php");
exit;
?>
