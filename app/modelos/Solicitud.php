<?php

require_once __DIR__ . "/Modelo.php";

/* Bandeja y flujo de actualización de solicitudes atendidas por técnicos. */
final class Solicitud extends Modelo
{
    public function obtenerParaTecnico(int $idTecnico, ?int $limite = null): array
    {
        if ($idTecnico < 1) {
            return [];
        }

        $sql = "SELECT s.id_solicitud AS id,
                       DATE(s.fecha_envio) AS fecha,
                       es.nombre_estado AS estado,
                       CONCAT_WS(' ', u.nombre, u.apellido) AS remitente,
                       s.asunto,
                       s.descripcion,
                       COALESCE(DATE_FORMAT(s.fecha_fin, '%Y-%m-%d'), '') AS finalizacion,
                       COALESCE(s.trabajo_realizado, '') AS trabajo,
                       s.id_tecnico
                FROM solicitud AS s
                INNER JOIN estado_solicitud AS es
                    ON es.id_estado_solicitud = s.id_estado_solicitud
                INNER JOIN usuario AS u
                    ON u.id_usuario = s.id_docente
                WHERE s.id_tecnico IS NULL OR s.id_tecnico = ?
                ORDER BY s.fecha_envio DESC, s.id_solicitud DESC";

        if ($limite !== null) {
            $limite = max(1, min($limite, 30));
            $sql .= " LIMIT ?";
        }

        $sentencia = self::conexion()->prepare($sql);

        try {
            if ($limite === null) {
                $sentencia->bind_param("i", $idTecnico);
            } else {
                $sentencia->bind_param("ii", $idTecnico, $limite);
            }

            $sentencia->execute();

            return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }

    public function actualizar(
        int $idTecnico,
        int $idSolicitud,
        string $estado,
        string $trabajoRealizado,
        string $fechaFinalizacion
    ): void {
        $estado = trim($estado);
        $trabajoRealizado = trim($trabajoRealizado);
        $fechaFinalizacion = trim($fechaFinalizacion);
        $this->validarActualizacion(
            $idTecnico,
            $idSolicitud,
            $estado,
            $trabajoRealizado,
            $fechaFinalizacion
        );

        $conexion = self::conexion();
        // La validación y la actualización comparten la misma transacción.
        $conexion->begin_transaction();

        try {
            $solicitud = $this->bloquearSolicitud($conexion, $idSolicitud);

            if (!$solicitud) {
                throw new DomainException("La solicitud seleccionada ya no existe.");
            }

            if (
                $solicitud["id_tecnico"] !== null &&
                (int) $solicitud["id_tecnico"] !== $idTecnico
            ) {
                throw new DomainException("La solicitud ya está asignada a otro técnico.");
            }

            if (strcasecmp((string) $solicitud["estado"], "Cancelada") === 0) {
                throw new DomainException("Una solicitud cancelada no puede modificarse.");
            }

            if (
                $fechaFinalizacion !== "" &&
                $fechaFinalizacion < substr((string) $solicitud["fecha_envio"], 0, 10)
            ) {
                throw new DomainException("La finalización no puede ser anterior al envío.");
            }

            $idEstado = $this->obtenerIdEstado($conexion, $estado);
            $fechaFin = $fechaFinalizacion === ""
                ? null
                : $fechaFinalizacion . " 23:59:59";
            $asignarTecnico = $estado !== "Pendiente" || $solicitud["id_tecnico"] !== null;
            $idTecnicoGuardado = $asignarTecnico ? $idTecnico : null;

            if ($estado === "Pendiente") {
                $sentencia = $conexion->prepare(
                    "UPDATE solicitud
                     SET id_tecnico = ?,
                         id_estado_solicitud = ?,
                         trabajo_realizado = NULLIF(?, ''),
                         fecha_fin = NULL
                     WHERE id_solicitud = ?"
                );
                $sentencia->bind_param(
                    "iisi",
                    $idTecnicoGuardado,
                    $idEstado,
                    $trabajoRealizado,
                    $idSolicitud
                );
            } elseif ($estado === "En proceso") {
                $sentencia = $conexion->prepare(
                    "UPDATE solicitud
                     SET id_tecnico = ?,
                         id_estado_solicitud = ?,
                         trabajo_realizado = NULLIF(?, ''),
                         fecha_inicio = COALESCE(fecha_inicio, NOW()),
                         fecha_fin = NULL
                     WHERE id_solicitud = ?"
                );
                $sentencia->bind_param(
                    "iisi",
                    $idTecnicoGuardado,
                    $idEstado,
                    $trabajoRealizado,
                    $idSolicitud
                );
            } else {
                $sentencia = $conexion->prepare(
                    "UPDATE solicitud
                     SET id_tecnico = ?,
                         id_estado_solicitud = ?,
                         trabajo_realizado = ?,
                         fecha_inicio = COALESCE(fecha_inicio, NOW()),
                         fecha_fin = ?
                     WHERE id_solicitud = ?"
                );
                $sentencia->bind_param(
                    "iissi",
                    $idTecnicoGuardado,
                    $idEstado,
                    $trabajoRealizado,
                    $fechaFin,
                    $idSolicitud
                );
            }

            try {
                $sentencia->execute();
            } finally {
                $sentencia->close();
            }

            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        }
    }

    private function validarActualizacion(
        int $idTecnico,
        int $idSolicitud,
        string $estado,
        string $trabajoRealizado,
        string $fechaFinalizacion
    ): void {
        if ($idTecnico < 1 || $idSolicitud < 1) {
            throw new DomainException("No fue posible identificar la solicitud o al técnico.");
        }

        if (!in_array($estado, ["Pendiente", "En proceso", "Completada"], true)) {
            throw new DomainException("Selecciona un estado válido.");
        }

        if (mb_strlen($trabajoRealizado) > 5000) {
            throw new DomainException("El trabajo realizado puede tener hasta 5000 caracteres.");
        }

        if ($estado === "Completada" && $trabajoRealizado === "") {
            throw new DomainException("Describe el trabajo realizado antes de completar la solicitud.");
        }

        if ($estado === "Completada" && $fechaFinalizacion === "") {
            throw new DomainException("Indica la fecha de finalización.");
        }

        if ($fechaFinalizacion !== "") {
            $fecha = DateTimeImmutable::createFromFormat("!Y-m-d", $fechaFinalizacion);

            if (!$fecha || $fecha->format("Y-m-d") !== $fechaFinalizacion) {
                throw new DomainException("Selecciona una fecha de finalización válida.");
            }

            if ($fecha > new DateTimeImmutable("today")) {
                throw new DomainException("La finalización no puede ser posterior a hoy.");
            }
        }
    }

    private function bloquearSolicitud(mysqli $conexion, int $idSolicitud): array|false
    {
        // FOR UPDATE evita que dos técnicos modifiquen simultáneamente la solicitud.
        $sentencia = $conexion->prepare(
            "SELECT s.id_tecnico, s.fecha_envio, es.nombre_estado AS estado
             FROM solicitud AS s
             INNER JOIN estado_solicitud AS es
                ON es.id_estado_solicitud = s.id_estado_solicitud
             WHERE s.id_solicitud = ?
             LIMIT 1
             FOR UPDATE"
        );

        try {
            $sentencia->bind_param("i", $idSolicitud);
            $sentencia->execute();
            $solicitud = $sentencia->get_result()->fetch_assoc();

            return $solicitud ?: false;
        } finally {
            $sentencia->close();
        }
    }

    private function obtenerIdEstado(mysqli $conexion, string $estado): int
    {
        $sentencia = $conexion->prepare(
            "SELECT id_estado_solicitud
             FROM estado_solicitud
             WHERE nombre_estado = ?
             LIMIT 1"
        );

        try {
            $sentencia->bind_param("s", $estado);
            $sentencia->execute();
            $idEstado = (int) ($sentencia->get_result()->fetch_assoc()["id_estado_solicitud"] ?? 0);
        } finally {
            $sentencia->close();
        }

        if ($idEstado < 1) {
            throw new RuntimeException("El estado seleccionado no está configurado.");
        }

        return $idEstado;
    }
}
