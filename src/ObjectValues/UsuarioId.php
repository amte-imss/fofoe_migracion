<?php

namespace App\ObjectValues;

final class UsuarioId
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function fromString($id)
    {
        return new self($id);
    }

    /**
     * @return int
     */
    public function asInt()
    {
        return $this->id;
    }
}
