<?php
// models/CanchaModel.php
require_once __DIR__ . '/../config/config.php';

class CanchaModel {

    private $db;

    public function __construct() {
        global $conexion;
        $this->db = $conexion;

        if (!$this->db) {
            die("Error de conexión a la base de datos en CanchaModel.");
        }
    }

    /* ============================================================
       OBTENER CANCHA POR ID
    ============================================================ */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_canchas WHERE id_cancha = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /* ============================================================
       OBTENER CANCHAS DISPONIBLES
    ============================================================ */
    public function obtenerTodasLasCanchas(): array {
        $result = $this->db->query("SELECT * FROM tbl_canchas WHERE estado = 'disponible'");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerCanchasDisponibles(): array {
        return $this->obtenerTodasLasCanchas();
    }

    /* ============================================================
       OBTENER CANCHAS + UBICACIÓN
       (SE USA EN GESTIONAR CANCHAS)
    ============================================================ */
    public function getCanchasConUbicacion(): array {
        $sql = "
            SELECT c.*, 
                   p.nombre_pais AS nombre_pais,
                   e.nombre_estado AS nombre_estado,
                   m.nombre_municipio AS nombre_municipio
            FROM tbl_canchas c
            INNER JOIN tbl_pais p ON c.id_pais = p.id_pais
            INNER JOIN tbl_estado e ON c.id_estado = e.id_estado
            INNER JOIN tbl_municipio m ON c.id_municipio = m.id_municipio
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ============================================================
       VERIFICAR CHOQUE DE HORARIO
    ============================================================ */
    public function checkOverlap(int $idCancha, string $fecha, string $horaInicio, string $horaFin): bool {

        $sql = "SELECT COUNT(*) AS total FROM tbl_reservas
                WHERE id_cancha = ?
                AND fecha_reserva = ?
                AND estado IN ('pendiente','confirmada')
                AND (
                     (hora_inicio < ? AND hora_fin > ?)
                  OR (hora_inicio < ? AND hora_fin > ?)
                  OR (hora_inicio >= ? AND hora_fin <= ?)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isssssss", $idCancha, $fecha, $horaFin, $horaInicio, $horaInicio, $horaFin, $horaInicio, $horaFin);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        return $data['total'] > 0;
    }

    /* ============================================================
       INSERTAR CANCHA
    ============================================================ */
    public function insertarCancha(array $data): bool|string {

    if (empty($data["nombre_cancha"])) {
        return "El nombre de la cancha está vacío";
    }

    $sql = "INSERT INTO tbl_canchas 
            (nombre_cancha, tipo_cancha, estado, id_pais, id_estado, id_municipio, direccion, capacidad, precio)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
        return "Error preparando consulta SQL";
    }

    $stmt->bind_param(
        "sssiiisid",
        $data['nombre_cancha'],
        $data['tipo_cancha'],
        $data['estado'],
        $data['id_pais'],
        $data['id_estado'],
        $data['id_municipio'],
        $data['direccion'],
        $data['capacidad'],
        $data['precio']
    );

    return $stmt->execute() ? true : "Error ejecutando inserción";
    }

    /* ============================================================
       ACTUALIZAR CANCHA
    ============================================================ */
    public function actualizarCancha(int $id_cancha, array $data): bool {

        $sql = "UPDATE tbl_canchas SET
                nombre_cancha = ?, tipo_cancha = ?, estado = ?,
                id_pais = ?, id_estado = ?, id_municipio = ?,
                direccion = ?, capacidad = ?, precio = ?
                WHERE id_cancha = ?";

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            "sssiiisidi",
            $data['nombre_cancha'],
            $data['tipo_cancha'],
            $data['estado'],
            $data['id_pais'],
            $data['id_estado'],
            $data['id_municipio'],
            $data['direccion'],
            $data['capacidad'],
            $data['precio'],
            $id_cancha
        );

        return $stmt->execute();
    }

    /* ============================================================
       ELIMINAR CANCHA
    ============================================================ */
    public function eliminarCancha(int $id_cancha): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_canchas WHERE id_cancha = ?");
        $stmt->bind_param("i", $id_cancha);
        return $stmt->execute();
    }

    /* ============================================================
       VERIFICAR SI YA EXISTE
    ============================================================ */
    public function canchaYaExiste(string $nombre, string $direccion): bool {
        $stmt = $this->db->prepare("SELECT id_cancha FROM tbl_canchas WHERE nombre_cancha = ? AND direccion = ?");
        $stmt->bind_param("ss", $nombre, $direccion);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /* ============================================================
       HORARIOS RESERVADOS
    ============================================================ */
    public function obtenerHorariosReservados(int $id_cancha): array {
        $stmt = $this->db->prepare("SELECT fecha_reserva AS fecha, hora_inicio, hora_fin 
                                    FROM tbl_reservas 
                                    WHERE id_cancha = ? AND estado != 'cancelada'");
        $stmt->bind_param("i", $id_cancha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* ============================================================
       NOMBRE ESTADO / MUNICIPIO
    ============================================================ */
    public function obtenerNombreEstado(int $id_estado): string {
        $stmt = $this->db->prepare("SELECT nombre_estado FROM tbl_estado WHERE id_estado = ?");
        $stmt->bind_param("i", $id_estado);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['nombre_estado'] ?? 'Desconocido';
    }

    public function obtenerNombreMunicipio(int $id_municipio): string {
        $stmt = $this->db->prepare("SELECT nombre_municipio FROM tbl_municipio WHERE id_municipio = ?");
        $stmt->bind_param("i", $id_municipio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['nombre_municipio'] ?? 'Desconocido';
    }

    /**
     * Devuelve todas las canchas con sus nombres de país/estado/municipio
     * Resultado: array de filas asociativas con keys:
     * id_cancha, nombre_cancha, tipo_cancha, estado, direccion, capacidad, precio,
     * id_pais, nombre_pais, id_estado, nombre_estado, id_municipio, nombre_municipio
     */
    public function getAllWithLocation(): array {
        $sql = "
            SELECT 
                c.id_cancha, c.nombre_cancha, c.tipo_cancha, c.estado, c.direccion, c.capacidad, c.precio,
                c.id_pais, p.nombre_pais,
                c.id_estado, e.nombre_estado,
                c.id_municipio, m.nombre_municipio
            FROM tbl_canchas c
            LEFT JOIN tbl_pais p ON c.id_pais = p.id_pais
            LEFT JOIN tbl_estado e ON c.id_estado = e.id_estado
            LEFT JOIN tbl_municipio m ON c.id_municipio = m.id_municipio
            ORDER BY p.nombre_pais, e.nombre_estado, m.nombre_municipio, c.nombre_cancha
        ";
        $result = $this->db->query($sql);
        if (!$result) {
            error_log("Error getAllWithLocation: " . $this->db->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }


}
