<?php

function normalizarFechaMetricasAdministrador(string $fecha): string
{
    $fecha = trim($fecha);

    if ($fecha === "") {
        return "";
    }

    $valor = DateTimeImmutable::createFromFormat("!Y-m-d", $fecha);

    if (!$valor || $valor->format("Y-m-d") !== $fecha) {
        throw new DomainException("Selecciona fechas válidas para generar las métricas.");
    }

    return $fecha;
}

function textoDuracionMetricasAdministrador(?int $segundos): string
{
    if ($segundos === null) {
        return "Sin datos";
    }

    $horas = intdiv($segundos, 3600);
    $minutos = intdiv($segundos % 3600, 60);

    if ($horas > 0) {
        return $horas . " h " . $minutos . " min";
    }

    return $minutos . " min";
}

function descargarCsvMetricasAdministrador(
    array $resumen,
    array $equipos,
    array $filtros,
    array $ubicaciones
): never {
    $nombreUbicacion = "Todos los laboratorios";

    foreach ($ubicaciones as $ubicacion) {
        if ((int) $ubicacion["id"] === (int) $filtros["ubicacion"]) {
            $nombreUbicacion = $ubicacion["nombre"];
            break;
        }
    }

    $archivo = "metricas-gestick-" . date("Y-m-d") . ".csv";
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"{$archivo}\"");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    $salida = fopen("php://output", "wb");
    fwrite($salida, "\xEF\xBB\xBF");
    fputcsv($salida, ["GesTIck - Reporte de métricas"], ";");
    fputcsv($salida, ["Desde", $filtros["desde"] ?: "Sin límite"], ";");
    fputcsv($salida, ["Hasta", $filtros["hasta"] ?: "Sin límite"], ";");
    fputcsv($salida, ["Laboratorio", $nombreUbicacion], ";");
    fputcsv($salida, [], ";");

    $tipo = $filtros["tipo"];

    if (in_array($tipo, ["general", "tickets"], true)) {
        fputcsv($salida, ["Tickets"], ";");
        fputcsv($salida, ["Total", $resumen["tickets_total"]], ";");
        fputcsv($salida, ["Resueltos", $resumen["tickets_resueltos"]], ";");
        fputcsv(
            $salida,
            ["Tiempo promedio de resolución", textoDuracionMetricasAdministrador($resumen["promedio_segundos"])],
            ";"
        );
        fputcsv($salida, [], ";");
        fputcsv($salida, ["Equipo", "Tipo", "Laboratorio", "Incidencias"], ";");

        foreach ($equipos as $equipo) {
            fputcsv(
                $salida,
                [$equipo["codigo"], $equipo["tipo"], $equipo["ubicacion"], $equipo["incidencias"]],
                ";"
            );
        }

        fputcsv($salida, [], ";");
    }

    if (in_array($tipo, ["general", "solicitudes"], true)) {
        fputcsv($salida, ["Solicitudes"], ";");
        fputcsv($salida, ["Total", $resumen["solicitudes_total"]], ";");
        fputcsv($salida, ["Completadas", $resumen["solicitudes_resueltas"]], ";");
        fputcsv($salida, [], ";");
    }

    if (in_array($tipo, ["general", "planillas"], true)) {
        fputcsv($salida, ["Planillas"], ";");
        fputcsv($salida, ["Registradas", $resumen["planillas_total"]], ";");
        fputcsv($salida, [], ";");
    }

    if (in_array($tipo, ["general", "prestamos"], true)) {
        fputcsv($salida, ["Préstamos"], ";");
        fputcsv($salida, ["Total", $resumen["prestamos_total"]], ";");
        fputcsv($salida, ["Activos", $resumen["prestamos_activos"]], ";");
        fputcsv($salida, ["Atrasados", $resumen["prestamos_atrasados"]], ";");
        fputcsv($salida, ["Devueltos", $resumen["prestamos_devueltos"]], ";");
    }

    fclose($salida);
    exit;
}

if ($pagina === "metricas") {
    require_once __DIR__ . "/../../modelos/MetricaAdministrador.php";
    $modeloMetricaAdministrador = new MetricaAdministrador();

    $tiposMetricasPermitidos = ["general", "tickets", "solicitudes", "planillas", "prestamos"];
    $tipoMetricaSolicitado = strtolower(trim((string) ($_GET["tipo"] ?? "general")));
    $filtrosMetricas = [
        "desde" => "",
        "hasta" => "",
        "ubicacion" => max(0, (int) ($_GET["ubicacion"] ?? 0)),
        "tipo" => in_array($tipoMetricaSolicitado, $tiposMetricasPermitidos, true)
            ? $tipoMetricaSolicitado
            : "general"
    ];
    $errorFiltrosMetricas = "";

    try {
        $filtrosMetricas["desde"] = normalizarFechaMetricasAdministrador(
            (string) ($_GET["desde"] ?? "")
        );
        $filtrosMetricas["hasta"] = normalizarFechaMetricasAdministrador(
            (string) ($_GET["hasta"] ?? "")
        );

        if (
            $filtrosMetricas["desde"] !== "" &&
            $filtrosMetricas["hasta"] !== "" &&
            $filtrosMetricas["desde"] > $filtrosMetricas["hasta"]
        ) {
            throw new DomainException(
                "La fecha inicial no puede ser posterior a la fecha final."
            );
        }
    } catch (DomainException $error) {
        $errorFiltrosMetricas = $error->getMessage();
        $filtrosMetricas["desde"] = "";
        $filtrosMetricas["hasta"] = "";
    }

    $resumenMetricasAdministrador = [
        "tickets_total" => 0,
        "tickets_resueltos" => 0,
        "promedio_segundos" => null,
        "solicitudes_total" => 0,
        "solicitudes_resueltas" => 0,
        "planillas_total" => 0,
        "prestamos_total" => 0,
        "prestamos_activos" => 0,
        "prestamos_devueltos" => 0,
        "prestamos_atrasados" => 0
    ];
    $equiposFallasMetricasAdministrador = [];
    $ubicacionesMetricasAdministrador = [];
    $errorMetricasAdministrador = false;

    try {
        $ubicacionesMetricasAdministrador = $modeloMetricaAdministrador->obtenerUbicaciones();
        $resumenMetricasAdministrador = array_replace(
            $resumenMetricasAdministrador,
            $modeloMetricaAdministrador->obtenerResumen($filtrosMetricas)
        );
        $equiposFallasMetricasAdministrador = $modeloMetricaAdministrador->obtenerEquiposConMasFallas(
            $filtrosMetricas,
            5
        );
    } catch (Throwable $error) {
        $errorMetricasAdministrador = true;
        error_log("GesTIck - error al cargar métricas: " . $error->getMessage());
    }

    if (
        !$errorMetricasAdministrador &&
        (string) ($_GET["descargar"] ?? "") === "csv"
    ) {
        descargarCsvMetricasAdministrador(
            $resumenMetricasAdministrador,
            $equiposFallasMetricasAdministrador,
            $filtrosMetricas,
            $ubicacionesMetricasAdministrador
        );
    }

    require __DIR__ . "/../../vistas/administrador/metricas.php";
    return;
}


