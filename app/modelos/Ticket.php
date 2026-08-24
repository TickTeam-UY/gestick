<?php

require_once __DIR__ . "/Modelo.php";

final class Ticket extends Modelo
{
private function convertirParaTecnico(array $ticket): array
{
    $fechaGenerado = (string) $ticket["fecha_generado"];
    $fechaFin = $ticket["fecha_fin"] ? substr((string) $ticket["fecha_fin"], 0, 10) : "";
    $ubicacion = $ticket["ubicacion"] ?: "Sin ubicación asignada";

    return [
        "id" => "T" . str_pad((string) $ticket["id_ticket"], 3, "0", STR_PAD_LEFT),
        "idNumerico" => (int) $ticket["id_ticket"],
        "prioridad" => $ticket["prioridad"],
        "estado" => $ticket["estado"],
        "fechaCreacion" => substr($fechaGenerado, 0, 10),
        "equipo" => $ticket["codigo"] . " - " . $ubicacion,
        "codigoEquipo" => $ticket["codigo"],
        "ubicacion" => $ubicacion,
        "titulo" => "Incidencia en " . $ticket["codigo"],
        "descripcion" => $ticket["descripcion"],
        "fechaFin" => $fechaFin,
        "solucion" => (string) ($ticket["solucion"] ?? ""),
        "docente" => trim((string) ($ticket["docente"] ?? "")) ?: "No indicado",
        "tecnico" => trim((string) ($ticket["tecnico"] ?? "")) ?: "Sin asignar",
        "disponible" => $ticket["id_tecnico"] === null,
        "asignadoAlTecnico" => $ticket["id_tecnico"] !== null
    ];
}

private function consultarParaTecnico(string $condicion, array $parametros = []): array
{
    $conexion = self::conexion();
    $sql = "SELECT
                t.id_ticket,
                t.id_tecnico,
                t.descripcion,
                t.solucion,
                t.fecha_generado,
                t.fecha_fin,
                e.codigo,
                COALESCE(u.nombre_ubicacion, 'Sin ubicación asignada') AS ubicacion,
                pr.nombre_prioridad AS prioridad,
                et.nombre_estado AS estado,
                CONCAT_WS(' ', ud.nombre, ud.apellido) AS docente,
                CONCAT_WS(' ', ute.nombre, ute.apellido) AS tecnico
            FROM ticket t
            JOIN equipo e ON e.id_equipo = t.id_equipo
            LEFT JOIN ubicacion u ON u.id_ubicacion = e.id_ubicacion
            JOIN prioridad pr ON pr.id_prioridad = t.id_prioridad
            JOIN estado_ticket et ON et.id_estado_ticket = t.id_estado_ticket
            LEFT JOIN planilla_detalle pd ON pd.id_planilla_detalle = t.id_planilla_detalle
            LEFT JOIN planilla p ON p.id_planilla = pd.id_planilla
            LEFT JOIN usuario ud ON ud.id_usuario = p.id_docente
            LEFT JOIN usuario ute ON ute.id_usuario = t.id_tecnico
            WHERE {$condicion}
            ORDER BY t.fecha_generado DESC, t.id_ticket DESC";
    $sentencia = $conexion->prepare($sql);

    try {
        $sentencia->execute($parametros);
        $filas = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }

    return array_map(
        fn(array $ticket): array => $this->convertirParaTecnico($ticket),
        $filas
    );
}

public function obtenerAsignados(int $idTecnico): array
{
    if ($idTecnico < 1) {
        return [];
    }

    return $this->consultarParaTecnico("t.id_tecnico = ?", [$idTecnico]);
}

public function obtenerPorEstado(int $idTecnico, string $estado): array
{
    if ($idTecnico < 1) {
        return [];
    }

    if ($estado === "Pendiente") {
        return $this->consultarParaTecnico(
            "et.nombre_estado = ? AND (t.id_tecnico = ? OR t.id_tecnico IS NULL)",
            [$estado, $idTecnico]
        );
    }

    return $this->consultarParaTecnico(
        "et.nombre_estado = ? AND t.id_tecnico = ?",
        [$estado, $idTecnico]
    );
}

public function obtenerRecientes(int $idTecnico, int $cantidad = 3): array
{
    return array_slice($this->obtenerAsignados($idTecnico), 0, max(1, $cantidad));
}

public function tomar(int $idTicket, int $idTecnico): void
{
    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
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
            throw new DomainException("No fue posible identificar al técnico activo.");
        }

        $sentenciaAsignar = $conexion->prepare(
            "UPDATE ticket t
             JOIN estado_ticket et ON et.id_estado_ticket = t.id_estado_ticket
             SET t.id_tecnico = ?
             WHERE t.id_ticket = ?
               AND t.id_tecnico IS NULL
               AND et.nombre_estado = 'Pendiente'"
        );

        try {
            $sentenciaAsignar->bind_param("ii", $idTecnico, $idTicket);
            $sentenciaAsignar->execute();
            $asignado = $sentenciaAsignar->affected_rows === 1;
        } finally {
            $sentenciaAsignar->close();
        }

