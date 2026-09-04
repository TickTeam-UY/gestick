<?php

/* Procesa los avances y cierres de tickets que está atendiendo el técnico. */
if ($pagina == "en_proceso") {
    require_once __DIR__ . "/../../modelos/Ticket.php";

    $csrfToken = tokenTecnico();
    $modeloTicket = new Ticket();
    $idTecnico = (int) ($_SESSION["usuario_id"] ?? 0);

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenTecnico($_POST["csrf_token"] ?? "")) {
            guardarMensajeTecnico(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirTecnico("en_proceso");
        }

        try {
            if ((string) ($_POST["accion"] ?? "") !== "actualizar_ticket") {
                throw new DomainException("La acción solicitada no es válida.");
            }

            $idTicket = (int) ($_POST["id_ticket"] ?? 0);
            $prioridad = trim((string) ($_POST["prioridad"] ?? ""));
            $estado = trim((string) ($_POST["estado"] ?? ""));
            $solucion = trim((string) ($_POST["solucion"] ?? ""));

            if ($idTicket < 1 || $idTecnico < 1) {
                throw new DomainException("El ticket seleccionado no es válido.");
            }

            $modeloTicket->actualizarPorTecnico(
                $idTicket,
                $idTecnico,
                $prioridad,
                $estado,
                $solucion === "" ? null : $solucion
            );
            guardarMensajeTecnico(
                "exito",
                "El ticket se actualizó correctamente."
            );
        } catch (DomainException $error) {
            guardarMensajeTecnico("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al actualizar ticket: " . $error->getMessage());
            guardarMensajeTecnico(
                "error",
                "No fue posible actualizar el ticket en la base de datos."
            );
        } catch (Throwable $error) {
            error_log(
                "GesTIck - error inesperado al actualizar ticket: " .
                $error->getMessage()
            );
            guardarMensajeTecnico(
                "error",
                "Ocurrió un error inesperado al actualizar el ticket."
            );
        }

        redirigirTecnico("en_proceso");
    }

    $enProceso = [];
    $errorTickets = false;

    try {
        $enProceso = $modeloTicket->obtenerPorEstado($idTecnico, "En proceso");
    } catch (Throwable $error) {
        $errorTickets = true;
        error_log(
            "GesTIck - error al cargar tickets en proceso: " . $error->getMessage()
        );
    }

    $mensajeTecnico = consumirMensajeTecnico();
}

require __DIR__ . "/../../vistas/tecnico/" . $pagina . ".php";
