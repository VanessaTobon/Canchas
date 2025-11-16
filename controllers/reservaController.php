<?php
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/CanchaModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../services/ReservaBuilder.php';
require_once __DIR__ . '/../services/ReservaFacade.php';
require_once __DIR__ . '/../services/UsuarioProxy.php';

class ReservaController {

    private ReservaFacade $facade;
    private ReservaBuilder $builder;
    private ReservaModel $reservaModel;
    private UsuarioProxy $usuarioProxy;
    private string $mensaje;
    private array $reservas;

    public function __construct() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->reservaModel = new ReservaModel();
        $this->facade       = new ReservaFacade();
        $this->builder      = new ReservaBuilder();
        $this->usuarioProxy = new UsuarioProxy($_SESSION['usuario'] ?? null);
        $this->mensaje      = "";
        $this->reservas     = [];

        error_log("✔ ReservaController inicializado");
    }

    /* ==========================================================
       OBTENER ID / ROL USUARIO
    ==========================================================*/
    private function obtenerIdUsuario(): ?int {
        return $_SESSION['usuario']['id_usuario']
            ?? $_SESSION['id_usuario']
            ?? null;
    }

    private function obtenerRolUsuario(): string {
        return $_SESSION['usuario']['rol']
            ?? $_SESSION['rol']
            ?? "cliente";
    }


    /* ==========================================================
       CANCELAR RESERVA CLIENTE
    ==========================================================*/
    public function cancelarReservaCliente($id_reserva, $motivo = null): bool {

        $id_reserva = (int)$id_reserva;
        $id_usuario = $this->obtenerIdUsuario();

        if (!$id_usuario) {
            return $this->fail("Debes iniciar sesión");
        }
        if (!$id_reserva) {
            return $this->fail("ID de reserva inválido");
        }
        if (empty($motivo)) {
            return $this->fail("⚠ Debes escribir un motivo");
        }

        $reserva = $this->reservaModel->getById($id_reserva);
        if (!$reserva) {
            return $this->fail("Reserva no encontrada");
        }

        if ((int)$reserva['id_usuario'] !== (int)$id_usuario) {
            return $this->fail("No puedes cancelar la reserva de otro usuario");
        }

        if ($reserva['estado'] === "cancelada") {
            return $this->fail("⚠ La reserva ya estaba cancelada");
        }

        if ($reserva['estado'] === "completada") {
            return $this->fail("⚠ No se puede cancelar una reserva completada");
        }

        // No permitir cancelar reservas pasadas
        $hoy = date("Y-m-d");
        if ($reserva["fecha_reserva"] < $hoy) {
            return $this->fail("⚠ No puedes cancelar reservas pasadas");
        }

        // Primer intento → modelo directo
        if ($this->reservaModel->updateEstado($id_reserva, "cancelada", $motivo)) {
            return $this->ok("Reserva cancelada");
        }

        // Segundo intento → Facade
        if ($this->facade->cancelarReserva($id_reserva, $motivo) === true) {
            return $this->ok("Reserva cancelada");
        }

        return $this->fail("Error interno cancelando reserva");
    }


    /* ==========================================================
       CONFIRMAR / COMPLETAR / CANCELAR (ADMIN)
    ==========================================================*/
    private function cambiarEstadoAdmin(int $id_reserva, string $nuevoEstado, ?string $motivo = null): bool {

        $reserva = $this->reservaModel->getById($id_reserva);
        if (!$reserva) {
            return $this->fail("Reserva no existe");
        }

        if ($reserva["estado"] === $nuevoEstado) {
            return $this->fail("⚠ Ya está en ese estado");
        }

        if ($this->reservaModel->updateEstado($id_reserva, $nuevoEstado, $motivo)) {
            return $this->ok("Estado actualizado");
        }

        // fallback → Facade
        $fn = [
            "confirmada" => "confirmarReserva",
            "completada" => "completarReserva",
            "cancelada"  => "cancelarReserva"
        ];

        if (isset($fn[$nuevoEstado]) && $this->facade->{$fn[$nuevoEstado]}($id_reserva, $motivo) === true) {
            return $this->ok("Estado actualizado");
        }

        return $this->fail("Error cambiando estado");
    }

    public function confirmarReservaAdmin($id)   { return $this->cambiarEstadoAdmin((int)$id, "confirmada"); }
    public function completarReservaAdmin($id)   { return $this->cambiarEstadoAdmin((int)$id, "completada"); }
    public function cancelarReservaAdmin($id,$m) { return $this->cambiarEstadoAdmin((int)$id, "cancelada", $m); }


    /* ==========================================================
       CREAR RESERVA
    ==========================================================*/
    public function procesarCreacionReserva(): bool {

        $id_usuario  = $this->obtenerIdUsuario();
        $id_cancha   = $_POST["id_cancha"]   ?? null;
        $fecha       = $_POST["fecha"]       ?? null;
        $hora_inicio = $_POST["hora_inicio"] ?? null;
        $hora_fin    = $_POST["hora_fin"]    ?? null;

        if (!$id_usuario) {
            return $this->fail("Debes iniciar sesión");
        }

        if (!$id_cancha || !$fecha || !$hora_inicio || !$hora_fin) {
            return $this->fail("Todos los campos son obligatorios");
        }

        $reservaData = $this->builder
        ->setUsuario((int)$id_usuario)
        ->setCancha((int)$id_cancha)
        ->setFecha($fecha)
        ->setHorario($hora_inicio, $hora_fin)
        ->setEstado("pendiente")
        ->build();

        $res = $this->usuarioProxy->crearReserva($reservaData);

        return $res === true
        ? $this->ok("✔ Reserva creada")
        : $this->fail("❌ $res");
       
    }


    /* ==========================================================
       OBTENER RESERVAS
    ==========================================================*/
    public function obtenerReservasUsuario($id_usuario = null): array {
        $id_usuario = $id_usuario ?? $this->obtenerIdUsuario();
        if (!$id_usuario) return [];

        try {
            return $this->reservaModel->getByUsuario((int)$id_usuario);
        } catch (Exception) {
            // fallback a la fachada
            return $this->facade->obtenerReservasPorUsuario((int)$id_usuario) ?? [];
        }
    }

    public function obtenerTodasReservas($estado = ""): array {
        return $this->reservaModel->getAll($estado);
    }


    /* ==========================================================
       DISPONIBILIDAD
    ==========================================================*/
    public function verificarDisponibilidad($id_cancha, $fecha, $h1, $h2): array {

        $res = $this->facade->verificarDisponibilidad(
            (int)$id_cancha, $fecha, $h1, $h2
        );

        return [
            "disponible" => $res === true,
            "mensaje"    => $res === true ? "✔ Disponible" : "❌ $res"
        ];
    }


    /* ==========================================================
       MANEJO DE MENSAJES
    ==========================================================*/
    private function ok(string $msg): bool {
        $this->mensaje = $msg;
        return true;
    }

    private function fail(string $msg): bool {
        $this->mensaje = $msg;
        return false;
    }

    public function getMensaje(): string {
        return $this->mensaje;
    }
}


/* ==========================================================
   FUNCIONES COMPATIBILIDAD (LEGADO)
==========================================================*/

function cancelarReserva($r, $u, $m = null) {
    return (new ReservaController())->cancelarReservaCliente($r, $m);
}

function completarReserva($r) {
    return (new ReservaController())->completarReservaAdmin($r);
}

function confirmarReserva($r) {
    return (new ReservaController())->confirmarReservaAdmin($r);
}

function obtenerReservasPorUsuario($u) {
    return (new ReservaController())->obtenerReservasUsuario($u);
}

function obtenerTodasLasReservas($e = "") {
    return (new ReservaController())->obtenerTodasReservas($e);
}

/**
 * NUEVA FUNCIÓN GLOBAL PARA CREAR RESERVA
 *    (envuelve a procesarCreacionReserva)
 */
function crearReserva() {
    return (new ReservaController())->procesarCreacionReserva();
}
?>