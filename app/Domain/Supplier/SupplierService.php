<?php
declare(strict_types=1);

namespace App\Domain\Supplier;

use mysqli;
use InvalidArgumentException;

class SupplierService implements SupplierServiceInterface
{
    public function __construct(private mysqli $conn) {}

    public function obtenerPorId(int $id, string $tipo): ?Supplier
    {
        $tableMap = [
            'agencia' => ['table' => 'agencias', 'name_col' => 'nombre_agencia'],
            'guia' => ['table' => 'guias_turisticos', 'name_col' => 'nombre_guia'],
            'local' => ['table' => 'lugares_locales', 'name_col' => 'nombre_local'],
        ];

        if (!isset($tableMap[$tipo])) {
            throw new InvalidArgumentException("Tipo de proveedor no válido: $tipo");
        }

        $config = $tableMap[$tipo];
        $table = $config['table'];
        $nameCol = $config['name_col'];

        $stmt = $this->conn->prepare("SELECT * FROM `$table` WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return new Supplier(
            id: (int)$row['id'],
            idUsuario: (int)($row['id_usuario'] ?? 0),
            tipo: $tipo,
            nombre: (string)($row[$nameCol] ?? ''),
            descripcion: (string)($row['descripcion'] ?? ''),
            email: (string)($row['contacto_email'] ?? ''),
            telefono: (string)($row['contacto_telefono'] ?? ''),
            imagenPerfil: (string)($row['imagen_perfil'] ?? ''),
            ciudad: (string)($row['ciudad_operacion'] ?? $row['ciudad'] ?? '')
        );
    }

    public function listarServicios(int $supplierId, string $tipo): array
    {
        $serviceTables = [
            'agencia' => ['table' => 'servicios_agencia', 'fk' => 'id_agencia'],
            'guia' => ['table' => 'servicios_guia', 'fk' => 'id_guia'],
            'local' => ['table' => 'servicios_local', 'fk' => 'id_local'],
        ];

        if (!isset($serviceTables[$tipo])) {
            throw new InvalidArgumentException("Tipo de proveedor no válido: $tipo");
        }

        $config = $serviceTables[$tipo];
        $table = $config['table'];
        $fk = $config['fk'];

        $stmt = $this->conn->prepare("SELECT * FROM `$table` WHERE `$fk` = ? ORDER BY id ASC");
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $res = $stmt->get_result();
        $services = [];
        while ($row = $res->fetch_assoc()) {
            $services[] = $row;
        }
        $stmt->close();

        return $services;
    }

    public function calcularPrecio(int $servicioId, string $tipo, array $opciones = []): float
    {
        $serviceTables = [
            'agencia' => 'servicios_agencia',
            'guia' => 'servicios_guia',
            'local' => 'servicios_local',
        ];

        if (!isset($serviceTables[$tipo])) {
            throw new InvalidArgumentException("Tipo de proveedor no válido: $tipo");
        }

        $table = $serviceTables[$tipo];
        $stmt = $this->conn->prepare("SELECT precio FROM `$table` WHERE id = ?");
        $stmt->bind_param("i", $servicioId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res) {
            return 0.0;
        }

        $unitPrice = (float)$res['precio'];
        $qty = (int)($opciones['cantidad'] ?? $opciones['personas'] ?? 1);
        if ($qty < 1) {
            $qty = 1;
        }

        return $unitPrice * $qty;
    }

    public function crearConfirmacion(int $pedidoId, int $supplierId, string $tipo): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO confirmacion_servicios (id_pedido_servicio, id_proveedor, tipo_proveedor, estado) VALUES (?, ?, ?, 'pendiente')"
        );
        $stmt->bind_param("iis", $pedidoId, $supplierId, $tipo);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
