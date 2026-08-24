<?php

require_once __DIR__ . "/../../modelos/Solicitud.php";

$modeloSolicitud = new Solicitud();
$idTecnico = (int) ($_SESSION["usuario_id"] ?? 0);
$csrfToken = tokenTecnico();
$solicitudes = [];
$errorSolicitudes = false;

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    if (!validarTokenTecnico((string) ($_POST["csrf_token"] ?? ""))) {
        guardarMensajeTecnico(
            "error",
            "La sesión del formulario venció. Vuelve a intentarlo."
        );
        redirigirTecnico("solicitudes");
    }

    try {
        if ((string) ($_POST["accion"] ?? "") !== "actualizar_solicitud") {
            throw new DomainException("La acción solicitada no es válida.");
        }

        $modeloSolicitud->actualizar(
            $idTecnico,
            (int) ($_POST["id_solicitud"] ?? 0),
            (string) ($_POST["estado"] ?? ""),
            (string) ($_POST["trabajo_realizado"] ?? ""),
            (string) ($_POST["fecha_fin"] ?? "")
        );
        guardarMensajeTecnico("exito", "Solicitud actualizada correctamente.");
    } catch (DomainException $error) {
        guardarMensajeTecnico("error", $error->getMessage());
    } catch (mysqli_sql_exception $error) {
        error_log("GesTIck - error al actualizar solicitud técnica: " . $error->getMessage());
        guardarMensajeTecnico(
            "error",
            "No fue posible guardar la solicitud en la base de datos."
        );
    } catch (Throwable $error) {
        error_log("GesTIck - error inesperado en solicitudes técnicas: " . $error->getMessage());
        guardarMensajeTecnico("error", "Ocurrió un error inesperado al guardar la solicitud.");
    }

    redirigirTecnico("solicitudes");
}

try {
    if ($idTecnico < 1) {
        throw new DomainException("No fue posible identificar al técnico.");
    }

    $solicitudes = $modeloSolicitud->obtenerParaTecnico($idTecnico);
} catch (Throwable $error) {
    $errorSolicitudes = true;
    error_log("GesTIck - error al cargar solicitudes técnicas: " . $error->getMessage());
}

$mensajeTecnico = consumirMensajeTecnico();

require __DIR__ . "/../../vistas/tecnico/solicitudes.php";
