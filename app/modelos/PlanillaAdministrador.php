<?php

require_once __DIR__ . "/Modelo.php";

/* Permite al administrador buscar y revisar las planillas registradas. */
final class PlanillaAdministrador extends Modelo
{
private function construirFiltros(array $filtros): array
{
    // Mantiene separados el SQL dinámico y los valores de la consulta preparada.
    $condiciones = [];
    $tipos = "";
    $valores = [];
    $buscar = trim((string) ($filtros["buscar"] ?? ""));

    if ($buscar !== "") {
        $termino = "%" . $buscar . "%";
        $condiciones[] = "(
            CAST(p.id_planilla AS CHAR) LIKE ?
            OR CONCAT(u.nombre, ' ', u.apellido) LIKE ?
            OR ub.nombre_ubicacion LIKE ?
            OR g.nombre_grupo LIKE ?
            OR a.nombre_asignatura LIKE ?
        )";
        $tipos .= "sssss";
        array_push($valores, $termino, $termino, $termino, $termino, $termino);
    }

    foreach ([
        "docente" => "p.id_docente",
        "ubicacion" => "p.id_ubicacion",
        "turno" => "p.id_turno",
        "asignatura" => "p.id_asignatura"
    ] as $filtro => $columna) {
        $valor = max(0, (int) ($filtros[$filtro] ?? 0));

        if ($valor > 0) {
            $condiciones[] = "{$columna} = ?";
            $tipos .= "i";
            $valores[] = $valor;
        }
    }

    return [
        "sql" => $condiciones ? " WHERE " . implode(" AND ", $condiciones) : "",
        "tipos" => $tipos,
        "valores" => $valores
    ];
}

