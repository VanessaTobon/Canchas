<?php

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/CanchaModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/ReservaFacade.php';

class UsuarioProxy {

    private UsuarioModel $userModel;
    private ReservaFacade $facade;
    private ?array $usuarioSesion;

    public function __construct(?array $usuarioSesion) {
        $this->userModel     = new UsuarioModel();
        $this->facade        = new ReservaFacade();
        $this->usuarioSesion = $usuarioSesion;
    }

    /**
     * Ver si el usuario logueado es admin
     */
    private function isAdmin(): bool {
        return $this->usuarioSesion
            && isset($this->usuarioSesion['rol'])
            && $this->usuarioSesion['rol'] === 'admin';
    }

    /**
     * Crear reserva a través del proxy
     * - Verifica que haya sesión
     * - Verifica que el id_usuario de la reserva sea el mismo de la sesión
     * - Luego delega en ReservaFacade::crearReserva($data)
     *
     */
    public function crearReserva(array $reservaData) {

        if (!$this->usuarioSesion) {
            return "Debes iniciar sesión";
        }

        if (!isset($this->usuarioSesion['id_usuario'])) {
            return "Sesión inválida";
        }

        if (!isset($reservaData['id_usuario'])) {
            return "Datos de reserva incompletos (id_usuario faltante)";
        }

        if ((int)$this->usuarioSesion['id_usuario'] !== (int)$reservaData['id_usuario']) {
            return "No puedes crear una reserva para otro usuario";
        }

        return $this->facade->crearReserva($reservaData);
    }

    /**
     * Eliminar cancha (solo admin)
     * - Usa directamente CanchaModel::delete($idCancha)
     */
    public function eliminarCancha(int $idCancha) {
    if (!$this->isAdmin()) return "Acceso denegado";

    $cm = new CanchaModel();
    return $cm->eliminarCancha($idCancha);  // ⬅️ usa el método correcto del modelo
    }

    /**
     * Cancelar reserva con control de permisos
     * - Admin: puede cancelar cualquier reserva
     * - Cliente: solo puede cancelar sus propias reservas
     *
     */
    public function cancelarReserva(int $idReserva, ?string $motivo = null) {

        $reservaModel = new ReservaModel();
        $reserva      = $reservaModel->getById($idReserva);

        if (!$reserva) {
            return "Reserva no existe";
        }

        // Si no es admin, debe estar logueado y ser dueño de la reserva
        if (!$this->isAdmin()) {

            if (!$this->usuarioSesion) {
                return "Debes iniciar sesión";
            }

            if (!isset($this->usuarioSesion['id_usuario'])) {
                return "Sesión inválida";
            }

            if ((int)$reserva['id_usuario'] !== (int)$this->usuarioSesion['id_usuario']) {
                return "No autorizado para cancelar esta reserva";
            }
        }

        return $this->facade->cancelarReserva($idReserva, $motivo);
    }
}