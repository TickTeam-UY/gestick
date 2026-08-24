<?php

require_once __DIR__ . "/Modelo.php";

final class UsuarioAdministrador extends Modelo
{
private function construirFiltros(array $filtros): array
{
    $condiciones = [];
    $tipos = "";
    $valores = [];
    $buscar = trim((string) ($filtros["buscar"] ?? ""));
    $rol = (string) ($filtros["rol"] ?? "");
    $estado = (string) ($filtros["estado"] ?? "activos");

    if ($buscar !== "") {
        $termino = "%" . $buscar . "%";
        $condiciones[] = "(
            CONCAT(u.nombre, ' ', u.apellido) LIKE ?
            OR u.correo LIKE ?
            OR CAST(u.id_usuario AS CHAR) LIKE ?
        )";
        $tipos .= "sss";
        array_push($valores, $termino, $termino, $termino);
    }

    if (in_array($rol, ["Administrador", "Tecnico", "Docente"], true)) {
        $condiciones[] = "u.rol = ?";
        $tipos .= "s";
        $valores[] = $rol;
    }

    if ($estado === "activos") {
        $condiciones[] = "u.activo = 1";
    } elseif ($estado === "inactivos") {
        $condiciones[] = "u.activo = 0";
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
         FROM usuario u" . $consultaFiltros["sql"]
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
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.correo,
            u.rol,
            u.activo,
            u.fecha_registro,
            te.id_turno,
            tu.nombre_turno
         FROM usuario u
         LEFT JOIN tecnico te ON te.id_usuario = u.id_usuario
         LEFT JOIN turno tu ON tu.id_turno = te.id_turno" .
         $consultaFiltros["sql"] .
        " ORDER BY u.activo DESC, u.apellido ASC, u.nombre ASC, u.id_usuario ASC
         LIMIT ? OFFSET ?"
    );

    $tipos = $consultaFiltros["tipos"] . "ii";
    $valores = array_merge(
        $consultaFiltros["valores"],
        [$limite, $desplazamiento]
    );

    try {
        self::enlazarParametros($sentencia, $tipos, $valores);
        $sentencia->execute();
        $usuarios = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $sentencia->close();
    }

    return [
        "usuarios" => $usuarios,
        "total" => $total,
        "total_paginas" => $totalPaginas,
        "pagina_actual" => $paginaActual
    ];
}

public function obtenerResumen(): array
{
    $resultado = self::conexion()->query(
        "SELECT
            COUNT(*) AS total,
            SUM(activo = 1) AS activos,
            SUM(activo = 0) AS inactivos,
            SUM(rol = 'Administrador' AND activo = 1) AS administradores,
            SUM(rol = 'Tecnico' AND activo = 1) AS tecnicos,
            SUM(rol = 'Docente' AND activo = 1) AS docentes
         FROM usuario"
    )->fetch_assoc();

    return array_map(
        static fn (mixed $valor): int => (int) $valor,
        $resultado ?: []
    );
}

public function obtenerTurnos(): array
{
    return self::conexion()->query(
        "SELECT id_turno, nombre_turno
         FROM turno
         ORDER BY id_turno ASC"
    )->fetch_all(MYSQLI_ASSOC);
}

public function buscarPorId(int $idUsuario): array|false
{
    $conexion = self::conexion();
    $sentencia = $conexion->prepare(
        "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.rol, u.activo,
                te.id_turno, tu.nombre_turno
         FROM usuario u
         LEFT JOIN tecnico te ON te.id_usuario = u.id_usuario
         LEFT JOIN turno tu ON tu.id_turno = te.id_turno
         WHERE u.id_usuario = ?
         LIMIT 1"
    );

    try {
        $sentencia->bind_param("i", $idUsuario);
        $sentencia->execute();
        $usuario = $sentencia->get_result()->fetch_assoc();

        return $usuario ?: false;
    } finally {
        $sentencia->close();
    }
}

private function insertarPerfilRol(
    mysqli $conexion,
    int $idUsuario,
    string $rol,
    ?int $idTurno
): void {
    if ($rol === "Tecnico") {
        if ($idTurno === null || $idTurno < 1) {
            throw new DomainException("Selecciona el turno del técnico.");
        }

        $sentencia = $conexion->prepare(
            "INSERT INTO tecnico (id_usuario, id_turno)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE id_turno = VALUES(id_turno)"
        );

        try {
            $sentencia->bind_param("ii", $idUsuario, $idTurno);
            $sentencia->execute();
        } finally {
            $sentencia->close();
        }

        return;
    }

    $tabla = $rol === "Administrador" ? "administrador" : "docente";
    $sentencia = $conexion->prepare(
        "INSERT INTO {$tabla} (id_usuario)
         VALUES (?)
         ON DUPLICATE KEY UPDATE id_usuario = VALUES(id_usuario)"
    );

    try {
        $sentencia->bind_param("i", $idUsuario);
        $sentencia->execute();
    } finally {
        $sentencia->close();
    }
}

