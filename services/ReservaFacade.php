<?php
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/CanchaModel.php';

class ReservaFacade {

    private ReservaModel $reservaModel;
    private CanchaModel $canchaModel;

    public function __construct() {
        $this->reservaModel = new ReservaModel();
        $this->canchaModel  = new CanchaModel();
        error_log("ReservaFacade inicializado");
    }

    /* =====================================================
       CREAR RESERVA (USADO POR CONTROLLER + BUILDER)
    ======================================================*/
    public function crearReserva(array $data) {

        error_log("Facade::crearReserva() ejecutado");

        if (!isset(
            $data['id_usuario'],
            $data['id_cancha'],
            $data['fecha_reserva'],
            $data['hora_inicio'],
            $data['hora_fin']
        )) {
            return "Datos incompletos para la reserva";
        }

        // VALIDAR DISPONIBILIDAD
        $disp = $this->verificarDisponibilidad(
            $data["id_cancha"],
            $data["fecha_reserva"],
            $data["hora_inicio"],
            $data["hora_fin"]
        );

        if ($disp !== true) {
            return $disp; // mensaje de error
        }

        // INSERTAR RESERVA usando el método create del modelo
        $resultado = $this->reservaModel->create($data);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? true : "❌ " . ($resultado['message'] ?? 'Error desconocido');
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado ? true : "Error guardando reserva";
        }
    }

    /* =====================================================
       CANCELAR / CONFIRMAR / COMPLETAR
    ======================================================*/
    public function cancelarReserva(int $id, string $motivo = null) {
        $resultado = $this->reservaModel->updateEstado($id, "cancelada", $motivo);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? "Reserva cancelada correctamente" : "❌ " . ($resultado['message'] ?? 'Error desconocido');
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado ? "Reserva cancelada correctamente" : "Error al cancelar la reserva";
        }
    }

    public function confirmarReserva(int $id) {
        $resultado = $this->reservaModel->updateEstado($id, "confirmada");
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? "Reserva confirmada correctamente" : "❌ " . ($resultado['message'] ?? 'Error desconocido');
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado ? "Reserva confirmada correctamente" : "Error al confirmar la reserva";
        }
    }

    public function completarReserva(int $id) {
        $resultado = $this->reservaModel->updateEstado($id, "completada");
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? "Reserva completada correctamente" : "❌ " . ($resultado['message'] ?? 'Error desconocido');
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado ? "Reserva completada correctamente" : "Error al completar la reserva";
        }
    }

    /* =====================================================
       LISTADOS
    ======================================================*/
    public function obtenerTodasLasReservas(string $estado = "") {
        $resultado = $this->reservaModel->getAll($estado);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? $resultado['reservas'] : [];
        } else {
            // Para compatibilidad con versiones antiguas que devuelven array directamente
            return is_array($resultado) ? $resultado : [];
        }
    }

    public function obtenerReservasPorUsuario(int $id_usuario) {
        $resultado = $this->reservaModel->getByUsuario($id_usuario);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? $resultado['reservas'] : [];
        } else {
            // Para compatibilidad con versiones antiguas que devuelven array directamente
            return is_array($resultado) ? $resultado : [];
        }
    }

    /* =====================================================
       DISPONIBILIDAD - CORREGIDO CON COMPATIBILIDAD
    ======================================================*/
    public function verificarDisponibilidad($id_cancha, $fecha, $hora_inicio, $hora_fin) {

        error_log("⏳ Facade verificando disponibilidad...");

        // Usar el método checkOverlap con compatibilidad para ambas versiones
        $resultado = $this->reservaModel->checkOverlap($id_cancha, $fecha, $hora_inicio, $hora_fin);
        
        // Manejar tanto la versión antigua (boolean) como la nueva (array)
        if (is_array($resultado) && isset($resultado['success'])) {
            // Versión nueva con array
            if (!$resultado['success']) {
                return "Error al verificar disponibilidad: " . ($resultado['message'] ?? 'Error desconocido');
            }
            
            if ($resultado['overlap']) {
                return "⚠ Ya hay una reserva en ese horario";
            }
        } else {
            // Versión antigua con boolean directo
            if ($resultado === true) {
                return "⚠ Ya hay una reserva en ese horario";
            }
        }

        return true;
    }

    /* =====================================================
       MÉTODOS ADICIONALES PARA COMPATIBILIDAD
    ======================================================*/
    public function obtenerReservaPorId(int $id) {
        return $this->reservaModel->getById($id);
    }

    public function puedeSerCancelada(int $id) {
        $resultado = $this->reservaModel->puedeSerCancelada($id);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? $resultado['puede'] : false;
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado === true;
        }
    }

    public function puedeSerConfirmada(int $id) {
        $resultado = $this->reservaModel->puedeSerConfirmada($id);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? $resultado['puede'] : false;
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado === true;
        }
    }

    public function puedeSerCompletada(int $id) {
        $resultado = $this->reservaModel->puedeSerCompletada($id);
        
        if (is_array($resultado) && isset($resultado['success'])) {
            return $resultado['success'] ? $resultado['puede'] : false;
        } else {
            // Para compatibilidad con versiones antiguas que devuelven boolean
            return $resultado === true;
        }
    }

    /* =====================================================
       MÉTODO ALTERNATIVO PARA DISPONIBILIDAD
    ======================================================*/
    public function verificarDisponibilidadAlternativo($id_cancha, $fecha, $hora_inicio, $hora_fin) {
        // Método alternativo que no depende del formato de retorno
        try {
            $ocupadas = $this->reservaModel->buscarOcupadas($id_cancha, $fecha, $hora_inicio, $hora_fin);
            
            if (is_array($ocupadas) && isset($ocupadas['success'])) {
                // Versión nueva con array
                if (!$ocupadas['success']) {
                    return "Error al verificar disponibilidad";
                }
                return empty($ocupadas['ocupadas']) ? true : "⚠ Ya hay una reserva en ese horario";
            } else {
                // Versión antigua con array directo
                return empty($ocupadas) ? true : "⚠ Ya hay una reserva en ese horario";
            }
        } catch (Exception $e) {
            return "Error al verificar disponibilidad: " . $e->getMessage();
        }
    }
}