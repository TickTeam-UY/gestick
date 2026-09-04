<?php

/* Valida el formulario y delega al modelo el registro completo de la planilla. */
if ($pagina === "llenar_planilla") {
    require_once __DIR__ . "/../../modelos/PlanillaDocente.php";
    $modeloPlanillaDocente = new PlanillaDocente();

    $csrfToken = tokenDocente();

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenDocente((string) ($_POST["csrf_token"] ?? ""))) {
            guardarMensajeDocente(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirDocente("llenar_planilla");
        }

        try {
            if ((string) ($_POST["accion"] ?? "") !== "guardar_planilla") {
                throw new DomainException("La acción solicitada no es válida.");
            }

            $resultadoGuardado = $modeloPlanillaDocente->guardar($idDocente, $_POST);
            $textoTickets = $resultadoGuardado["tickets_creados"] === 1
                ? " Se generó 1 ticket pendiente."
                : ($resultadoGuardado["tickets_creados"] > 1
                    ? " Se generaron {$resultadoGuardado["tickets_creados"]} tickets pendientes."
                    : "");

            guardarMensajeDocente(
                "exito",
                "La planilla #{$resultadoGuardado["id_planilla"]} se guardó correctamente." .
                $textoTickets
            );
            unset($_SESSION["datos_planilla_docente"]);
        } catch (DomainException $error) {
            $_SESSION["datos_planilla_docente"] = $_POST;
            guardarMensajeDocente("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            $_SESSION["datos_planilla_docente"] = $_POST;
            error_log("GesTIck - error al guardar planilla docente: " . $error->getMessage());
            guardarMensajeDocente(
                "error",
                "No fue posible guardar la planilla en la base de datos."
            );
        } catch (Throwable $error) {
            $_SESSION["datos_planilla_docente"] = $_POST;
            error_log("GesTIck - error inesperado al guardar planilla: " . $error->getMessage());
            guardarMensajeDocente(
                "error",
                "Ocurrió un error inesperado al guardar la planilla."
            );
        }

        redirigirDocente("llenar_planilla");
    }

    $opcionesPlanillaDocente = [
        "grupos" => [],
        "asignaturas" => [],
        "turnos" => [],
        "ubicaciones" => [],
        "equipos" => [],
        "alumnos" => [],
        "siguiente_id" => 1
    ];
    $errorCargaPlanillaDocente = false;

    try {
        $opcionesPlanillaDocente = $modeloPlanillaDocente->obtenerOpciones($idDocente);
    } catch (Throwable $error) {
        $errorCargaPlanillaDocente = true;
        error_log("GesTIck - error al preparar planilla docente: " . $error->getMessage());
    }

    $mensajeDocente = consumirMensajeDocente();
    $datosAnterioresPlanilla = $_SESSION["datos_planilla_docente"] ?? [];
    unset($_SESSION["datos_planilla_docente"]);

    require __DIR__ . "/../../vistas/docente/llenar_planilla.php";
    return;
}
