<?php

require_once __DIR__ . "/../modelos/UsuarioAdministrador.php";

/*
 * Capa de aplicación para administrar usuarios. Valida la entrada, aplica
 * permisos y delega la persistencia en UsuarioAdministrador.
 */
class UsuarioController
{
    private UsuarioAdministrador $modelo;

    public function __construct()
    {
        $this->modelo = new UsuarioAdministrador();
    }

    public function administrar(): void
    {
        $csrfToken = tokenAdministrador();
        $usuarioSesionId = (int) ($_SESSION["usuario_id"] ?? 0);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Patrón Post/Redirect/Get: evita repetir una operación al actualizar la página.
            $this->procesarFormulario($usuarioSesionId);
            redirigirAdministrador("usuarios");
        }

        $filtrosUsuarios = $this->leerFiltros();
        $usuariosAdministrador = [];
        $turnosUsuariosAdministrador = [];
        $resumenUsuariosAdministrador = $this->resumenVacio();
        $totalUsuariosAdministrador = 0;
        $totalPaginasUsuariosAdministrador = 1;
        $paginaActualUsuariosAdministrador = 1;
        $errorUsuariosAdministrador = false;

        try {
            $resultadoUsuarios = $this->modelo->obtener($filtrosUsuarios, 10);
            $usuariosAdministrador = $resultadoUsuarios["usuarios"];
            $totalUsuariosAdministrador = $resultadoUsuarios["total"];
            $totalPaginasUsuariosAdministrador = $resultadoUsuarios["total_paginas"];
            $paginaActualUsuariosAdministrador = $resultadoUsuarios["pagina_actual"];
            $filtrosUsuarios["pagina"] = $paginaActualUsuariosAdministrador;
            $turnosUsuariosAdministrador = $this->modelo->obtenerTurnos();
            $resumenUsuariosAdministrador = array_replace(
                $resumenUsuariosAdministrador,
                $this->modelo->obtenerResumen()
            );
        } catch (Throwable $error) {
            $errorUsuariosAdministrador = true;
            error_log("GesTIck - error al cargar usuarios: " . $error->getMessage());
        }

        $mensajeAdministrador = consumirMensajeAdministrador();

