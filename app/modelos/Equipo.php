<?php

require_once __DIR__ . "/Modelo.php";

/*
 * MODELO EQUIPO
 *
 * Es la fuente compartida por Administrador y Técnico. Los cambios realizados
 * por Administración se reflejan en la vista del técnico porque ambas capas
 * consultan las tablas `ubicacion` y `equipo`.
 */

final class Equipo extends Modelo
{
public function obtenerLaboratorios(bool $incluirSinUbicacion = true): array
{
    $conexion = self::conexion();
    $sentenciaUbicaciones = $conexion->prepare(
        "SELECT id_ubicacion, nombre_ubicacion
         FROM ubicacion
         ORDER BY nombre_ubicacion"
    );

    try {
        $sentenciaUbicaciones->execute();
        $ubicaciones = $sentenciaUbicaciones->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentenciaUbicaciones->close();
    }

    $laboratorios = [];

    foreach ($ubicaciones as $ubicacion) {
        $clave = "ubicacion-" . $ubicacion["id_ubicacion"];
        $laboratorios[$clave] = [
            "id" => (int) $ubicacion["id_ubicacion"],
            "nombre" => $ubicacion["nombre_ubicacion"],
            "es_sin_ubicacion" => false,
            "equipos" => []
        ];
    }

    $sentenciaEquipos = $conexion->prepare(
        "SELECT e.id_equipo,
                e.codigo,
                e.numero_serie,
                e.modelo,
                e.id_tipo_equipo,
                te.nombre_tipo,
                e.id_estado_equipo,
                ee.nombre_estado,
                e.id_ubicacion,
                u.nombre_ubicacion,
                e.es_prestable
         FROM equipo AS e
         INNER JOIN tipo_equipo AS te
             ON te.id_tipo_equipo = e.id_tipo_equipo
         INNER JOIN estado_equipo AS ee
             ON ee.id_estado_equipo = e.id_estado_equipo
         LEFT JOIN ubicacion AS u
             ON u.id_ubicacion = e.id_ubicacion
         ORDER BY COALESCE(u.nombre_ubicacion, 'ZZZ Sin ubicación'), e.codigo"
    );

    try {
        $sentenciaEquipos->execute();
        $equipos = $sentenciaEquipos->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentenciaEquipos->close();
    }

    $componentesPorEquipo = [];
    $sentenciaComponentes = $conexion->prepare(
        "SELECT ec.id_equipo,
                ec.tipo_componente AS tipo,
                m.nombre_marca AS marca,
                ec.numero_serie AS serie,
                ec.estado
         FROM equipo_componente ec
         JOIN marca m ON m.id_marca = ec.id_marca
         ORDER BY ec.id_equipo,
                  FIELD(ec.tipo_componente, 'Monitor', 'Teclado', 'Torre', 'Mouse')"
    );

    try {
        $sentenciaComponentes->execute();

        foreach ($sentenciaComponentes->get_result()->fetch_all(MYSQLI_ASSOC) as $componente) {
            $componentesPorEquipo[(int) $componente["id_equipo"]][] = [
                "tipo" => $componente["tipo"],
                "marca" => $componente["marca"],
                "serie" => $componente["serie"] ?? "",
                "estado" => $componente["estado"]
            ];
        }
    } finally {
        $sentenciaComponentes->close();
    }

    $historialPorEquipo = [];
    $sentenciaHistorial = $conexion->prepare(
        "SELECT t.id_equipo,
                DATE_FORMAT(COALESCE(t.fecha_fin, t.fecha_inicio, t.fecha_generado), '%d/%m/%Y') AS fecha,
                t.descripcion,
                COALESCE(NULLIF(TRIM(t.solucion), ''), t.descripcion) AS cambio,
                CONCAT_WS(' ', ut.nombre, ut.apellido) AS tecnico
         FROM ticket t
         LEFT JOIN usuario ut ON ut.id_usuario = t.id_tecnico
         ORDER BY t.id_equipo,
                  COALESCE(t.fecha_fin, t.fecha_inicio, t.fecha_generado) DESC,
                  t.id_ticket DESC"
    );

    try {
        $sentenciaHistorial->execute();

        foreach ($sentenciaHistorial->get_result()->fetch_all(MYSQLI_ASSOC) as $registro) {
            $idEquipoHistorial = (int) $registro["id_equipo"];

            if (count($historialPorEquipo[$idEquipoHistorial] ?? []) >= 5) {
                continue;
            }

            $historialPorEquipo[$idEquipoHistorial][] = [
                "fecha" => $registro["fecha"],
                "componente" => $this->detectarComponente((string) $registro["descripcion"]),
                "cambio" => $registro["cambio"],
                "tecnico" => trim((string) $registro["tecnico"]) ?: "Sin asignar"
            ];
        }
    } finally {
        $sentenciaHistorial->close();
    }

    foreach ($equipos as $equipo) {
        $idUbicacion = $equipo["id_ubicacion"] !== null
            ? (int) $equipo["id_ubicacion"]
            : null;

        if ($idUbicacion === null) {
            if (!$incluirSinUbicacion) {
                continue;
            }

            $clave = "sin-ubicacion";

            if (!isset($laboratorios[$clave])) {
                $laboratorios[$clave] = [
                    "id" => null,
                    "nombre" => "Sin ubicación asignada",
                    "es_sin_ubicacion" => true,
                    "equipos" => []
                ];
            }
        } else {
            $clave = "ubicacion-" . $idUbicacion;
        }

        if (!isset($laboratorios[$clave])) {
            continue;
        }

        $laboratorios[$clave]["equipos"][] = [
            "id" => (int) $equipo["id_equipo"],
            "codigo" => $equipo["codigo"],
            "estado" => $equipo["nombre_estado"],
            "id_estado" => (int) $equipo["id_estado_equipo"],
            "tipo" => $equipo["nombre_tipo"],
            "id_tipo" => (int) $equipo["id_tipo_equipo"],
            "serie" => $equipo["numero_serie"] ?? "",
            "modelo" => $equipo["modelo"] ?? "",
            "id_ubicacion" => $idUbicacion,
            "ubicacion" => $equipo["nombre_ubicacion"] ?? "Sin ubicación asignada",
            "prestable" => (int) $equipo["es_prestable"] === 1,
            "componentes" => $componentesPorEquipo[(int) $equipo["id_equipo"]] ?? [],
            "historial" => $historialPorEquipo[(int) $equipo["id_equipo"]] ?? [],
            "observacion" => $equipo["modelo"]
                ? "Modelo: " . $equipo["modelo"]
                : "Sin observaciones."
        ];
    }

    return array_values($laboratorios);
}

public function obtenerTipos(): array
{
    $sentencia = self::conexion()->prepare(
        "SELECT id_tipo_equipo AS id, nombre_tipo AS nombre
         FROM tipo_equipo
         ORDER BY nombre_tipo"
    );

    try {
        $sentencia->execute();

        return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }
}

public function obtenerEstados(): array
{
    $sentencia = self::conexion()->prepare(
        "SELECT id_estado_equipo AS id, nombre_estado AS nombre
         FROM estado_equipo
         ORDER BY id_estado_equipo"
    );

    try {
        $sentencia->execute();

        return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }
}

public function ubicacionExiste(int $idUbicacion): bool
{
    $sentencia = self::conexion()->prepare(
        "SELECT 1 FROM ubicacion WHERE id_ubicacion = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("i", $idUbicacion);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_row() !== null;
    } finally {
        $sentencia->close();
    }
}

public function tipoExiste(int $idTipoEquipo): bool
{
    $sentencia = self::conexion()->prepare(
        "SELECT 1 FROM tipo_equipo WHERE id_tipo_equipo = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("i", $idTipoEquipo);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_row() !== null;
    } finally {
        $sentencia->close();
    }
}

public function estadoExiste(int $idEstadoEquipo): bool
{
    $sentencia = self::conexion()->prepare(
        "SELECT 1 FROM estado_equipo WHERE id_estado_equipo = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("i", $idEstadoEquipo);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_row() !== null;
    } finally {
        $sentencia->close();
    }
}

private function existe(int $idEquipo): bool
{
    $sentencia = self::conexion()->prepare(
        "SELECT 1 FROM equipo WHERE id_equipo = ? LIMIT 1"
    );

    try {
        $sentencia->bind_param("i", $idEquipo);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_row() !== null;
    } finally {
        $sentencia->close();
    }
}

public function crearUbicacion(string $nombre): int
{
    $conexion = self::conexion();
    $sentencia = $conexion->prepare(
        "INSERT INTO ubicacion (nombre_ubicacion) VALUES (?)"
    );

    try {
        $sentencia->bind_param("s", $nombre);
        $sentencia->execute();

        return (int) $conexion->insert_id;
    } finally {
        $sentencia->close();
    }
}

public function actualizarUbicacion(int $idUbicacion, string $nombre): void
{
    if (!$this->ubicacionExiste($idUbicacion)) {
        throw new DomainException("El laboratorio o salón seleccionado ya no existe.");
    }

    $sentencia = self::conexion()->prepare(
        "UPDATE ubicacion SET nombre_ubicacion = ? WHERE id_ubicacion = ?"
    );

    try {
        $sentencia->bind_param("si", $nombre, $idUbicacion);
        $sentencia->execute();
    } finally {
        $sentencia->close();
    }
}

public function eliminarUbicacion(int $idUbicacion): void
{
    // Las dependencias se comprueban para no romper el historial del inventario.
    $sentenciaDependencias = self::conexion()->prepare(
        "SELECT
            (SELECT COUNT(*) FROM equipo WHERE id_ubicacion = ?) AS equipos,
            (SELECT COUNT(*) FROM planilla WHERE id_ubicacion = ?) AS planillas"
    );

    try {
        $sentenciaDependencias->bind_param("ii", $idUbicacion, $idUbicacion);
        $sentenciaDependencias->execute();
        $dependencias = $sentenciaDependencias->get_result()->fetch_assoc();
    } finally {
        $sentenciaDependencias->close();
    }

    if ((int) ($dependencias["equipos"] ?? 0) > 0) {
        throw new DomainException(
            "No se puede eliminar la ubicación mientras tenga equipos asignados."
        );
    }

    if ((int) ($dependencias["planillas"] ?? 0) > 0) {
        throw new DomainException(
            "No se puede eliminar la ubicación porque aparece en planillas registradas."
        );
    }

    $sentencia = self::conexion()->prepare(
        "DELETE FROM ubicacion WHERE id_ubicacion = ?"
    );

    try {
        $sentencia->bind_param("i", $idUbicacion);
        $sentencia->execute();

        if ($sentencia->affected_rows !== 1) {
            throw new DomainException("El laboratorio o salón seleccionado ya no existe.");
        }
    } finally {
        $sentencia->close();
    }
}

public function crear(
    string $codigo,
    ?string $numeroSerie,
    ?string $modelo,
    int $idTipoEquipo,
    int $idEstadoEquipo,
    ?int $idUbicacion,
    int $esPrestable
): int {
    // El controlador entrega identificadores ya validados contra sus catálogos.
    $conexion = self::conexion();
    $sentencia = $conexion->prepare(
        "INSERT INTO equipo
            (codigo, numero_serie, modelo, id_tipo_equipo, id_estado_equipo, id_ubicacion, es_prestable)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    try {
        $sentencia->bind_param(
            "sssiiii",
            $codigo,
            $numeroSerie,
            $modelo,
            $idTipoEquipo,
            $idEstadoEquipo,
            $idUbicacion,
            $esPrestable
        );
        $sentencia->execute();

        return (int) $conexion->insert_id;
    } finally {
        $sentencia->close();
    }
}

public function actualizar(
    int $idEquipo,
    string $codigo,
    ?string $numeroSerie,
    ?string $modelo,
    int $idTipoEquipo,
    int $idEstadoEquipo,
    ?int $idUbicacion,
    int $esPrestable
): void {
    if (!$this->existe($idEquipo)) {
        throw new DomainException("El equipo seleccionado ya no existe.");
    }

    $sentencia = self::conexion()->prepare(
        "UPDATE equipo
         SET codigo = ?,
             numero_serie = ?,
             modelo = ?,
             id_tipo_equipo = ?,
             id_estado_equipo = ?,
             id_ubicacion = ?,
             es_prestable = ?
         WHERE id_equipo = ?"
    );

    try {
        $sentencia->bind_param(
            "sssiiiii",
            $codigo,
            $numeroSerie,
            $modelo,
            $idTipoEquipo,
            $idEstadoEquipo,
            $idUbicacion,
            $esPrestable,
            $idEquipo
        );
        $sentencia->execute();
    } finally {
        $sentencia->close();
    }
}

public function eliminar(int $idEquipo): void
{
    // Un equipo utilizado por otro módulo conserva su trazabilidad y no se elimina.
    $sentenciaDependencias = self::conexion()->prepare(
        "SELECT
            (SELECT COUNT(*) FROM equipo_componente WHERE id_equipo = ?) AS componentes,
            (SELECT COUNT(*) FROM planilla_detalle WHERE id_equipo = ?) AS planillas,
            (SELECT COUNT(*) FROM prestamo_equipo WHERE id_equipo = ?) AS prestamos,
            (SELECT COUNT(*) FROM ticket WHERE id_equipo = ?) AS tickets"
    );

    try {
        $sentenciaDependencias->bind_param(
            "iiii",
            $idEquipo,
            $idEquipo,
            $idEquipo,
            $idEquipo
        );
        $sentenciaDependencias->execute();
        $dependencias = $sentenciaDependencias->get_result()->fetch_assoc();
    } finally {
        $sentenciaDependencias->close();
    }

    if (array_sum(array_map("intval", $dependencias ?: [])) > 0) {
        throw new DomainException(
            "No se puede eliminar el equipo porque tiene historial, componentes, tickets o préstamos asociados."
        );
    }

    $sentencia = self::conexion()->prepare(
        "DELETE FROM equipo WHERE id_equipo = ?"
    );

    try {
        $sentencia->bind_param("i", $idEquipo);
        $sentencia->execute();

        if ($sentencia->affected_rows !== 1) {
            throw new DomainException("El equipo seleccionado ya no existe.");
        }
    } finally {
        $sentencia->close();
    }
}

private function detectarComponente(string $descripcion): string
{
    // Clasifica descripciones históricas para mostrarlas en la ficha del equipo.
    $descripcion = mb_strtolower($descripcion);

    foreach (["Monitor", "Teclado", "Torre", "Mouse"] as $componente) {
        if (str_contains($descripcion, mb_strtolower($componente))) {
            return $componente;
        }
    }

    return "Equipo";
}
}
