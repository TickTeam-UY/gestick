<?php

require_once __DIR__ . "/Modelo.php";

/* Construye la bandeja global de solicitudes visible para el administrador. */
final class SolicitudAdministrador extends Modelo
{
    public function obtener(array $filtros, int $porPagina = 10): array
    {
        $conexion = self::conexion();
        $porPagina = max(1, min($porPagina, 50));
        [$condiciones, $parametros] = $this->construirFiltros($filtros);
        $desde = " FROM solicitud AS s
            INNER JOIN estado_solicitud AS es
                ON es.id_estado_solicitud = s.id_estado_solicitud
            INNER JOIN usuario AS ud
                ON ud.id_usuario = s.id_docente";
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
        $orden = $this->obtenerOrden((string) ($filtros["orden"] ?? "fecha"));
        $sql = "SELECT s.id_solicitud,
                       s.id_docente,
                       s.asunto,
                       s.descripcion,
                       s.fecha_envio,
                       es.nombre_estado AS estado,
                       CONCAT_WS(' ', ud.nombre, ud.apellido) AS docente,
                       ud.correo AS correo_docente"
            . $desde
            . $donde
            . " ORDER BY " . $orden
            . " LIMIT ? OFFSET ?";
        $parametrosListado = [...$parametros, $porPagina, $desplazamiento];
        $sentencia = $conexion->prepare($sql);

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
            "pagina_actual" => $paginaActual,
            "por_pagina" => $porPagina
        ];
    }

    public function obtenerDocentes(): array
    {
        $sentencia = self::conexion()->prepare(
            "SELECT DISTINCT u.id_usuario AS id,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS nombre
             FROM solicitud AS s
             INNER JOIN usuario AS u ON u.id_usuario = s.id_docente
             ORDER BY u.nombre, u.apellido"
        );

        try {
            $sentencia->execute();

            return $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }
    }

    private function construirFiltros(array $filtros): array
    {
        // Solo se incorporan filtros conocidos; sus valores nunca se concatenan al SQL.
        $condiciones = [];
        $parametros = [];
        $buscar = trim((string) ($filtros["buscar"] ?? ""));

        if ($buscar !== "") {
            $condiciones[] = "CONCAT_WS(' ',
                    s.id_solicitud,
                    s.asunto,
                    s.descripcion,
                    es.nombre_estado,
                    CONCAT_WS(' ', ud.nombre, ud.apellido),
                    ud.correo
                ) LIKE CONCAT('%', ?, '%')";
            $parametros[] = $buscar;
        }

        $idDocente = (int) ($filtros["docente"] ?? 0);

        if ($idDocente > 0) {
            $condiciones[] = "s.id_docente = ?";
            $parametros[] = $idDocente;
        }

        return [$condiciones, $parametros];
    }

    private function obtenerOrden(string $ordenSolicitado): string
    {
        $ordenes = [
            "fecha" => "s.fecha_envio DESC, s.id_solicitud DESC",
            "id" => "s.id_solicitud DESC",
            "estado" => "CASE LOWER(es.nombre_estado)
                    WHEN 'pendiente' THEN 1
                    WHEN 'en proceso' THEN 2
                    WHEN 'completada' THEN 3
                    WHEN 'finalizada' THEN 3
                    ELSE 4
                END, s.fecha_envio DESC, s.id_solicitud DESC"
        ];

        return $ordenes[$ordenSolicitado] ?? $ordenes["fecha"];
    }
}