        if (!$asignado) {
            throw new DomainException(
                "Este ticket ya fue asignado a otro técnico o dejó de estar pendiente."
            );
        }

        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

public function actualizarPorTecnico(
    int $idTicket,
    int $idTecnico,
    string $prioridad,
    string $estado,
    ?string $solucion
): void {
    if ($idTicket < 1 || $idTecnico < 1) {
        throw new DomainException("El ticket o el técnico no son válidos.");
    }

    $prioridad = trim($prioridad);
    $estado = trim($estado);
    $solucion = trim((string) $solucion);

    if (mb_strlen($prioridad) > 30 || mb_strlen($estado) > 30) {
        throw new DomainException("Los datos seleccionados no son válidos.");
    }

    if (mb_strlen($solucion) > 5000) {
        throw new DomainException("La solución puede tener hasta 5000 caracteres.");
    }

    if (!in_array($estado, ["Pendiente", "En proceso", "Resuelto"], true)) {
        throw new DomainException("Selecciona un estado válido.");
    }

    if ($estado === "Resuelto" && $solucion === "") {
        throw new DomainException("Escribe la solución antes de resolver el ticket.");
    }

    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
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
            throw new DomainException("No fue posible identificar al técnico activo.");
        }

        $sentenciaTicket = $conexion->prepare(
            "SELECT t.id_ticket, et.nombre_estado AS estado_actual
             FROM ticket t
             JOIN estado_ticket et ON et.id_estado_ticket = t.id_estado_ticket
             WHERE t.id_ticket = ? AND t.id_tecnico = ?
             LIMIT 1
             FOR UPDATE"
        );

        try {
            $sentenciaTicket->bind_param("ii", $idTicket, $idTecnico);
            $sentenciaTicket->execute();
            $ticket = $sentenciaTicket->get_result()->fetch_assoc();
        } finally {
            $sentenciaTicket->close();
        }

        if (!$ticket) {
            throw new DomainException(
                "El ticket ya no existe o no está asignado a tu cuenta."
            );
        }

        $transicionesPermitidas = [
            "Pendiente" => ["Pendiente", "En proceso", "Resuelto"],
            "En proceso" => ["En proceso", "Resuelto"],
            "Resuelto" => ["Resuelto"]
        ];
        $estadoActual = (string) $ticket["estado_actual"];

        if (
            !isset($transicionesPermitidas[$estadoActual]) ||
            !in_array($estado, $transicionesPermitidas[$estadoActual], true)
        ) {
            throw new DomainException(
                "No puedes regresar un ticket de {$estadoActual} a {$estado}."
            );
        }

        $sentenciaPrioridad = $conexion->prepare(
            "SELECT id_prioridad
             FROM prioridad
             WHERE nombre_prioridad = ?
             LIMIT 1"
        );

        try {
            $sentenciaPrioridad->bind_param("s", $prioridad);
            $sentenciaPrioridad->execute();
            $filaPrioridad = $sentenciaPrioridad->get_result()->fetch_assoc();
        } finally {
            $sentenciaPrioridad->close();
        }

        if (!$filaPrioridad) {
            throw new DomainException("Selecciona una prioridad válida.");
        }

        $sentenciaEstado = $conexion->prepare(
            "SELECT id_estado_ticket
             FROM estado_ticket
             WHERE nombre_estado = ?
             LIMIT 1"
        );

        try {
            $sentenciaEstado->bind_param("s", $estado);
            $sentenciaEstado->execute();
            $filaEstado = $sentenciaEstado->get_result()->fetch_assoc();
        } finally {
            $sentenciaEstado->close();
        }

        if (!$filaEstado) {
            throw new DomainException("Selecciona un estado válido.");
        }

        $idPrioridad = (int) $filaPrioridad["id_prioridad"];
        $idEstado = (int) $filaEstado["id_estado_ticket"];
        $solucionGuardada = $solucion === "" ? null : $solucion;

        if ($estado === "Pendiente") {
            $sql = "UPDATE ticket
                    SET id_prioridad = ?, id_estado_ticket = ?, solucion = ?,
                        fecha_inicio = NULL, fecha_fin = NULL
                    WHERE id_ticket = ? AND id_tecnico = ?";
        } elseif ($estado === "En proceso") {
            $sql = "UPDATE ticket
                    SET id_prioridad = ?, id_estado_ticket = ?, solucion = ?,
                        fecha_inicio = COALESCE(fecha_inicio, NOW()), fecha_fin = NULL
                    WHERE id_ticket = ? AND id_tecnico = ?";
        } else {
            $sql = "UPDATE ticket
                    SET id_prioridad = ?, id_estado_ticket = ?, solucion = ?,
                        fecha_inicio = COALESCE(fecha_inicio, NOW()),
                        fecha_fin = COALESCE(fecha_fin, NOW())
                    WHERE id_ticket = ? AND id_tecnico = ?";
        }

        $sentenciaActualizar = $conexion->prepare($sql);

        try {
            $sentenciaActualizar->bind_param(
                "iisii",
                $idPrioridad,
                $idEstado,
                $solucionGuardada,
                $idTicket,
                $idTecnico
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
