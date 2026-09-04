<?php

/* Lista tickets sin asignar y permite que el técnico tome uno disponible. */
if ($pagina == "pendientes") {
    require_once __DIR__ . "/../../modelos/Ticket.php";

    $csrfToken = tokenTecnico();
    $idTecnico = (int) ($_SESSION["usuario_id"] ?? 0);
    $modeloTicket = new Ticket();

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenTecnico($_POST["csrf_token"] ?? "")) {
            guardarMensajeTecnico(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirTecnico("pendientes");
        }

        try {
            if ((string) ($_POST["accion"] ?? "") !== "tomar_ticket") {
                throw new DomainException("La acción solicitada no es válida.");
            }

            $idTicket = (int) ($_POST["id_ticket"] ?? 0);

            if ($idTecnico < 1) {
                throw new DomainException("No fue posible identificar al técnico.");
            }

            if ($idTicket < 1) {
                throw new DomainException("El ticket seleccionado no es válido.");
            }

            $modeloTicket->tomar($idTicket, $idTecnico);
            guardarMensajeTecnico(
                "exito",
                "El ticket fue asignado a tu cuenta correctamente."
            );
        } catch (DomainException $error) {
            guardarMensajeTecnico("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al tomar ticket: " . $error->getMessage());
            guardarMensajeTecnico(
                "error",
                "No fue posible asignar el ticket en la base de datos."
            );
        } catch (Throwable $error) {
            error_log("GesTIck - error inesperado al tomar ticket: " . $error->getMessage());
            guardarMensajeTecnico(
                "error",
                "Ocurrió un error inesperado al asignar el ticket."
            );
        }

        redirigirTecnico("pendientes");
    }

    $pendientes = $modeloTicket->obtenerPorEstado($idTecnico, "Pendiente");
    $mensajeTecnico = consumirMensajeTecnico();
}

require __DIR__ . "/../../vistas/tecnico/" . $pagina . ".php";
