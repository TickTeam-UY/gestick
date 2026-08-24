<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Solicitudes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tecnico.css?v=20260824-solicitudes-db-1">
</head>
<body>

    <button class="boton-menu" id="botonMenu" aria-label="Abrir menú">☰</button>

    <aside class="menu-lateral" id="menuLateral">
        <div class="logo-menu">
            <img src="<?php echo BASE_URL; ?>/public/imagenes/logoG.png" alt="Logo GesTIck">
        </div>

        <nav class="navegacion">
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=inicio">Inicio</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets">Mis tickets</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=pendientes">Pendientes</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=en_proceso">En proceso</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=equipos">Equipos</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=solicitudes" class="activo">Solicitudes</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mi_perfil">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-tecnico" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina">

        <header class="encabezado-principal">
            <div>
                <h1>Solicitudes</h1>
                <p>Listado de solicitudes recibidas</p>
            </div>

            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mi_perfil" class="usuario-superior" aria-label="Ir al perfil del usuario">
                <div class="avatar"></div>

                <div class="datos-usuario">
                    <strong><?php echo htmlspecialchars($_SESSION["rol"] == "Tecnico" ? "Técnico" : $_SESSION["rol"]); ?></strong>
                    <span><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                </div>
            </a>
        </header>

        <main class="contenido-mis-tickets">

            <?php if ($mensajeTecnico): ?>
                <div class="mensaje-tecnico mensaje-tecnico-<?php echo htmlspecialchars($mensajeTecnico["tipo"], ENT_QUOTES, "UTF-8"); ?>" role="status">
                    <?php echo htmlspecialchars($mensajeTecnico["texto"], ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php endif; ?>

            <?php if ($errorSolicitudes): ?>
                <div class="mensaje-tecnico mensaje-tecnico-error" role="alert">
                    No fue posible cargar las solicitudes desde la base de datos.
                </div>
            <?php endif; ?>

            <section class="panel panel-tickets-completo">
                <div class="barra-solicitudes">
                    <div class="ordenar">
                        <label for="ordenSolicitudes">Ordenar por</label>
                        <select id="ordenSolicitudes">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguas</option>
                            <option value="estado">Estado</option>
                        </select>
                    </div>

                    <h2>Solicitudes</h2>

                    <div class="contador-tickets">
                        <span id="cantidadSolicitudes"></span>
                    </div>
                </div>

                <div class="grid-tickets" id="listaSolicitudes"></div>

                <nav class="paginacion" id="paginacionSolicitudes" aria-label="Paginación de solicitudes"></nav>
            </section>

        </main>

        <!-- INFORME DE SOLICITUD -->
        <div class="modal-fondo" id="modalSolicitud">
            <form
                class="modal-informe modal-solicitud"
                role="dialog"
                aria-modal="true"
                aria-labelledby="tituloSolicitud"
                method="POST"
                action="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=solicitudes">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8"); ?>">
                <input type="hidden" name="accion" value="actualizar_solicitud">
                <input type="hidden" name="id_solicitud" id="solicitudIdCampo">
                <div class="modal-cabecera">
                    <h2 id="tituloSolicitud">Solicitud <span id="solicitudId"></span></h2>
                    <button type="button" class="cerrar-x" data-cerrar="modalSolicitud" aria-label="Cerrar">×</button>
                </div>

                <div class="formulario-informe formulario-solicitud">
                    <div class="campo-informe">
                        <label for="solicitudFecha">Fecha de inicio</label>
                        <input type="text" id="solicitudFecha" readonly>
                    </div>

                    <div class="campo-informe">
                        <label for="solicitudEstado">Estado</label>
                        <select id="solicitudEstado" name="estado" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En proceso">En proceso</option>
                            <option value="Completada">Completada</option>
                            <option value="Cancelada" disabled>Cancelada</option>
                        </select>
                    </div>

                    <div class="campo-informe">
                        <label for="solicitudRemitente">Remitente</label>
                        <input type="text" id="solicitudRemitente" readonly>
                    </div>

                    <div class="campo-informe">
                        <label for="solicitudAsunto">Asunto</label>
                        <input type="text" id="solicitudAsunto" readonly>
                    </div>

                    <div class="campo-informe campo-completo">
                        <label for="solicitudDescripcion">Mensaje</label>
                        <textarea id="solicitudDescripcion" rows="4" readonly></textarea>
                    </div>

                    <div class="campo-informe">
                        <label for="solicitudFinalizacion">Finalización</label>
                        <input type="date" id="solicitudFinalizacion" name="fecha_fin" max="<?php echo date("Y-m-d"); ?>">
                    </div>

                    <div class="campo-informe">
                        <label for="solicitudTrabajo">Trabajo realizado</label>
                        <textarea id="solicitudTrabajo" name="trabajo_realizado" rows="4" maxlength="5000" placeholder="Escriba aquí"></textarea>
                    </div>
                </div>

                <div class="acciones-modal">
                    <button type="submit" class="boton-guardar" id="guardarSolicitud">Guardar</button>
                    <button type="button" class="boton-cerrar" data-cerrar="modalSolicitud">Cerrar ventana</button>
                </div>
            </form>
        </div>

        <footer>
            <p>GesTIck - Sistema de Gestión de Recursos y Soporte de Informática</p>
        </footer>

    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const botonMenu = document.getElementById("botonMenu");
        const menuLateral = document.getElementById("menuLateral");

        botonMenu.addEventListener("click", function () {
            menuLateral.classList.toggle("abierto");
        });

        const solicitudes = <?php echo json_encode(
            $solicitudes,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        const porPagina = 8;
        let paginaActual = 1;
        let solicitudesOrdenadas = [...solicitudes];

        const listaSolicitudes = document.getElementById("listaSolicitudes");
        const paginacionSolicitudes = document.getElementById("paginacionSolicitudes");
        const ordenSolicitudes = document.getElementById("ordenSolicitudes");
        const cantidadSolicitudes = document.getElementById("cantidadSolicitudes");
        const modalSolicitud = document.getElementById("modalSolicitud");

        function fechaVisible(fecha) {
            if (!fecha) return "—";
            const partes = fecha.split("-");
            return partes[2] + "/" + partes[1] + "/" + partes[0];
        }

        function claseEstadoSolicitud(estado) {
            if (estado === "En proceso") return "estado-proceso";
            if (estado === "Completada") return "estado-resuelto";
            return "estado-pendiente";
        }

        function ordenarSolicitudes() {
            solicitudesOrdenadas = [...solicitudes];

            if (ordenSolicitudes.value === "recientes") {
                solicitudesOrdenadas.sort(function (a, b) {
                    return b.fecha.localeCompare(a.fecha);
                });
            }

            if (ordenSolicitudes.value === "antiguos") {
                solicitudesOrdenadas.sort(function (a, b) {
                    return a.fecha.localeCompare(b.fecha);
                });
            }

            if (ordenSolicitudes.value === "estado") {
                const orden = {
                    "Pendiente": 1,
                    "En proceso": 2,
                    "Completada": 3,
                    "Cancelada": 4
                };

                solicitudesOrdenadas.sort(function (a, b) {
                    return (orden[a.estado] || 5) - (orden[b.estado] || 5);
                });
            }

            paginaActual = 1;
            renderSolicitudes();
        }

        function renderSolicitudes() {
            listaSolicitudes.innerHTML = "";

            const inicio = (paginaActual - 1) * porPagina;
            const fin = inicio + porPagina;
            const pagina = solicitudesOrdenadas.slice(inicio, fin);

            pagina.forEach(function (solicitud) {
                const tarjeta = document.createElement("article");
                tarjeta.className = "tarjeta tarjeta-mis-ticket";

                const superior = document.createElement("div");
                superior.className = "tarjeta-superior";

                const tipo = document.createElement("span");
                tipo.className = "badge solicitud";
                tipo.textContent = "Solicitud";

                const estado = document.createElement("span");
                estado.className = "badge " + claseEstadoSolicitud(solicitud.estado);
                estado.textContent = solicitud.estado;

                const fecha = document.createElement("span");
                fecha.className = "fecha";
                fecha.textContent = fechaVisible(solicitud.fecha);
                superior.append(tipo, estado, fecha);

                const asunto = document.createElement("h3");
                asunto.textContent = solicitud.asunto;
                const remitente = document.createElement("p");
                remitente.textContent = solicitud.remitente;
                const verMas = document.createElement("button");
                verMas.type = "button";
                verMas.className = "ver-mas";
                verMas.textContent = "Ver más";
                verMas.addEventListener("click", function () {
                    abrirSolicitud(solicitud);
                });

                tarjeta.append(superior, asunto, remitente, verMas);

                listaSolicitudes.appendChild(tarjeta);
            });

            if (pagina.length === 0) {
                const vacio = document.createElement("p");
                vacio.className = "mensaje-tecnico";
                vacio.textContent = "No hay solicitudes disponibles.";
                listaSolicitudes.appendChild(vacio);
            }

            cantidadSolicitudes.textContent =
                solicitudesOrdenadas.length +
                (solicitudesOrdenadas.length === 1 ? " solicitud" : " solicitudes");

            renderPaginacion();
        }

        function renderPaginacion() {
            paginacionSolicitudes.innerHTML = "";

            const totalPaginas = Math.max(
                1,
                Math.ceil(solicitudesOrdenadas.length / porPagina)
            );

            const anterior = document.createElement("button");
            anterior.textContent = "← Anterior";
            anterior.disabled = paginaActual === 1;

            anterior.addEventListener("click", function () {
                paginaActual--;
                renderSolicitudes();
            });

            paginacionSolicitudes.appendChild(anterior);

            for (let i = 1; i <= totalPaginas; i++) {
                const boton = document.createElement("button");
                boton.textContent = i;

                if (i === paginaActual) {
                    boton.classList.add("pagina-activa");
                }

                boton.addEventListener("click", function () {
                    paginaActual = i;
                    renderSolicitudes();
                });

                paginacionSolicitudes.appendChild(boton);
            }

            const siguiente = document.createElement("button");
            siguiente.textContent = "Siguiente →";
            siguiente.disabled = paginaActual === totalPaginas;

            siguiente.addEventListener("click", function () {
                paginaActual++;
                renderSolicitudes();
            });

            paginacionSolicitudes.appendChild(siguiente);
        }

        function abrirSolicitud(solicitud) {
            document.getElementById("solicitudId").textContent = solicitud.id;
            document.getElementById("solicitudIdCampo").value = solicitud.id;
            document.getElementById("solicitudFecha").value = fechaVisible(solicitud.fecha);
            document.getElementById("solicitudEstado").value = solicitud.estado;
            document.getElementById("solicitudRemitente").value = solicitud.remitente;
            document.getElementById("solicitudAsunto").value = solicitud.asunto;
            document.getElementById("solicitudDescripcion").value = solicitud.descripcion;
            document.getElementById("solicitudFinalizacion").value = solicitud.finalizacion;
            document.getElementById("solicitudTrabajo").value = solicitud.trabajo;

            const esCancelada = solicitud.estado === "Cancelada";
            document.getElementById("solicitudEstado").disabled = esCancelada;
            document.getElementById("solicitudFinalizacion").disabled = esCancelada;
            document.getElementById("solicitudTrabajo").disabled = esCancelada;
            document.getElementById("guardarSolicitud").hidden = esCancelada;
            actualizarObligatoriosSolicitud();

            modalSolicitud.classList.add("mostrar");
        }

        function actualizarObligatoriosSolicitud() {
            const completada = document.getElementById("solicitudEstado").value === "Completada";
            document.getElementById("solicitudFinalizacion").required = completada;
            document.getElementById("solicitudTrabajo").required = completada;
        }

        ordenSolicitudes.addEventListener("change", ordenarSolicitudes);

        document.querySelectorAll("[data-cerrar]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.cerrar).classList.remove("mostrar");
            });
        });

        modalSolicitud.addEventListener("click", function (evento) {
            if (evento.target === modalSolicitud) {
                modalSolicitud.classList.remove("mostrar");
            }
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                modalSolicitud.classList.remove("mostrar");
            }
        });

        document.getElementById("solicitudEstado").addEventListener("change", actualizarObligatoriosSolicitud);

        ordenarSolicitudes();
    </script>

</body>
</html>