        require __DIR__ . "/../vistas/administrador/usuarios.php";
    }

    private function procesarFormulario(int $usuarioSesionId): void
    {
        if (!validarTokenAdministrador($_POST["csrf_token"] ?? "")) {
            guardarMensajeAdministrador(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            return;
        }

        // La acción declarada por el formulario determina una operación permitida.
        try {
            $accion = (string) ($_POST["accion"] ?? "");

            switch ($accion) {
                case "crear_usuario":
                    $this->crearUsuario();
                    break;

                case "actualizar_usuario":
                    $this->actualizarUsuario($usuarioSesionId);
                    break;

                case "desactivar_usuario":
                case "reactivar_usuario":
                    $this->cambiarEstadoUsuario($accion, $usuarioSesionId);
                    break;

                default:
                    throw new DomainException("La acción solicitada no es válida.");
            }
        } catch (DomainException $error) {
            guardarMensajeAdministrador("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            $this->guardarErrorBaseDeDatos($error);
        } catch (Throwable $error) {
            error_log("GesTIck - error inesperado al administrar usuarios: " . $error->getMessage());
            guardarMensajeAdministrador("error", "Ocurrió un error inesperado al procesar el usuario.");
        }
    }

    private function crearUsuario(): void
    {
        $usuario = $this->validarDatos($_POST, true);
        $this->modelo->crear(
            $usuario["nombre"],
            $usuario["apellido"],
            $usuario["correo"],
            $usuario["rol"],
            (string) $usuario["contrasena"],
            $usuario["id_turno"]
        );
        guardarMensajeAdministrador("exito", "Usuario creado correctamente.");
    }

    private function actualizarUsuario(int $usuarioSesionId): void
    {
        $idUsuario = $this->leerIdUsuario();
        $usuario = $this->validarDatos($_POST, false);

        if ($idUsuario === $usuarioSesionId && $usuario["rol"] !== "Administrador") {
            // La cuenta en uso no puede retirarse a sí misma el acceso administrativo.
            throw new DomainException("No puedes cambiar el rol de tu propia cuenta.");
        }

        $this->modelo->actualizar(
            $idUsuario,
            $usuario["nombre"],
            $usuario["apellido"],
            $usuario["correo"],
            $usuario["rol"],
            $usuario["id_turno"],
            $usuario["contrasena"]
        );

        if ($idUsuario === $usuarioSesionId) {
            $_SESSION["nombre"] = trim($usuario["nombre"] . " " . $usuario["apellido"]);
            $_SESSION["correo"] = $usuario["correo"];
        }

        guardarMensajeAdministrador("exito", "Usuario actualizado correctamente.");
    }

    private function cambiarEstadoUsuario(string $accion, int $usuarioSesionId): void
    {
        $idUsuario = $this->leerIdUsuario();

        if ($idUsuario === $usuarioSesionId && $accion === "desactivar_usuario") {
            // Impide cerrar accidentalmente la cuenta con la que se administra el sistema.
            throw new DomainException("No puedes desactivar tu propia cuenta.");
        }

        $activar = $accion === "reactivar_usuario";
        $this->modelo->cambiarEstado($idUsuario, $activar);
        guardarMensajeAdministrador(
            "exito",
            $activar
                ? "Usuario reactivado correctamente."
                : "Usuario desactivado correctamente."
        );
    }

    private function leerIdUsuario(): int
    {
        $idUsuario = (int) ($_POST["id_usuario"] ?? 0);

        if ($idUsuario < 1) {
            throw new DomainException("El usuario seleccionado no es válido.");
        }

        return $idUsuario;
    }

    private function validarDatos(array $datos, bool $esCreacion): array
    {
        // La validación se repite en el servidor aunque el formulario también use HTML.
        $nombre = trim((string) ($datos["nombre"] ?? ""));
        $apellido = trim((string) ($datos["apellido"] ?? ""));
        $correo = strtolower(trim((string) ($datos["correo"] ?? "")));
        $rol = trim((string) ($datos["rol"] ?? ""));
        $contrasena = (string) ($datos["contrasena"] ?? "");
        $idTurno = $rol === "Tecnico" ? (int) ($datos["id_turno"] ?? 0) : null;

        if ($nombre === "" || $apellido === "") {
            throw new DomainException("El nombre y el apellido son obligatorios.");
        }

        if (mb_strlen($nombre) > 60 || mb_strlen($apellido) > 60) {
            throw new DomainException("El nombre y el apellido pueden tener hasta 60 caracteres.");
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
            throw new DomainException("Ingresa un correo electrónico válido.");
        }

        if (!in_array($rol, ["Administrador", "Tecnico", "Docente"], true)) {
            throw new DomainException("Selecciona un rol válido.");
        }

        if ($rol === "Tecnico") {
            $turnosValidos = array_map(
                static fn (array $turno): int => (int) $turno["id_turno"],
                $this->modelo->obtenerTurnos()
            );

            if (!in_array($idTurno, $turnosValidos, true)) {
                throw new DomainException("Selecciona un turno válido para el técnico.");
            }
        }

        if ($esCreacion && $contrasena === "") {
            throw new DomainException("La contraseña es obligatoria para crear el usuario.");
        }

        if ($contrasena !== "" && (strlen($contrasena) < 8 || strlen($contrasena) > 72)) {
            throw new DomainException("La contraseña debe tener entre 8 y 72 caracteres.");
        }

        return [
            "nombre" => $nombre,
            "apellido" => $apellido,
            "correo" => $correo,
            "rol" => $rol,
            "id_turno" => $idTurno,
            "contrasena" => $contrasena === "" ? null : $contrasena
        ];
    }

    private function leerFiltros(): array
    {
        $rol = (string) ($_GET["rol"] ?? "");
        $estado = (string) ($_GET["estado"] ?? "activos");

        return [
            "buscar" => mb_substr(trim((string) ($_GET["buscar"] ?? "")), 0, 100),
            "rol" => in_array($rol, ["Administrador", "Tecnico", "Docente"], true)
                ? $rol
                : "",
            "estado" => in_array($estado, ["activos", "inactivos", "todos"], true)
                ? $estado
                : "activos",
            "pagina" => max(1, (int) ($_GET["p"] ?? 1))
        ];
    }

    private function resumenVacio(): array
    {
        return [
            "total" => 0,
            "activos" => 0,
            "inactivos" => 0,
            "administradores" => 0,
            "tecnicos" => 0,
            "docentes" => 0
        ];
    }

    private function guardarErrorBaseDeDatos(mysqli_sql_exception $error): void
    {
        error_log("GesTIck - error al administrar usuarios: " . $error->getMessage());

        // Se traduce el error técnico a un mensaje comprensible sin exponer detalles SQL.
        switch ($error->getCode()) {
            case 1062:
                guardarMensajeAdministrador("error", "El correo electrónico ya está registrado.");
                break;

            case 1451:
                guardarMensajeAdministrador(
                    "error",
                    "No se puede cambiar el rol porque el usuario tiene información asociada."
                );
                break;

            case 1452:
                guardarMensajeAdministrador("error", "El rol o turno seleccionado ya no está disponible.");
                break;

            default:
                guardarMensajeAdministrador(
                    "error",
                    "No fue posible guardar los cambios en la base de datos."
                );
        }
    }
}
