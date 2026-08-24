<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - En proceso</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tecnico.css?v=20260823-responsive-1">
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
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=en_proceso" class="activo">En proceso</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=equipos">Equipos</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=solicitudes">Solicitudes</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mi_perfil">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-tecnico" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina">

        <header class="encabezado-principal">
            <div>
                <h1>En proceso</h1>
                <p>Tickets que están siendo atendidos actualmente</p>
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

            <?php if ($errorTickets): ?>
                <div class="mensaje-tecnico mensaje-tecnico-error" role="alert">
                    No fue posible cargar los tickets en proceso desde la base de datos.
                </div>
            <?php endif; ?>

            <section class="panel panel-tickets-completo">
                <div class="barra-proceso">

                    <div class="controles-pendientes">
                        <div class="ordenar">
                            <label for="ordenProceso">Ordenar por</label>
                            <select id="ordenProceso">
                                <option value="recientes">Más recientes</option>
                                <option value="antiguos">Más antiguos</option>
                                <option value="prioridad">Prioridad</option>
                            </select>
                        </div>

                        <div class="ordenar">
                            <label for="mostrarProceso">Mostrar solo</label>
                            <select id="mostrarProceso">
                                <option value="todos">Todos</option>
                                <option value="Alta">Prioridad alta</option>
                                <option value="Media">Prioridad media</option>
                                <option value="Baja">Prioridad baja</option>
                            </select>
                        </div>
                    </div>

                    <h2>En proceso</h2>

                    <div class="contador-tickets">
                        <span id="cantidadProceso"></span>
                    </div>
                </div>

                <div class="grid-tickets" id="listaProceso"></div>

                <nav class="paginacion" id="paginacionProceso" aria-label="Paginación de tickets en proceso"></nav>
            </section>

        </main>

        <!-- INFORME DE TICKET -->
        <div class="modal-fondo" id="modalTicket">
            <section class="modal-informe" role="dialog" aria-modal="true" aria-labelledby="tituloTicket">
                <form id="formActualizarTicket" method="POST" action="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=en_proceso">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8"); ?>">
                    <input type="hidden" name="accion" value="actualizar_ticket">
                    <input type="hidden" name="id_ticket" id="ticketIdNumerico">
                <div class="modal-cabecera">
                    <h2 id="tituloTicket">Ticket <span id="ticketId"></span></h2>
                    <button type="button" class="cerrar-x" data-cerrar="modalTicket" aria-label="Cerrar">×</button>
                </div>

                <div class="formulario-informe">
                    <div class="campo-informe">
                        <label for="ticketPrioridad">Prioridad</label>
                        <select id="ticketPrioridad" name="prioridad" required>
                            <option value="Alta">Alta</option>
                            <option value="Media">Media</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>

                    <div class="campo-informe">
                        <label for="ticketEquipo">Equipo</label>
                        <input type="text" id="ticketEquipo" readonly>
                    </div>

                    <div class="campo-informe">
                        <label for="ticketEstado">Estado</label>
                        <select id="ticketEstado" name="estado" required>
                            <option value="En proceso">En proceso</option>
                            <option value="Resuelto">Resuelto</option>
                        </select>
                    </div>

                    <div class="campo-informe">
                        <label for="ticketFecha">Finalización</label>
                        <input type="date" id="ticketFecha" readonly>
                    </div>

                    <div class="campo-informe campo-completo">
                        <label for="ticketDescripcion">Descripción</label>
                        <textarea id="ticketDescripcion" rows="3" readonly></textarea>
                    </div>

                    <div class="campo-informe campo-completo">
                        <label for="ticketSolucion">Solución</label>
                        <textarea id="ticketSolucion" name="solucion" rows="4" maxlength="5000" placeholder="Escriba aquí"></textarea>
                    </div>
                </div>

                <div class="acciones-modal">
                    <button type="submit" class="boton-guardar" id="guardarTicket">Guardar</button>
                    <button type="button" class="boton-cerrar" data-cerrar="modalTicket">Cerrar ventana</button>
                </div>
                </form>
            </section>
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

        const enProceso = <?php echo json_encode($enProceso, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const porPagina = 8;
        let paginaActual = 1;
        let procesoVisible = [...enProceso];

        const listaProceso = document.getElementById("listaProceso");
        const paginacionProceso = document.getElementById("paginacionProceso");
        const ordenProceso = document.getElementById("ordenProceso");
        const mostrarProceso = document.getElementById("mostrarProceso");
        const cantidadProceso = document.getElementById("cantidadProceso");
        const modalTicket = document.getElementById("modalTicket");

        function fechaVisible(fecha) {
            const partes = fecha.split("-");
            return partes[2] + "/" + partes[1] + "/" + partes[0];
        }

        function clasePrioridad(prioridad) {
            if (prioridad === "Alta") return "prioridad-alta";
            if (prioridad === "Media") return "prioridad-media";
            return "prioridad-baja";
        }

        function escaparHtml(valor) {
            const elemento = document.createElement("span");
            elemento.textContent = String(valor ?? "");
            return elemento.innerHTML;
        }

        function actualizarProceso() {
            procesoVisible = [...enProceso];

            if (mostrarProceso.value !== "todos") {
                procesoVisible = procesoVisible.filter(function (ticket) {
                    return ticket.prioridad === mostrarProceso.value;
                });
            }

            if (ordenProceso.value === "recientes") {
                procesoVisible.sort(function (a, b) {
                    return b.fechaCreacion.localeCompare(a.fechaCreacion);
                });
            }

            if (ordenProceso.value === "antiguos") {
                procesoVisible.sort(function (a, b) {
                    return a.fechaCreacion.localeCompare(b.fechaCreacion);
                });
            }

            if (ordenProceso.value === "prioridad") {
                const orden = { Alta: 3, Media: 2, Baja: 1 };

                procesoVisible.sort(function (a, b) {
                    return orden[b.prioridad] - orden[a.prioridad];
                });
            }

            paginaActual = 1;
            renderProceso();
        }

        function renderProceso() {
            listaProceso.innerHTML = "";

            const inicio = (paginaActual - 1) * porPagina;
            const fin = inicio + porPagina;
            const pagina = procesoVisible.slice(inicio, fin);

            pagina.forEach(function (ticket) {
                const tarjeta = document.createElement("article");
                tarjeta.className = "tarjeta tarjeta-mis-ticket";

                tarjeta.innerHTML = `
                    <div class="tarjeta-superior">
                        <span class="badge ${clasePrioridad(ticket.prioridad)}">${escaparHtml(ticket.prioridad)}</span>
                        <span class="badge estado-proceso">En proceso</span>
                        <span class="fecha">${fechaVisible(ticket.fechaCreacion)}</span>
                    </div>

                    <h3>${escaparHtml(ticket.titulo)}</h3>
                    <p>${escaparHtml(ticket.equipo)}</p>

                    <button type="button" class="ver-mas">Ver más</button>
                `;

                tarjeta.querySelector(".ver-mas").addEventListener("click", function () {
                    abrirTicket(ticket);
                });

                listaProceso.appendChild(tarjeta);
            });

            cantidadProceso.textContent =
                procesoVisible.length + (procesoVisible.length === 1 ? " en proceso" : " en proceso");

            renderPaginacion();
        }

        function renderPaginacion() {
            paginacionProceso.innerHTML = "";

            const totalPaginas = Math.max(1, Math.ceil(procesoVisible.length / porPagina));

            const anterior = document.createElement("button");
            anterior.textContent = "← Anterior";
            anterior.disabled = paginaActual === 1;

            anterior.addEventListener("click", function () {
                paginaActual--;
                renderProceso();
            });

            paginacionProceso.appendChild(anterior);

            for (let i = 1; i <= totalPaginas; i++) {
                const boton = document.createElement("button");
                boton.textContent = i;

                if (i === paginaActual) {
                    boton.classList.add("pagina-activa");
                }

                boton.addEventListener("click", function () {
                    paginaActual = i;
                    renderProceso();
                });

                paginacionProceso.appendChild(boton);
            }

            const siguiente = document.createElement("button");
            siguiente.textContent = "Siguiente →";
            siguiente.disabled = paginaActual === totalPaginas;

            siguiente.addEventListener("click", function () {
                paginaActual++;
                renderProceso();
            });

            paginacionProceso.appendChild(siguiente);
        }

        function abrirTicket(ticket) {
            document.getElementById("ticketId").textContent = ticket.id;
            document.getElementById("ticketIdNumerico").value = ticket.idNumerico;
            document.getElementById("ticketPrioridad").value = ticket.prioridad;
            document.getElementById("ticketEquipo").value = ticket.equipo;
            document.getElementById("ticketEstado").value = ticket.estado;
            document.getElementById("ticketFecha").value = ticket.fechaFin;
            document.getElementById("ticketDescripcion").value = ticket.descripcion;
            document.getElementById("ticketSolucion").value = ticket.solucion;

            modalTicket.classList.add("mostrar");
        }

        ordenProceso.addEventListener("change", actualizarProceso);
        mostrarProceso.addEventListener("change", actualizarProceso);

        document.querySelectorAll("[data-cerrar]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.cerrar).classList.remove("mostrar");
            });
        });

        modalTicket.addEventListener("click", function (evento) {
            if (evento.target === modalTicket) {
                modalTicket.classList.remove("mostrar");
            }
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                modalTicket.classList.remove("mostrar");
            }
        });

        document.getElementById("formActualizarTicket").addEventListener("submit", function (evento) {
            const estado = document.getElementById("ticketEstado").value;
            const solucion = document.getElementById("ticketSolucion").value.trim();

            if (estado === "Resuelto" && solucion === "") {
                evento.preventDefault();
                window.alert("Escribe la solución antes de resolver el ticket.");
            }
        });

        actualizarProceso();
    </script>

</body>
</html>
