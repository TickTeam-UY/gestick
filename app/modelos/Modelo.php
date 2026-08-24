<?php

require_once __DIR__ . "/../../config/conexion.php";

/*
 * Clase base de la capa de datos.
 */
abstract class Modelo
{
    protected static function conexion(): mysqli
    {
        return Conexion::obtener();
    }

    public static function enlazarParametros(
        mysqli_stmt $sentencia,
        string $tipos,
        array &$valores
    ): void {
        if ($tipos === "") {
            return;
        }

        $referencias = [$tipos];

        foreach ($valores as $indice => $valor) {
            $valores[$indice] = $valor;
            $referencias[] = &$valores[$indice];
        }

        $sentencia->bind_param(...$referencias);
    }
}
