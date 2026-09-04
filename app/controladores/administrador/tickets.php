<?php

/* Procesa la asignación y reasignación de tickets y prepara su bandeja. */
if ($pagina === "tickets") {
    require_once __DIR__ . "/../../modelos/TicketAdministrador.php";
    $modeloTicketAdministrador = new TicketAdministrador();

    $csrfToken = tokenAdministrador();

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenAdministrador($_POST["csrf_token"] ?? "")) {
            guardarMensajeAdministrador(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirAdministrador("tickets");
        }

        try {
            if ((string) ($_POST["accion"] ?? "") !== "administrar_ticket") {
                throw new DomainException("La acción solicitada no es válida.");
            }

            $idTicket = (int) ($_POST["id_ticket"] ?? 0);
            $idPrioridad = (int) ($_POST["id_prioridad"] ?? 0);
            $idEstado = (int) ($_POST["id_estado_ticket"] ?? 0);
            $valorTecnico = trim((string) ($_POST["id_tecnico"] ?? ""));
            $idTecnico = $valorTecnico === "" ? null : (int) $valorTecnico;
            $solucion = trim((string) ($_POST["solucion"] ?? ""));

            if ($idTicket < 1) {
                throw new DomainException("El ticket seleccionado no es válido.");
            }

            if ($idTecnico !== null && $idTecnico < 1) {
                throw new DomainException("El técnico seleccionado no es válido.");
            }

            if (mb_strlen($solucion) > 5000) {
                throw new DomainException("La solución puede tener hasta 5000 caracteres.");
            }

            $modeloTicketAdministrador->administrar(
                $idTicket,
                $idTecnico,
                $idPrioridad,
                $idEstado,
                $solucion === "" ? null : $solucion
            );
            guardarMensajeAdministrador(
                "exito",
                "La asignación y el estado del ticket se actualizaron correctamente."
            );
        } catch (DomainException $error) {
            guardarMensajeAdministrador("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al administrar ticket: " . $error->getMessage());
            guardarMensajeAdministrador(
                "error",
                "No fue posible actualizar el ticket en la base de datos."
            );
        } catch (Throwable $error) {
            error_log("GesTIck - error inesperado al administrar ticket: " . $error->getMessage());
            guardarMensajeAdministrador(
                "error",
                "Ocurrió un error inesperado al procesar el ticket."
            );
        }

        redirigirAdministrador("tickets");
    }

    $ordenSolicitado = $_GET["orden"] ?? "fecha";
    $ordenesPermitidos = ["fecha", "prioridad", "id"];

    $filtrosTickets = [
        "buscar" => mb_substr(trim((string) ($_GET["buscar"] ?? "")), 0, 100),
        "orden" => in_array($ordenSolicitado, $ordenesPermitidos, true)
            ? $ordenSolicitado
            : "fecha",
        "ubicacion" => max(0, (int) ($_GET["ubicacion"] ?? 0)),
        "docente" => max(0, (int) ($_GET["docente"] ?? 0)),
        "tecnico" => max(0, (int) ($_GET["tecnico"] ?? 0)),
        "tipo_equipo" => max(0, (int) ($_GET["tipo_equipo"] ?? 0)),
        "finalizado" => (int) ($_GET["finalizado"] ?? 0) === 1 ? 1 : 0,
        "pagina" => max(1, (int) ($_GET["p"] ?? 1))
    ];

    $ticketsAdministrador = [];
    $opcionesFiltrosTickets = [
        "ubicaciones" => [],
        "docentes" => [],
        "tecnicos" => [],
        "prioridades" => [],
        "estados" => [],
        "tipos_equipo" => []
    ];
    $totalTickets = 0;
    $totalPaginasTickets = 1;
    $paginaActualTickets = 1;
    $errorTickets = false;

    try {
        $resultadoTickets = $modeloTicketAdministrador->obtener($filtrosTickets, 10);
        $ticketsAdministrador = $resultadoTickets["tickets"];
        $totalTickets = $resultadoTickets["total"];
        $totalPaginasTickets = $resultadoTickets["total_paginas"];
        $paginaActualTickets = $resultadoTickets["pagina_actual"];
        $filtrosTickets["pagina"] = $paginaActualTickets;
        $opcionesFiltrosTickets = $modeloTicketAdministrador->obtenerOpcionesFiltros();
    } catch (Throwable $error) {
        $errorTickets = true;
        error_log("GesTIck - error al cargar tickets del administrador: " . $error->getMessage());
    }

    $mensajeAdministrador = consumirMensajeAdministrador();

    require __DIR__ . "/../../vistas/administrador/tickets.php";
    return;
}
