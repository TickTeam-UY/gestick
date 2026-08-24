<?php

require_once __DIR__ . "/Modelo.php";

final class PrestamoAdministrador extends Modelo
{
private function construirFiltros(array $filtros): array
{
    $condiciones = [];
    $tipos = "";
    $valores = [];
    $buscar = trim((string) ($filtros["buscar"] ?? ""));
    $estado = max(0, (int) ($filtros["estado"] ?? 0));

    if ($buscar !== "") {
        $termino = "%" . $buscar . "%";
        $condiciones[] = "(
            CAST(p.id_prestamo AS CHAR) LIKE ?
            OR CONCAT(al.nombre, ' ', al.apellido) LIKE ?
            OR g.nombre_grupo LIKE ?
            OR EXISTS (
                SELECT 1
                FROM prestamo_equipo pex
                JOIN equipo ex ON ex.id_equipo = pex.id_equipo
                WHERE pex.id_prestamo = p.id_prestamo
                  AND ex.codigo LIKE ?
            )
        )";
        $tipos .= "ssss";
        array_push($valores, $termino, $termino, $termino, $termino);
    }

    if ($estado > 0) {
        $condiciones[] = "p.id_estado_prestamo = ?";
        $tipos .= "i";
        $valores[] = $estado;
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

    $sentenciaTotal = $conexion->prepare(
        "SELECT COUNT(*) AS total
         FROM prestamo p
         JOIN alumno al ON al.id_alumno = p.id_alumno
         JOIN grupo g ON g.id_grupo = al.id_grupo" .
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
            p.id_prestamo,
            p.id_alumno,
            p.fecha_prestamo,
            p.fecha_devolucion,
            p.id_estado_prestamo,
            ep.nombre_estado AS estado,
            CONCAT(al.nombre, ' ', al.apellido) AS alumno,
            g.nombre_grupo AS grupo,
            (SELECT COUNT(*) FROM prestamo_equipo pe WHERE pe.id_prestamo = p.id_prestamo) AS cantidad_equipos
         FROM prestamo p
         JOIN alumno al ON al.id_alumno = p.id_alumno
         JOIN grupo g ON g.id_grupo = al.id_grupo
         JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo" .
         $consultaFiltros["sql"] .
        " ORDER BY
            CASE ep.nombre_estado WHEN 'Atrasado' THEN 1 WHEN 'Activo' THEN 2 ELSE 3 END,
            p.fecha_prestamo DESC,
            p.id_prestamo DESC
         LIMIT ? OFFSET ?"
    );
    $tipos = $consultaFiltros["tipos"] . "ii";
    $valores = array_merge($consultaFiltros["valores"], [$limite, $desplazamiento]);

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();
        $prestamos = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }

    if ($prestamos) {
        $ids = array_map(
            static fn (array $prestamo): int => (int) $prestamo["id_prestamo"],
            $prestamos
        );
        $marcadores = implode(", ", array_fill(0, count($ids), "?"));
        $sentenciaEquipos = $conexion->prepare(
            "SELECT
                pe.id_prestamo,
                e.id_equipo,
                e.codigo,
                e.numero_serie,
                e.modelo,
                te.nombre_tipo AS tipo,
                ee.nombre_estado AS estado_equipo,
                COALESCE(ub.nombre_ubicacion, 'Sin ubicación') AS ubicacion
             FROM prestamo_equipo pe
             JOIN equipo e ON e.id_equipo = pe.id_equipo
             JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
             JOIN estado_equipo ee ON ee.id_estado_equipo = e.id_estado_equipo
             LEFT JOIN ubicacion ub ON ub.id_ubicacion = e.id_ubicacion
             WHERE pe.id_prestamo IN ({$marcadores})
             ORDER BY pe.id_prestamo ASC, e.codigo ASC"
        );
        $tiposIds = str_repeat("i", count($ids));

        try {
            self::enlazarParametros($sentenciaEquipos, $tiposIds, $ids);
            $sentenciaEquipos->execute();
            $equipos = $sentenciaEquipos->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentenciaEquipos->close();
        }

        $equiposPorPrestamo = [];

        foreach ($equipos as $equipo) {
            $equiposPorPrestamo[(int) $equipo["id_prestamo"]][] = $equipo;
        }

        foreach ($prestamos as &$prestamo) {
            $prestamo["equipos"] = $equiposPorPrestamo[(int) $prestamo["id_prestamo"]] ?? [];
        }

        unset($prestamo);
    }

