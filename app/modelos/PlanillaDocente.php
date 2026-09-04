<?php

require_once __DIR__ . "/Modelo.php";

/* Registra una planilla del docente junto con la asistencia y estado de cada equipo. */
final class PlanillaDocente extends Modelo
{
public function obtenerOpciones(int $idDocente): array
{
    // Las opciones se limitan a grupos, turnos y asignaturas vinculados al docente.
    if ($idDocente < 1) {
        throw new DomainException("No fue posible identificar la cuenta del docente.");
    }

    $conexion = self::conexion();
    $sentenciaDocente = $conexion->prepare(
        "SELECT u.id_usuario
         FROM docente d
         JOIN usuario u ON u.id_usuario = d.id_usuario
         WHERE d.id_usuario = ? AND u.activo = 1
         LIMIT 1"
    );

    try {
        $sentenciaDocente->bind_param("i", $idDocente);
        $sentenciaDocente->execute();
        $docenteValido = $sentenciaDocente->get_result()->fetch_assoc();
    } finally {
        $sentenciaDocente->close();
    }

    if (!$docenteValido) {
        throw new DomainException("La cuenta no corresponde a un docente activo.");
    }

    $consultas = [
        "grupos" => "SELECT g.id_grupo AS id, g.nombre_grupo AS nombre
                      FROM docente_grupo dg
                      JOIN grupo g ON g.id_grupo = dg.id_grupo
                      WHERE dg.id_docente = ?
                      ORDER BY g.nombre_grupo",
        "asignaturas" => "SELECT a.id_asignatura AS id, a.nombre_asignatura AS nombre
                           FROM docente_asignatura da
                           JOIN asignatura a ON a.id_asignatura = da.id_asignatura
                           WHERE da.id_docente = ?
                           ORDER BY a.nombre_asignatura",
        "turnos" => "SELECT t.id_turno AS id, t.nombre_turno AS nombre
                      FROM docente_turno dt
                      JOIN turno t ON t.id_turno = dt.id_turno
                      WHERE dt.id_docente = ?
                      ORDER BY t.id_turno",
        "alumnos" => "SELECT DISTINCT al.id_alumno AS id,
                              al.id_grupo,
                              CONCAT_WS(' ', al.nombre, al.apellido) AS nombre
                       FROM docente_grupo dg
                       JOIN alumno al ON al.id_grupo = dg.id_grupo
                       WHERE dg.id_docente = ?
                       ORDER BY al.id_grupo, al.apellido, al.nombre, al.id_alumno"
    ];
    $opciones = [];

    foreach ($consultas as $clave => $sql) {
        $sentencia = $conexion->prepare($sql);

        try {
            $sentencia->bind_param("i", $idDocente);
            $sentencia->execute();
            $opciones[$clave] = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }

    $opciones["ubicaciones"] = $conexion->query(
        "SELECT u.id_ubicacion AS id,
                u.nombre_ubicacion AS nombre,
                COUNT(e.id_equipo) AS cantidad_equipos
         FROM ubicacion u
         JOIN equipo e ON e.id_ubicacion = u.id_ubicacion
         GROUP BY u.id_ubicacion, u.nombre_ubicacion
         ORDER BY u.nombre_ubicacion"
    )->fetch_all(MYSQLI_ASSOC);
    $opciones["equipos"] = $conexion->query(
        "SELECT e.id_equipo AS id,
                e.id_ubicacion,
                e.codigo,
                COALESCE(e.modelo, 'Sin modelo') AS modelo,
                te.nombre_tipo AS tipo,
                ee.nombre_estado AS estado_actual
         FROM equipo e
         JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
         JOIN estado_equipo ee ON ee.id_estado_equipo = e.id_estado_equipo
         WHERE e.id_ubicacion IS NOT NULL
         ORDER BY e.id_ubicacion, e.codigo"
    )->fetch_all(MYSQLI_ASSOC);
    $opciones["siguiente_id"] = (int) ($conexion->query(
        "SELECT COALESCE(MAX(id_planilla), 0) + 1 AS siguiente FROM planilla"
    )->fetch_assoc()["siguiente"] ?? 1);

    return $opciones;
}

private function valorRelacionadoConDocente(
    mysqli $conexion,
    string $tabla,
    string $columna,
    int $idDocente,
    int $valor
): bool {
    // Solo se permiten nombres de tabla y columna definidos por el propio modelo.
    $tablasPermitidas = [
        "docente_grupo" => "id_grupo",
        "docente_asignatura" => "id_asignatura",
        "docente_turno" => "id_turno"
    ];

    if (($tablasPermitidas[$tabla] ?? null) !== $columna) {
        throw new LogicException("Relación de planilla no permitida.");
    }

    $sentencia = $conexion->prepare(
        "SELECT 1 FROM {$tabla} WHERE id_docente = ? AND {$columna} = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("ii", $idDocente, $valor);
        $sentencia->execute();

        return (bool) $sentencia->get_result()->fetch_row();
    } finally {
        $sentencia->close();
    }
}

private function validarFecha(string $fecha): string
{
    $fecha = trim($fecha);
    $valor = DateTimeImmutable::createFromFormat("!Y-m-d", $fecha);

    if (!$valor || $valor->format("Y-m-d") !== $fecha) {
        throw new DomainException("Selecciona una fecha válida.");
    }

    if ($valor > new DateTimeImmutable("today")) {
        throw new DomainException("La fecha de la planilla no puede ser posterior a hoy.");
    }

    return $fecha;
}

private function validarHora(string $hora, string $etiqueta): string
{
    $hora = trim($hora);

    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora)) {
        throw new DomainException("Selecciona una {$etiqueta} válida.");
    }

