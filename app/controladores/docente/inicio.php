<?php

/* Carga el resumen y las solicitudes recientes del docente autenticado. */
require_once __DIR__ . "/../../modelos/InicioDocente.php";

$modeloInicioDocente = new InicioDocente();
$resumenDocente = $modeloInicioDocente->resumenVacio();
$solicitudesRecientesDocente = [];
$avisosDocente = [];
$errorInicioDocente = false;

try {
    if ($idDocente < 1) {
        throw new DomainException("No fue posible identificar la cuenta del docente.");
    }

    $resumenDocente = $modeloInicioDocente->obtenerResumen($idDocente);
    $solicitudesRecientesDocente = $modeloInicioDocente->obtenerSolicitudesRecientes($idDocente, 8);

    if ($resumenDocente["pendientes"] > 0) {
        $avisosDocente[] = [
            "tipo" => "pendiente",
            "titulo" => "Solicitudes pendientes",
            "texto" => $resumenDocente["pendientes"] === 1
                ? "Tienes una solicitud esperando revisión."
                : "Tienes {$resumenDocente["pendientes"]} solicitudes esperando revisión."
        ];
    }

    if ($resumenDocente["en_proceso"] > 0) {
        $avisosDocente[] = [
            "tipo" => "proceso",
            "titulo" => "Trabajo en curso",
            "texto" => $resumenDocente["en_proceso"] === 1
                ? "Una de tus solicitudes está siendo atendida."
                : "{$resumenDocente["en_proceso"]} de tus solicitudes están siendo atendidas."
        ];
    }

    if ($resumenDocente["completadas"] > 0) {
        $avisosDocente[] = [
            "tipo" => "completada",
            "titulo" => "Solicitudes completadas",
            "texto" => $resumenDocente["completadas"] === 1
                ? "Ya puedes revisar una solicitud completada."
                : "Ya puedes revisar {$resumenDocente["completadas"]} solicitudes completadas."
        ];
    }
} catch (Throwable $error) {
    $errorInicioDocente = true;
    error_log("GesTIck - error al cargar el inicio del docente: " . $error->getMessage());
}

require __DIR__ . "/../../vistas/docente/inicio.php";
