<?php
require_once '../config/config.php';
require_once '../models/UsuarioModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Crear instancia del modelo UNA sola vez
$usuarioModel = new UsuarioModel();

/**
 * Registrar nuevo usuario.
 *
 * @param string $nombre
 * @param string $email
 * @param string $password
 * @param string $rol
 * @param string $telefono
 * @param string $direccion
 * @return bool
 */
function registrarUsuario($nombre, $email, $password, $rol, $telefono, $direccion)
{
    global $usuarioModel;  // <-- Usamos el modelo, no funciones globales

    // Verificar si el correo ya existe
    if ($usuarioModel->existeEmail($email)) {
        return false;
    }

    // Encriptar contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Crear usuario
    return $usuarioModel->crearUsuario([
        'nombre'    => $nombre,
        'email'     => $email,
        'password'  => $hash,
        'telefono'  => $telefono,
        'direccion' => $direccion,
        'rol'       => $rol
    ]);
}

/**
 * Iniciar sesión.
 *
 * @param string $email
 * @param string $password
 * @return bool
 */
function iniciarSesion($email, $password)
{
    global $usuarioModel;

    // Buscar usuario por email
    $usuario = $usuarioModel->obtenerUsuarioPorEmail($email);

    if (!$usuario) {
        return false;
    }

    // Verificar contraseña
    if (!password_verify($password, $usuario['password'])) {
        return false;
    }

    // Guardar datos en sesión
    $_SESSION['usuario'] = [
        'id_usuario' => $usuario['id_usuario'],
        'nombre'     => $usuario['nombre'],
        'rol'        => $usuario['rol']
    ];

    return true;
}

/**
 * Cerrar sesión.
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
