<?php

/* Coordina la creación, consulta y cancelación de solicitudes del docente. */
if ($pagina === "mis_solicitudes") {
    require_once __DIR__ . "/../../modelos/SolicitudDocente.php";

    $csrfToken = tokenDocente();
    $modeloSolicitudesDocente = new SolicitudDocente();

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenDocente((string) ($_POST["csrf_token"] ?? ""))) {
            guardarMensajeDocente(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirDocente("mis_solicitudes");
        }

        $accionSolicitud = (string) ($_POST["accion"] ?? "");

        try {
            if ($accionSolicitud === "cancelar_solicitud") {
                $idSolicitud = (int) ($_POST["id_solicitud"] ?? 0);
                $modeloSolicitudesDocente->cancelar($idDocente, $idSolicitud);
                guardarMensajeDocente(
                    "exito",
                    "La solicitud #{$idSolicitud} fue cancelada."
                );
                redirigirDocente("mis_solicitudes");
            }

            if ($accionSolicitud !== "crear_solicitud") {
                throw new DomainException("La acción solicitada no es válida.");
            }

            $idSolicitud = $modeloSolicitudesDocente->crear(
                $idDocente,
                (string) ($_POST["asunto"] ?? ""),
                (string) ($_POST["descripcion"] ?? "")
            );
            unset($_SESSION["datos_solicitud_docente"]);
            guardarMensajeDocente(
                "exito",
                "La solicitud #{$idSolicitud} fue enviada a Soporte informático."
            );
            redirigirDocente("mis_solicitudes");
        } catch (DomainException $error) {
            if ($accionSolicitud === "crear_solicitud") {
                $_SESSION["datos_solicitud_docente"] = [
                    "asunto" => (string) ($_POST["asunto"] ?? ""),
                    "descripcion" => (string) ($_POST["descripcion"] ?? "")
                ];
            }
            guardarMensajeDocente("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            if ($accionSolicitud === "crear_solicitud") {
                $_SESSION["datos_solicitud_docente"] = [
                    "asunto" => (string) ($_POST["asunto"] ?? ""),
                    "descripcion" => (string) ($_POST["descripcion"] ?? "")
                ];
            }
            error_log("GesTIck - error al procesar solicitud docente: " . $error->getMessage());
            guardarMensajeDocente(
                "error",
                "No fue posible procesar la solicitud en la base de datos."
            );
        } catch (Throwable $error) {
            if ($accionSolicitud === "crear_solicitud") {
                $_SESSION["datos_solicitud_docente"] = [
                    "asunto" => (string) ($_POST["asunto"] ?? ""),
                    "descripcion" => (string) ($_POST["descripcion"] ?? "")
                ];
            }
            error_log("GesTIck - error inesperado al procesar solicitud: " . $error->getMessage());
            guardarMensajeDocente(
                "error",
                "Ocurrió un error inesperado al procesar la solicitud."
            );
        }

        redirigirDocente("mis_solicitudes");
    }

    $ordenSolicitudes = (string) ($_GET["orden"] ?? "recientes");
    $filtrosSolicitudesDocente = [
        "buscar" => mb_substr(trim((string) ($_GET["buscar"] ?? "")), 0, 100),
        "orden" => in_array($ordenSolicitudes, ["recientes", "antiguas"], true)
            ? $ordenSolicitudes
            : "recientes",
        "pagina" => max(1, (int) ($_GET["p"] ?? 1))
    ];
    $solicitudesDocente = [];
    $totalSolicitudesDocente = 0;
    $totalPaginasSolicitudesDocente = 1;
    $paginaActualSolicitudesDocente = 1;
    $errorSolicitudesDocente = false;

    try {
        $resultadoSolicitudes = $modeloSolicitudesDocente->obtenerPorDocente(
            $idDocente,
            $filtrosSolicitudesDocente,
            8
        );
        $solicitudesDocente = $resultadoSolicitudes["solicitudes"];
        $totalSolicitudesDocente = $resultadoSolicitudes["total"];
        $totalPaginasSolicitudesDocente = $resultadoSolicitudes["total_paginas"];
        $paginaActualSolicitudesDocente = $resultadoSolicitudes["pagina_actual"];
        $filtrosSolicitudesDocente["pagina"] = $paginaActualSolicitudesDocente;
    } catch (Throwable $error) {
        $errorSolicitudesDocente = true;
        error_log("GesTIck - error al cargar solicitudes del docente: " . $error->getMessage());
    }

    $mensajeDocente = consumirMensajeDocente();
    $datosAnterioresSolicitud = $_SESSION["datos_solicitud_docente"] ?? [];
    unset($_SESSION["datos_solicitud_docente"]);
    $abrirFormularioSolicitud = $mensajeDocente
        && $mensajeDocente["tipo"] === "error"
        && $datosAnterioresSolicitud;

    require __DIR__ . "/../../vistas/docente/mis_solicitudes.php";
    return;
}
