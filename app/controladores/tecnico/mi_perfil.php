<?php

/* Gestiona los datos personales y la contraseña del técnico autenticado. */
if ($pagina === "mi_perfil") {
    require_once __DIR__ . "/../../modelos/Usuario.php";

    $csrfToken = tokenTecnico();
    $usuarioId = (int) ($_SESSION["usuario_id"] ?? 0);
    $errorPerfilTecnico = false;

    if ($usuarioId < 1 && !empty($_SESSION["correo"])) {
        try {
            $usuarioSesion = Usuario::buscarPorCorreo((string) $_SESSION["correo"]);

            if ($usuarioSesion && in_array($usuarioSesion["rol"] ?? "", ["Tecnico", "Técnico"], true)) {
                $usuarioId = (int) $usuarioSesion["id_usuario"];
                $_SESSION["usuario_id"] = $usuarioId;
            }
        } catch (Throwable $error) {
            $errorPerfilTecnico = true;
            error_log("GesTIck - error al identificar el perfil técnico: " . $error->getMessage());
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!validarTokenTecnico($_POST["csrf_token"] ?? "")) {
            guardarMensajeTecnico("error", "La sesión del formulario venció. Vuelve a intentarlo.");
            redirigirTecnico("mi_perfil");
        }

        try {
            if ($usuarioId < 1) {
                throw new DomainException("No fue posible identificar la cuenta del técnico.");
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
                guardarMensajeTecnico("exito", "Tus datos se actualizaron correctamente.");
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
                guardarMensajeTecnico("exito", "Tu contraseña se actualizó correctamente.");
            } else {
                throw new DomainException("La acción solicitada no es válida.");
            }
        } catch (DomainException $error) {
            guardarMensajeTecnico("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al actualizar el perfil técnico: " . $error->getMessage());
            guardarMensajeTecnico(
                "error",
                $error->getCode() === 1062
                    ? "El correo electrónico ya está registrado por otra cuenta."
                    : "No fue posible guardar los cambios en la base de datos."
            );
        } catch (Throwable $error) {
            error_log("GesTIck - error al actualizar el perfil técnico: " . $error->getMessage());
            guardarMensajeTecnico("error", "Ocurrió un error inesperado al actualizar el perfil.");
        }

        redirigirTecnico("mi_perfil");
    }

    $perfilTecnico = false;

    if ($usuarioId > 0) {
        try {
            $perfilTecnico = Usuario::buscarPorId($usuarioId);

            if (!$perfilTecnico || !in_array($perfilTecnico["rol"] ?? "", ["Tecnico", "Técnico"], true)) {
                $perfilTecnico = false;
                $errorPerfilTecnico = true;
            }
        } catch (Throwable $error) {
            $errorPerfilTecnico = true;
            error_log("GesTIck - error al cargar el perfil técnico: " . $error->getMessage());
        }
    } else {
        $errorPerfilTecnico = true;
    }

    $mensajeTecnico = consumirMensajeTecnico();

    require __DIR__ . "/../../vistas/tecnico/mi_perfil.php";
    return;
}