    return $hora;
}

public function guardar(int $idDocente, array $datos): array
{
    if ($idDocente < 1) {
        throw new DomainException("No fue posible identificar la cuenta del docente.");
    }

    $idGrupo = (int) ($datos["id_grupo"] ?? 0);
    $idAsignatura = (int) ($datos["id_asignatura"] ?? 0);
    $idTurno = (int) ($datos["id_turno"] ?? 0);
    $idUbicacion = (int) ($datos["id_ubicacion"] ?? 0);
    $fecha = $this->validarFecha((string) ($datos["fecha"] ?? ""));
    $horaInicio = $this->validarHora(
        (string) ($datos["hora_inicio"] ?? ""),
        "hora de entrada"
    );
    $horaFin = $this->validarHora(
        (string) ($datos["hora_fin"] ?? ""),
        "hora de salida"
    );
    $detallesEntrada = $datos["detalles"] ?? [];

    if ($idGrupo < 1 || $idAsignatura < 1 || $idTurno < 1 || $idUbicacion < 1) {
        throw new DomainException("Completa el grupo, turno, asignatura y aula.");
    }

    if ($horaFin <= $horaInicio) {
        throw new DomainException("La hora de salida debe ser posterior a la hora de entrada.");
    }

    if (!is_array($detallesEntrada) || !$detallesEntrada) {
        throw new DomainException("Selecciona un aula con equipos para completar la planilla.");
    }

    if (count($detallesEntrada) > 100) {
        throw new DomainException("La planilla contiene más equipos de los permitidos.");
    }

    $conexion = self::conexion();
    // Cabecera, detalles y estados de equipos forman una única operación atómica.
    $conexion->begin_transaction();

    try {
        if (!$this->valorRelacionadoConDocente(
            $conexion,
            "docente_grupo",
            "id_grupo",
            $idDocente,
            $idGrupo
        )) {
            throw new DomainException("El grupo seleccionado no está asignado a tu cuenta.");
        }

        if (!$this->valorRelacionadoConDocente(
            $conexion,
            "docente_asignatura",
            "id_asignatura",
            $idDocente,
            $idAsignatura
        )) {
            throw new DomainException("La asignatura seleccionada no está asignada a tu cuenta.");
        }

        if (!$this->valorRelacionadoConDocente(
            $conexion,
            "docente_turno",
            "id_turno",
            $idDocente,
            $idTurno
        )) {
            throw new DomainException("El turno seleccionado no está asignado a tu cuenta.");
        }

        // El bloqueo conserva el inventario estable mientras se registra la planilla.
        $sentenciaEquipos = $conexion->prepare(
            "SELECT id_equipo, codigo
             FROM equipo
             WHERE id_ubicacion = ?
             ORDER BY id_equipo
             FOR UPDATE"
        );

        try {
            $sentenciaEquipos->bind_param("i", $idUbicacion);
            $sentenciaEquipos->execute();
            $equiposUbicacion = $sentenciaEquipos->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentenciaEquipos->close();
        }

        if (!$equiposUbicacion) {
            throw new DomainException("El aula seleccionada no tiene equipos registrados.");
        }

        $equiposPermitidos = [];

        foreach ($equiposUbicacion as $equipo) {
            $equiposPermitidos[(int) $equipo["id_equipo"]] = $equipo["codigo"];
        }

        $sentenciaAlumnos = $conexion->prepare(
            "SELECT id_alumno FROM alumno WHERE id_grupo = ?"
        );

        try {
            $sentenciaAlumnos->bind_param("i", $idGrupo);
            $sentenciaAlumnos->execute();
            $filasAlumnos = $sentenciaAlumnos->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentenciaAlumnos->close();
        }

        $alumnosPermitidos = array_fill_keys(
            array_map(static fn (array $fila): int => (int) $fila["id_alumno"], $filasAlumnos),
            true
        );
        $estadosPermitidos = ["Correcto", "Faltante", "Dañado"];
        $detalles = [];
        $idsEquiposRecibidos = [];
        $idsAlumnosUsados = [];

        foreach (array_values($detallesEntrada) as $detalleEntrada) {
            if (!is_array($detalleEntrada)) {
                throw new DomainException("Los datos de los equipos no son válidos.");
            }

            $idEquipo = (int) ($detalleEntrada["id_equipo"] ?? 0);
            $valorAlumno = trim((string) ($detalleEntrada["id_alumno"] ?? ""));
            $idAlumno = $valorAlumno === "" ? null : (int) $valorAlumno;
            $estado = trim((string) ($detalleEntrada["estado"] ?? ""));
            $observacion = trim((string) ($detalleEntrada["observacion"] ?? ""));

            if ($idEquipo < 1 || !isset($equiposPermitidos[$idEquipo])) {
                throw new DomainException("Uno de los equipos no pertenece al aula seleccionada.");
            }

            if (isset($idsEquiposRecibidos[$idEquipo])) {
                throw new DomainException("Un equipo aparece repetido en la planilla.");
            }

            if (!in_array($estado, $estadosPermitidos, true)) {
                throw new DomainException("Selecciona un estado válido para cada equipo.");
            }

            if ($idAlumno !== null) {
                if ($idAlumno < 1 || !isset($alumnosPermitidos[$idAlumno])) {
                    throw new DomainException("Uno de los alumnos no pertenece al grupo seleccionado.");
                }

                if (isset($idsAlumnosUsados[$idAlumno])) {
                    throw new DomainException("Un alumno no puede ocupar más de un equipo en la misma planilla.");
                }

                $idsAlumnosUsados[$idAlumno] = true;
            }

            if (mb_strlen($observacion) > 255) {
                throw new DomainException("Cada descripción puede tener hasta 255 caracteres.");
            }

            if ($estado !== "Correcto" && $observacion === "") {
                throw new DomainException(
                    "Describe el problema de cada equipo marcado como dañado o faltante."
                );
            }

            $idsEquiposRecibidos[$idEquipo] = true;
            $detalles[] = [
                "id_equipo" => $idEquipo,
                "id_alumno" => $idAlumno,
                "estado" => $estado,
                "observacion" => $observacion === "" ? null : $observacion
            ];
        }

        $idsEsperados = array_keys($equiposPermitidos);
        $idsRecibidos = array_keys($idsEquiposRecibidos);
        sort($idsEsperados, SORT_NUMERIC);
        sort($idsRecibidos, SORT_NUMERIC);

        if ($idsEsperados !== $idsRecibidos) {
            throw new DomainException(
                "La planilla debe incluir todos los equipos registrados en el aula."
            );
        }

        $sentenciaPlanilla = $conexion->prepare(
            "INSERT INTO planilla
                (id_docente, id_grupo, id_asignatura, id_turno, id_ubicacion, fecha, hora_inicio, hora_fin)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        try {
            $sentenciaPlanilla->bind_param(
                "iiiiisss",
                $idDocente,
                $idGrupo,
                $idAsignatura,
                $idTurno,
                $idUbicacion,
                $fecha,
                $horaInicio,
                $horaFin
            );
            $sentenciaPlanilla->execute();
            $idPlanilla = (int) $conexion->insert_id;
        } finally {
            $sentenciaPlanilla->close();
        }

        $sentenciaDetalle = $conexion->prepare(
            "INSERT INTO planilla_detalle
                (id_planilla, id_alumno, id_equipo, estado_registrado, observacion)
             VALUES (?, ?, ?, ?, ?)"
        );
        $incidencias = array_filter(
            $detalles,
            static fn (array $detalle): bool => $detalle["estado"] !== "Correcto"
        );
        $idPrioridadMedia = null;
        $idEstadoPendiente = null;
        $sentenciaTicket = null;

        if ($incidencias) {
            $idPrioridadMedia = (int) ($conexion->query(
                "SELECT id_prioridad FROM prioridad WHERE nombre_prioridad = 'Media' LIMIT 1"
            )->fetch_assoc()["id_prioridad"] ?? 0);
            $idEstadoPendiente = (int) ($conexion->query(
                "SELECT id_estado_ticket FROM estado_ticket WHERE nombre_estado = 'Pendiente' LIMIT 1"
            )->fetch_assoc()["id_estado_ticket"] ?? 0);

            if ($idPrioridadMedia < 1 || $idEstadoPendiente < 1) {
                throw new RuntimeException("No existe la configuración inicial para generar tickets.");
            }

            $sentenciaTicket = $conexion->prepare(
                "INSERT INTO ticket
                    (id_planilla_detalle, id_equipo, id_prioridad, id_estado_ticket, descripcion)
                 VALUES (?, ?, ?, ?, ?)"
            );
        }

        $ticketsCreados = 0;

        try {
            foreach ($detalles as $detalle) {
                $idAlumno = $detalle["id_alumno"];
                $idEquipo = $detalle["id_equipo"];
                $estado = $detalle["estado"];
                $observacion = $detalle["observacion"];
                $sentenciaDetalle->bind_param(
                    "iiiss",
                    $idPlanilla,
                    $idAlumno,
                    $idEquipo,
                    $estado,
                    $observacion
                );
                $sentenciaDetalle->execute();
                $idDetalle = (int) $conexion->insert_id;

                if ($estado !== "Correcto" && $sentenciaTicket instanceof mysqli_stmt) {
                    $codigoEquipo = $equiposPermitidos[$idEquipo];
                    $tipoIncidencia = $estado === "Faltante" ? "Equipo faltante" : "Equipo dañado";
                    $descripcionTicket = mb_substr(
                        "{$tipoIncidencia} {$codigoEquipo}: {$observacion}. " .
                        "Incidencia registrada en la planilla #{$idPlanilla}.",
                        0,
                        255
                    );
                    $sentenciaTicket->bind_param(
                        "iiiis",
                        $idDetalle,
                        $idEquipo,
                        $idPrioridadMedia,
                        $idEstadoPendiente,
                        $descripcionTicket
                    );
                    $sentenciaTicket->execute();
                    $ticketsCreados++;
                }
            }
        } finally {
            $sentenciaDetalle->close();

            if ($sentenciaTicket instanceof mysqli_stmt) {
                $sentenciaTicket->close();
            }
        }

        $conexion->commit();

        return [
            "id_planilla" => $idPlanilla,
            "tickets_creados" => $ticketsCreados
        ];
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}
}
