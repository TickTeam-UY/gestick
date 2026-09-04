<?php

require_once __DIR__ . "/Modelo.php";

/* Calcula el resumen y la actividad reciente del docente autenticado. */
final class InicioDocente extends Modelo
{
    public function resumenVacio(): array
    {
        return [
            "total" => 0,
            "pendientes" => 0,
            "en_proceso" => 0,
            "completadas" => 0
        ];
    }

    public function obtenerResumen(int $idDocente): array
    {
        if ($idDocente < 1) {
            return $this->resumenVacio();
        }

        $sentencia = self::conexion()->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(es.nombre_estado = 'Pendiente'), 0) AS pendientes,
                    COALESCE(SUM(es.nombre_estado = 'En proceso'), 0) AS en_proceso,
                    COALESCE(SUM(es.nombre_estado = 'Completada'), 0) AS completadas
             FROM solicitud AS s
             INNER JOIN estado_solicitud AS es
                ON es.id_estado_solicitud = s.id_estado_solicitud
             WHERE s.id_docente = ?"
        );

        try {
            $sentencia->bind_param("i", $idDocente);
            $sentencia->execute();
            $resumen = $sentencia->get_result()->fetch_assoc();
        } finally {
            $sentencia->close();
        }

        return [
            "total" => (int) ($resumen["total"] ?? 0),
            "pendientes" => (int) ($resumen["pendientes"] ?? 0),
            "en_proceso" => (int) ($resumen["en_proceso"] ?? 0),
            "completadas" => (int) ($resumen["completadas"] ?? 0)
        ];
    }

    public function obtenerSolicitudesRecientes(int $idDocente, int $cantidad = 8): array
    {
        if ($idDocente < 1) {
            return [];
        }

        $cantidad = max(1, min($cantidad, 20));
        $sentencia = self::conexion()->prepare(
            "SELECT s.id_solicitud,
                    s.asunto,
                    s.descripcion,
                    s.fecha_envio,
                    es.nombre_estado AS estado
             FROM solicitud AS s
             INNER JOIN estado_solicitud AS es
                ON es.id_estado_solicitud = s.id_estado_solicitud
             WHERE s.id_docente = ?
             ORDER BY s.fecha_envio DESC, s.id_solicitud DESC
             LIMIT ?"
        );

        try {
            $sentencia->bind_param("ii", $idDocente, $cantidad);
            $sentencia->execute();

            return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }
}
