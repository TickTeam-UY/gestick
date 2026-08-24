<?php

require_once __DIR__ . "/Modelo.php";

final class PerfilDocente extends Modelo
{
    public function obtenerGrupos(int $idDocente): array
    {
        if ($idDocente < 1) {
            return [];
        }

        $sentencia = self::conexion()->prepare(
            "SELECT g.id_grupo,
                    g.nombre_grupo,
                    COALESCE(turnos.nombres, 'Sin turno') AS turno,
                    a.id_alumno,
                    a.nombre AS nombre_alumno,
                    a.apellido AS apellido_alumno
             FROM docente_grupo AS dg
             INNER JOIN grupo AS g ON g.id_grupo = dg.id_grupo
             LEFT JOIN (
                SELECT dt.id_docente,
                       GROUP_CONCAT(
                           DISTINCT t.nombre_turno
                           ORDER BY t.id_turno
                           SEPARATOR ', '
                       ) AS nombres
                FROM docente_turno AS dt
                INNER JOIN turno AS t ON t.id_turno = dt.id_turno
                GROUP BY dt.id_docente
             ) AS turnos ON turnos.id_docente = dg.id_docente
             LEFT JOIN alumno AS a ON a.id_grupo = g.id_grupo
             WHERE dg.id_docente = ?
             ORDER BY g.nombre_grupo, a.apellido, a.nombre, a.id_alumno"
        );

        try {
            $sentencia->bind_param("i", $idDocente);
            $sentencia->execute();
            $filas = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $sentencia->close();
        }

        $grupos = [];

        foreach ($filas as $fila) {
            $idGrupo = (int) $fila["id_grupo"];

            if (!isset($grupos[$idGrupo])) {
                $grupos[$idGrupo] = [
                    "id_grupo" => $idGrupo,
                    "grupo" => (string) $fila["nombre_grupo"],
                    "turno" => (string) $fila["turno"],
                    "estudiantes" => []
                ];
            }

            if ($fila["id_alumno"] !== null) {
                $grupos[$idGrupo]["estudiantes"][] = [
                    "id_alumno" => (int) $fila["id_alumno"],
                    "nombre" => trim(
                        (string) $fila["nombre_alumno"] . " " .
                        (string) $fila["apellido_alumno"]
                    )
                ];
            }
        }

        return array_values($grupos);
    }
}
