<?php
declare(strict_types=1);

namespace App\Domain\Supplier;

interface SupplierServiceInterface
{
    public function obtenerPorId(int $id, string $tipo): ?Supplier;

    public function listarServicios(int $supplierId, string $tipo): array;

    public function calcularPrecio(int $servicioId, string $tipo, array $opciones = []): float;

    public function crearConfirmacion(int $pedidoId, int $supplierId, string $tipo): bool;
}
