<?php

function escaparMetricasAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function porcentajeMetricasAdministrador(int $valor, int $total): int
{
    return $total > 0 ? (int) round(($valor / $total) * 100) : 0;
}

function duracionMetricasAdministrador(?int $segundos): string
{
    if ($segundos === null) {
        return "—";
    }

    $horas = intdiv($segundos, 3600);
    $minutos = intdiv($segundos % 3600, 60);

    if ($horas > 0) {
        return $horas . "h " . $minutos . "m";
    }

    return $minutos . " min";
}

function urlDescargaMetricasAdministrador(array $filtros): string
{
    $parametros = [
        "pagina" => "metricas",
        "tipo" => $filtros["tipo"],
        "descargar" => "csv"
    ];

    foreach (["desde", "hasta", "ubicacion"] as $filtro) {
        if (($filtros[$filtro] ?? "") !== "" && (string) $filtros[$filtro] !== "0") {
            $parametros[$filtro] = $filtros[$filtro];
        }
    }

    return BASE_URL . "/app/controladores/AdministradorController.php?" . http_build_query($parametros);
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$urlMetricasAdministrador = $urlAdministrador . "?pagina=metricas";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
$tipoMetrica = $filtrosMetricas["tipo"];
$mostrarTickets = in_array($tipoMetrica, ["general", "tickets"], true);
$mostrarSolicitudes = in_array($tipoMetrica, ["general", "solicitudes"], true);
$mostrarPlanillas = in_array($tipoMetrica, ["general", "planillas"], true);
$mostrarPrestamos = in_array($tipoMetrica, ["general", "prestamos"], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Métricas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-metricas.css?v=20260822-1">
</head>
<body>
    <button class="boton-menu-admin" id="botonMenuAdmin" type="button" aria-label="Abrir menú" aria-expanded="false">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>

    <div class="fondo-menu-admin" id="fondoMenuAdmin"></div>

    <aside class="menu-admin" id="menuAdmin">
        <div class="titulo-menu-admin">
            <img src="<?php echo BASE_URL; ?>/public/imagenes/logoG.png" alt="GesTIck">
        </div>

        <nav class="navegacion-admin" aria-label="Navegación del administrador">
            <a href="<?php echo $urlAdministrador; ?>?pagina=inicio"><span>Inicio</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=tickets"><span>Tickets</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=solicitudes"><span>Solicitudes</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=planillas"><span>Planillas</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=equipos"><span>Equipos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=prestamos"><span>Préstamos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=usuarios"><span>Usuarios</span></a>
            <a class="activo" href="<?php echo $urlMetricasAdministrador; ?>"><span>Métricas</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=mi_perfil"><span>Mi perfil</span></a>
        </nav>

        <a class="cerrar-sesion-admin" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina-admin">
        <header class="encabezado-admin">
            <div class="presentacion-admin">
                <h1>Métricas</h1>
                <p>Consulta indicadores del sistema y genera reportes con los filtros que necesites.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparMetricasAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparMetricasAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-metricas-admin">
            <?php if ($errorFiltrosMetricas !== ""): ?>
                <div class="mensaje-admin mensaje-error" role="alert">
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    <span><?php echo escaparMetricasAdministrador($errorFiltrosMetricas); ?> Se muestran las métricas sin límite de fechas.</span>
                </div>
            <?php endif; ?>

            <?php if ($errorMetricasAdministrador): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar las métricas. Comprueba que la conexión con la base de datos siga disponible.
                </div>
            <?php endif; ?>

            <section class="panel-filtros-metricas-admin" aria-labelledby="tituloFiltrosMetricas">
                <header>
                    <div>
                        <span class="sobrelinea-panel">Análisis del sistema</span>
                        <h2 id="tituloFiltrosMetricas">Reportes y métricas</h2>
                    </div>
                    <a class="boton-descargar-metricas" href="<?php echo escaparMetricasAdministrador(urlDescargaMetricasAdministrador($filtrosMetricas)); ?>">
                        <i class="bi bi-download" aria-hidden="true"></i>
                        Descargar reporte
                    </a>
                </header>

                <form class="filtros-metricas-admin" method="GET" action="<?php echo $urlAdministrador; ?>">
                    <input type="hidden" name="pagina" value="metricas">

                    <label>
                        <span>Desde</span>
                        <input type="date" name="desde" value="<?php echo escaparMetricasAdministrador($filtrosMetricas["desde"]); ?>">
                    </label>

                    <label>
                        <span>Hasta</span>
                        <input type="date" name="hasta" value="<?php echo escaparMetricasAdministrador($filtrosMetricas["hasta"]); ?>">
                    </label>

                    <label>
                        <span>Laboratorio o salón</span>
                        <select name="ubicacion">
                            <option value="0">Todos</option>
                            <?php foreach ($ubicacionesMetricasAdministrador as $ubicacion): ?>
                                <option
                                    value="<?php echo escaparMetricasAdministrador($ubicacion["id"]); ?>"
                                    <?php echo (int) $filtrosMetricas["ubicacion"] === (int) $ubicacion["id"] ? " selected" : ""; ?>>
                                    <?php echo escaparMetricasAdministrador($ubicacion["nombre"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Tipo de reporte</span>
                        <select name="tipo">
                            <option value="general"<?php echo $tipoMetrica === "general" ? " selected" : ""; ?>>General</option>
                            <option value="tickets"<?php echo $tipoMetrica === "tickets" ? " selected" : ""; ?>>Tickets</option>
                            <option value="solicitudes"<?php echo $tipoMetrica === "solicitudes" ? " selected" : ""; ?>>Solicitudes</option>
                            <option value="planillas"<?php echo $tipoMetrica === "planillas" ? " selected" : ""; ?>>Planillas</option>
                            <option value="prestamos"<?php echo $tipoMetrica === "prestamos" ? " selected" : ""; ?>>Préstamos</option>
                        </select>
                    </label>

                    <button class="boton-aplicar-metricas" type="submit">Aplicar</button>
                    <a class="boton-limpiar-metricas" href="<?php echo $urlMetricasAdministrador; ?>">Limpiar</a>
                </form>

                <?php if ((int) $filtrosMetricas["ubicacion"] > 0): ?>
                    <p class="nota-filtros-metricas">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        El laboratorio se aplica a tickets, planillas y préstamos. Las solicitudes no tienen una ubicación asociada.
                    </p>
                <?php endif; ?>
            </section>

            <section class="tarjetas-metricas-admin" aria-label="Indicadores principales">
                <?php if ($mostrarTickets): ?>
                    <article class="tarjeta-metrica-admin metrica-tickets">
                        <div class="cabecera-tarjeta-metrica"><span>Tickets resueltos</span><i class="bi bi-ticket-perforated" aria-hidden="true"></i></div>
                        <strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["tickets_resueltos"]); ?></strong>
                        <p>de <?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["tickets_total"]); ?> tickets</p>
                        <div class="progreso-metrica"><i style="width: <?php echo porcentajeMetricasAdministrador($resumenMetricasAdministrador["tickets_resueltos"], $resumenMetricasAdministrador["tickets_total"]); ?>%"></i></div>
                    </article>

                    <article class="tarjeta-metrica-admin metrica-tiempo">
                        <div class="cabecera-tarjeta-metrica"><span>Tiempo promedio de resolución</span><i class="bi bi-stopwatch" aria-hidden="true"></i></div>
                        <strong class="valor-tiempo-metrica"><?php echo escaparMetricasAdministrador(duracionMetricasAdministrador($resumenMetricasAdministrador["promedio_segundos"])); ?></strong>
                        <p><?php echo $resumenMetricasAdministrador["promedio_segundos"] === null ? "Aún no hay tickets resueltos" : "Desde el inicio de atención hasta el cierre"; ?></p>
                    </article>
                <?php endif; ?>

                <?php if ($mostrarSolicitudes): ?>
                    <article class="tarjeta-metrica-admin metrica-solicitudes">
                        <div class="cabecera-tarjeta-metrica"><span>Solicitudes completadas</span><i class="bi bi-check2-circle" aria-hidden="true"></i></div>
                        <strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["solicitudes_resueltas"]); ?></strong>
                        <p>de <?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["solicitudes_total"]); ?> solicitudes</p>
                        <div class="progreso-metrica"><i style="width: <?php echo porcentajeMetricasAdministrador($resumenMetricasAdministrador["solicitudes_resueltas"], $resumenMetricasAdministrador["solicitudes_total"]); ?>%"></i></div>
                    </article>
                <?php endif; ?>

                <?php if ($mostrarPlanillas): ?>
                    <article class="tarjeta-metrica-admin metrica-planillas">
                        <div class="cabecera-tarjeta-metrica"><span>Planillas registradas</span><i class="bi bi-clipboard-data" aria-hidden="true"></i></div>
                        <strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["planillas_total"]); ?></strong>
                        <p>en el período seleccionado</p>
                    </article>
                <?php endif; ?>

                <?php if ($mostrarPrestamos && $tipoMetrica === "prestamos"): ?>
                    <article class="tarjeta-metrica-admin metrica-prestamos">
                        <div class="cabecera-tarjeta-metrica"><span>Préstamos registrados</span><i class="bi bi-arrow-left-right" aria-hidden="true"></i></div>
                        <strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_total"]); ?></strong>
                        <p><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_activos"]); ?> activos actualmente</p>
                    </article>
                <?php endif; ?>
            </section>

            <section class="grid-detalle-metricas-admin">
                <?php if ($mostrarTickets): ?>
                    <article class="panel-equipos-fallas-admin">
                        <header>
                            <div><span class="sobrelinea-panel">Incidencias</span><h2>Equipos con más fallas</h2></div>
                            <span><?php echo count($equiposFallasMetricasAdministrador); ?> equipos</span>
                        </header>

                        <div class="tabla-fallas-contenedor">
                            <table class="tabla-fallas-metricas">
                                <thead><tr><th>#</th><th>Equipo</th><th>Tipo</th><th>Laboratorio</th><th>Incidencias</th></tr></thead>
                                <tbody>
                                    <?php foreach ($equiposFallasMetricasAdministrador as $indice => $equipo): ?>
                                        <tr>
                                            <td><?php echo $indice + 1; ?></td>
                                            <td><strong><?php echo escaparMetricasAdministrador($equipo["codigo"]); ?></strong></td>
                                            <td><?php echo escaparMetricasAdministrador($equipo["tipo"]); ?></td>
                                            <td><?php echo escaparMetricasAdministrador($equipo["ubicacion"]); ?></td>
                                            <td><span class="cantidad-incidencias-metrica"><?php echo escaparMetricasAdministrador($equipo["incidencias"]); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (!$equiposFallasMetricasAdministrador && !$errorMetricasAdministrador): ?>
                                <div class="sin-datos-metricas"><i class="bi bi-check2-circle" aria-hidden="true"></i><p>No hay incidencias en el período seleccionado.</p></div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ($mostrarPrestamos): ?>
                    <article class="panel-prestamos-metricas-admin">
                        <header><span class="sobrelinea-panel">Inventario prestable</span><h2>Préstamos de equipos</h2></header>

                        <div class="lista-prestamos-metricas">
                            <div><span>Préstamos activos</span><strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_activos"]); ?></strong></div>
                            <div><span>Total de préstamos</span><strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_total"]); ?></strong></div>
                        </div>

                        <button class="ver-detalle-metricas" id="abrirDetallePrestamos" type="button">Ver detalle</button>
                    </article>
                <?php endif; ?>
            </section>

            <div class="pie-acciones-metricas">
                <a class="boton-descargar-metricas boton-descargar-inferior" href="<?php echo escaparMetricasAdministrador(urlDescargaMetricasAdministrador($filtrosMetricas)); ?>">
                    <i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i>
                    Descargar reporte CSV
                </a>
            </div>
        </main>

        <footer class="footer-admin"><p>GesTIck · Sistema de gestión de recursos y soporte de informática</p></footer>
    </div>

    <?php if ($mostrarPrestamos): ?>
        <dialog class="dialog-admin dialog-detalle-metricas" id="dialogDetallePrestamos">
            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Estado de préstamos</span><h2>Detalle del período</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>
            <div class="cuerpo-dialog-admin resumen-dialog-prestamos">
                <article><span>Activos</span><strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_activos"]); ?></strong></article>
                <article><span>Atrasados</span><strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_atrasados"]); ?></strong></article>
                <article><span>Devueltos</span><strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_devueltos"]); ?></strong></article>
                <article><span>Total</span><strong><?php echo escaparMetricasAdministrador($resumenMetricasAdministrador["prestamos_total"]); ?></strong></article>
            </div>
            <footer class="acciones-dialog-admin"><button type="button" class="boton-admin boton-admin-principal" data-cerrar-dialog>Cerrar</button></footer>
        </dialog>
    <?php endif; ?>

    <script>
        const botonMenuAdmin = document.getElementById("botonMenuAdmin");
        const menuAdmin = document.getElementById("menuAdmin");
        const fondoMenuAdmin = document.getElementById("fondoMenuAdmin");

        function cambiarMenuAdmin(abierto) {
            menuAdmin.classList.toggle("abierto", abierto);
            fondoMenuAdmin.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-admin-abierto", abierto);
            botonMenuAdmin.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        botonMenuAdmin.addEventListener("click", function () {
            cambiarMenuAdmin(!menuAdmin.classList.contains("abierto"));
        });

        fondoMenuAdmin.addEventListener("click", function () {
            cambiarMenuAdmin(false);
        });

        const abrirDetallePrestamos = document.getElementById("abrirDetallePrestamos");
        const dialogDetallePrestamos = document.getElementById("dialogDetallePrestamos");

        if (abrirDetallePrestamos && dialogDetallePrestamos) {
            abrirDetallePrestamos.addEventListener("click", function () {
                dialogDetallePrestamos.showModal();
            });
        }

        document.querySelectorAll("[data-cerrar-dialog]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                boton.closest("dialog").close();
            });
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuAdmin(false);
            }
        });
    </script>
</body>
</html>
