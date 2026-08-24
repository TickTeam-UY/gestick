<?php

require_once __DIR__ . "/Modelo.php";

final class SolicitudDocente extends Modelo
{
    public function crear(int $idDocente, string $asunto, string $descripcion): int
    {
        $asunto = trim($asunto);
        $descripcion = trim($descripcion);
        $this->validarDatosCreacion($idDocente, $asunto, $descripcion);

        $conexion = self::conexion();
        $conexion->begin_transaction();

        try {
            $this->verificarDocenteActivo($conexion, $idDocente);
            $idEstadoPendiente = $this->obtenerIdEstado($conexion, "Pendiente");
            $sentencia = $conexion->prepare(
                "INSERT INTO solicitud
                    (id_docente, id_estado_solicitud, asunto, descripcion, fecha_envio)
                 VALUES (?, ?, ?, ?, NOW())"
            );

            try {
                $sentencia->bind_param(
                    "iiss",
                    $idDocente,
                    $idEstadoPendiente,
                    $asunto,
                    $descripcion
                );
                $sentencia->execute();
                $idSolicitud = (int) $conexion->insert_id;
            } finally {
                $sentencia->close();
            }

            $conexion->commit();

            return $idSolicitud;
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        }
    }

    public function cancelar(int $idDocente, int $idSolicitud): void
    {
        if ($idDocente < 1 || $idSolicitud < 1) {
            throw new DomainException("No fue posible identificar la solicitud.");
        }

        $conexion = self::conexion();
        $conexion->begin_transaction();

        try {
            $sentenciaSolicitud = $conexion->prepare(
                "SELECT es.nombre_estado AS estado
                 FROM solicitud s
                 JOIN estado_solicitud es
                    ON es.id_estado_solicitud = s.id_estado_solicitud
                 JOIN docente d
                    ON d.id_usuario = s.id_docente
                 JOIN usuario u
                    ON u.id_usuario = d.id_usuario
                 WHERE s.id_solicitud = ?
                   AND s.id_docente = ?
                   AND u.activo = 1
                 LIMIT 1
                 FOR UPDATE"
            );

            try {
                $sentenciaSolicitud->bind_param("ii", $idSolicitud, $idDocente);
                $sentenciaSolicitud->execute();
                $solicitud = $sentenciaSolicitud->get_result()->fetch_assoc();
            } finally {
                $sentenciaSolicitud->close();
            }

            if (!$solicitud) {
                throw new DomainException("La solicitud no existe o no pertenece a tu cuenta.");
            }

            if (strcasecmp((string) $solicitud["estado"], "Pendiente") !== 0) {
                throw new DomainException("Solo puedes cancelar solicitudes que todavía estén pendientes.");
            }

            $idEstadoCancelada = $this->obtenerIdEstado($conexion, "Cancelada");
            $sentenciaCancelar = $conexion->prepare(
                "UPDATE solicitud
                 SET id_estado_solicitud = ?
                 WHERE id_solicitud = ? AND id_docente = ?"
            );

            try {
                $sentenciaCancelar->bind_param(
                    "iii",
                    $idEstadoCancelada,
                    $idSolicitud,
                    $idDocente
                );
                $sentenciaCancelar->execute();

                if ($sentenciaCancelar->affected_rows !== 1) {
                    throw new RuntimeException("No fue posible actualizar la solicitud.");
                }
            } finally {
                $sentenciaCancelar->close();
            }

            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        }
    }

    public function obtenerPorDocente(
        int $idDocente,
        array $filtros,
        int $porPagina = 8
    ): array {
        if ($idDocente < 1) {
            return $this->resultadoVacio();
        }

        $conexion = self::conexion();
        $porPagina = max(1, min($porPagina, 30));
        $condiciones = ["s.id_docente = ?"];
        $parametros = [$idDocente];
        $buscar = trim((string) ($filtros["buscar"] ?? ""));

        if ($buscar !== "") {
            $condiciones[] = "CONCAT_WS(' ', s.id_solicitud, s.asunto, s.descripcion, es.nombre_estado)
                LIKE CONCAT('%', ?, '%')";
            $parametros[] = $buscar;
        }

        $desde = " FROM solicitud s
            JOIN estado_solicitud es
                ON es.id_estado_solicitud = s.id_estado_solicitud";
        $donde = " WHERE " . implode(" AND ", $condiciones);
        $sentenciaTotal = $conexion->prepare("SELECT COUNT(*) AS total" . $desde . $donde);

        try {
            $sentenciaTotal->execute($parametros);
            $total = (int) ($sentenciaTotal->get_result()->fetch_assoc()["total"] ?? 0);
        } finally {
            $sentenciaTotal->close();
        }

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $paginaActual = min(
            max(1, (int) ($filtros["pagina"] ?? 1)),
            $totalPaginas
        );
        $desplazamiento = ($paginaActual - 1) * $porPagina;
        $orden = ($filtros["orden"] ?? "recientes") === "antiguas"
            ? "s.fecha_envio ASC, s.id_solicitud ASC"
            : "s.fecha_envio DESC, s.id_solicitud DESC";
        $sentencia = $conexion->prepare(
            "SELECT s.id_solicitud,
                    s.asunto,
                    s.descripcion,
                    s.fecha_envio,
                    es.nombre_estado AS estado" .
            $desde .
            $donde .
            " ORDER BY {$orden}
              LIMIT ? OFFSET ?"
        );
        $parametrosListado = [...$parametros, $porPagina, $desplazamiento];

        try {
            $sentencia->execute($parametrosListado);
            $solicitudes = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }

        return [
            "solicitudes" => $solicitudes,
            "total" => $total,
            "total_paginas" => $totalPaginas,
            "pagina_actual" => $paginaActual
        ];
    }

    private function validarDatosCreacion(
        int $idDocente,
        string $asunto,
        string $descripcion
    ): void {
        if ($idDocente < 1) {
            throw new DomainException("No fue posible identificar la cuenta del docente.");
        }

        if ($asunto === "" || mb_strlen($asunto) > 120) {
            throw new DomainException("El asunto es obligatorio y puede tener hasta 120 caracteres.");
        }

        if ($descripcion === "" || mb_strlen($descripcion) > 255) {
            throw new DomainException("El mensaje es obligatorio y puede tener hasta 255 caracteres.");
        }
    }

    private function verificarDocenteActivo(mysqli $conexion, int $idDocente): void
    {
        $sentencia = $conexion->prepare(
            "SELECT u.id_usuario
             FROM docente d
             JOIN usuario u ON u.id_usuario = d.id_usuario
             WHERE d.id_usuario = ? AND u.activo = 1
             LIMIT 1"
        );

        try {
            $sentencia->bind_param("i", $idDocente);
            $sentencia->execute();
            $docente = $sentencia->get_result()->fetch_assoc();
        } finally {
            $sentencia->close();
        }

        if (!$docente) {
            throw new DomainException("La cuenta no corresponde a un docente activo.");
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
            throw new RuntimeException("No existe el estado {$estado} de las solicitudes.");
        }

        return $idEstado;
    }

    private function resultadoVacio(): array
    {
        return [
            "solicitudes" => [],
            "total" => 0,
            "total_paginas" => 1,
            "pagina_actual" => 1
        ];
    }
}