    return [
        "prestamos" => $prestamos,
        "total" => $total,
        "total_paginas" => $totalPaginas,
        "pagina_actual" => $paginaActual
    ];
}

public function obtenerResumen(): array
{
    $resultado = self::conexion()->query(
        "SELECT
            COUNT(DISTINCT p.id_prestamo) AS total,
            COUNT(DISTINCT CASE WHEN ep.nombre_estado = 'Activo' THEN p.id_prestamo END) AS activos,
            COUNT(DISTINCT CASE WHEN ep.nombre_estado = 'Atrasado' THEN p.id_prestamo END) AS atrasados,
            COUNT(DISTINCT CASE WHEN ep.nombre_estado = 'Devuelto' THEN p.id_prestamo END) AS devueltos,
            COUNT(DISTINCT CASE WHEN ep.nombre_estado IN ('Activo', 'Atrasado') THEN pe.id_equipo END) AS equipos_prestados
         FROM prestamo p
         JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
         LEFT JOIN prestamo_equipo pe ON pe.id_prestamo = p.id_prestamo"
    )->fetch_assoc();

    return array_map(
        static fn (mixed $valor): int => (int) $valor,
        $resultado ?: []
    );
}

public function obtenerEstados(): array
{
    return self::conexion()->query(
        "SELECT id_estado_prestamo AS id, nombre_estado AS nombre
         FROM estado_prestamo
         ORDER BY id_estado_prestamo ASC"
    )->fetch_all(MYSQLI_ASSOC);
}

public function obtenerAlumnos(): array
{
    return self::conexion()->query(
        "SELECT al.id_alumno AS id, al.nombre, al.apellido, g.nombre_grupo AS grupo
         FROM alumno al
         JOIN grupo g ON g.id_grupo = al.id_grupo
         ORDER BY g.nombre_grupo ASC, al.apellido ASC, al.nombre ASC"
    )->fetch_all(MYSQLI_ASSOC);
}

public function obtenerEquiposDisponibles(?int $idPrestamo = null): array
{
    $conexion = self::conexion();
    $consultaPrestamoActual = $idPrestamo !== null
        ? " OR EXISTS (
            SELECT 1 FROM prestamo_equipo pea
            WHERE pea.id_prestamo = ? AND pea.id_equipo = e.id_equipo
          )"
        : "";
    $sentencia = $conexion->prepare(
        "SELECT
            e.id_equipo AS id,
            e.codigo,
            e.modelo,
            te.nombre_tipo AS tipo,
            ee.nombre_estado AS estado,
            COALESCE(ub.nombre_ubicacion, 'Sin ubicación') AS ubicacion,
            EXISTS (
                SELECT 1 FROM prestamo_equipo pea
                WHERE pea.id_prestamo = ? AND pea.id_equipo = e.id_equipo
            ) AS asignado_actual
         FROM equipo e
         JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
         JOIN estado_equipo ee ON ee.id_estado_equipo = e.id_estado_equipo
         LEFT JOIN ubicacion ub ON ub.id_ubicacion = e.id_ubicacion
         WHERE e.es_prestable = 1
           AND (
                (
                    ee.nombre_estado = 'Correcto'
                    AND NOT EXISTS (
                        SELECT 1
                        FROM prestamo_equipo pe
                        JOIN prestamo p ON p.id_prestamo = pe.id_prestamo
                        JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
                        WHERE pe.id_equipo = e.id_equipo
                          AND ep.nombre_estado IN ('Activo', 'Atrasado')
                    )
                )" . $consultaPrestamoActual .
           ")
         ORDER BY te.nombre_tipo ASC, e.codigo ASC"
    );
    $prestamo = $idPrestamo ?? 0;
    $tipos = $idPrestamo !== null ? "ii" : "i";
    $valores = $idPrestamo !== null ? [$prestamo, $prestamo] : [$prestamo];

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }
}

