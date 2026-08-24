<?php

require_once __DIR__ . "/Modelo.php";

final class Administrador extends Modelo
{
    public function obtenerUltimosTickets(int $limite = 2): array
    {
        $limite = max(1, min($limite, 10));
        $sentencia = self::conexion()->prepare(
            "SELECT id_ticket, equipo, descripcion, prioridad, estado, fecha_generado
             FROM vista_tickets
             ORDER BY fecha_generado DESC, id_ticket DESC
             LIMIT ?"
        );

        try {
            $sentencia->bind_param("i", $limite);
            $sentencia->execute();

            return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }

    public function obtenerUltimasSolicitudes(int $limite = 2): array
    {
        $limite = max(1, min($limite, 10));
        $sentencia = self::conexion()->prepare(
            "SELECT s.id_solicitud,
                    s.asunto,
                    s.descripcion,
                    es.nombre_estado AS estado,
                    s.fecha_envio,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS docente
             FROM solicitud AS s
             INNER JOIN estado_solicitud AS es
                ON es.id_estado_solicitud = s.id_estado_solicitud
             INNER JOIN usuario AS u
                ON u.id_usuario = s.id_docente
             ORDER BY s.fecha_envio DESC, s.id_solicitud DESC
             LIMIT ?"
        );

        try {
            $sentencia->bind_param("i", $limite);
            $sentencia->execute();

            return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }

    public function obtenerPlanillasRecientes(int $limite = 5): array
    {
        $limite = max(1, min($limite, 20));
        $sentencia = self::conexion()->prepare(
            "SELECT p.id_planilla,
                    ub.nombre_ubicacion AS laboratorio,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS docente,
                    p.fecha
             FROM planilla AS p
             INNER JOIN ubicacion AS ub
                ON ub.id_ubicacion = p.id_ubicacion
             INNER JOIN usuario AS u
                ON u.id_usuario = p.id_docente
             ORDER BY p.fecha DESC, p.id_planilla DESC
             LIMIT ?"
        );

        try {
            $sentencia->bind_param("i", $limite);
            $sentencia->execute();

            return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }

    public function obtenerAvisos(): array
    {
        $sentencia = self::conexion()->prepare(
            "SELECT
                (
                    SELECT COUNT(*)
                    FROM ticket AS t
                    INNER JOIN estado_ticket AS et
                        ON et.id_estado_ticket = t.id_estado_ticket
                    WHERE et.nombre_estado = 'Pendiente'
                ) AS tickets_pendientes,
                (
                    SELECT COUNT(*)
                    FROM solicitud AS s
                    INNER JOIN estado_solicitud AS es
                        ON es.id_estado_solicitud = s.id_estado_solicitud
                    WHERE es.nombre_estado = 'Pendiente'
                ) AS solicitudes_pendientes,
                (
                    SELECT COUNT(*)
                    FROM equipo AS e
                    INNER JOIN estado_equipo AS ee
                        ON ee.id_estado_equipo = e.id_estado_equipo
                    WHERE ee.nombre_estado IN ('En reparación', 'Dañado', 'Faltante')
                ) AS equipos_con_alerta"
        );

        try {
            $sentencia->execute();
            $cantidades = $sentencia->get_result()->fetch_assoc();
        } finally {
            $sentencia->close();
        }

        $tickets = (int) ($cantidades["tickets_pendientes"] ?? 0);
        $solicitudes = (int) ($cantidades["solicitudes_pendientes"] ?? 0);
        $equipos = (int) ($cantidades["equipos_con_alerta"] ?? 0);

        return [
            [
                "icono" => "bi-ticket-perforated",
                "titulo" => $tickets === 1 ? "1 ticket pendiente" : "$tickets tickets pendientes",
                "detalle" => $tickets > 0
                    ? "Requieren revisión del equipo técnico."
                    : "No hay tickets pendientes."
            ],
            [
                "icono" => "bi-chat-left-text",
                "titulo" => $solicitudes === 1
                    ? "1 solicitud pendiente"
                    : "$solicitudes solicitudes pendientes",
                "detalle" => $solicitudes > 0
                    ? "Esperan respuesta o asignación."
                    : "No hay solicitudes pendientes."
            ],
            [
                "icono" => "bi-pc-display-horizontal",
                "titulo" => $equipos === 1 ? "1 equipo con alerta" : "$equipos equipos con alerta",
                "detalle" => $equipos > 0
                    ? "Figuran dañados, faltantes o en reparación."
                    : "Todos los equipos están en estado correcto."
            ]
        ];
    }
}
