<?php

if ($pagina === "solicitudes") {
    require_once __DIR__ . "/../../modelos/SolicitudAdministrador.php";
    $modeloSolicitudesAdministrador = new SolicitudAdministrador();

    $ordenSolicitado = $_GET["orden"] ?? "fecha";
    $ordenesPermitidos = ["fecha", "estado", "id"];
    $filtrosSolicitudes = [
        "buscar" => mb_substr(trim((string) ($_GET["buscar"] ?? "")), 0, 100),
        "orden" => in_array($ordenSolicitado, $ordenesPermitidos, true)
            ? $ordenSolicitado
            : "fecha",
        "docente" => max(0, (int) ($_GET["docente"] ?? 0)),
        "pagina" => max(1, (int) ($_GET["p"] ?? 1))
    ];

    $solicitudesAdministrador = [];
    $docentesSolicitudes = [];
    $totalSolicitudes = 0;
    $totalPaginasSolicitudes = 1;
    $paginaActualSolicitudes = 1;
    $errorSolicitudes = false;

    try {
        $resultadoSolicitudes = $modeloSolicitudesAdministrador->obtener($filtrosSolicitudes, 10);
        $solicitudesAdministrador = $resultadoSolicitudes["solicitudes"];
        $totalSolicitudes = $resultadoSolicitudes["total"];
        $totalPaginasSolicitudes = $resultadoSolicitudes["total_paginas"];
        $paginaActualSolicitudes = $resultadoSolicitudes["pagina_actual"];
        $filtrosSolicitudes["pagina"] = $paginaActualSolicitudes;
        $docentesSolicitudes = $modeloSolicitudesAdministrador->obtenerDocentes();
    } catch (Throwable $error) {
        $errorSolicitudes = true;
        error_log("GesTIck - error al cargar solicitudes del administrador: " . $error->getMessage());
    }

    require __DIR__ . "/../../vistas/administrador/solicitudes.php";
    return;
}