private function obtenerIdEstadoPrestamo(mysqli $conexion, string $nombre): int
{
    $sentencia = $conexion->prepare(
        "SELECT id_estado_prestamo FROM estado_prestamo WHERE nombre_estado = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("s", $nombre);
        $sentencia->execute();
        $estado = $sentencia->get_result()->fetch_assoc();
    } finally {
        $sentencia->close();
    }

    if (!$estado) {
        throw new RuntimeException("El estado de préstamo requerido no existe.");
    }

    return (int) $estado["id_estado_prestamo"];
}

private function obtenerIdEstadoEquipo(mysqli $conexion, string $nombre): int
{
    $sentencia = $conexion->prepare(
        "SELECT id_estado_equipo FROM estado_equipo WHERE nombre_estado = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("s", $nombre);
        $sentencia->execute();
        $estado = $sentencia->get_result()->fetch_assoc();
    } finally {
        $sentencia->close();
    }

    if (!$estado) {
        throw new RuntimeException("El estado de equipo requerido no existe.");
    }

    return (int) $estado["id_estado_equipo"];
}

private function validarAlumno(mysqli $conexion, int $idAlumno): void
{
    $sentencia = $conexion->prepare("SELECT id_alumno FROM alumno WHERE id_alumno = ? LIMIT 1");

    try {
        $sentencia->bind_param("i", $idAlumno);
        $sentencia->execute();
        $existe = $sentencia->get_result()->fetch_assoc();
    } finally {
        $sentencia->close();
    }

    if (!$existe) {
        throw new DomainException("Selecciona un alumno válido.");
    }
}

private function validarEquiposNuevos(mysqli $conexion, array $idsEquipos): void
{
    $idsEquipos = array_values(array_unique(array_map("intval", $idsEquipos)));
    $marcadores = implode(", ", array_fill(0, count($idsEquipos), "?"));
    $sentencia = $conexion->prepare(
        "SELECT e.id_equipo
         FROM equipo e
         JOIN estado_equipo ee ON ee.id_estado_equipo = e.id_estado_equipo
         WHERE e.id_equipo IN ({$marcadores})
           AND e.es_prestable = 1
           AND ee.nombre_estado = 'Correcto'
           AND NOT EXISTS (
                SELECT 1
                FROM prestamo_equipo pe
                JOIN prestamo p ON p.id_prestamo = pe.id_prestamo
                JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
                WHERE pe.id_equipo = e.id_equipo
                  AND ep.nombre_estado IN ('Activo', 'Atrasado')
           )
         FOR UPDATE"
    );
    $tipos = str_repeat("i", count($idsEquipos));

    try {
        self::enlazarParametros($sentencia, $tipos, $idsEquipos);
        $sentencia->execute();
        $disponibles = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }

    if (count($disponibles) !== count($idsEquipos)) {
        throw new DomainException("Uno de los equipos seleccionados ya no está disponible.");
    }
}

private function insertarEquipos(
    mysqli $conexion,
    int $idPrestamo,
    array $idsEquipos
): void {
    $sentencia = $conexion->prepare(
        "INSERT INTO prestamo_equipo (id_prestamo, id_equipo) VALUES (?, ?)"
    );

    try {
        foreach ($idsEquipos as $idEquipo) {
            $idEquipo = (int) $idEquipo;
            $sentencia->bind_param("ii", $idPrestamo, $idEquipo);
            $sentencia->execute();
        }
    } finally {
        $sentencia->close();
    }
}

private function actualizarEstadoEquipos(
    mysqli $conexion,
    array $idsEquipos,
    int $idEstado,
    ?int $estadoActualRequerido = null
): void {
    if (!$idsEquipos) {
        return;
    }

    $idsEquipos = array_values(array_unique(array_map("intval", $idsEquipos)));
    $marcadores = implode(", ", array_fill(0, count($idsEquipos), "?"));
    $sql = "UPDATE equipo SET id_estado_equipo = ? WHERE id_equipo IN ({$marcadores})";
    $tipos = "i" . str_repeat("i", count($idsEquipos));
    $valores = array_merge([$idEstado], $idsEquipos);

    if ($estadoActualRequerido !== null) {
        $sql .= " AND id_estado_equipo = ?";
        $tipos .= "i";
        $valores[] = $estadoActualRequerido;
    }

    $sentencia = $conexion->prepare($sql);

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();
    } finally {
        $sentencia->close();
    }
}

