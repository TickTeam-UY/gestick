<?php

require_once __DIR__ . "/Modelo.php";

/* Consultas y acciones de asignación de tickets exclusivas del administrador. */
final class TicketAdministrador extends Modelo
{
public function obtener(array $filtros, int $porPagina = 10): array
{
    // Cada filtro añade su cláusula y su parámetro preparado en paralelo.
    $conexion = self::conexion();
    $porPagina = max(1, min($porPagina, 50));
    $condiciones = [];
    $parametros = [];

    $buscar = trim((string) ($filtros["buscar"] ?? ""));

    if ($buscar !== "") {
        $condiciones[] = "CONCAT_WS(' ',
                t.id_ticket,
                e.codigo,
                e.modelo,
                te.nombre_tipo,
                ub.nombre_ubicacion,
                t.descripcion,
                pr.nombre_prioridad,
                et.nombre_estado,
                CONCAT_WS(' ', ut.nombre, ut.apellido),
                CONCAT_WS(' ', ud.nombre, ud.apellido)
            ) LIKE CONCAT('%', ?, '%')";
        $parametros[] = $buscar;
    }

    $filtrosEnteros = [
        "ubicacion" => "e.id_ubicacion",
        "docente" => "pl.id_docente",
        "tecnico" => "t.id_tecnico",
        "tipo_equipo" => "e.id_tipo_equipo"
    ];

    foreach ($filtrosEnteros as $clave => $columna) {
        $valor = (int) ($filtros[$clave] ?? 0);

        if ($valor > 0) {
            $condiciones[] = "$columna = ?";
            $parametros[] = $valor;
        }
    }

    if ((int) ($filtros["finalizado"] ?? 0) === 1) {
        $condiciones[] = "(
            t.fecha_fin IS NOT NULL
            OR LOWER(et.nombre_estado) IN ('resuelto', 'finalizado', 'cerrado')
        )";
    }

    $desde = " FROM ticket AS t
        INNER JOIN equipo AS e
            ON e.id_equipo = t.id_equipo
        INNER JOIN tipo_equipo AS te
            ON te.id_tipo_equipo = e.id_tipo_equipo
        LEFT JOIN ubicacion AS ub
            ON ub.id_ubicacion = e.id_ubicacion
        LEFT JOIN usuario AS ut
            ON ut.id_usuario = t.id_tecnico
        INNER JOIN prioridad AS pr
            ON pr.id_prioridad = t.id_prioridad
        INNER JOIN estado_ticket AS et
            ON et.id_estado_ticket = t.id_estado_ticket
        LEFT JOIN planilla_detalle AS pd
            ON pd.id_planilla_detalle = t.id_planilla_detalle
        LEFT JOIN planilla AS pl
            ON pl.id_planilla = pd.id_planilla
        LEFT JOIN usuario AS ud
            ON ud.id_usuario = pl.id_docente";

    $donde = $condiciones ? " WHERE " . implode(" AND ", $condiciones) : "";
    $sentenciaTotal = $conexion->prepare("SELECT COUNT(*) AS total" . $desde . $donde);

    try {
        $sentenciaTotal->execute($parametros);
        $total = (int) ($sentenciaTotal->get_result()->fetch_assoc()["total"] ?? 0);
    } finally {
        $sentenciaTotal->close();
    }

    $totalPaginas = max(1, (int) ceil($total / $porPagina));
    $paginaActual = min(max(1, (int) ($filtros["pagina"] ?? 1)), $totalPaginas);
    $desplazamiento = ($paginaActual - 1) * $porPagina;

    $ordenes = [
        "fecha" => "t.fecha_generado DESC, t.id_ticket DESC",
        "id" => "t.id_ticket DESC",
        "prioridad" => "CASE LOWER(pr.nombre_prioridad)
                WHEN 'crítica' THEN 1
                WHEN 'critica' THEN 1
                WHEN 'alta' THEN 2
                WHEN 'media' THEN 3
                WHEN 'baja' THEN 4
                ELSE 5
            END, t.fecha_generado DESC, t.id_ticket DESC"
    ];
    $orden = $ordenes[$filtros["orden"] ?? "fecha"] ?? $ordenes["fecha"];

    $sql = "SELECT t.id_ticket,
                   t.id_tecnico,
                   t.id_prioridad,
                   t.id_estado_ticket,
                   t.descripcion,
                   t.solucion,
                   t.fecha_generado,
                   t.fecha_inicio,
                   t.fecha_fin,
                   e.codigo AS equipo,
                   e.modelo,
                   te.nombre_tipo AS tipo_equipo,
                   COALESCE(ub.nombre_ubicacion, 'Sin ubicación asignada') AS ubicacion,
                   CONCAT_WS(' ', ut.nombre, ut.apellido) AS tecnico,
                   pr.nombre_prioridad AS prioridad,
                   et.nombre_estado AS estado,
                   pl.id_planilla,
                   CONCAT_WS(' ', ud.nombre, ud.apellido) AS docente"
        . $desde
        . $donde
        . " ORDER BY " . $orden
        . " LIMIT ? OFFSET ?";

    $parametrosListado = [...$parametros, $porPagina, $desplazamiento];
    $sentencia = $conexion->prepare($sql);

    try {
        $sentencia->execute($parametrosListado);
        $tickets = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }

    return [
        "tickets" => $tickets,
        "total" => $total,
        "total_paginas" => $totalPaginas,
        "pagina_actual" => $paginaActual,
        "por_pagina" => $porPagina
    ];
}

