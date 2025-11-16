<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? null;
$nombreUsuario = $usuario['nombre'] ?? 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) : 'Reserva de Canchas'; ?></title>
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../assets/css/estilos.css">

    <!-- Font Awesome (Íconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Funciones JS -->
    <script src="../assets/js/funciones.js" defer></script>
    
</head>
<body>

<header>
    <nav class="navbar">
        <h1>Reserva de Canchas</h1>
        <div class="usuario-info">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($nombreUsuario); ?></span>
        </div>
    </nav>
</header>

<?php include 'navbar.php'; ?>
