<?php
require_once __DIR__ . '/../models/CanchaModel.php';
require_once __DIR__ . '/../services/CanchaFacade.php';


class CanchaController {
    private $canchaModel;

   public function __construct() {
    $this->canchaModel = new CanchaModel();
    $this->facade      = new CanchaFacade();
    }


        public function obtenerCompositeUbicacionesAsArray(): array {
            return $this->facade->obtenerCompositeUbicaciones(true);
        }

        // para compatibilidad con vistas actuales
        public function obtenerCanchasConUbicacionCompleta() {
            return $this->facade->obtenerCompositeUbicaciones(true);
        }

    /**
     * Obtener todas las canchas
     */
    public function obtenerTodasLasCanchas() {
        try {
            return $this->canchaModel->obtenerTodasLasCanchas();
        } catch (Exception $e) {
            error_log("Error al obtener todas las canchas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener canchas disponibles (para el admin)
     */
    public function obtenerCanchasDisponibles() {
        try {
            return $this->canchaModel->obtenerCanchasDisponibles();
        } catch (Exception $e) {
            error_log("Error al obtener canchas disponibles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener canchas con información de ubicación (para el cliente)
     */
    public function getCanchasConUbicacion() {
        try {
            return $this->canchaModel->getCanchasConUbicacion();
        } catch (Exception $e) {
            error_log("Error al obtener canchas con ubicación: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Agregar nueva cancha
     */
    public function agregarCancha($nombre, $tipo, $estado, $id_pais, $id_estado, $id_municipio, $direccion, $capacidad, $precio) {
        $nombre = trim(htmlspecialchars($nombre));
        $direccion = trim(htmlspecialchars($direccion));

        if (
            empty($nombre) || empty($tipo) || empty($estado) ||
            !$id_pais || !$id_estado || !$id_municipio ||
            empty($direccion) || $capacidad <= 0 || $precio <= 0
        ) {
            error_log("⚠ Datos incompletos para agregar cancha");
            return false;
        }

        if ($this->canchaYaExiste($nombre, $direccion)) {
            error_log("⚠ La cancha ya existe con el mismo nombre y dirección");
            return false;
        }

        try {
            return $this->canchaModel->insertarCancha([
                'nombre' => $nombre,
                'tipo' => $tipo,
                'estado' => $estado,
                'id_pais' => $id_pais,
                'id_estado' => $id_estado,
                'id_municipio' => $id_municipio,
                'direccion' => $direccion,
                'capacidad' => $capacidad,
                'precio' => $precio
            ]);
        } catch (Exception $e) {
            error_log("Error al insertar cancha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Editar cancha existente
     */
    public function editarCancha($id_cancha, $nombre, $tipo, $estado, $id_pais, $id_estado, $id_municipio, $direccion, $capacidad, $precio) {
        $nombre = trim(htmlspecialchars($nombre));
        $direccion = trim(htmlspecialchars($direccion));

        if (
            !$id_cancha || empty($nombre) || empty($tipo) || empty($estado) ||
            !$id_pais || !$id_estado || !$id_municipio ||
            empty($direccion) || $capacidad <= 0 || $precio <= 0
        ) {
            error_log("⚠ Datos inválidos para editar cancha");
            return false;
        }

        try {
            return $this->canchaModel->actualizarCancha($id_cancha, [
                'nombre' => $nombre,
                'tipo' => $tipo,
                'estado' => $estado,
                'id_pais' => $id_pais,
                'id_estado' => $id_estado,
                'id_municipio' => $id_municipio,
                'direccion' => $direccion,
                'capacidad' => $capacidad,
                'precio' => $precio
            ]);
        } catch (Exception $e) {
            error_log("Error al actualizar cancha: " . $e->getMessage());
            return false;
        }

    }

    /**
     * Eliminar cancha
     */
    public function eliminarCancha($id_cancha) {
        if (!$id_cancha || !is_numeric($id_cancha)) {
            error_log("ID inválido para eliminar cancha");
            return false;
        }

        try {
            return $this->canchaModel->eliminarCancha($id_cancha);
        } catch (Exception $e) {
            error_log("Error al eliminar cancha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si ya existe una cancha por nombre y dirección
     */
    public function canchaYaExiste($nombre, $direccion) {
        try {
            return $this->canchaModel->canchaYaExiste($nombre, $direccion);
        } catch (Exception $e) {
            error_log("Error al verificar existencia de cancha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener horarios ya reservados por cancha
     */
    public function obtenerHorariosPorCancha($id_cancha) {
        if (!$id_cancha || !is_numeric($id_cancha)) {
            error_log("ID inválido para obtener horarios");
            return [];
        }

        try {
            return $this->canchaModel->obtenerHorariosReservados($id_cancha);
        } catch (Exception $e) {
            error_log("Error al obtener horarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener cancha por ID
     */
    public function obtenerCanchaPorId($id_cancha) {
        if (!$id_cancha || !is_numeric($id_cancha)) {
            return null;
        }

        try {
            return $this->canchaModel->getById($id_cancha);
        } catch (Exception $e) {
            error_log("Error al obtener cancha por ID: " . $e->getMessage());
            return null;
        }
    }
}

// ===============================
// FUNCIONES DE COMPATIBILIDAD TEMPORAL
// ===============================

/**
 * @deprecated Usar CanchaController en su lugar
 */
function obtenerTodasLasCanchas() {
    $controller = new CanchaController();
    return $controller->obtenerTodasLasCanchas();
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function obtenerCanchasDisponibles() {
    $controller = new CanchaController();
    return $controller->obtenerCanchasDisponibles();
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function getCanchasConUbicacion() {
    $controller = new CanchaController();
    return $controller->getCanchasConUbicacion();
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function agregarCancha($nombre, $tipo, $estado, $id_pais, $id_estado, $id_municipio, $direccion, $capacidad, $precio) {
    $controller = new CanchaController();
    return $controller->agregarCancha($nombre, $tipo, $estado, $id_pais, $id_estado, $id_municipio, $direccion, $capacidad, $precio);
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function editarCancha($id_cancha, $nombre, $tipo, $estado, $id_pais, $id_estado, $id_municipio, $direccion, $capacidad, $precio) {
    $controller = new CanchaController();
    return $controller->editarCancha($id_cancha, $nombre, $tipo, $estado, $id_pais, $id_estado, $id_municipio, $direccion, $capacidad, $precio);
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function eliminarCancha($id_cancha) {
    $controller = new CanchaController();
    return $controller->eliminarCancha($id_cancha);
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function canchaYaExiste($nombre, $direccion) {
    $controller = new CanchaController();
    return $controller->canchaYaExiste($nombre, $direccion);
}

/**
 * @deprecated Usar CanchaController en su lugar
 */
function obtenerHorariosPorCancha($id_cancha) {
    $controller = new CanchaController();
    return $controller->obtenerHorariosPorCancha($id_cancha);
}