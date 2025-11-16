<?php
// models/UsuarioModel.php
require_once __DIR__ . '/../config/config.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        global $conexion;
        $this->db = $conexion;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id_usuario, nombre, email, rol FROM tbl_usuarios WHERE id_usuario = ?");
        if (!$stmt) {
            error_log("Error preparando getById: " . $this->db->error);
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc() ?: null;
    }

    public function obtenerUsuarioPorEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT id_usuario, nombre, email, password, rol FROM tbl_usuarios WHERE email = ?");
        if (!$stmt) {
            error_log("Error preparando obtenerUsuarioPorEmail: " . $this->db->error);
            return null;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc() ?: null;
    }

    public function crearUsuario(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO tbl_usuarios (nombre, email, password, telefono, direccion, rol) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error preparando crearUsuario: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ssssss", 
            $data['nombre'], 
            $data['email'], 
            $data['password'], 
            $data['telefono'], 
            $data['direccion'], 
            $data['rol']
        );

        return $stmt->execute();
    }

    public function existeEmail(string $email): bool {
        $stmt = $this->db->prepare("SELECT id_usuario FROM tbl_usuarios WHERE email = ?");
        if (!$stmt) {
            error_log("Error preparando existeEmail: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->num_rows > 0;
    }
}