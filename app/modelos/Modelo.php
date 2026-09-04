<?php

require_once __DIR__ . "/../../config/conexion.php";

/*
 * Clase base de la capa de datos. Evita repetir la obtención de la conexión
 * y el enlace de parámetros en consultas construidas de forma dinámica.
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
        // bind_param exige referencias; este arreglo las prepara sin usar SQL concatenado.
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