public function obtener(array $filtros, int $limite = 10): array
{
    $conexion = self::conexion();
    $limite = max(1, min(50, $limite));
    $paginaSolicitada = max(1, (int) ($filtros["pagina"] ?? 1));
    $consultaFiltros = $this->construirFiltros($filtros);
    $ordenes = [
        "fecha" => "p.fecha DESC, p.hora_inicio DESC, p.id_planilla DESC",
        "antiguas" => "p.fecha ASC, p.hora_inicio ASC, p.id_planilla ASC",
        "id" => "p.id_planilla DESC",
        "docente" => "u.apellido ASC, u.nombre ASC, p.fecha DESC"
    ];
    $orden = $ordenes[$filtros["orden"] ?? "fecha"] ?? $ordenes["fecha"];

    $sentenciaTotal = $conexion->prepare(
        "SELECT COUNT(*) AS total
         FROM planilla p
         JOIN docente d ON d.id_usuario = p.id_docente
         JOIN usuario u ON u.id_usuario = d.id_usuario
         JOIN grupo g ON g.id_grupo = p.id_grupo
         JOIN asignatura a ON a.id_asignatura = p.id_asignatura
         JOIN ubicacion ub ON ub.id_ubicacion = p.id_ubicacion" .
         $consultaFiltros["sql"]
    );
    $valoresTotal = $consultaFiltros["valores"];

    try {
        self::enlazarParametros(
            $sentenciaTotal,
            $consultaFiltros["tipos"],
            $valoresTotal
        );
        $sentenciaTotal->execute();
        $total = (int) $sentenciaTotal->get_result()->fetch_assoc()["total"];
    } finally {
        $sentenciaTotal->close();
    }

    $totalPaginas = max(1, (int) ceil($total / $limite));
    $paginaActual = min($paginaSolicitada, $totalPaginas);
    $desplazamiento = ($paginaActual - 1) * $limite;

    $sentencia = $conexion->prepare(
        "SELECT
            p.id_planilla,
            p.id_docente,
            p.id_grupo,
            p.id_asignatura,
            p.id_turno,
            p.id_ubicacion,
            p.fecha,
            p.hora_inicio,
            p.hora_fin,
            CONCAT(u.nombre, ' ', u.apellido) AS docente,
            u.correo AS correo_docente,
            g.nombre_grupo AS grupo,
            a.nombre_asignatura AS asignatura,
            t.nombre_turno AS turno,
            ub.nombre_ubicacion AS ubicacion,
            (SELECT COUNT(*) FROM planilla_detalle pd WHERE pd.id_planilla = p.id_planilla) AS total_equipos,
            (SELECT COUNT(*) FROM planilla_detalle pd WHERE pd.id_planilla = p.id_planilla AND pd.estado_registrado = 'Correcto') AS equipos_correctos,
            (SELECT COUNT(*) FROM planilla_detalle pd WHERE pd.id_planilla = p.id_planilla AND pd.estado_registrado = 'Faltante') AS equipos_faltantes,
            (SELECT COUNT(*) FROM planilla_detalle pd WHERE pd.id_planilla = p.id_planilla AND pd.estado_registrado = 'Dañado') AS equipos_danados
         FROM planilla p
         JOIN docente d ON d.id_usuario = p.id_docente
         JOIN usuario u ON u.id_usuario = d.id_usuario
         JOIN grupo g ON g.id_grupo = p.id_grupo
         JOIN asignatura a ON a.id_asignatura = p.id_asignatura
         JOIN turno t ON t.id_turno = p.id_turno
         JOIN ubicacion ub ON ub.id_ubicacion = p.id_ubicacion" .
         $consultaFiltros["sql"] .
        " ORDER BY {$orden}
         LIMIT ? OFFSET ?"
    );
    $tipos = $consultaFiltros["tipos"] . "ii";
    $valores = array_merge($consultaFiltros["valores"], [$limite, $desplazamiento]);

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();
        $planillas = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }

    if ($planillas) {
        $ids = array_map(
            static fn (array $planilla): int => (int) $planilla["id_planilla"],
            $planillas
        );
        $marcadores = implode(", ", array_fill(0, count($ids), "?"));
        $sentenciaDetalles = $conexion->prepare(
            "SELECT
                pd.id_planilla_detalle,
                pd.id_planilla,
                pd.estado_registrado,
                pd.observacion,
                e.id_equipo,
                e.codigo,
                e.numero_serie,
                e.modelo,
                te.nombre_tipo AS tipo_equipo,
                CASE
                    WHEN al.id_alumno IS NULL THEN NULL
                    ELSE CONCAT(al.nombre, ' ', al.apellido)
                END AS alumno
             FROM planilla_detalle pd
             JOIN equipo e ON e.id_equipo = pd.id_equipo
             JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
             LEFT JOIN alumno al ON al.id_alumno = pd.id_alumno
             WHERE pd.id_planilla IN ({$marcadores})
             ORDER BY pd.id_planilla ASC, e.codigo ASC"
        );
        $tiposIds = str_repeat("i", count($ids));

        try {
            self::enlazarParametros($sentenciaDetalles, $tiposIds, $ids);
            $sentenciaDetalles->execute();
            $detalles = $sentenciaDetalles->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentenciaDetalles->close();
        }

        $detallesPorPlanilla = [];

        foreach ($detalles as $detalle) {
            $detallesPorPlanilla[(int) $detalle["id_planilla"]][] = $detalle;
        }

        foreach ($planillas as &$planilla) {
            $planilla["detalles"] = $detallesPorPlanilla[(int) $planilla["id_planilla"]] ?? [];
        }

        unset($planilla);
    }

    return [
        "planillas" => $planillas,
        "total" => $total,
        "total_paginas" => $totalPaginas,
        "pagina_actual" => $paginaActual
    ];
}

public function obtenerOpcionesFiltros(): array
{
    $conexion = self::conexion();

    return [
        "docentes" => $conexion->query(
            "SELECT DISTINCT u.id_usuario AS id, CONCAT(u.nombre, ' ', u.apellido) AS nombre
             FROM planilla p
             JOIN usuario u ON u.id_usuario = p.id_docente
             ORDER BY u.apellido ASC, u.nombre ASC"
        )->fetch_all(MYSQLI_ASSOC),
        "ubicaciones" => $conexion->query(
            "SELECT DISTINCT ub.id_ubicacion AS id, ub.nombre_ubicacion AS nombre
             FROM planilla p
             JOIN ubicacion ub ON ub.id_ubicacion = p.id_ubicacion
             ORDER BY ub.nombre_ubicacion ASC"
        )->fetch_all(MYSQLI_ASSOC),
        "turnos" => $conexion->query(
            "SELECT DISTINCT t.id_turno AS id, t.nombre_turno AS nombre
             FROM planilla p
             JOIN turno t ON t.id_turno = p.id_turno
             ORDER BY t.id_turno ASC"
        )->fetch_all(MYSQLI_ASSOC),
        "asignaturas" => $conexion->query(
            "SELECT DISTINCT a.id_asignatura AS id, a.nombre_asignatura AS nombre
             FROM planilla p
             JOIN asignatura a ON a.id_asignatura = p.id_asignatura
             ORDER BY a.nombre_asignatura ASC"
        )->fetch_all(MYSQLI_ASSOC)
    ];
}
}

