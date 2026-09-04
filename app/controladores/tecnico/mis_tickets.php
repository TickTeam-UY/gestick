<?php

/* Prepara todos los tickets asignados al técnico y procesa sus cambios. */
if ($pagina == "mis_tickets") {
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
            redirigirTecnico("mis_tickets");
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

        redirigirTecnico("mis_tickets");
    }

    $tickets = [];
    $errorTickets = false;

    try {
        $tickets = $modeloTicket->obtenerAsignados($idTecnico);
    } catch (Throwable $error) {
        $errorTickets = true;
        error_log("GesTIck - error al cargar tickets del técnico: " . $error->getMessage());
    }

    $mensajeTecnico = consumirMensajeTecnico();
}

require __DIR__ . "/../../vistas/tecnico/" . $pagina . ".php";
