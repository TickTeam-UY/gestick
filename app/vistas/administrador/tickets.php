<?php

function escaparTicketsAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function fechaTicketsAdministrador(?string $fecha, bool $incluirHora = false): string
{
    if (!$fecha) {
        return "Sin registrar";
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false
        ? "Sin registrar"
        : date($incluirHora ? "d/m/Y H:i" : "d/m/Y", $marcaTiempo);
}

function claseTicketAdministrador(string $valor): string
{
    $normalizado = strtolower(str_replace(
        ["á", "é", "í", "ó", "ú", "ñ", " "],
        ["a", "e", "i", "o", "u", "n", "-"],
        $valor
    ));

    return preg_replace("/[^a-z0-9-]/", "", $normalizado) ?: "neutra";
}

function urlTicketsAdministrador(array $cambios = []): string
{
    global $urlAdministrador, $filtrosTickets;

    $parametros = ["pagina" => "tickets"];
    $valores = [
        "buscar" => $filtrosTickets["buscar"] ?? "",
        "orden" => $filtrosTickets["orden"] ?? "fecha",
        "ubicacion" => (int) ($filtrosTickets["ubicacion"] ?? 0),
        "docente" => (int) ($filtrosTickets["docente"] ?? 0),
        "tecnico" => (int) ($filtrosTickets["tecnico"] ?? 0),
        "tipo_equipo" => (int) ($filtrosTickets["tipo_equipo"] ?? 0),
        "finalizado" => (int) ($filtrosTickets["finalizado"] ?? 0),
        "p" => (int) ($filtrosTickets["pagina"] ?? 1)
    ];

    foreach ($cambios as $clave => $valor) {
        $valores[$clave] = $valor;
    }

    foreach ($valores as $clave => $valor) {
        if ($valor !== "" && $valor !== 0 && !($clave === "orden" && $valor === "fecha") && !($clave === "p" && $valor === 1)) {
            $parametros[$clave] = $valor;
        }
    }

    return $urlAdministrador . "?" . http_build_query($parametros);
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
$cantidadFiltrosActivos = count(array_filter([
    $filtrosTickets["ubicacion"] ?? 0,
    $filtrosTickets["docente"] ?? 0,
    $filtrosTickets["tecnico"] ?? 0,
    $filtrosTickets["tipo_equipo"] ?? 0,
    $filtrosTickets["finalizado"] ?? 0
]));
$hayCriteriosActivos = $cantidadFiltrosActivos > 0 || ($filtrosTickets["buscar"] ?? "") !== "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Tickets del administrador</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-tickets.css?v=20260822-4">
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
            <a class="activo" href="<?php echo $urlAdministrador; ?>?pagina=tickets" aria-current="page"><span>Tickets</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=solicitudes"><span>Solicitudes</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=planillas"><span>Planillas</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=equipos"><span>Equipos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=prestamos"><span>Préstamos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=usuarios"><span>Usuarios</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=metricas"><span>Métricas</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=mi_perfil"><span>Mi perfil</span></a>
        </nav>

        <a class="cerrar-sesion-admin" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina-admin">
        <header class="encabezado-admin encabezado-tickets-admin">
            <div class="presentacion-admin">
                <h1>Tickets</h1>
                <p>Asigna, reasigna y supervisa las incidencias registradas por los docentes.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparTicketsAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparTicketsAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-tickets-admin">
            <?php if ($mensajeAdministrador): ?>
                <div class="mensaje-admin mensaje-<?php echo escaparTicketsAdministrador($mensajeAdministrador["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeAdministrador["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparTicketsAdministrador($mensajeAdministrador["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorTickets): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar los tickets. Comprueba que el servidor local continúe disponible.
                </div>
            <?php endif; ?>

            <section class="panel-tickets-admin" aria-labelledby="tituloListadoTickets">
                <form class="barra-listado-tickets-admin" method="get" action="<?php echo escaparTicketsAdministrador($urlAdministrador); ?>">
                    <input type="hidden" name="pagina" value="tickets">
                    <input type="hidden" name="orden" value="<?php echo escaparTicketsAdministrador($filtrosTickets["orden"] ?? "fecha"); ?>">
                    <input type="hidden" name="ubicacion" value="<?php echo (int) ($filtrosTickets["ubicacion"] ?? 0); ?>">
                    <input type="hidden" name="docente" value="<?php echo (int) ($filtrosTickets["docente"] ?? 0); ?>">
                    <input type="hidden" name="tecnico" value="<?php echo (int) ($filtrosTickets["tecnico"] ?? 0); ?>">
                    <input type="hidden" name="tipo_equipo" value="<?php echo (int) ($filtrosTickets["tipo_equipo"] ?? 0); ?>">
                    <input type="hidden" name="finalizado" value="<?php echo (int) ($filtrosTickets["finalizado"] ?? 0); ?>">

                    <div class="controles-listado-tickets-admin">
                        <details class="menu-desplegable-tickets-admin menu-orden-tickets-admin">
                            <summary>
                                Ordenar por
                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </summary>
                            <div class="opciones-menu-tickets-admin opciones-orden-tickets-admin">
                                <a class="<?php echo ($filtrosTickets["orden"] ?? "") === "prioridad" ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["orden" => "prioridad", "p" => 1])); ?>">
                                    Prioridad
                                </a>
                                <a class="<?php echo ($filtrosTickets["orden"] ?? "") === "fecha" ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["orden" => "fecha", "p" => 1])); ?>">
                                    Fecha
                                </a>
                                <a class="<?php echo ($filtrosTickets["orden"] ?? "") === "id" ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["orden" => "id", "p" => 1])); ?>">
                                    ID
                                </a>
                            </div>
                        </details>

                        <details class="menu-desplegable-tickets-admin filtros-tickets-admin" <?php echo $cantidadFiltrosActivos > 0 ? "data-con-filtros" : ""; ?>>
                            <summary>
                                Filtrar por
                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </summary>
                            <div class="opciones-menu-tickets-admin opciones-filtros-tickets-admin">
                                <details class="opcion-filtro-tickets-admin" <?php echo (int) $filtrosTickets["ubicacion"] > 0 ? "data-activo" : ""; ?>>
                                    <summary>Laboratorio <i class="bi bi-chevron-right" aria-hidden="true"></i></summary>
                                    <div class="submenu-filtro-tickets-admin">
                                        <a class="<?php echo (int) $filtrosTickets["ubicacion"] === 0 ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["ubicacion" => 0, "p" => 1])); ?>">Todos</a>
                                        <?php foreach ($opcionesFiltrosTickets["ubicaciones"] as $opcion): ?>
                                            <a class="<?php echo (int) $filtrosTickets["ubicacion"] === (int) $opcion["id"] ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["ubicacion" => (int) $opcion["id"], "p" => 1])); ?>">
                                                <?php echo escaparTicketsAdministrador($opcion["nombre"]); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>

                                <details class="opcion-filtro-tickets-admin" <?php echo (int) $filtrosTickets["docente"] > 0 ? "data-activo" : ""; ?>>
                                    <summary>Docente <i class="bi bi-chevron-right" aria-hidden="true"></i></summary>
                                    <div class="submenu-filtro-tickets-admin">
                                        <a class="<?php echo (int) $filtrosTickets["docente"] === 0 ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["docente" => 0, "p" => 1])); ?>">Todos</a>
                                        <?php foreach ($opcionesFiltrosTickets["docentes"] as $opcion): ?>
                                            <a class="<?php echo (int) $filtrosTickets["docente"] === (int) $opcion["id"] ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["docente" => (int) $opcion["id"], "p" => 1])); ?>">
                                                <?php echo escaparTicketsAdministrador($opcion["nombre"]); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>

                                <details class="opcion-filtro-tickets-admin" <?php echo (int) $filtrosTickets["tecnico"] > 0 ? "data-activo" : ""; ?>>
                                    <summary>Técnico asignado <i class="bi bi-chevron-right" aria-hidden="true"></i></summary>
                                    <div class="submenu-filtro-tickets-admin">
                                        <a class="<?php echo (int) $filtrosTickets["tecnico"] === 0 ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["tecnico" => 0, "p" => 1])); ?>">Todos</a>
                                        <?php foreach ($opcionesFiltrosTickets["tecnicos"] as $opcion): ?>
                                            <a class="<?php echo (int) $filtrosTickets["tecnico"] === (int) $opcion["id"] ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["tecnico" => (int) $opcion["id"], "p" => 1])); ?>">
                                                <?php echo escaparTicketsAdministrador($opcion["nombre"]); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>

                                <details class="opcion-filtro-tickets-admin" <?php echo (int) $filtrosTickets["tipo_equipo"] > 0 ? "data-activo" : ""; ?>>
                                    <summary>Periférico <i class="bi bi-chevron-right" aria-hidden="true"></i></summary>
                                    <div class="submenu-filtro-tickets-admin">
                                        <a class="<?php echo (int) $filtrosTickets["tipo_equipo"] === 0 ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["tipo_equipo" => 0, "p" => 1])); ?>">Todos</a>
                                        <?php foreach ($opcionesFiltrosTickets["tipos_equipo"] as $opcion): ?>
                                            <a class="<?php echo (int) $filtrosTickets["tipo_equipo"] === (int) $opcion["id"] ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["tipo_equipo" => (int) $opcion["id"], "p" => 1])); ?>">
                                                <?php echo escaparTicketsAdministrador($opcion["nombre"]); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>

                                <a class="opcion-finalizado-tickets-admin <?php echo (int) $filtrosTickets["finalizado"] === 1 ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["finalizado" => (int) $filtrosTickets["finalizado"] === 1 ? 0 : 1, "p" => 1])); ?>">
                                    Finalizado
                                    <?php if ((int) $filtrosTickets["finalizado"] === 1): ?><i class="bi bi-check" aria-hidden="true"></i><?php endif; ?>
                                </a>

                                <?php if ($cantidadFiltrosActivos > 0): ?>
                                    <a class="limpiar-filtros-menu-admin" href="<?php echo $urlAdministrador; ?>?pagina=tickets">Limpiar filtros</a>
                                <?php endif; ?>
                            </div>
                        </details>
                    </div>

                    <h2 id="tituloListadoTickets">Todos los tickets</h2>

                    <label class="buscador-tickets-admin">
                        <span class="solo-lector">Buscar ticket</span>
                        <input type="search" name="buscar" value="<?php echo escaparTicketsAdministrador($filtrosTickets["buscar"] ?? ""); ?>" placeholder="Buscar ticket..." maxlength="100">
                        <button type="submit" aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
                    </label>
                </form>

                <div class="resumen-listado-tickets-admin">
                    <p>
                        <?php echo $totalTickets === 1 ? "1 ticket encontrado" : escaparTicketsAdministrador($totalTickets) . " tickets encontrados"; ?>
                        <?php if ($hayCriteriosActivos): ?> con los criterios seleccionados<?php endif; ?>.
                    </p>
                    <?php if ($hayCriteriosActivos): ?>
                        <a href="<?php echo $urlAdministrador; ?>?pagina=tickets">Quitar búsqueda y filtros</a>
                    <?php endif; ?>
                </div>

                <?php if (!$errorTickets && !$ticketsAdministrador): ?>
                    <div class="vacio-tickets-admin">
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                        <h3>No encontramos tickets</h3>
                        <p>Prueba quitando la búsqueda o alguno de los filtros seleccionados.</p>
                        <?php if ($hayCriteriosActivos): ?>
                            <a class="boton-admin boton-admin-secundario" href="<?php echo $urlAdministrador; ?>?pagina=tickets">Ver todos</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="grid-tickets-admin">
                    <?php foreach ($ticketsAdministrador as $ticket): ?>
                        <?php
                        $detalleTicket = [
                            "id" => (int) $ticket["id_ticket"],
                            "id_tecnico" => $ticket["id_tecnico"] === null ? null : (int) $ticket["id_tecnico"],
                            "id_prioridad" => (int) $ticket["id_prioridad"],
                            "id_estado" => (int) $ticket["id_estado_ticket"],
                            "equipo" => $ticket["equipo"],
                            "modelo" => $ticket["modelo"] ?: "Sin registrar",
                            "tipo" => $ticket["tipo_equipo"],
                            "ubicacion" => $ticket["ubicacion"],
                            "prioridad" => $ticket["prioridad"],
                            "estado" => $ticket["estado"],
                            "tecnico" => trim($ticket["tecnico"] ?? "") ?: "Sin asignar",
                            "docente" => trim($ticket["docente"] ?? "") ?: "No indicado",
                            "planilla" => $ticket["id_planilla"] ? "#" . $ticket["id_planilla"] : "Sin planilla asociada",
                            "generado" => fechaTicketsAdministrador($ticket["fecha_generado"], true),
                            "inicio" => fechaTicketsAdministrador($ticket["fecha_inicio"], true),
                            "fin" => fechaTicketsAdministrador($ticket["fecha_fin"], true),
                            "descripcion" => $ticket["descripcion"],
                            "solucion" => trim($ticket["solucion"] ?? "") ?: "Todavía no se registró una solución."
                        ];
                        $jsonTicket = json_encode($detalleTicket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        ?>
                        <article class="tarjeta-ticket-admin" id="ticket-<?php echo (int) $ticket["id_ticket"]; ?>">
                            <div class="meta-ticket-admin">
                                <span class="etiqueta-admin prioridad-<?php echo claseTicketAdministrador($ticket["prioridad"]); ?>">
                                    <?php echo escaparTicketsAdministrador($ticket["prioridad"]); ?>
                                </span>
                                <span class="etiqueta-admin estado-<?php echo claseTicketAdministrador($ticket["estado"]); ?>">
                                    <?php echo escaparTicketsAdministrador($ticket["estado"]); ?>
                                </span>
                                <time datetime="<?php echo escaparTicketsAdministrador($ticket["fecha_generado"]); ?>">
                                    <?php echo fechaTicketsAdministrador($ticket["fecha_generado"]); ?>
                                </time>
                            </div>
                            <h3>Ticket #<?php echo (int) $ticket["id_ticket"]; ?> · <?php echo escaparTicketsAdministrador($ticket["equipo"]); ?></h3>
                            <p class="ubicacion-ticket-admin"><?php echo escaparTicketsAdministrador($ticket["ubicacion"]); ?> · <?php echo escaparTicketsAdministrador($ticket["tipo_equipo"]); ?></p>
                            <p class="descripcion-ticket-admin"><?php echo escaparTicketsAdministrador($ticket["descripcion"]); ?></p>
                            <div class="pie-tarjeta-ticket-admin">
                                <span>
                                    <strong>Técnico:</strong>
                                    <?php echo escaparTicketsAdministrador(trim($ticket["tecnico"] ?? "") ?: "Sin asignar"); ?>
                                </span>
                                <div class="acciones-tarjeta-ticket-admin">
                                    <button type="button" class="ver-ticket-admin" data-ticket="<?php echo escaparTicketsAdministrador($jsonTicket ?: "{}"); ?>">
                                        Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="administrar-ticket-admin" data-administrar-ticket="<?php echo escaparTicketsAdministrador($jsonTicket ?: "{}"); ?>">
                                        <i class="bi bi-person-gear" aria-hidden="true"></i> Administrar
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if (!$errorTickets && $totalPaginasTickets > 1): ?>
                    <nav class="paginacion-tickets-admin" aria-label="Paginación de tickets">
                        <?php if ($paginaActualTickets > 1): ?>
                            <a class="paso-paginacion-ticket" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["p" => $paginaActualTickets - 1])); ?>">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior
                            </a>
                        <?php else: ?>
                            <span class="paso-paginacion-ticket deshabilitado"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</span>
                        <?php endif; ?>

                        <div class="numeros-paginacion-ticket">
                            <?php
                            $inicioPaginas = max(1, $paginaActualTickets - 2);
                            $finPaginas = min($totalPaginasTickets, $paginaActualTickets + 2);
                            ?>
                            <?php if ($inicioPaginas > 1): ?>
                                <a href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["p" => 1])); ?>">1</a>
                                <?php if ($inicioPaginas > 2): ?><span>…</span><?php endif; ?>
                            <?php endif; ?>
                            <?php for ($numeroPagina = $inicioPaginas; $numeroPagina <= $finPaginas; $numeroPagina++): ?>
                                <a class="<?php echo $numeroPagina === $paginaActualTickets ? "activo" : ""; ?>" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["p" => $numeroPagina])); ?>" <?php echo $numeroPagina === $paginaActualTickets ? 'aria-current="page"' : ""; ?>>
                                    <?php echo $numeroPagina; ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ($finPaginas < $totalPaginasTickets): ?>
                                <?php if ($finPaginas < $totalPaginasTickets - 1): ?><span>…</span><?php endif; ?>
                                <a href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["p" => $totalPaginasTickets])); ?>"><?php echo $totalPaginasTickets; ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if ($paginaActualTickets < $totalPaginasTickets): ?>
                            <a class="paso-paginacion-ticket" href="<?php echo escaparTicketsAdministrador(urlTicketsAdministrador(["p" => $paginaActualTickets + 1])); ?>">
                                Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        <?php else: ?>
                            <span class="paso-paginacion-ticket deshabilitado">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>

            <dialog class="dialog-admin dialog-ticket-admin" id="dialogDetalleTicket">
                <div class="cabecera-dialog-admin">
                    <div>
                        <span class="sobrelinea-panel">Detalle de la incidencia</span>
                        <h2 id="tituloDetalleTicket">Ticket</h2>
                    </div>
                    <button class="cerrar-dialog-admin" type="button" data-cerrar-ticket aria-label="Cerrar">×</button>
                </div>

                <div class="cuerpo-dialog-admin cuerpo-detalle-ticket-admin">
                    <div class="etiquetas-detalle-ticket-admin">
                        <span class="etiqueta-admin" id="detallePrioridadTicket"></span>
                        <span class="etiqueta-admin" id="detalleEstadoTicket"></span>
                    </div>

                    <dl class="datos-detalle-ticket-admin">
                        <div><dt>Equipo</dt><dd id="detalleEquipoTicket"></dd></div>
                        <div><dt>Modelo</dt><dd id="detalleModeloTicket"></dd></div>
                        <div><dt>Tipo</dt><dd id="detalleTipoTicket"></dd></div>
                        <div><dt>Laboratorio o salón</dt><dd id="detalleUbicacionTicket"></dd></div>
                        <div><dt>Técnico asignado</dt><dd id="detalleTecnicoTicket"></dd></div>
                        <div><dt>Docente</dt><dd id="detalleDocenteTicket"></dd></div>
                        <div><dt>Planilla de origen</dt><dd id="detallePlanillaTicket"></dd></div>
                        <div><dt>Generado</dt><dd id="detalleGeneradoTicket"></dd></div>
                        <div><dt>Inicio</dt><dd id="detalleInicioTicket"></dd></div>
                        <div><dt>Finalización</dt><dd id="detalleFinTicket"></dd></div>
                    </dl>

                    <section class="texto-detalle-ticket-admin">
                        <h3>Descripción</h3>
                        <p id="detalleDescripcionTicket"></p>
                    </section>
                    <section class="texto-detalle-ticket-admin">
                        <h3>Solución</h3>
                        <p id="detalleSolucionTicket"></p>
                    </section>
                </div>

                <div class="acciones-dialog-admin">
                    <button class="boton-admin boton-admin-secundario" type="button" data-cerrar-ticket>Cerrar ventana</button>
                </div>
            </dialog>

            <dialog class="dialog-admin dialog-gestion-ticket-admin" id="dialogGestionTicket">
                <form method="POST" action="<?php echo $urlAdministrador; ?>?pagina=tickets" class="formulario-dialog-admin" id="formGestionTicket">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparTicketsAdministrador($csrfToken); ?>">
                    <input type="hidden" name="accion" value="administrar_ticket">
                    <input type="hidden" name="id_ticket" id="gestionTicketId">

                    <header class="cabecera-dialog-admin">
                        <div>
                            <span class="sobrelinea-panel">Asignación y seguimiento</span>
                            <h2 id="tituloGestionTicket">Administrar ticket</h2>
                        </div>
                        <button class="cerrar-dialog-admin" type="button" data-cerrar-gestion-ticket aria-label="Cerrar">×</button>
                    </header>

                    <div class="cuerpo-dialog-admin cuerpo-gestion-ticket-admin">
                        <div class="resumen-gestion-ticket-admin">
                            <strong id="gestionTicketEquipo"></strong>
                            <span id="gestionTicketOrigen"></span>
                        </div>

                        <div class="grid-gestion-ticket-admin">
                            <label class="campo-dialog-admin campo-completo-dialog">
                                <span>Técnico asignado</span>
                                <select name="id_tecnico" id="gestionTicketTecnico">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($opcionesFiltrosTickets["tecnicos"] as $tecnico): ?>
                                        <option value="<?php echo escaparTicketsAdministrador($tecnico["id"]); ?>"><?php echo escaparTicketsAdministrador($tecnico["nombre"]); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small>El administrador puede asignar o cambiar el técnico responsable.</small>
                            </label>

                            <label class="campo-dialog-admin">
                                <span>Prioridad</span>
                                <select name="id_prioridad" id="gestionTicketPrioridad" required>
                                    <?php foreach ($opcionesFiltrosTickets["prioridades"] as $prioridad): ?>
                                        <option value="<?php echo escaparTicketsAdministrador($prioridad["id"]); ?>"><?php echo escaparTicketsAdministrador($prioridad["nombre"]); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="campo-dialog-admin">
                                <span>Estado</span>
                                <select name="id_estado_ticket" id="gestionTicketEstado" required>
                                    <?php foreach ($opcionesFiltrosTickets["estados"] as $estado): ?>
                                        <option value="<?php echo escaparTicketsAdministrador($estado["id"]); ?>" data-nombre-estado="<?php echo escaparTicketsAdministrador($estado["nombre"]); ?>"><?php echo escaparTicketsAdministrador($estado["nombre"]); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="campo-dialog-admin campo-completo-dialog">
                                <span>Solución o seguimiento</span>
                                <textarea name="solucion" id="gestionTicketSolucion" rows="5" maxlength="5000" placeholder="Describe el trabajo realizado o las observaciones del seguimiento."></textarea>
                                <small id="ayudaSolucionTicket">La solución será obligatoria al marcar el ticket como resuelto.</small>
                            </label>
                        </div>
                    </div>

                    <footer class="acciones-dialog-admin">
                        <button class="boton-admin boton-admin-secundario" type="button" data-cerrar-gestion-ticket>Cancelar</button>
                        <button class="boton-admin boton-admin-principal" type="submit">Guardar cambios</button>
                    </footer>
                </form>
            </dialog>
        </main>

        <footer class="footer-admin">
            <p>GesTIck · Sistema de gestión de recursos y soporte de informática</p>
        </footer>
    </div>

    <script>
        const botonMenuAdmin = document.getElementById("botonMenuAdmin");
        const menuAdmin = document.getElementById("menuAdmin");
        const fondoMenuAdmin = document.getElementById("fondoMenuAdmin");
        const dialogDetalleTicket = document.getElementById("dialogDetalleTicket");
        const dialogGestionTicket = document.getElementById("dialogGestionTicket");

        function cambiarMenuAdmin(abierto) {
            menuAdmin.classList.toggle("abierto", abierto);
            fondoMenuAdmin.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-admin-abierto", abierto);
            botonMenuAdmin.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        function claseValorTicket(valor) {
            return valor.toLocaleLowerCase("es")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/ñ/g, "n")
                .replace(/\s+/g, "-")
                .replace(/[^a-z0-9-]/g, "") || "neutra";
        }

        function completarDetalleTicket(ticket) {
            document.getElementById("tituloDetalleTicket").textContent = "Ticket #" + ticket.id;
            document.getElementById("detalleEquipoTicket").textContent = ticket.equipo;
            document.getElementById("detalleModeloTicket").textContent = ticket.modelo;
            document.getElementById("detalleTipoTicket").textContent = ticket.tipo;
            document.getElementById("detalleUbicacionTicket").textContent = ticket.ubicacion;
            document.getElementById("detalleTecnicoTicket").textContent = ticket.tecnico;
            document.getElementById("detalleDocenteTicket").textContent = ticket.docente;
            document.getElementById("detallePlanillaTicket").textContent = ticket.planilla;
            document.getElementById("detalleGeneradoTicket").textContent = ticket.generado;
            document.getElementById("detalleInicioTicket").textContent = ticket.inicio;
            document.getElementById("detalleFinTicket").textContent = ticket.fin;
            document.getElementById("detalleDescripcionTicket").textContent = ticket.descripcion;
            document.getElementById("detalleSolucionTicket").textContent = ticket.solucion;

            const prioridad = document.getElementById("detallePrioridadTicket");
            const estado = document.getElementById("detalleEstadoTicket");
            prioridad.className = "etiqueta-admin prioridad-" + claseValorTicket(ticket.prioridad);
            estado.className = "etiqueta-admin estado-" + claseValorTicket(ticket.estado);
            prioridad.textContent = ticket.prioridad;
            estado.textContent = ticket.estado;
        }

        botonMenuAdmin.addEventListener("click", function () {
            cambiarMenuAdmin(!menuAdmin.classList.contains("abierto"));
        });

        fondoMenuAdmin.addEventListener("click", function () {
            cambiarMenuAdmin(false);
        });

        document.querySelectorAll(".menu-desplegable-tickets-admin").forEach(function (menuDesplegable) {
            menuDesplegable.addEventListener("toggle", function () {
                if (!menuDesplegable.open) {
                    return;
                }

                document.querySelectorAll(".menu-desplegable-tickets-admin[open]").forEach(function (otroMenu) {
                    if (otroMenu !== menuDesplegable) {
                        otroMenu.removeAttribute("open");
                    }
                });
            });
        });

        document.querySelectorAll(".opcion-filtro-tickets-admin").forEach(function (opcionFiltro) {
            opcionFiltro.addEventListener("toggle", function () {
                if (!opcionFiltro.open) {
                    return;
                }

                document.querySelectorAll(".opcion-filtro-tickets-admin[open]").forEach(function (otraOpcion) {
                    if (otraOpcion !== opcionFiltro) {
                        otraOpcion.removeAttribute("open");
                    }
                });
            });
        });

        document.querySelectorAll("[data-ticket]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                completarDetalleTicket(JSON.parse(boton.dataset.ticket));
                dialogDetalleTicket.showModal();
            });
        });

        function actualizarRequisitosGestionTicket() {
            const estado = document.getElementById("gestionTicketEstado");
            const tecnico = document.getElementById("gestionTicketTecnico");
            const solucion = document.getElementById("gestionTicketSolucion");
            const opcionEstado = estado.options[estado.selectedIndex];
            const nombreEstado = opcionEstado ? opcionEstado.dataset.nombreEstado : "";

            tecnico.required = nombreEstado !== "Pendiente";
            solucion.required = nombreEstado === "Resuelto";
        }

        document.querySelectorAll("[data-administrar-ticket]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const ticket = JSON.parse(boton.dataset.administrarTicket);
                document.getElementById("gestionTicketId").value = ticket.id;
                document.getElementById("tituloGestionTicket").textContent = "Administrar ticket #" + ticket.id;
                document.getElementById("gestionTicketEquipo").textContent = ticket.equipo + " · " + ticket.ubicacion;
                document.getElementById("gestionTicketOrigen").textContent = "Docente: " + ticket.docente + " · " + ticket.planilla;
                document.getElementById("gestionTicketTecnico").value = ticket.id_tecnico || "";
                document.getElementById("gestionTicketPrioridad").value = ticket.id_prioridad;
                document.getElementById("gestionTicketEstado").value = ticket.id_estado;
                document.getElementById("gestionTicketSolucion").value = ticket.solucion === "Todavía no se registró una solución." ? "" : ticket.solucion;
                actualizarRequisitosGestionTicket();
                dialogGestionTicket.showModal();
            });
        });

        document.getElementById("gestionTicketEstado").addEventListener("change", actualizarRequisitosGestionTicket);

        document.querySelectorAll("[data-cerrar-gestion-ticket]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                dialogGestionTicket.close();
            });
        });

        document.querySelectorAll("[data-cerrar-ticket]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                dialogDetalleTicket.close();
            });
        });

        dialogDetalleTicket.addEventListener("click", function (evento) {
            const limites = dialogDetalleTicket.getBoundingClientRect();
            const dentro = evento.clientX >= limites.left && evento.clientX <= limites.right
                && evento.clientY >= limites.top && evento.clientY <= limites.bottom;

            if (!dentro) {
                dialogDetalleTicket.close();
            }
        });

        document.addEventListener("click", function (evento) {
            document.querySelectorAll(".menu-desplegable-tickets-admin[open]").forEach(function (menuDesplegable) {
                if (!menuDesplegable.contains(evento.target)) {
                    menuDesplegable.removeAttribute("open");
                }
            });
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuAdmin(false);
                document.querySelectorAll(".menu-desplegable-tickets-admin[open]").forEach(function (menuDesplegable) {
                    menuDesplegable.removeAttribute("open");
                });
            }
        });

        if (window.location.hash.startsWith("#ticket-")) {
            const tarjetaObjetivo = document.querySelector(window.location.hash);
            const botonDetalle = tarjetaObjetivo ? tarjetaObjetivo.querySelector("[data-ticket]") : null;

            if (botonDetalle) {
                completarDetalleTicket(JSON.parse(botonDetalle.dataset.ticket));
                dialogDetalleTicket.showModal();
            }
        }
    </script>
</body>
</html>
