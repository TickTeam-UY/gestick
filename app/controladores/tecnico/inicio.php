<?php

if ($pagina == "inicio") {
    require_once __DIR__ . "/../../modelos/Ticket.php";
    require_once __DIR__ . "/../../modelos/Solicitud.php";

    $modeloTicket = new Ticket();
    $modeloSolicitud = new Solicitud();
    $idTecnico = (int) ($_SESSION["usuario_id"] ?? 0);
    $ticketsRecientes = $modeloTicket->obtenerRecientes($idTecnico, 3);
    $solicitudesRecientes = $modeloSolicitud->obtenerParaTecnico($idTecnico, 3);
}

require __DIR__ . "/../../vistas/tecnico/" . $pagina . ".php";
