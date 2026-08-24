<?php

function escaparSolicitudesAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function fechaSolicitudesAdministrador(?string $fecha, bool $incluirHora = false): string
{
    if (!$fecha) {
        return "Sin registrar";
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false
        ? "Sin registrar"
        : date($incluirHora ? "d/m/Y H:i" : "d/m/Y", $marcaTiempo);
}

function claseSolicitudAdministrador(string $valor): string
{
    $normalizado = strtolower(str_replace(
        ["á", "é", "í", "ó", "ú", "ñ", " "],
        ["a", "e", "i", "o", "u", "n", "-"],
        $valor
    ));

    return preg_replace("/[^a-z0-9-]/", "", $normalizado) ?: "neutra";
}

function urlSolicitudesAdministrador(array $cambios = []): string
{
    global $urlAdministrador, $filtrosSolicitudes;

    $parametros = ["pagina" => "solicitudes"];
    $valores = [
        "buscar" => $filtrosSolicitudes["buscar"] ?? "",
        "orden" => $filtrosSolicitudes["orden"] ?? "fecha",
        "docente" => (int) ($filtrosSolicitudes["docente"] ?? 0),
        "p" => (int) ($filtrosSolicitudes["pagina"] ?? 1)
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
$cantidadFiltrosSolicitudes = (int) (($filtrosSolicitudes["docente"] ?? 0) > 0);
$hayCriteriosSolicitudes = $cantidadFiltrosSolicitudes > 0 || ($filtrosSolicitudes["buscar"] ?? "") !== "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Solicitudes del administrador</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-tickets.css?v=20260822-3">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-solicitudes.css?v=20260822-1">
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
            <a class="activo" href="<?php echo $urlAdministrador; ?>?pagina=solicitudes" aria-current="page"><span>Solicitudes</span></a>
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
                <h1>Solicitudes</h1>
                <p>Consulta las solicitudes enviadas por los docentes.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparSolicitudesAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparSolicitudesAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-tickets-admin">
            <?php if ($errorSolicitudes): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar las solicitudes. Comprueba que el servidor local continúe disponible.
                </div>
            <?php endif; ?>

            <section class="panel-tickets-admin" aria-labelledby="tituloListadoSolicitudes">
                <form class="barra-listado-tickets-admin" method="get" action="<?php echo escaparSolicitudesAdministrador($urlAdministrador); ?>">
                    <input type="hidden" name="pagina" value="solicitudes">
                    <input type="hidden" name="orden" value="<?php echo escaparSolicitudesAdministrador($filtrosSolicitudes["orden"] ?? "fecha"); ?>">
                    <input type="hidden" name="docente" value="<?php echo (int) ($filtrosSolicitudes["docente"] ?? 0); ?>">

                    <div class="controles-listado-tickets-admin">
                        <details class="menu-desplegable-tickets-admin menu-orden-tickets-admin">
                            <summary>Ordenar por <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                            <div class="opciones-menu-tickets-admin opciones-orden-tickets-admin">
                                <a class="<?php echo ($filtrosSolicitudes["orden"] ?? "") === "fecha" ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["orden" => "fecha", "p" => 1])); ?>">Fecha</a>
                                <a class="<?php echo ($filtrosSolicitudes["orden"] ?? "") === "estado" ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["orden" => "estado", "p" => 1])); ?>">Estado</a>
                                <a class="<?php echo ($filtrosSolicitudes["orden"] ?? "") === "id" ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["orden" => "id", "p" => 1])); ?>">ID</a>
                            </div>
                        </details>

                        <details class="menu-desplegable-tickets-admin filtros-tickets-admin" <?php echo $cantidadFiltrosSolicitudes > 0 ? "data-con-filtros" : ""; ?>>
                            <summary>Filtrar por <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                            <div class="opciones-menu-tickets-admin opciones-filtros-tickets-admin opciones-filtros-solicitudes-admin">
                                <details class="opcion-filtro-tickets-admin" <?php echo (int) $filtrosSolicitudes["docente"] > 0 ? "data-activo" : ""; ?>>
                                    <summary>Docente <i class="bi bi-chevron-right" aria-hidden="true"></i></summary>
                                    <div class="submenu-filtro-tickets-admin">
                                        <a class="<?php echo (int) $filtrosSolicitudes["docente"] === 0 ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["docente" => 0, "p" => 1])); ?>">Todos</a>
                                        <?php foreach ($docentesSolicitudes as $docente): ?>
                                            <a class="<?php echo (int) $filtrosSolicitudes["docente"] === (int) $docente["id"] ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["docente" => (int) $docente["id"], "p" => 1])); ?>">
                                                <?php echo escaparSolicitudesAdministrador($docente["nombre"]); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>

                                <details class="opcion-filtro-tickets-admin opcion-filtro-sin-relacion-admin">
                                    <summary>Técnico asignado <i class="bi bi-chevron-right" aria-hidden="true"></i></summary>
                                    <div class="submenu-filtro-tickets-admin">
                                        <span class="opcion-sin-datos-solicitudes-admin">No hay asignaciones registradas</span>
                                    </div>
                                </details>

                                <?php if ($cantidadFiltrosSolicitudes > 0): ?>
                                    <a class="limpiar-filtros-menu-admin" href="<?php echo $urlAdministrador; ?>?pagina=solicitudes">Limpiar filtros</a>
                                <?php endif; ?>
                            </div>
                        </details>
                    </div>

                    <h2 id="tituloListadoSolicitudes">Todas las solicitudes</h2>

                    <label class="buscador-tickets-admin">
                        <span class="solo-lector">Buscar solicitud</span>
                        <input type="search" name="buscar" value="<?php echo escaparSolicitudesAdministrador($filtrosSolicitudes["buscar"] ?? ""); ?>" placeholder="Buscar solicitud..." maxlength="100">
                        <button type="submit" aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
                    </label>
                </form>

                <div class="resumen-listado-tickets-admin">
                    <p>
                        <?php echo $totalSolicitudes === 1 ? "1 solicitud encontrada" : escaparSolicitudesAdministrador($totalSolicitudes) . " solicitudes encontradas"; ?>
                        <?php if ($hayCriteriosSolicitudes): ?> con los criterios seleccionados<?php endif; ?>.
                    </p>
                    <?php if ($hayCriteriosSolicitudes): ?>
                        <a href="<?php echo $urlAdministrador; ?>?pagina=solicitudes">Quitar búsqueda y filtros</a>
                    <?php endif; ?>
                </div>

                <?php if (!$errorSolicitudes && !$solicitudesAdministrador): ?>
                    <div class="vacio-tickets-admin">
                        <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                        <h3>No encontramos solicitudes</h3>
                        <p>Prueba quitando la búsqueda o el filtro seleccionado.</p>
                        <?php if ($hayCriteriosSolicitudes): ?>
                            <a class="boton-admin boton-admin-secundario" href="<?php echo $urlAdministrador; ?>?pagina=solicitudes">Ver todas</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="grid-tickets-admin">
                    <?php foreach ($solicitudesAdministrador as $solicitud): ?>
                        <?php
                        $detalleSolicitud = [
                            "id" => (int) $solicitud["id_solicitud"],
                            "asunto" => $solicitud["asunto"],
                            "descripcion" => $solicitud["descripcion"],
                            "estado" => $solicitud["estado"],
                            "docente" => $solicitud["docente"],
                            "correo" => $solicitud["correo_docente"],
                            "fecha" => fechaSolicitudesAdministrador($solicitud["fecha_envio"], true),
                            "tecnico" => "Sin asignar"
                        ];
                        $jsonSolicitud = json_encode($detalleSolicitud, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        ?>
                        <article class="tarjeta-ticket-admin tarjeta-solicitud-admin estado-solicitud-<?php echo claseSolicitudAdministrador($solicitud["estado"]); ?>" id="solicitud-<?php echo (int) $solicitud["id_solicitud"]; ?>">
                            <div class="meta-ticket-admin">
                                <span class="etiqueta-admin estado-<?php echo claseSolicitudAdministrador($solicitud["estado"]); ?>">
                                    <?php echo escaparSolicitudesAdministrador($solicitud["estado"]); ?>
                                </span>
                                <time datetime="<?php echo escaparSolicitudesAdministrador($solicitud["fecha_envio"]); ?>">
                                    <?php echo fechaSolicitudesAdministrador($solicitud["fecha_envio"]); ?>
                                </time>
                            </div>
                            <h3>Solicitud #<?php echo (int) $solicitud["id_solicitud"]; ?> · <?php echo escaparSolicitudesAdministrador($solicitud["asunto"]); ?></h3>
                            <p class="ubicacion-ticket-admin">Docente: <?php echo escaparSolicitudesAdministrador($solicitud["docente"]); ?></p>
                            <p class="descripcion-ticket-admin"><?php echo escaparSolicitudesAdministrador($solicitud["descripcion"]); ?></p>
                            <div class="pie-tarjeta-ticket-admin">
                                <span><strong>Técnico:</strong> Sin asignar</span>
                                <button type="button" class="ver-ticket-admin" data-solicitud="<?php echo escaparSolicitudesAdministrador($jsonSolicitud ?: "{}"); ?>">
                                    Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if (!$errorSolicitudes && $totalPaginasSolicitudes > 1): ?>
                    <nav class="paginacion-tickets-admin" aria-label="Paginación de solicitudes">
                        <?php if ($paginaActualSolicitudes > 1): ?>
                            <a class="paso-paginacion-ticket" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["p" => $paginaActualSolicitudes - 1])); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</a>
                        <?php else: ?>
                            <span class="paso-paginacion-ticket deshabilitado"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</span>
                        <?php endif; ?>

                        <div class="numeros-paginacion-ticket">
                            <?php
                            $inicioPaginas = max(1, $paginaActualSolicitudes - 2);
                            $finPaginas = min($totalPaginasSolicitudes, $paginaActualSolicitudes + 2);
                            ?>
                            <?php if ($inicioPaginas > 1): ?>
                                <a href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["p" => 1])); ?>">1</a>
                                <?php if ($inicioPaginas > 2): ?><span>…</span><?php endif; ?>
                            <?php endif; ?>
                            <?php for ($numeroPagina = $inicioPaginas; $numeroPagina <= $finPaginas; $numeroPagina++): ?>
                                <a class="<?php echo $numeroPagina === $paginaActualSolicitudes ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["p" => $numeroPagina])); ?>" <?php echo $numeroPagina === $paginaActualSolicitudes ? 'aria-current="page"' : ""; ?>><?php echo $numeroPagina; ?></a>
                            <?php endfor; ?>
                            <?php if ($finPaginas < $totalPaginasSolicitudes): ?>
                                <?php if ($finPaginas < $totalPaginasSolicitudes - 1): ?><span>…</span><?php endif; ?>
                                <a href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["p" => $totalPaginasSolicitudes])); ?>"><?php echo $totalPaginasSolicitudes; ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if ($paginaActualSolicitudes < $totalPaginasSolicitudes): ?>
                            <a class="paso-paginacion-ticket" href="<?php echo escaparSolicitudesAdministrador(urlSolicitudesAdministrador(["p" => $paginaActualSolicitudes + 1])); ?>">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        <?php else: ?>
                            <span class="paso-paginacion-ticket deshabilitado">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>

            <dialog class="dialog-admin dialog-ticket-admin" id="dialogDetalleSolicitud">
                <div class="cabecera-dialog-admin">
                    <div>
                        <span class="sobrelinea-panel">Detalle del pedido</span>
                        <h2 id="tituloDetalleSolicitud">Solicitud</h2>
                    </div>
                    <button class="cerrar-dialog-admin" type="button" data-cerrar-solicitud aria-label="Cerrar">×</button>
                </div>

                <div class="cuerpo-dialog-admin cuerpo-detalle-ticket-admin">
                    <div class="etiquetas-detalle-ticket-admin">
                        <span class="etiqueta-admin" id="detalleEstadoSolicitud"></span>
                    </div>

                    <dl class="datos-detalle-ticket-admin">
                        <div><dt>Docente</dt><dd id="detalleDocenteSolicitud"></dd></div>
                        <div><dt>Correo electrónico</dt><dd id="detalleCorreoSolicitud"></dd></div>
                        <div><dt>Fecha de envío</dt><dd id="detalleFechaSolicitud"></dd></div>
                        <div><dt>Técnico asignado</dt><dd id="detalleTecnicoSolicitud"></dd></div>
                    </dl>

                    <section class="texto-detalle-ticket-admin">
                        <h3>Asunto</h3>
                        <p id="detalleAsuntoSolicitud"></p>
                    </section>
                    <section class="texto-detalle-ticket-admin">
                        <h3>Descripción</h3>
                        <p id="detalleDescripcionSolicitud"></p>
                    </section>
                </div>

                <div class="acciones-dialog-admin">
                    <button class="boton-admin boton-admin-secundario" type="button" data-cerrar-solicitud>Cerrar ventana</button>
                </div>
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
        const dialogDetalleSolicitud = document.getElementById("dialogDetalleSolicitud");

        function cambiarMenuAdmin(abierto) {
            menuAdmin.classList.toggle("abierto", abierto);
            fondoMenuAdmin.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-admin-abierto", abierto);
            botonMenuAdmin.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        function claseValorSolicitud(valor) {
            return valor.toLocaleLowerCase("es")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/\s+/g, "-")
                .replace(/[^a-z0-9-]/g, "") || "neutra";
        }

        function completarDetalleSolicitud(solicitud) {
            document.getElementById("tituloDetalleSolicitud").textContent = "Solicitud #" + solicitud.id;
            document.getElementById("detalleDocenteSolicitud").textContent = solicitud.docente;
            document.getElementById("detalleCorreoSolicitud").textContent = solicitud.correo;
            document.getElementById("detalleFechaSolicitud").textContent = solicitud.fecha;
            document.getElementById("detalleTecnicoSolicitud").textContent = solicitud.tecnico;
            document.getElementById("detalleAsuntoSolicitud").textContent = solicitud.asunto;
            document.getElementById("detalleDescripcionSolicitud").textContent = solicitud.descripcion;

            const estado = document.getElementById("detalleEstadoSolicitud");
            estado.className = "etiqueta-admin estado-" + claseValorSolicitud(solicitud.estado);
            estado.textContent = solicitud.estado;
        }

        botonMenuAdmin.addEventListener("click", function () {
            cambiarMenuAdmin(!menuAdmin.classList.contains("abierto"));
        });

        fondoMenuAdmin.addEventListener("click", function () {
            cambiarMenuAdmin(false);
        });

        document.querySelectorAll(".menu-desplegable-tickets-admin").forEach(function (menuDesplegable) {
            menuDesplegable.addEventListener("toggle", function () {
                if (!menuDesplegable.open) return;

                document.querySelectorAll(".menu-desplegable-tickets-admin[open]").forEach(function (otroMenu) {
                    if (otroMenu !== menuDesplegable) otroMenu.removeAttribute("open");
                });
            });
        });

        document.querySelectorAll(".opcion-filtro-tickets-admin").forEach(function (opcionFiltro) {
            opcionFiltro.addEventListener("toggle", function () {
                if (!opcionFiltro.open) return;

                document.querySelectorAll(".opcion-filtro-tickets-admin[open]").forEach(function (otraOpcion) {
                    if (otraOpcion !== opcionFiltro) otraOpcion.removeAttribute("open");
                });
            });
        });

        document.querySelectorAll("[data-solicitud]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                completarDetalleSolicitud(JSON.parse(boton.dataset.solicitud));
                dialogDetalleSolicitud.showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-solicitud]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                dialogDetalleSolicitud.close();
            });
        });

        dialogDetalleSolicitud.addEventListener("click", function (evento) {
            const limites = dialogDetalleSolicitud.getBoundingClientRect();
            const dentro = evento.clientX >= limites.left && evento.clientX <= limites.right
                && evento.clientY >= limites.top && evento.clientY <= limites.bottom;

            if (!dentro) dialogDetalleSolicitud.close();
        });

        document.addEventListener("click", function (evento) {
            document.querySelectorAll(".menu-desplegable-tickets-admin[open]").forEach(function (menuDesplegable) {
                if (!menuDesplegable.contains(evento.target)) menuDesplegable.removeAttribute("open");
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

        if (window.location.hash.startsWith("#solicitud-")) {
            const tarjetaObjetivo = document.querySelector(window.location.hash);
            const botonDetalle = tarjetaObjetivo ? tarjetaObjetivo.querySelector("[data-solicitud]") : null;

            if (botonDetalle) {
                completarDetalleSolicitud(JSON.parse(botonDetalle.dataset.solicitud));
                dialogDetalleSolicitud.showModal();
            }
        }
    </script>
</body>
</html>
