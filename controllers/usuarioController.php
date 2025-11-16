<?php
require_once '../config/config.php';
require_once '../models/usuarioModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Registrar nuevo usuario.
 *
 * @param string $nombre
 * @param string $email
 * @param string $password
 * @param string $rol ('admin' | 'usuario')
 * @param string $telefono
 * @param string $direccion
 * @return bool
 */
function registrarUsuario($nombre, $email, $password, $rol, $telefono, $direccion)
{
    global $conexion;

    // Verificar si el correo ya existe
    if (existeEmail($conexion, $email)) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO tbl_usuarios (nombre, email, password, rol, telefono, direccion)
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);

    if (!$stmt) {
        error_log("Error al preparar la consulta: " . $conexion->error);
        return false;
    }

    $stmt->bind_param("ssssss", $nombre, $email, $hash, $rol, $telefono, $direccion);
    return $stmt->execute();
}

/**
 * Iniciar sesión de usuario.
 *
 * @param string $email
 * @param string $password
 * @return bool
 */
function iniciarSesion($email, $password)
{
    global $conexion;

    $query = "SELECT id_usuario, nombre, password, rol FROM tbl_usuarios WHERE email = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return false;

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['usuario'] = [
                'id_usuario' => $row['id_usuario'],
                'nombre'     => $row['nombre'],
                'rol'        => $row['rol']
            ];
            return true;
        }
    }

    return false;
}

/**
 * Cerrar sesión del usuario.
 *
 * @return array
 */
function cerrarSesion()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_unset();
    session_destroy();
    return ["success" => "Sesión cerrada correctamente."];
}
