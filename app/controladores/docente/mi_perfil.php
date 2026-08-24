<?php

if ($pagina === "mi_perfil") {
    require_once __DIR__ . "/../../modelos/Usuario.php";
    require_once __DIR__ . "/../../modelos/PerfilDocente.php";

    $modeloPerfilDocente = new PerfilDocente();
    $csrfToken = tokenDocente();
    $errorPerfilDocente = false;

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenDocente((string) ($_POST["csrf_token"] ?? ""))) {
            guardarMensajeDocente(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirDocente("mi_perfil");
        }

        try {
            if ($idDocente < 1) {
                throw new DomainException("No fue posible identificar la cuenta del docente.");
            }

            $accionPerfil = (string) ($_POST["accion"] ?? "");

            if ($accionPerfil === "actualizar_perfil") {
                $datosPerfil = validarDatosPerfil($_POST);
                Usuario::actualizarDatosPerfil(
                    $idDocente,
                    $datosPerfil["nombre"],
                    $datosPerfil["apellido"],
                    $datosPerfil["correo"]
                );
                $_SESSION["nombre"] = trim(
                    $datosPerfil["nombre"] . " " . $datosPerfil["apellido"]
                );
                $_SESSION["correo"] = $datosPerfil["correo"];
                guardarMensajeDocente("exito", "Tus datos se actualizaron correctamente.");
            } elseif ($accionPerfil === "actualizar_contrasena") {
                $contrasenaActual = (string) ($_POST["contrasena_actual"] ?? "");
                $contrasenaNueva = (string) ($_POST["contrasena_nueva"] ?? "");
                $repetirContrasena = (string) ($_POST["repetir_contrasena"] ?? "");

                if ($contrasenaActual === "" || $contrasenaNueva === "" || $repetirContrasena === "") {
                    throw new DomainException("Completa los tres campos de contraseña.");
                }

                if ($contrasenaNueva !== $repetirContrasena) {
                    throw new DomainException("Las contraseñas nuevas no coinciden.");
                }

                if (strlen($contrasenaNueva) < 8 || strlen($contrasenaNueva) > 72) {
                    throw new DomainException("La nueva contraseña debe tener entre 8 y 72 caracteres.");
                }

                Usuario::actualizarContrasenaPerfil(
                    $idDocente,
                    $contrasenaActual,
                    $contrasenaNueva
                );
                guardarMensajeDocente("exito", "Tu contraseña se actualizó correctamente.");
            } else {
                throw new DomainException("La acción solicitada no es válida.");
            }
        } catch (DomainException $error) {
            guardarMensajeDocente("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al actualizar perfil docente: " . $error->getMessage());
            guardarMensajeDocente(
                "error",
                $error->getCode() === 1062
                    ? "El correo electrónico ya está registrado por otra cuenta."
                    : "No fue posible guardar los cambios en la base de datos."
            );
        } catch (Throwable $error) {
            error_log("GesTIck - error inesperado en perfil docente: " . $error->getMessage());
            guardarMensajeDocente(
                "error",
                "Ocurrió un error inesperado al actualizar el perfil."
            );
        }

        redirigirDocente("mi_perfil");
    }

    $perfilDocente = false;
    $gruposPerfilDocente = [];

    try {
        $perfilDocente = Usuario::buscarPorId($idDocente);

        if (!$perfilDocente || ($perfilDocente["rol"] ?? "") !== "Docente") {
            $perfilDocente = false;
            $errorPerfilDocente = true;
        } else {
            $gruposPerfilDocente = $modeloPerfilDocente->obtenerGrupos($idDocente);
        }
    } catch (Throwable $error) {
        $errorPerfilDocente = true;
        error_log("GesTIck - error al cargar perfil docente: " . $error->getMessage());
    }

    $mensajeDocente = consumirMensajeDocente();

    require __DIR__ . "/../../vistas/docente/mi_perfil.php";
    return;
}
