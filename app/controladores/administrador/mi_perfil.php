<?php

if ($pagina === "mi_perfil") {
    require_once __DIR__ . "/../../modelos/Usuario.php";

    $csrfToken = tokenAdministrador();
    $usuarioId = (int) ($_SESSION["usuario_id"] ?? 0);
    $errorPerfil = false;

    if ($usuarioId < 1 && !empty($_SESSION["correo"])) {
        try {
            $usuarioSesion = Usuario::buscarPorCorreo((string) $_SESSION["correo"]);

            if ($usuarioSesion && ($usuarioSesion["rol"] ?? "") === "Administrador") {
                $usuarioId = (int) $usuarioSesion["id_usuario"];
                $_SESSION["usuario_id"] = $usuarioId;
            }
        } catch (Throwable $error) {
            $errorPerfil = true;
            error_log("GesTIck - error al identificar el perfil administrador: " . $error->getMessage());
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!validarTokenAdministrador($_POST["csrf_token"] ?? "")) {
            guardarMensajeAdministrador(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirAdministrador("mi_perfil");
        }

        try {
            if ($usuarioId < 1) {
                throw new DomainException("No fue posible identificar la cuenta del administrador.");
            }

            $accion = $_POST["accion"] ?? "";

            if ($accion === "actualizar_perfil") {
                $datosPerfil = validarDatosPerfil($_POST);
                Usuario::actualizarDatosPerfil(
                    $usuarioId,
                    $datosPerfil["nombre"],
                    $datosPerfil["apellido"],
                    $datosPerfil["correo"]
                );

                $_SESSION["nombre"] = trim($datosPerfil["nombre"] . " " . $datosPerfil["apellido"]);
                $_SESSION["correo"] = $datosPerfil["correo"];
                guardarMensajeAdministrador("exito", "Tus datos se actualizaron correctamente.");
            } elseif ($accion === "actualizar_contrasena") {
                $contrasenaActual = $_POST["contrasena_actual"] ?? "";
                $contrasenaNueva = $_POST["contrasena_nueva"] ?? "";
                $repetirContrasena = $_POST["repetir_contrasena"] ?? "";

                if ($contrasenaActual === "" || $contrasenaNueva === "" || $repetirContrasena === "") {
                    throw new DomainException("Completa los tres campos de contraseña.");
                }

                if ($contrasenaNueva !== $repetirContrasena) {
                    throw new DomainException("Las contraseñas nuevas no coinciden.");
                }

                if (strlen($contrasenaNueva) < 8 || strlen($contrasenaNueva) > 72) {
                    throw new DomainException("La nueva contraseña debe tener entre 8 y 72 caracteres.");
                }

                Usuario::actualizarContrasenaPerfil($usuarioId, $contrasenaActual, $contrasenaNueva);
                guardarMensajeAdministrador("exito", "Tu contraseña se actualizó correctamente.");
            } else {
                throw new DomainException("La acción solicitada no es válida.");
            }
        } catch (DomainException $error) {
            guardarMensajeAdministrador("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al actualizar el perfil administrador: " . $error->getMessage());

            guardarMensajeAdministrador(
                "error",
                $error->getCode() === 1062
                    ? "El correo electrónico ya está registrado por otra cuenta."
                    : "No fue posible guardar los cambios en la base de datos."
            );
        } catch (Throwable $error) {
            error_log("GesTIck - error al actualizar el perfil administrador: " . $error->getMessage());
            guardarMensajeAdministrador("error", "Ocurrió un error inesperado al actualizar el perfil.");
        }

        redirigirAdministrador("mi_perfil");
    }

    $perfilAdministrador = false;

    if ($usuarioId > 0) {
        try {
            $perfilAdministrador = Usuario::buscarPorId($usuarioId);

            if (!$perfilAdministrador || ($perfilAdministrador["rol"] ?? "") !== "Administrador") {
                $perfilAdministrador = false;
                $errorPerfil = true;
            }
        } catch (Throwable $error) {
            $errorPerfil = true;
            error_log("GesTIck - error al cargar el perfil administrador: " . $error->getMessage());
        }
    } else {
        $errorPerfil = true;
    }

    $mensajeAdministrador = consumirMensajeAdministrador();

    require __DIR__ . "/../../vistas/administrador/mi_perfil.php";
    return;
}
