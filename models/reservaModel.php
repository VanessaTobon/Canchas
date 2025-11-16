<?php
// models/ReservaModel.php
require_once __DIR__ . '/../config/config.php';

class ReservaModel {

    private $db;

    public function __construct() {
        global $conexion;
        $this->db = $conexion;

        if ($this->db->connect_error) {
            error_log("ERROR de conexión en ReservaModel: " . $this->db->connect_error);
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    /* =========================================================
        OBTENER RESERVA POR ID
    ========================================================== */
    public function getById(int $id): ?array {
        $sql = "
            SELECT r.*, c.nombre_cancha, u.nombre as nombre_usuario 
            FROM tbl_reservas r 
            JOIN tbl_canchas c ON r.id_cancha = c.id_cancha 
            JOIN tbl_usuarios u ON r.id_usuario = u.id_usuario 
            WHERE r.id_reserva = ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        error_log("🔍 ReservaModel->getById($id) = " . ($res ? "ENCONTRADA" : "NO ENCONTRADA"));

        return $res ?: null;
    }

    /* =========================================================
        CREAR RESERVA
    ========================================================== */
    public function create(array $data): bool {

        $sql = "
            INSERT INTO tbl_reservas (id_usuario, id_cancha, fecha_reserva, hora_inicio, hora_fin, estado) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) return false;

        $id_usuario     = $data['id_usuario'];
        $id_cancha      = $data['id_cancha'];
        $fecha_reserva  = $data['fecha_reserva'];
        $hora_inicio    = $data['hora_inicio'];
        $hora_fin       = $data['hora_fin'];
        $estado         = $data['estado'] ?? 'pendiente';

        $stmt->bind_param("iissss",
            $id_usuario, $id_cancha,
            $fecha_reserva, $hora_inicio,
            $hora_fin, $estado
        );

        $ok = $stmt->execute();

        error_log($ok
            ? "Reserva creada (Usuario $id_usuario, Cancha $id_cancha)"
            : "ERROR create(): " . $stmt->error
        );

        return $ok;
    }

    /* =========================================================
        VALIDAR TRASLAPE
    ========================================================== */
    public function checkOverlap(int $idCancha, string $fecha, string $inicio, string $fin): bool {
        $sql = "
            SELECT COUNT(*) AS total 
            FROM tbl_reservas
            WHERE id_cancha = ? 
            AND fecha_reserva = ?
            AND estado IN ('pendiente','confirmada')
            AND (
                  (hora_inicio < ? AND hora_fin > ?)
               OR (hora_inicio < ? AND hora_fin > ?)
               OR (hora_inicio >= ? AND hora_fin <= ?)
            )
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("isssssss",
            $idCancha, $fecha,
            $fin, $inicio,
            $inicio, $fin,
            $inicio, $fin
        );

        $stmt->execute();
        $num = $stmt->get_result()->fetch_assoc()['total'];

        error_log("⏱ checkOverlap cancha:$idCancha → " . ($num > 0 ? "❌ OCUPADA" : "✔ LIBRE"));

        return $num > 0;
    }

    /* =========================================================
        ACTUALIZAR ESTADO - CORREGIDO
    ========================================================== */
    public function updateEstado(int $idReserva, string $estado, ?string $motivo = null): bool {
        try {
            if ($estado === "cancelada") {
                // Verificar si la tabla tiene la columna motivo_cancelacion
                $sql = "
                    UPDATE tbl_reservas
                    SET estado = ?, motivo_cancelacion = ?
                    WHERE id_reserva = ?
                ";
                $stmt = $this->db->prepare($sql);
                if (!$stmt) {
                    return ['success' => false, 'message' => 'Error al preparar la consulta de cancelación'];
                }
                $stmt->bind_param("ssi", $estado, $motivo, $idReserva);
            } else {
                $sql = "
                    UPDATE tbl_reservas
                    SET estado = ?
                    WHERE id_reserva = ?
                ";
                $stmt = $this->db->prepare($sql);
                if (!$stmt) {
                    return ['success' => false, 'message' => 'Error al preparar la consulta de actualización'];
                }
                $stmt->bind_param("si", $estado, $idReserva);
            }

            $ok = $stmt->execute();

            error_log($ok
                ? "🔄 Estado actualizado → Reserva $idReserva = $estado"
                : "❌ ERROR updateEstado(): " . $stmt->error
            );

            return $ok;
        } catch (Exception $e) {
            error_log("❌ EXCEPCIÓN en updateEstado: " . $e->getMessage());
            return false;
        }
    }

    /* =========================================================
        RESERVAS POR USUARIO
    ========================================================== */
    public function getByUsuario(int $idUsuario): array {
        $sql = "
            SELECT r.*, c.nombre_cancha
            FROM tbl_reservas r
            JOIN tbl_canchas c ON r.id_cancha = c.id_cancha
            WHERE id_usuario = ?
            ORDER BY fecha_reserva DESC, hora_inicio DESC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
        /* =========================================================
            OBTENER TODAS LAS RESERVAS (Opcional: filtrar por estado)
        ========================================================= */
        public function getAll(string $estado = ""): array {

            // Consulta base
            $sql = "
                SELECT 
                    r.*,
                    c.nombre_cancha AS cancha,
                    u.nombre       AS usuario,
                    u.email        AS email_usuario
                FROM tbl_reservas r
                JOIN tbl_canchas  c ON r.id_cancha  = c.id_cancha
                JOIN tbl_usuarios u ON r.id_usuario = u.id_usuario
            ";

            // Si se envía un estado, agregar filtro dinámico
            if (!empty($estado)) {
                $sql .= " WHERE r.estado = ?";
            }

            // Preparar consulta
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return [];

            // Vincular parámetro si es necesario
            if (!empty($estado)) {
                $stmt->bind_param("s", $estado);
            }

            // Ejecutar consulta
            $stmt->execute();
            $result = $stmt->get_result();

            // Retornar siempre array, incluso vacío
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

    /* =========================================================
        VALIDADORES DE ESTADO
    ========================================================== */
    public function puedeSerConfirmada(int $idReserva): bool {
        $r = $this->getById($idReserva);
        return $r && $r["estado"] === "pendiente";
    }

    public function puedeSerCompletada(int $idReserva): bool {
        $r = $this->getById($idReserva);
        return $r && $r["estado"] === "confirmada";
    }

    public function puedeSerCancelada(int $idReserva): bool {
        $r = $this->getById($idReserva);
        return $r && in_array($r["estado"], ["pendiente","confirmada"]);
    }

    /* =========================================================
        VERIFICAR ESTRUCTURA DE LA TABLA
    ========================================================== */
    public function verificarEstructuraTabla(): array {
        $sql = "DESCRIBE tbl_reservas";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->execute();
        $result = $stmt->get_result();
        $columnas = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        error_log("🔍 Estructura de tbl_reservas:");
        foreach ($columnas as $col) {
            error_log("   - " . $col['Field'] . " (" . $col['Type'] . ")");
        }

        return $columnas;
    }

    // Dentro de la clase ReservaModel
    public function buscarOcupadas(int $id_cancha, string $fecha, string $hora_inicio, string $hora_fin): array
    {
        $sql = "
            SELECT *
            FROM tbl_reservas
            WHERE id_cancha = ?
            AND fecha_reserva = ?
            AND estado IN ('pendiente', 'confirmada')
            AND NOT (
                    hora_fin   <= ?
                OR  hora_inicio >= ?
            )
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en prepare(): " . $this->db->error);
        }

        // i = int, s = string, s = string, s = string
        $stmt->bind_param('isss', $id_cancha, $fecha, $hora_inicio, $hora_fin);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows   = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $rows ?: [];
    }
}
?>