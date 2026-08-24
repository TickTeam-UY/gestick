<?php

function escaparSolicitudesDocente(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function fechaSolicitudesDocente(?string $fecha, bool $incluirHora = false): string
{
    $marcaTiempo = $fecha ? strtotime($fecha) : false;

    return $marcaTiempo === false
        ? "Sin fecha"
        : date($incluirHora ? "d/m/Y H:i" : "d/m/Y", $marcaTiempo);
}

function claseEstadoSolicitudesDocente(string $estado): string
{
    $normalizado = strtolower(str_replace(
        ["á", "é", "í", "ó", "ú", "ñ", " "],
        ["a", "e", "i", "o", "u", "n", "-"],
        $estado
    ));

    return preg_replace('/[^a-z0-9-]/', '', $normalizado) ?: "neutro";
}

function urlSolicitudesDocente(array $cambios = []): string
{
    global $urlDocente, $filtrosSolicitudesDocente;

    $valores = [
        "pagina" => "mis_solicitudes",
        "buscar" => $filtrosSolicitudesDocente["buscar"] ?? "",
        "orden" => $filtrosSolicitudesDocente["orden"] ?? "recientes",
        "p" => $filtrosSolicitudesDocente["pagina"] ?? 1
    ];

    foreach ($cambios as $clave => $valor) {
        $valores[$clave] = $valor;
    }

    if ($valores["buscar"] === "") {
        unset($valores["buscar"]);
    }

    if ($valores["orden"] === "recientes") {
        unset($valores["orden"]);
    }

    if ((int) $valores["p"] === 1) {
        unset($valores["p"]);
    }

    return $urlDocente . "?" . http_build_query($valores);
}

$urlDocente = BASE_URL . "/app/controladores/DocenteController.php";
$urlBandejaSolicitudes = $urlDocente . "?pagina=mis_solicitudes";
$nombreDocente = trim((string) ($_SESSION["nombre"] ?? "Docente"));
$asuntoAnterior = (string) ($datosAnterioresSolicitud["asunto"] ?? "");
$descripcionAnterior = (string) ($datosAnterioresSolicitud["descripcion"] ?? "");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Mis solicitudes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente.css?v=20260823-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente-solicitudes.css?v=20260822-1">
</head>
<body>
    <button class="boton-menu-docente" id="botonMenuDocente" type="button" aria-label="Abrir menú" aria-expanded="false">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>

    <div class="fondo-menu-docente" id="fondoMenuDocente"></div>

    <aside class="menu-docente" id="menuDocente">
        <div class="logo-menu-docente">
            <img src="<?php echo BASE_URL; ?>/public/imagenes/logoG.png" alt="GesTIck">
        </div>

        <nav class="navegacion-docente" aria-label="Navegación del docente">
            <a href="<?php echo $urlDocente; ?>?pagina=inicio">Inicio</a>
            <a href="<?php echo $urlDocente; ?>?pagina=llenar_planilla">Llenar planilla</a>
            <a class="activo" href="<?php echo $urlBandejaSolicitudes; ?>">Solicitud</a>
            <a href="<?php echo $urlDocente; ?>?pagina=mi_perfil">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-docente" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            Cerrar sesión
        </a>
    </aside>

    <div class="pagina-docente">
        <header class="encabezado-docente encabezado-solicitudes-docente">
            <div class="presentacion-docente">
                <span class="marca-docente">GesTIck</span>
                <h1>Mis solicitudes</h1>
                <p>Envía pedidos a Soporte informático y sigue su estado desde un solo lugar.</p>
            </div>

            <a class="usuario-docente" href="<?php echo $urlDocente; ?>?pagina=mi_perfil" aria-label="Ir a mi perfil">
                <span class="avatar-docente" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-docente">
                    <strong>Docente</strong>
                    <span><?php echo escaparSolicitudesDocente($nombreDocente); ?></span>
                </span>
            </a>
        </header>

        <main class="contenido-solicitudes-docente">
            <?php if ($mensajeDocente): ?>
                <div class="mensaje-solicitudes-docente mensaje-<?php echo escaparSolicitudesDocente($mensajeDocente["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeDocente["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparSolicitudesDocente($mensajeDocente["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorSolicitudesDocente): ?>
                <div class="mensaje-solicitudes-docente mensaje-error" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>No fue posible cargar tus solicitudes. Comprueba que MySQL continúe disponible.</span>
                </div>
            <?php endif; ?>

            <section class="panel-bandeja-solicitudes" aria-labelledby="tituloBandejaSolicitudes">
                <form class="barra-solicitudes-docente" method="GET" action="<?php echo $urlDocente; ?>">
                    <input type="hidden" name="pagina" value="mis_solicitudes">

                    <label class="orden-solicitudes-docente">
                        <span>Ordenar por</span>
                        <select name="orden" onchange="this.form.submit()">
                            <option value="recientes"<?php echo $filtrosSolicitudesDocente["orden"] === "recientes" ? " selected" : ""; ?>>Más nuevas</option>
                            <option value="antiguas"<?php echo $filtrosSolicitudesDocente["orden"] === "antiguas" ? " selected" : ""; ?>>Más antiguas</option>
                        </select>
                    </label>

                    <div class="titulo-bandeja-solicitudes">
                        <span class="sobrelinea-docente">Seguimiento personal</span>
                        <h2 id="tituloBandejaSolicitudes">Mis solicitudes</h2>
                    </div>

                    <label class="buscador-solicitudes-docente">
                        <span class="solo-lector-docente">Buscar solicitud</span>
                        <input type="search" name="buscar" maxlength="100" value="<?php echo escaparSolicitudesDocente($filtrosSolicitudesDocente["buscar"]); ?>" placeholder="Buscar solicitud...">
                        <button type="submit" aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
                    </label>

                    <button class="boton-nueva-solicitud" type="button" data-abrir-solicitud>
                        <i class="bi bi-plus" aria-hidden="true"></i> Nueva solicitud
                    </button>
                </form>

                <div class="resumen-bandeja-solicitudes">
                    <span><?php echo (int) $totalSolicitudesDocente; ?> <?php echo $totalSolicitudesDocente === 1 ? "solicitud" : "solicitudes"; ?></span>
                    <?php if ($filtrosSolicitudesDocente["buscar"] !== ""): ?>
                        <a href="<?php echo $urlBandejaSolicitudes; ?>">Quitar búsqueda</a>
                    <?php endif; ?>
                </div>

                <?php if (!$solicitudesDocente && !$errorSolicitudesDocente): ?>
                    <div class="vacio-solicitudes-docente">
                        <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                        <h3><?php echo $filtrosSolicitudesDocente["buscar"] === "" ? "Aún no enviaste solicitudes" : "No encontramos coincidencias"; ?></h3>
                        <p><?php echo $filtrosSolicitudesDocente["buscar"] === "" ? "Crea una solicitud para comunicarte con Soporte informático." : "Prueba con otro asunto, estado o número de solicitud."; ?></p>
                        <?php if ($filtrosSolicitudesDocente["buscar"] === ""): ?><button type="button" data-abrir-solicitud>Crear solicitud</button><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="grid-solicitudes-docente">
                    <?php foreach ($solicitudesDocente as $solicitud): ?>
                        <?php
                        $detalleSolicitud = [
                            "id" => (int) $solicitud["id_solicitud"],
                            "destinatario" => "Soporte informático",
                            "asunto" => $solicitud["asunto"],
                            "descripcion" => $solicitud["descripcion"],
                            "estado" => $solicitud["estado"],
                            "fecha" => fechaSolicitudesDocente($solicitud["fecha_envio"], true)
                        ];
                        $jsonSolicitud = json_encode(
                            $detalleSolicitud,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                        );
                        ?>
                        <article class="tarjeta-solicitud-docente" id="solicitud-<?php echo (int) $solicitud["id_solicitud"]; ?>">
                            <header>
                                <span class="id-solicitud-docente">Solicitud #<?php echo (int) $solicitud["id_solicitud"]; ?></span>
                                <time datetime="<?php echo escaparSolicitudesDocente($solicitud["fecha_envio"]); ?>"><?php echo fechaSolicitudesDocente($solicitud["fecha_envio"]); ?></time>
                            </header>
                            <h3><?php echo escaparSolicitudesDocente($solicitud["asunto"]); ?></h3>
                            <p><?php echo escaparSolicitudesDocente($solicitud["descripcion"]); ?></p>
                            <footer>
                                <span class="estado-solicitud estado-<?php echo claseEstadoSolicitudesDocente($solicitud["estado"]); ?>"><?php echo escaparSolicitudesDocente($solicitud["estado"]); ?></span>
                                <div class="acciones-solicitud-docente">
                                    <?php if (strcasecmp((string) $solicitud["estado"], "Pendiente") === 0): ?>
                                        <form method="POST" action="<?php echo $urlBandejaSolicitudes; ?>" data-cancelar-solicitud>
                                            <input type="hidden" name="csrf_token" value="<?php echo escaparSolicitudesDocente($csrfToken); ?>">
                                            <input type="hidden" name="accion" value="cancelar_solicitud">
                                            <input type="hidden" name="id_solicitud" value="<?php echo (int) $solicitud["id_solicitud"]; ?>">
                                            <button type="submit" class="boton-cancelar-solicitud">Cancelar</button>
                                        </form>
                                    <?php endif; ?>
                                    <button type="button" data-ver-solicitud data-solicitud="<?php echo escaparSolicitudesDocente($jsonSolicitud ?: "{}"); ?>">Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                                </div>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPaginasSolicitudesDocente > 1): ?>
                    <nav class="paginacion-solicitudes-docente" aria-label="Paginación de solicitudes">
                        <?php if ($paginaActualSolicitudesDocente > 1): ?>
                            <a href="<?php echo escaparSolicitudesDocente(urlSolicitudesDocente(["p" => $paginaActualSolicitudesDocente - 1])); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</a>
                        <?php else: ?>
                            <span class="deshabilitado"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</span>
                        <?php endif; ?>

                        <div>
                            <?php for ($numero = 1; $numero <= $totalPaginasSolicitudesDocente; $numero++): ?>
                                <a class="<?php echo $numero === $paginaActualSolicitudesDocente ? "activo" : ""; ?>" href="<?php echo escaparSolicitudesDocente(urlSolicitudesDocente(["p" => $numero])); ?>"><?php echo $numero; ?></a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($paginaActualSolicitudesDocente < $totalPaginasSolicitudesDocente): ?>
                            <a href="<?php echo escaparSolicitudesDocente(urlSolicitudesDocente(["p" => $paginaActualSolicitudesDocente + 1])); ?>">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        <?php else: ?>
                            <span class="deshabilitado">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>
        </main>

        <footer class="footer-docente">
            GesTIck · Sistema de gestión de recursos y soporte de informática
        </footer>
    </div>

    <dialog class="dialog-solicitud-docente" id="dialogCrearSolicitud">
        <form method="POST" action="<?php echo $urlBandejaSolicitudes; ?>" class="formulario-crear-solicitud">
            <input type="hidden" name="csrf_token" value="<?php echo escaparSolicitudesDocente($csrfToken); ?>">
            <input type="hidden" name="accion" value="crear_solicitud">

            <header>
                <div>
                    <span class="sobrelinea-docente">Nuevo pedido</span>
                    <h2>Crear solicitud</h2>
                </div>
                <button type="button" data-cerrar-crear aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-crear-solicitud">
                <div class="fecha-nueva-solicitud"><i class="bi bi-calendar3" aria-hidden="true"></i> <?php echo date("d/m/Y"); ?></div>

                <label class="campo-solicitud-docente">
                    <span>Destinatario</span>
                    <input type="text" value="Soporte informático" readonly>
                    <small>La recibirán Administración y los técnicos.</small>
                </label>

                <label class="campo-solicitud-docente">
                    <span>Asunto</span>
                    <input type="text" name="asunto" maxlength="120" value="<?php echo escaparSolicitudesDocente($asuntoAnterior); ?>" placeholder="Resume lo que necesitas" required autofocus>
                </label>

                <label class="campo-solicitud-docente campo-mensaje-solicitud">
                    <span>Mensaje</span>
                    <textarea name="descripcion" id="mensajeNuevaSolicitud" maxlength="255" rows="5" placeholder="Explica tu solicitud" required><?php echo escaparSolicitudesDocente($descripcionAnterior); ?></textarea>
                    <small><span id="contadorMensajeSolicitud">0</span>/255 caracteres</small>
                </label>
            </div>

            <footer>
                <button type="button" class="cancelar-solicitud" data-cerrar-crear>Cancelar</button>
                <button type="submit" class="enviar-solicitud"><i class="bi bi-send" aria-hidden="true"></i> Enviar solicitud</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-solicitud-docente dialog-detalle-solicitud" id="dialogDetalleSolicitud">
        <div class="formulario-crear-solicitud">
            <header>
                <div>
                    <span class="sobrelinea-docente">Solicitud enviada</span>
                    <h2 id="tituloDetalleSolicitud">Solicitud</h2>
                </div>
                <button type="button" data-cerrar-detalle aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-detalle-solicitud">
                <div class="meta-detalle-solicitud">
                    <span id="estadoDetalleSolicitud" class="estado-solicitud"></span>
                    <time id="fechaDetalleSolicitud"></time>
                </div>
                <dl>
                    <div><dt>Destinatario</dt><dd id="destinatarioDetalleSolicitud"></dd></div>
                    <div><dt>Asunto</dt><dd id="asuntoDetalleSolicitud"></dd></div>
                </dl>
                <section>
                    <h3>Mensaje</h3>
                    <p id="mensajeDetalleSolicitud"></p>
                </section>
            </div>

            <footer>
                <button type="button" class="cancelar-solicitud" data-cerrar-detalle>Cerrar ventana</button>
            </footer>
        </div>
    </dialog>

    <script>
        const botonMenuDocente = document.getElementById("botonMenuDocente");
        const menuDocente = document.getElementById("menuDocente");
        const fondoMenuDocente = document.getElementById("fondoMenuDocente");
        const dialogCrearSolicitud = document.getElementById("dialogCrearSolicitud");
        const dialogDetalleSolicitud = document.getElementById("dialogDetalleSolicitud");
        const mensajeNuevaSolicitud = document.getElementById("mensajeNuevaSolicitud");
        const contadorMensajeSolicitud = document.getElementById("contadorMensajeSolicitud");
        const urlBandejaSolicitudes = <?php echo json_encode($urlBandejaSolicitudes, JSON_UNESCAPED_SLASHES); ?>;
        const abrirFormularioSolicitud = <?php echo $abrirFormularioSolicitud ? "true" : "false"; ?>;

        function cambiarMenuDocente(abierto) {
            menuDocente.classList.toggle("abierto", abierto);
            fondoMenuDocente.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-docente-abierto", abierto);
            botonMenuDocente.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        function cerrarFormularioSolicitud() {
            dialogCrearSolicitud.close();
            window.history.replaceState({}, "", urlBandejaSolicitudes);
        }

        function actualizarContadorSolicitud() {
            contadorMensajeSolicitud.textContent = mensajeNuevaSolicitud.value.length;
        }

        function completarDetalleSolicitud(solicitud) {
            document.getElementById("tituloDetalleSolicitud").textContent = "Solicitud #" + solicitud.id;
            document.getElementById("destinatarioDetalleSolicitud").textContent = solicitud.destinatario;
            document.getElementById("asuntoDetalleSolicitud").textContent = solicitud.asunto;
            document.getElementById("mensajeDetalleSolicitud").textContent = solicitud.descripcion;
            document.getElementById("fechaDetalleSolicitud").textContent = solicitud.fecha;
            const estado = document.getElementById("estadoDetalleSolicitud");
            const claseEstado = solicitud.estado.toLocaleLowerCase("es")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/\s+/g, "-")
                .replace(/[^a-z0-9-]/g, "");
            estado.className = "estado-solicitud estado-" + (claseEstado || "neutro");
            estado.textContent = solicitud.estado;
        }

        botonMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(!menuDocente.classList.contains("abierto"));
        });

        fondoMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(false);
        });

        document.querySelectorAll("[data-cerrar-crear]").forEach(function (boton) {
            boton.addEventListener("click", cerrarFormularioSolicitud);
        });

        document.querySelectorAll("[data-abrir-solicitud]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                dialogCrearSolicitud.showModal();
            });
        });

        document.querySelectorAll("[data-cancelar-solicitud]").forEach(function (formulario) {
            formulario.addEventListener("submit", function (evento) {
                if (!window.confirm("¿Seguro que deseas cancelar esta solicitud? Esta acción no se puede deshacer.")) {
                    evento.preventDefault();
                }
            });
        });

        document.querySelectorAll("[data-ver-solicitud]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                completarDetalleSolicitud(JSON.parse(boton.dataset.solicitud));
                dialogDetalleSolicitud.showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-detalle]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                dialogDetalleSolicitud.close();
            });
        });

        dialogCrearSolicitud.addEventListener("click", function (evento) {
            if (evento.target === dialogCrearSolicitud) {
                cerrarFormularioSolicitud();
            }
        });

        dialogDetalleSolicitud.addEventListener("click", function (evento) {
            if (evento.target === dialogDetalleSolicitud) {
                dialogDetalleSolicitud.close();
            }
        });

        mensajeNuevaSolicitud.addEventListener("input", actualizarContadorSolicitud);
        actualizarContadorSolicitud();

        if (abrirFormularioSolicitud) {
            dialogCrearSolicitud.showModal();
        }

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuDocente(false);
            }
        });
    </script>
</body>
</html>
