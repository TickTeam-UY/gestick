<?php

require_once __DIR__ . "/Modelo.php";

/* Genera indicadores administrativos a partir de filtros de fecha y ubicación. */
final class MetricaAdministrador extends Modelo
{
private function condicionesFecha(
    string $columna,
    array $filtros,
    string $prefijo = ""
): array {
    // Devuelve la cláusula y sus parámetros para reutilizarla en varias métricas.
    $condiciones = [];
    $tipos = "";
    $valores = [];

    if (($filtros["desde"] ?? "") !== "") {
        $condiciones[] = "{$columna} >= ?";
        $tipos .= "s";
        $valores[] = $filtros["desde"] . $prefijo;
    }

    if (($filtros["hasta"] ?? "") !== "") {
        $condiciones[] = "{$columna} <= ?";
        $tipos .= "s";
        $valores[] = $filtros["hasta"] . ($prefijo === " 00:00:00" ? " 23:59:59" : $prefijo);
    }

    return [
        "condiciones" => $condiciones,
        "tipos" => $tipos,
        "valores" => $valores
    ];
}

private function ejecutar(
    mysqli $conexion,
    string $sql,
    string $tipos,
    array $valores
): array {
    $sentencia = $conexion->prepare($sql);

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_assoc() ?: [];
    } finally {
        $sentencia->close();
    }
}

public function obtenerUbicaciones(): array
{
    return self::conexion()->query(
        "SELECT id_ubicacion AS id, nombre_ubicacion AS nombre
         FROM ubicacion
         ORDER BY nombre_ubicacion ASC"
    )->fetch_all(MYSQLI_ASSOC);
}

public function obtenerResumen(array $filtros): array
{
    $conexion = self::conexion();
    $idUbicacion = max(0, (int) ($filtros["ubicacion"] ?? 0));

    $filtroTickets = $this->condicionesFecha(
        "DATE(t.fecha_generado)",
        $filtros
    );

    if ($idUbicacion > 0) {
        $filtroTickets["condiciones"][] = "e.id_ubicacion = ?";
        $filtroTickets["tipos"] .= "i";
        $filtroTickets["valores"][] = $idUbicacion;
    }

    $dondeTickets = $filtroTickets["condiciones"]
        ? " WHERE " . implode(" AND ", $filtroTickets["condiciones"])
        : "";
    $tickets = $this->ejecutar(
        $conexion,
        "SELECT
            COUNT(*) AS total,
            SUM(et.nombre_estado = 'Resuelto') AS resueltos,
            AVG(
                CASE
                    WHEN et.nombre_estado = 'Resuelto' AND t.fecha_fin IS NOT NULL
                    THEN TIMESTAMPDIFF(
                        SECOND,
                        COALESCE(t.fecha_inicio, t.fecha_generado),
                        t.fecha_fin
                    )
                END
            ) AS promedio_segundos
         FROM ticket t
         JOIN estado_ticket et ON et.id_estado_ticket = t.id_estado_ticket
         JOIN equipo e ON e.id_equipo = t.id_equipo" . $dondeTickets,
        $filtroTickets["tipos"],
        $filtroTickets["valores"]
    );

    $filtroSolicitudes = $this->condicionesFecha(
        "DATE(s.fecha_envio)",
        $filtros
    );
    $dondeSolicitudes = $filtroSolicitudes["condiciones"]
        ? " WHERE " . implode(" AND ", $filtroSolicitudes["condiciones"])
        : "";
    $solicitudes = $this->ejecutar(
        $conexion,
        "SELECT
            COUNT(*) AS total,
            SUM(es.nombre_estado = 'Completada') AS resueltas
         FROM solicitud s
         JOIN estado_solicitud es ON es.id_estado_solicitud = s.id_estado_solicitud" .
         $dondeSolicitudes,
        $filtroSolicitudes["tipos"],
        $filtroSolicitudes["valores"]
    );

    $filtroPlanillas = $this->condicionesFecha("p.fecha", $filtros);

    if ($idUbicacion > 0) {
        $filtroPlanillas["condiciones"][] = "p.id_ubicacion = ?";
        $filtroPlanillas["tipos"] .= "i";
        $filtroPlanillas["valores"][] = $idUbicacion;
    }

    $dondePlanillas = $filtroPlanillas["condiciones"]
        ? " WHERE " . implode(" AND ", $filtroPlanillas["condiciones"])
        : "";
    $planillas = $this->ejecutar(
        $conexion,
        "SELECT COUNT(*) AS total FROM planilla p" . $dondePlanillas,
        $filtroPlanillas["tipos"],
        $filtroPlanillas["valores"]
    );

    $filtroPrestamos = $this->condicionesFecha("p.fecha_prestamo", $filtros);

    if ($idUbicacion > 0) {
        $filtroPrestamos["condiciones"][] = "EXISTS (
            SELECT 1
            FROM prestamo_equipo pef
            JOIN equipo ef ON ef.id_equipo = pef.id_equipo
            WHERE pef.id_prestamo = p.id_prestamo
              AND ef.id_ubicacion = ?
        )";
        $filtroPrestamos["tipos"] .= "i";
        $filtroPrestamos["valores"][] = $idUbicacion;
    }

    $dondePrestamos = $filtroPrestamos["condiciones"]
        ? " WHERE " . implode(" AND ", $filtroPrestamos["condiciones"])
        : "";
    $prestamos = $this->ejecutar(
        $conexion,
        "SELECT
            COUNT(*) AS total,
            SUM(ep.nombre_estado IN ('Activo', 'Atrasado')) AS activos,
            SUM(ep.nombre_estado = 'Devuelto') AS devueltos,
            SUM(ep.nombre_estado = 'Atrasado') AS atrasados
         FROM prestamo p
         JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo" .
         $dondePrestamos,
        $filtroPrestamos["tipos"],
        $filtroPrestamos["valores"]
    );

    return [
        "tickets_total" => (int) ($tickets["total"] ?? 0),
        "tickets_resueltos" => (int) ($tickets["resueltos"] ?? 0),
        "promedio_segundos" => $tickets["promedio_segundos"] === null
            ? null
            : (int) round((float) $tickets["promedio_segundos"]),
        "solicitudes_total" => (int) ($solicitudes["total"] ?? 0),
        "solicitudes_resueltas" => (int) ($solicitudes["resueltas"] ?? 0),
        "planillas_total" => (int) ($planillas["total"] ?? 0),
        "prestamos_total" => (int) ($prestamos["total"] ?? 0),
        "prestamos_activos" => (int) ($prestamos["activos"] ?? 0),
        "prestamos_devueltos" => (int) ($prestamos["devueltos"] ?? 0),
        "prestamos_atrasados" => (int) ($prestamos["atrasados"] ?? 0)
    ];
}

