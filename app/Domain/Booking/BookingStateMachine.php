<?php
declare(strict_types=1);

namespace App\Domain\Booking;

use DomainException;

/**
 * Máquina de estados canónica para pedidos y reservas (Sección 3.5).
 * 
 * Flujo principal:
 * borrador -> pendiente_confirmacion -> confirmado -> pagado -> en_curso -> completado
 * 
 * Excepciones y cancelaciones:
 * Cualquier estado activo puede pasar a 'cancelado'.
 * 'pagado' o 'completado' puede pasar a 'reembolsado'.
 */
class BookingStateMachine {
    public const ESTADO_BORRADOR               = 'borrador';
    public const ESTADO_PENDIENTE_CONFIRMACION = 'pendiente_confirmacion';
    public const ESTADO_CONFIRMADO             = 'confirmado';
    public const ESTADO_PAGADO                 = 'pagado';
    public const ESTADO_EN_CURSO               = 'en_curso';
    public const ESTADO_COMPLETADO             = 'completado';
    public const ESTADO_CANCELADO              = 'cancelado';
    public const ESTADO_REEMBOLSADO            = 'reembolsado';

    /**
     * Matriz estricta de transiciones permitidas.
     */
    private const TRANSICIONES_PERMITIDAS = [
        self::ESTADO_BORRADOR => [
            self::ESTADO_PENDIENTE_CONFIRMACION,
            self::ESTADO_CANCELADO,
        ],
        self::ESTADO_PENDIENTE_CONFIRMACION => [
            self::ESTADO_CONFIRMADO,
            self::ESTADO_CANCELADO,
        ],
        self::ESTADO_CONFIRMADO => [
            self::ESTADO_PAGADO,
            self::ESTADO_CANCELADO,
        ],
        self::ESTADO_PAGADO => [
            self::ESTADO_EN_CURSO,
            self::ESTADO_CANCELADO,
            self::ESTADO_REEMBOLSADO,
        ],
        self::ESTADO_EN_CURSO => [
            self::ESTADO_COMPLETADO,
            self::ESTADO_CANCELADO,
        ],
        self::ESTADO_COMPLETADO => [
            self::ESTADO_REEMBOLSADO,
        ],
        self::ESTADO_CANCELADO => [],
        self::ESTADO_REEMBOLSADO => [],
    ];

    /**
     * Verifica si una transición es válida.
     */
    public static function esTransicionValida(string $estadoActual, string $nuevoEstado): bool {
        if (!isset(self::TRANSICIONES_PERMITIDAS[$estadoActual])) {
            return false;
        }

        return in_array($nuevoEstado, self::TRANSICIONES_PERMITIDAS[$estadoActual], true);
    }

    /**
     * Aplica la transición o lanza DomainException.
     */
    public static function transicionar(string $estadoActual, string $nuevoEstado): string {
        if (!self::esTransicionValida($estadoActual, $nuevoEstado)) {
            throw new DomainException(
                sprintf("Transición de estado inválida: no se puede pasar de '%s' a '%s'.", $estadoActual, $nuevoEstado)
            );
        }

        return $nuevoEstado;
    }

    /**
     * Retorna los estados válidos alcanzables desde el estado dado.
     */
    public static function obtenerSiguientesEstados(string $estadoActual): array {
        return self::TRANSICIONES_PERMITIDAS[$estadoActual] ?? [];
    }

    /**
     * Retorna si el estado es final (sin transiciones posteriores).
     */
    public static function esEstadoFinal(string $estado): bool {
        return empty(self::TRANSICIONES_PERMITIDAS[$estado] ?? []);
    }
}