private function eliminarPerfilRol(
    mysqli $conexion,
    int $idUsuario,
    string $rol
): void {
    $tabla = match ($rol) {
        "Administrador" => "administrador",
        "Tecnico" => "tecnico",
        default => "docente"
    };
    $sentencia = $conexion->prepare("DELETE FROM {$tabla} WHERE id_usuario = ?");

    try {
        $sentencia->bind_param("i", $idUsuario);
        $sentencia->execute();
    } finally {
        $sentencia->close();
    }
}

private function contarAdministradoresActivos(mysqli $conexion): int
{
    return (int) $conexion->query(
        "SELECT COUNT(*) AS total
         FROM usuario
         WHERE rol = 'Administrador' AND activo = 1"
    )->fetch_assoc()["total"];
}

public function crear(
    string $nombre,
    string $apellido,
    string $correo,
    string $rol,
    string $contrasena,
    ?int $idTurno
): int {
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    if ($hash === false) {
        throw new RuntimeException("No fue posible proteger la contraseña.");
    }

    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
        $sentencia = $conexion->prepare(
            "INSERT INTO usuario (nombre, apellido, correo, contrasena, rol, activo)
             VALUES (?, ?, ?, ?, ?, 1)"
        );

        try {
            $sentencia->bind_param("sssss", $nombre, $apellido, $correo, $hash, $rol);
            $sentencia->execute();
            $idUsuario = (int) $conexion->insert_id;
        } finally {
            $sentencia->close();
        }

        $this->insertarPerfilRol($conexion, $idUsuario, $rol, $idTurno);
        $conexion->commit();

        return $idUsuario;
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

public function actualizar(
    int $idUsuario,
    string $nombre,
    string $apellido,
    string $correo,
    string $rol,
    ?int $idTurno,
    ?string $contrasenaNueva = null
): void {
    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
        $sentenciaActual = $conexion->prepare(
            "SELECT rol, activo
             FROM usuario
             WHERE id_usuario = ?
             FOR UPDATE"
        );

        try {
            $sentenciaActual->bind_param("i", $idUsuario);
            $sentenciaActual->execute();
            $usuarioActual = $sentenciaActual->get_result()->fetch_assoc();
        } finally {
            $sentenciaActual->close();
        }

        if (!$usuarioActual) {
            throw new DomainException("El usuario seleccionado ya no existe.");
        }

        $rolAnterior = (string) $usuarioActual["rol"];

        if (
            $rolAnterior === "Administrador" &&
            $rol !== "Administrador" &&
            (int) $usuarioActual["activo"] === 1 &&
            $this->contarAdministradoresActivos($conexion) <= 1
        ) {
            throw new DomainException("Debe permanecer al menos un administrador activo.");
        }

        if ($rolAnterior !== $rol) {
            $this->eliminarPerfilRol($conexion, $idUsuario, $rolAnterior);
        }

        $this->insertarPerfilRol($conexion, $idUsuario, $rol, $idTurno);

        if ($contrasenaNueva !== null) {
            $hash = password_hash($contrasenaNueva, PASSWORD_DEFAULT);

            if ($hash === false) {
                throw new RuntimeException("No fue posible proteger la contraseña.");
            }

            $sentencia = $conexion->prepare(
                "UPDATE usuario
                 SET nombre = ?, apellido = ?, correo = ?, rol = ?, contrasena = ?
                 WHERE id_usuario = ?"
            );

            try {
                $sentencia->bind_param(
                    "sssssi",
                    $nombre,
                    $apellido,
                    $correo,
                    $rol,
                    $hash,
                    $idUsuario
                );
                $sentencia->execute();
            } finally {
                $sentencia->close();
            }
        } else {
            $sentencia = $conexion->prepare(
                "UPDATE usuario
                 SET nombre = ?, apellido = ?, correo = ?, rol = ?
                 WHERE id_usuario = ?"
            );

            try {
                $sentencia->bind_param("ssssi", $nombre, $apellido, $correo, $rol, $idUsuario);
                $sentencia->execute();
            } finally {
                $sentencia->close();
            }
        }

        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

public function cambiarEstado(int $idUsuario, bool $activar): void
{
    $conexion = self::conexion();
    $conexion->begin_transaction();

    try {
        $sentenciaActual = $conexion->prepare(
            "SELECT rol, activo
             FROM usuario
             WHERE id_usuario = ?
             FOR UPDATE"
        );

        try {
            $sentenciaActual->bind_param("i", $idUsuario);
            $sentenciaActual->execute();
            $usuario = $sentenciaActual->get_result()->fetch_assoc();
        } finally {
            $sentenciaActual->close();
        }

        if (!$usuario) {
            throw new DomainException("El usuario seleccionado ya no existe.");
        }

        if (
            !$activar &&
            $usuario["rol"] === "Administrador" &&
            (int) $usuario["activo"] === 1 &&
            $this->contarAdministradoresActivos($conexion) <= 1
        ) {
            throw new DomainException("Debe permanecer al menos un administrador activo.");
        }

        $estado = $activar ? 1 : 0;
        $sentencia = $conexion->prepare(
            "UPDATE usuario SET activo = ? WHERE id_usuario = ?"
        );

        try {
            $sentencia->bind_param("ii", $estado, $idUsuario);
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
}