public function obtenerEquiposConMasFallas(array $filtros, int $limite = 5): array
{
    $conexion = self::conexion();
    $limite = max(1, min(20, $limite));
    $idUbicacion = max(0, (int) ($filtros["ubicacion"] ?? 0));
    $consultaFiltros = $this->condicionesFecha(
        "DATE(t.fecha_generado)",
        $filtros
    );

    if ($idUbicacion > 0) {
        $consultaFiltros["condiciones"][] = "e.id_ubicacion = ?";
        $consultaFiltros["tipos"] .= "i";
        $consultaFiltros["valores"][] = $idUbicacion;
    }

    $donde = $consultaFiltros["condiciones"]
        ? " WHERE " . implode(" AND ", $consultaFiltros["condiciones"])
        : "";
    $sentencia = $conexion->prepare(
        "SELECT
            e.id_equipo,
            e.codigo,
            te.nombre_tipo AS tipo,
            COALESCE(u.nombre_ubicacion, 'Sin ubicación') AS ubicacion,
            COUNT(t.id_ticket) AS incidencias
         FROM ticket t
         JOIN equipo e ON e.id_equipo = t.id_equipo
         JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
         LEFT JOIN ubicacion u ON u.id_ubicacion = e.id_ubicacion" .
         $donde .
        " GROUP BY e.id_equipo, e.codigo, te.nombre_tipo, u.nombre_ubicacion
         ORDER BY incidencias DESC, e.codigo ASC
         LIMIT ?"
    );
    $tipos = $consultaFiltros["tipos"] . "i";
    $valores = array_merge($consultaFiltros["valores"], [$limite]);

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }
}
}
