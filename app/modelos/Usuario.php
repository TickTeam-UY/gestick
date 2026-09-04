<?php

require_once __DIR__ . "/Modelo.php";

/*
 * MODELO USUARIO
 *
 * Representa las operaciones de la tabla `usuario`. Todas las consultas
 * reciben sus valores mediante sentencias preparadas.
 */
class Usuario extends Modelo
{
    public static function buscarPorCorreo(string $correo): array|false
    {
        $conexion = self::conexion();
        $sentencia = $conexion->prepare(
            "SELECT id_usuario, nombre, apellido, correo, contrasena, rol, activo
             FROM usuario
             WHERE correo = ?
             LIMIT 1"
        );

        try {
            $sentencia->bind_param("s", $correo);
            $sentencia->execute();
            $usuario = $sentencia->get_result()->fetch_assoc();

            return $usuario ?: false;
        } finally {
            $sentencia->close();
        }
    }

    public static function buscarPorId(int $idUsuario): array|false
    {
        $conexion = self::conexion();
        $sentencia = $conexion->prepare(
            "SELECT id_usuario, nombre, apellido, correo, rol, activo, fecha_registro
             FROM usuario
             WHERE id_usuario = ?
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

    public static function actualizarDatosPerfil(
        int $idUsuario,
        string $nombre,
        string $apellido,
        string $correo
    ): void {
        $conexion = self::conexion();
        $sentencia = $conexion->prepare(
            "UPDATE usuario
             SET nombre = ?, apellido = ?, correo = ?
             WHERE id_usuario = ? AND activo = 1"
        );

        try {
            $sentencia->bind_param("sssi", $nombre, $apellido, $correo, $idUsuario);
            $sentencia->execute();

            // Cero filas también puede significar que los datos enviados eran iguales.
            if ($sentencia->affected_rows < 1) {
                $usuarioActual = self::buscarPorId($idUsuario);

                if ($usuarioActual === false || (int) $usuarioActual["activo"] !== 1) {
                    throw new DomainException("La cuenta de usuario ya no está disponible.");
                }
            }
        } finally {
            $sentencia->close();
        }
    }

    public static function actualizarContrasenaPerfil(
        int $idUsuario,
        string $contrasenaActual,
        string $contrasenaNueva
    ): void {
        // Antes de reemplazarla se comprueba la contraseña que ya pertenece a la cuenta.
        $conexion = self::conexion();
        $sentenciaConsulta = $conexion->prepare(
            "SELECT contrasena
             FROM usuario
             WHERE id_usuario = ? AND activo = 1
             LIMIT 1"
        );

        try {
            $sentenciaConsulta->bind_param("i", $idUsuario);
            $sentenciaConsulta->execute();
            $credencial = $sentenciaConsulta->get_result()->fetch_assoc();
        } finally {
            $sentenciaConsulta->close();
        }

        if (!$credencial) {
            throw new DomainException("La cuenta de usuario ya no está disponible.");
        }

        if (!self::verificarContrasena($contrasenaActual, $credencial["contrasena"])) {
            throw new DomainException("La contraseña actual no es correcta.");
        }

        $hash = password_hash($contrasenaNueva, PASSWORD_DEFAULT);

        if ($hash === false) {
            throw new RuntimeException("No fue posible proteger la nueva contraseña.");
        }

        $sentenciaActualizacion = $conexion->prepare(
            "UPDATE usuario SET contrasena = ? WHERE id_usuario = ? AND activo = 1"
        );

        try {
            $sentenciaActualizacion->bind_param("si", $hash, $idUsuario);
            $sentenciaActualizacion->execute();
        } finally {
            $sentenciaActualizacion->close();
        }
    }

    public static function verificarContrasena(
        string $contrasena,
        string $valorAlmacenado
    ): bool {
        // password_verify reconoce el algoritmo y la sal almacenados dentro del hash.
        if (password_verify($contrasena, $valorAlmacenado)) {
            return true;
        }

        /* Compatibilidad temporal con credenciales antiguas guardadas como texto. */
        $informacionHash = password_get_info($valorAlmacenado);

        if (($informacionHash["algoName"] ?? "unknown") !== "unknown") {
            return false;
        }

        return hash_equals($valorAlmacenado, $contrasena);
    }

    public static function autenticar(string $correo, string $contrasena): array|false
    {
        $usuario = self::buscarPorCorreo($correo);

        if ($usuario === false || (int) $usuario["activo"] !== 1) {
            return false;
        }

        if (!self::verificarContrasena($contrasena, $usuario["contrasena"])) {
            return false;
        }

        // Los datos sensibles o internos no deben copiarse a la sesión.
        unset($usuario["contrasena"], $usuario["activo"]);

        return $usuario;
    }
}
