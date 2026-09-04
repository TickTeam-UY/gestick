<?php

/* Lee los filtros y prepara la bandeja de planillas para la vista. */
if ($pagina === "planillas") {
    require_once __DIR__ . "/../../modelos/PlanillaAdministrador.php";
    $modeloPlanillaAdministrador = new PlanillaAdministrador();

    $ordenSolicitado = (string) ($_GET["orden"] ?? "fecha");
    $ordenesPermitidos = ["fecha", "antiguas", "id", "docente"];
    $filtrosPlanillas = [
        "buscar" => mb_substr(trim((string) ($_GET["buscar"] ?? "")), 0, 100),
        "orden" => in_array($ordenSolicitado, $ordenesPermitidos, true)
            ? $ordenSolicitado
            : "fecha",
        "docente" => max(0, (int) ($_GET["docente"] ?? 0)),
        "ubicacion" => max(0, (int) ($_GET["ubicacion"] ?? 0)),
        "turno" => max(0, (int) ($_GET["turno"] ?? 0)),
        "asignatura" => max(0, (int) ($_GET["asignatura"] ?? 0)),
        "pagina" => max(1, (int) ($_GET["p"] ?? 1))
    ];
    $planillasAdministrador = [];
    $opcionesFiltrosPlanillas = [
        "docentes" => [],
        "ubicaciones" => [],
        "turnos" => [],
        "asignaturas" => []
    ];
    $totalPlanillasAdministrador = 0;
    $totalPaginasPlanillasAdministrador = 1;
    $paginaActualPlanillasAdministrador = 1;
    $errorPlanillasAdministrador = false;

    try {
        $resultadoPlanillas = $modeloPlanillaAdministrador->obtener($filtrosPlanillas, 10);
        $planillasAdministrador = $resultadoPlanillas["planillas"];
        $totalPlanillasAdministrador = $resultadoPlanillas["total"];
        $totalPaginasPlanillasAdministrador = $resultadoPlanillas["total_paginas"];
        $paginaActualPlanillasAdministrador = $resultadoPlanillas["pagina_actual"];
        $filtrosPlanillas["pagina"] = $paginaActualPlanillasAdministrador;
        $opcionesFiltrosPlanillas = $modeloPlanillaAdministrador->obtenerOpcionesFiltros();
    } catch (Throwable $error) {
        $errorPlanillasAdministrador = true;
        error_log("GesTIck - error al cargar planillas: " . $error->getMessage());
    }

    require __DIR__ . "/../../vistas/administrador/planillas.php";
    return;
}