public function obtenerOpcionesFiltros(): array
{
    $conexion = self::conexion();

    $consultas = [
        "ubicaciones" => "SELECT id_ubicacion AS id, nombre_ubicacion AS nombre
                           FROM ubicacion
                           ORDER BY nombre_ubicacion",
        "docentes" => "SELECT u.id_usuario AS id,
                              CONCAT_WS(' ', u.nombre, u.apellido) AS nombre
                       FROM docente AS d
                       INNER JOIN usuario AS u ON u.id_usuario = d.id_usuario
                       WHERE u.activo = 1
                       ORDER BY u.nombre, u.apellido",
        "tecnicos" => "SELECT u.id_usuario AS id,
                              CONCAT_WS(' ', u.nombre, u.apellido) AS nombre
                       FROM tecnico AS t
                       INNER JOIN usuario AS u ON u.id_usuario = t.id_usuario
                       WHERE u.activo = 1
                       ORDER BY u.nombre, u.apellido",
        "prioridades" => "SELECT id_prioridad AS id, nombre_prioridad AS nombre
                           FROM prioridad
                           ORDER BY id_prioridad",
        "estados" => "SELECT id_estado_ticket AS id, nombre_estado AS nombre
                       FROM estado_ticket
                       ORDER BY id_estado_ticket",
        "tipos_equipo" => "SELECT id_tipo_equipo AS id, nombre_tipo AS nombre
                            FROM tipo_equipo
                            ORDER BY nombre_tipo"
    ];

    $opciones = [];

    foreach ($consultas as $clave => $sql) {
        $resultado = $conexion->query($sql);
        $opciones[$clave] = $resultado->fetch_all(MYSQLI_ASSOC);
    }

    return $opciones;
}

