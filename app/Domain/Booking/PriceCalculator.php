<?php

namespace App\Domain\Booking;

use mysqli;

class PriceCalculator {
    /**
     * Obtiene el precio oficial unitario de un servicio/menú/guía directamente desde la base de datos.
     * Ignora cualquier precio suministrado por el cliente.
     */
    public static function getVerifiedPrice(mysqli $conn, string $tipoProveedor, string $tipoItem, int $idItem, int $idProveedor): float {
        $precio = 0.00;

        if ($tipoItem === 'servicio') {
            if ($tipoProveedor === 'agencia') {
                $stmt = $conn->prepare("SELECT precio FROM servicios_agencia WHERE id = ? AND id_agencia = ?");
                $stmt->bind_param("ii", $idItem, $idProveedor);
            } elseif ($tipoProveedor === 'guia') {
                $stmt = $conn->prepare("SELECT precio FROM servicios_guia WHERE id = ? AND id_guia = ?");
                $stmt->bind_param("ii", $idItem, $idProveedor);
            } elseif ($tipoProveedor === 'local') {
                $stmt = $conn->prepare("SELECT precio FROM servicios_local WHERE id = ? AND id_local = ?");
                $stmt->bind_param("ii", $idItem, $idProveedor);
            } else {
                return 0.00;
            }
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $precio = (float)($res['precio'] ?? 0.00);
            $stmt->close();

        } elseif ($tipoItem === 'menu') {
            if ($tipoProveedor === 'local') {
                $stmt = $conn->prepare("SELECT precio FROM menus_local WHERE id = ? AND id_local = ?");
                $stmt->bind_param("ii", $idItem, $idProveedor);
            } elseif ($tipoProveedor === 'agencia') {
                $stmt = $conn->prepare("SELECT precio FROM menus_agencia WHERE id = ? AND id_agencia = ?");
                $stmt->bind_param("ii", $idItem, $idProveedor);
            } else {
                return 0.00;
            }
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $precio = (float)($res['precio'] ?? 0.00);
            $stmt->close();

        } elseif ($tipoItem === 'guia_destino') {
            $stmt = $conn->prepare("SELECT precio_hora FROM guias_turisticos WHERE id = ?");
            $stmt->bind_param("i", $idProveedor);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $precio = (float)($res['precio_hora'] ?? 0.00);
            $stmt->close();
        }

        return $precio;
    }
}
