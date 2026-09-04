<?php

/* Prepara los resúmenes del panel inicial y captura fallos sin romper la vista. */
require_once __DIR__ . "/../../modelos/Administrador.php";

$modeloAdministrador = new Administrador();
$ticketsRecientes = [];
$solicitudesRecientes = [];
$planillasRecientes = [];
$avisos = [];
$errorResumen = false;

try {
    $ticketsRecientes = $modeloAdministrador->obtenerUltimosTickets(2);
    $solicitudesRecientes = $modeloAdministrador->obtenerUltimasSolicitudes(2);
    $planillasRecientes = $modeloAdministrador->obtenerPlanillasRecientes(5);
    $avisos = $modeloAdministrador->obtenerAvisos();
} catch (Throwable $error) {
    $errorResumen = true;
    error_log("GesTIck - error al cargar el inicio del administrador: " . $error->getMessage());
}

require __DIR__ . "/../../vistas/administrador/inicio.php";