public function crear(
    int $idAlumno,
    string $fechaPrestamo,
    array $idsEquipos
): int {
    $idsEquipos = array_values(array_unique(array_filter(array_map("intval", $idsEquipos))));

    if (!$idsEquipos) {
        throw new DomainException("Selecciona al menos un equipo para el préstamo.");
    }

    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
        $this->validarAlumno($conexion, $idAlumno);
        $this->validarEquiposNuevos($conexion, $idsEquipos);
        $idEstadoActivo = $this->obtenerIdEstadoPrestamo($conexion, "Activo");
        $idEstadoPrestado = $this->obtenerIdEstadoEquipo($conexion, "Prestado");
        $sentencia = $conexion->prepare(
            "INSERT INTO prestamo (id_alumno, fecha_prestamo, fecha_devolucion, id_estado_prestamo)
             VALUES (?, ?, NULL, ?)"
        );

        try {
            $sentencia->bind_param("isi", $idAlumno, $fechaPrestamo, $idEstadoActivo);
            $sentencia->execute();
            $idPrestamo = (int) $conexion->insert_id;
        } finally {
            $sentencia->close();
        }

        $this->insertarEquipos($conexion, $idPrestamo, $idsEquipos);
        $this->actualizarEstadoEquipos(
            $conexion,
            $idsEquipos,
            $idEstadoPrestado
        );
        $conexion->commit();

        return $idPrestamo;
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

public function actualizar(
    int $idPrestamo,
    int $idAlumno,
    string $fechaPrestamo,
    array $idsEquipos
): void {
    $idsEquipos = array_values(array_unique(array_filter(array_map("intval", $idsEquipos))));

    if (!$idsEquipos) {
        throw new DomainException("Selecciona al menos un equipo para el préstamo.");
    }

    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
        $sentenciaPrestamo = $conexion->prepare(
            "SELECT ep.nombre_estado
             FROM prestamo p
             JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
             WHERE p.id_prestamo = ?
             FOR UPDATE"
        );

        try {
            $sentenciaPrestamo->bind_param("i", $idPrestamo);
            $sentenciaPrestamo->execute();
            $prestamo = $sentenciaPrestamo->get_result()->fetch_assoc();
        } finally {
            $sentenciaPrestamo->close();
        }

        if (!$prestamo) {
            throw new DomainException("El préstamo seleccionado ya no existe.");
        }

        if ($prestamo["nombre_estado"] === "Devuelto") {
            throw new DomainException("No se puede modificar un préstamo ya devuelto.");
        }

        $this->validarAlumno($conexion, $idAlumno);
        $sentenciaActuales = $conexion->prepare(
            "SELECT id_equipo FROM prestamo_equipo WHERE id_prestamo = ? FOR UPDATE"
        );

        try {
            $sentenciaActuales->bind_param("i", $idPrestamo);
            $sentenciaActuales->execute();
            $idsActuales = array_map(
                static fn (array $fila): int => (int) $fila["id_equipo"],
                $sentenciaActuales->get_result()->fetch_all(MYSQLI_ASSOC)
            );
        } finally {
            $sentenciaActuales->close();
        }

        $idsAgregados = array_values(array_diff($idsEquipos, $idsActuales));
        $idsQuitados = array_values(array_diff($idsActuales, $idsEquipos));

        if ($idsAgregados) {
            $this->validarEquiposNuevos($conexion, $idsAgregados);
        }

        $sentenciaActualizacion = $conexion->prepare(
            "UPDATE prestamo SET id_alumno = ?, fecha_prestamo = ? WHERE id_prestamo = ?"
        );

        try {
            $sentenciaActualizacion->bind_param("isi", $idAlumno, $fechaPrestamo, $idPrestamo);
            $sentenciaActualizacion->execute();
        } finally {
            $sentenciaActualizacion->close();
        }

        $sentenciaEliminar = $conexion->prepare(
            "DELETE FROM prestamo_equipo WHERE id_prestamo = ?"
        );

        try {
            $sentenciaEliminar->bind_param("i", $idPrestamo);
            $sentenciaEliminar->execute();
        } finally {
            $sentenciaEliminar->close();
        }

        $this->insertarEquipos($conexion, $idPrestamo, $idsEquipos);
        $idEstadoCorrecto = $this->obtenerIdEstadoEquipo($conexion, "Correcto");
        $idEstadoPrestado = $this->obtenerIdEstadoEquipo($conexion, "Prestado");
        $this->actualizarEstadoEquipos(
            $conexion,
            $idsQuitados,
            $idEstadoCorrecto,
            $idEstadoPrestado
        );
        $this->actualizarEstadoEquipos(
            $conexion,
            $idsEquipos,
            $idEstadoPrestado
        );
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

public function marcarAtrasado(int $idPrestamo): void
{
    $conexion = self::conexion();
    $idEstadoAtrasado = $this->obtenerIdEstadoPrestamo($conexion, "Atrasado");
    $sentencia = $conexion->prepare(
        "UPDATE prestamo p
         JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
         SET p.id_estado_prestamo = ?
         WHERE p.id_prestamo = ? AND ep.nombre_estado = 'Activo'"
    );

    try {
        $sentencia->bind_param("ii", $idEstadoAtrasado, $idPrestamo);
        $sentencia->execute();

        if ($sentencia->affected_rows < 1) {
            throw new DomainException("Solo los préstamos activos pueden marcarse como atrasados.");
        }
    } finally {
        $sentencia->close();
    }
}

public function devolver(int $idPrestamo, string $fechaDevolucion): void
{
    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
        $sentenciaPrestamo = $conexion->prepare(
            "SELECT ep.nombre_estado, p.fecha_prestamo
             FROM prestamo p
             JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
             WHERE p.id_prestamo = ?
             FOR UPDATE"
        );

        try {
            $sentenciaPrestamo->bind_param("i", $idPrestamo);
            $sentenciaPrestamo->execute();
            $prestamo = $sentenciaPrestamo->get_result()->fetch_assoc();
        } finally {
            $sentenciaPrestamo->close();
        }

        if (!$prestamo) {
            throw new DomainException("El préstamo seleccionado ya no existe.");
        }

        if ($prestamo["nombre_estado"] === "Devuelto") {
            throw new DomainException("Este préstamo ya fue devuelto.");
        }

        if ($fechaDevolucion < $prestamo["fecha_prestamo"]) {
            throw new DomainException(
                "La fecha de devolución no puede ser anterior a la fecha del préstamo."
            );
        }

        $sentenciaEquipos = $conexion->prepare(
            "SELECT id_equipo FROM prestamo_equipo WHERE id_prestamo = ? FOR UPDATE"
        );

        try {
            $sentenciaEquipos->bind_param("i", $idPrestamo);
            $sentenciaEquipos->execute();
            $idsEquipos = array_map(
                static fn (array $fila): int => (int) $fila["id_equipo"],
                $sentenciaEquipos->get_result()->fetch_all(MYSQLI_ASSOC)
            );
        } finally {
            $sentenciaEquipos->close();
        }

        $idEstadoDevuelto = $this->obtenerIdEstadoPrestamo($conexion, "Devuelto");
        $sentenciaActualizar = $conexion->prepare(
            "UPDATE prestamo
             SET fecha_devolucion = ?, id_estado_prestamo = ?
             WHERE id_prestamo = ?"
        );

        try {
            $sentenciaActualizar->bind_param(
                "sii",
                $fechaDevolucion,
                $idEstadoDevuelto,
                $idPrestamo
            );
            $sentenciaActualizar->execute();
        } finally {
            $sentenciaActualizar->close();
        }

        $idEstadoCorrecto = $this->obtenerIdEstadoEquipo($conexion, "Correcto");
        $idEstadoPrestado = $this->obtenerIdEstadoEquipo($conexion, "Prestado");
        $this->actualizarEstadoEquipos(
            $conexion,
            $idsEquipos,
            $idEstadoCorrecto,
            $idEstadoPrestado
        );
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}
}