public function administrar(
    int $idTicket,
    ?int $idTecnico,
    int $idPrioridad,
    int $idEstado,
    ?string $solucion
): void {
    $conexion = self::conexion();
    // Bloquear el ticket impide que una asignación y una autoasignación se pisen.
    $conexion->begin_transaction();

    try {
        $sentenciaTicket = $conexion->prepare(
            "SELECT id_ticket FROM ticket WHERE id_ticket = ? FOR UPDATE"
        );

        try {
            $sentenciaTicket->bind_param("i", $idTicket);
            $sentenciaTicket->execute();
            $ticket = $sentenciaTicket->get_result()->fetch_assoc();
        } finally {
            $sentenciaTicket->close();
        }

        if (!$ticket) {
            throw new DomainException("El ticket seleccionado ya no existe.");
        }

        if ($idTecnico !== null) {
            $sentenciaTecnico = $conexion->prepare(
                "SELECT u.id_usuario
                 FROM tecnico te
                 JOIN usuario u ON u.id_usuario = te.id_usuario
                 WHERE u.id_usuario = ? AND u.activo = 1
                 LIMIT 1"
            );

            try {
                $sentenciaTecnico->bind_param("i", $idTecnico);
                $sentenciaTecnico->execute();
                $tecnico = $sentenciaTecnico->get_result()->fetch_assoc();
            } finally {
                $sentenciaTecnico->close();
            }

            if (!$tecnico) {
                throw new DomainException("Selecciona un técnico activo válido.");
            }
        }

        $sentenciaPrioridad = $conexion->prepare(
            "SELECT id_prioridad FROM prioridad WHERE id_prioridad = ? LIMIT 1"
        );

        try {
            $sentenciaPrioridad->bind_param("i", $idPrioridad);
            $sentenciaPrioridad->execute();
            $prioridad = $sentenciaPrioridad->get_result()->fetch_assoc();
        } finally {
            $sentenciaPrioridad->close();
        }

        if (!$prioridad) {
            throw new DomainException("Selecciona una prioridad válida.");
        }

        $sentenciaEstado = $conexion->prepare(
            "SELECT nombre_estado FROM estado_ticket WHERE id_estado_ticket = ? LIMIT 1"
        );

        try {
            $sentenciaEstado->bind_param("i", $idEstado);
            $sentenciaEstado->execute();
            $estado = $sentenciaEstado->get_result()->fetch_assoc();
        } finally {
            $sentenciaEstado->close();
        }

        if (!$estado) {
            throw new DomainException("Selecciona un estado válido.");
        }

        $nombreEstado = $estado["nombre_estado"];

        if ($nombreEstado !== "Pendiente" && $idTecnico === null) {
            throw new DomainException(
                "Asigna un técnico antes de poner el ticket en proceso o resolverlo."
            );
        }

        $solucion = trim((string) $solucion);

        if ($nombreEstado === "Resuelto" && $solucion === "") {
            throw new DomainException("Escribe la solución antes de cerrar el ticket.");
        }

        $solucionGuardada = $solucion === "" ? null : $solucion;

        if ($nombreEstado === "Pendiente") {
            $sql = "UPDATE ticket
                    SET id_tecnico = ?, id_prioridad = ?, id_estado_ticket = ?,
                        solucion = ?, fecha_inicio = NULL, fecha_fin = NULL
                    WHERE id_ticket = ?";
        } elseif ($nombreEstado === "En proceso") {
            $sql = "UPDATE ticket
                    SET id_tecnico = ?, id_prioridad = ?, id_estado_ticket = ?,
                        solucion = ?, fecha_inicio = COALESCE(fecha_inicio, NOW()), fecha_fin = NULL
                    WHERE id_ticket = ?";
        } else {
            $sql = "UPDATE ticket
                    SET id_tecnico = ?, id_prioridad = ?, id_estado_ticket = ?,
                        solucion = ?, fecha_inicio = COALESCE(fecha_inicio, NOW()),
                        fecha_fin = COALESCE(fecha_fin, NOW())
                    WHERE id_ticket = ?";
        }

        $sentenciaActualizar = $conexion->prepare($sql);

        try {
            $sentenciaActualizar->bind_param(
                "iiisi",
                $idTecnico,
                $idPrioridad,
                $idEstado,
                $solucionGuardada,
                $idTicket
            );
            $sentenciaActualizar->execute();
        } finally {
            $sentenciaActualizar->close();
        }

        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}
}
