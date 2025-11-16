<?php
// services/ReservaBuilder.php
declare(strict_types=1);

class ReservaBuilder {
    private array $data = [];

    // Constantes para estados
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_CONFIRMADA = 'confirmada';
    public const ESTADO_CANCELADA = 'cancelada';
    public const ESTADO_COMPLETADA = 'completada';

    /**
     * Establece el usuario de la reserva
     */
    public function setUsuario(int $idUsuario): self {
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException("ID de usuario inválido");
        }
        $this->data['id_usuario'] = $idUsuario;
        return $this;
    }

    /**
     * Establece la cancha de la reserva
     */
    public function setCancha(int $idCancha): self {
        if ($idCancha <= 0) {
            throw new InvalidArgumentException("ID de cancha inválido");
        }
        $this->data['id_cancha'] = $idCancha;
        return $this;
    }

    /**
     * Establece la fecha de la reserva
     */
    public function setFecha(string $fecha): self {
        if (!$this->validarFormatoFecha($fecha)) {
            throw new InvalidArgumentException("Formato de fecha inválido. Use YYYY-MM-DD");
        }
        
        if (!$this->validarFechaFutura($fecha)) {
            throw new InvalidArgumentException("No se pueden hacer reservas en fechas pasadas");
        }
        
        $this->data['fecha_reserva'] = $fecha;
        return $this;
    }

    /**
     * Establece el horario de la reserva
     */
    public function setHorario(string $horaInicio, string $horaFin): self {
        if (!$this->validarFormatoHora($horaInicio) || !$this->validarFormatoHora($horaFin)) {
            throw new InvalidArgumentException("Formato de hora inválido. Use HH:MM");
        }
        
        if ($horaInicio >= $horaFin) {
            throw new InvalidArgumentException("La hora de inicio debe ser anterior a la hora de fin");
        }

        $this->data['hora_inicio'] = $horaInicio;
        $this->data['hora_fin'] = $horaFin;
        return $this;
    }

    /**
     * Establece el estado de la reserva
     */
    public function setEstado(string $estado = self::ESTADO_PENDIENTE): self {
        $estadosValidos = [
            self::ESTADO_PENDIENTE,
            self::ESTADO_CONFIRMADA,
            self::ESTADO_CANCELADA,
            self::ESTADO_COMPLETADA
        ];
        
        if (!in_array($estado, $estadosValidos)) {
            throw new InvalidArgumentException("Estado de reserva no válido: " . $estado);
        }
        
        $this->data['estado'] = $estado;
        return $this;
    }

    /**
     * Agrega notas adicionales a la reserva
     */
    public function setNotas(string $notas): self {
        $this->data['notas'] = trim($notas);
        return $this;
    }

    /**
     * Establece el precio de la reserva
     */
    public function setPrecio(float $precio): self {
        if ($precio < 0) {
            throw new InvalidArgumentException("El precio no puede ser negativo");
        }
        $this->data['precio_total'] = $precio;
        return $this;
    }

    /**
     * Valida todos los datos antes de construir
     */
    public function validate(): array {
        $errors = [];
        
        // Campos requeridos
        $required = [
            'id_usuario' => 'ID de usuario',
            'id_cancha' => 'ID de cancha', 
            'fecha_reserva' => 'Fecha de reserva',
            'hora_inicio' => 'Hora de inicio',
            'hora_fin' => 'Hora de fin'
        ];

        foreach ($required as $field => $label) {
            if (empty($this->data[$field])) {
                $errors[] = "El campo {$label} es requerido";
            }
        }

        // Validaciones de formato
        if (isset($this->data['fecha_reserva']) && !$this->validarFormatoFecha($this->data['fecha_reserva'])) {
            $errors[] = "Formato de fecha inválido";
        }

        if ((isset($this->data['hora_inicio']) && !$this->validarFormatoHora($this->data['hora_inicio'])) ||
            (isset($this->data['hora_fin']) && !$this->validarFormatoHora($this->data['hora_fin']))) {
            $errors[] = "Formato de hora inválido";
        }

        // Validación de lógica de negocio
        if (isset($this->data['hora_inicio']) && isset($this->data['hora_fin'])) {
            if ($this->data['hora_inicio'] >= $this->data['hora_fin']) {
                $errors[] = "La hora de inicio debe ser anterior a la hora de fin";
            }
        }

        // Validar que no sea fecha pasada
        if (isset($this->data['fecha_reserva']) && isset($this->data['hora_inicio'])) {
            if (!$this->validarFechaFutura($this->data['fecha_reserva'], $this->data['hora_inicio'])) {
                $errors[] = "No se pueden hacer reservas en fechas u horas pasadas";
            }
        }

        return $errors;
    }

    /**
     * Construye y retorna los datos validados de la reserva
     */
    public function build(): array {
        $errors = $this->validate();
        
        if (!empty($errors)) {
            throw new InvalidArgumentException(
                "ReservaBuilder - Errores de validación: " . implode('; ', $errors)
            );
        }

        // Asegurar valores por defecto
        $this->data['estado'] = $this->data['estado'] ?? self::ESTADO_PENDIENTE;
        $this->data['fecha_creacion'] = date('Y-m-d H:i:s');
        
        return $this->data;
    }

    /**
     * Obtiene los datos actuales sin validar (para debug)
     */
    public function getCurrentData(): array {
        return $this->data;
    }

    /**
     * Reinicia el builder para nueva construcción
     */
    public function reset(): self {
        $this->data = [];
        return $this;
    }

    // ==================== MÉTODOS PRIVADOS DE VALIDACIÓN ====================

    private function validarFormatoFecha(string $fecha): bool {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) &&
               (bool) strtotime($fecha);
    }

    private function validarFormatoHora(string $hora): bool {
        return (bool) preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora);
    }

    private function validarFechaFutura(string $fecha, string $hora = '00:00'): bool {
        $fechaHoraReserva = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $hora);
        $ahora = new DateTime();
        
        return $fechaHoraReserva > $ahora;
    }
}