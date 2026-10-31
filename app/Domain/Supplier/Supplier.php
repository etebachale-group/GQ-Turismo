<?php
declare(strict_types=1);

namespace App\Domain\Supplier;

class Supplier
{
    public function __construct(
        public int $id,
        public int $idUsuario,
        public string $tipo, // 'agencia', 'guia', 'local'
        public string $nombre,
        public string $descripcion = '',
        public string $email = '',
        public string $telefono = '',
        public string $imagenPerfil = '',
        public string $ciudad = ''
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'id_usuario' => $this->idUsuario,
            'tipo' => $this->tipo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'imagen_perfil' => $this->imagenPerfil,
            'ciudad' => $this->ciudad,
        ];
    }
}
