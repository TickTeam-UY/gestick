<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Mis tickets</title>

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
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets" class="activo">Mis tickets</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=pendientes">Pendientes</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=en_proceso">En proceso</a>
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
                <h1>Mis tickets</h1>
                <p>Listado de tickets asignados al técnico</p>
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
                    No fue posible cargar los tickets desde la base de datos.
                </div>
            <?php endif; ?>

            <section class="panel panel-tickets-completo">
                <div class="barra-tickets">
                    <div class="ordenar">
                        <label for="ordenTickets">Ordenar por</label>
                        <select id="ordenTickets">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="prioridad">Prioridad</option>
                            <option value="estado">Estado</option>
                        </select>
                    </div>

                    <h2>Tickets</h2>

                    <div class="contador-tickets">
                        <span id="cantidadTickets"></span>
                    </div>
                </div>

                <div class="grid-tickets" id="listaTickets"></div>

                <nav class="paginacion" id="paginacion" aria-label="Paginación de tickets"></nav>
            </section>

        </main>

        <!-- INFORME DE TICKET -->
        <div class="modal-fondo" id="modalTicket">
            <section class="modal-informe" role="dialog" aria-modal="true" aria-labelledby="tituloTicket">
                <form id="formActualizarTicket" method="POST" action="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets">
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
                            <option value="Pendiente">Pendiente</option>
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

        const tickets = <?php echo json_encode($tickets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const porPagina = 8;
        let paginaActual = 1;
        let ticketsOrdenados = [...tickets];

        const listaTickets = document.getElementById("listaTickets");
        const paginacion = document.getElementById("paginacion");
        const ordenTickets = document.getElementById("ordenTickets");
        const cantidadTickets = document.getElementById("cantidadTickets");
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

        function claseEstado(estado) {
            if (estado === "En proceso") return "estado-proceso";
            if (estado === "Resuelto") return "estado-resuelto";
            return "estado-pendiente";
        }

        function escaparHtml(valor) {
            const elemento = document.createElement("span");
            elemento.textContent = String(valor ?? "");
            return elemento.innerHTML;
        }

        function renderTickets() {
            listaTickets.innerHTML = "";

            const inicio = (paginaActual - 1) * porPagina;
            const fin = inicio + porPagina;
            const paginaTickets = ticketsOrdenados.slice(inicio, fin);

            paginaTickets.forEach(function (ticket) {
                const tarjeta = document.createElement("article");
                tarjeta.className = "tarjeta tarjeta-mis-ticket";

                tarjeta.innerHTML = `
                    <div class="tarjeta-superior">
                        <span class="badge ${clasePrioridad(ticket.prioridad)}">${escaparHtml(ticket.prioridad)}</span>
                        <span class="badge ${claseEstado(ticket.estado)}">${escaparHtml(ticket.estado)}</span>
                        <span class="fecha">${fechaVisible(ticket.fechaCreacion)}</span>
                    </div>

                    <h3>${escaparHtml(ticket.titulo)}</h3>
                    <p>${escaparHtml(ticket.equipo)}</p>

                    <button type="button" class="ver-mas">Ver más</button>
                `;

                tarjeta.querySelector(".ver-mas").addEventListener("click", function () {
                    abrirTicket(ticket);
                });

                listaTickets.appendChild(tarjeta);
            });

            cantidadTickets.textContent = ticketsOrdenados.length + " tickets";
            renderPaginacion();
        }

        function renderPaginacion() {
            paginacion.innerHTML = "";
            const totalPaginas = Math.ceil(ticketsOrdenados.length / porPagina);

            const anterior = document.createElement("button");
            anterior.textContent = "← Anterior";
            anterior.disabled = paginaActual === 1;
            anterior.addEventListener("click", function () {
                paginaActual--;
                renderTickets();
            });
            paginacion.appendChild(anterior);

            for (let i = 1; i <= totalPaginas; i++) {
                const boton = document.createElement("button");
                boton.textContent = i;

                if (i === paginaActual) {
                    boton.classList.add("pagina-activa");
                }

                boton.addEventListener("click", function () {
                    paginaActual = i;
                    renderTickets();
                });

                paginacion.appendChild(boton);
            }

            const siguiente = document.createElement("button");
            siguiente.textContent = "Siguiente →";
            siguiente.disabled = paginaActual === totalPaginas;
            siguiente.addEventListener("click", function () {
                paginaActual++;
                renderTickets();
            });
            paginacion.appendChild(siguiente);
        }

        function ordenarTickets() {
            ticketsOrdenados = [...tickets];

            if (ordenTickets.value === "recientes") {
                ticketsOrdenados.sort((a, b) => b.fechaCreacion.localeCompare(a.fechaCreacion));
            }

            if (ordenTickets.value === "antiguos") {
                ticketsOrdenados.sort((a, b) => a.fechaCreacion.localeCompare(b.fechaCreacion));
            }

            if (ordenTickets.value === "prioridad") {
                const orden = { Alta: 3, Media: 2, Baja: 1 };
                ticketsOrdenados.sort((a, b) => orden[b.prioridad] - orden[a.prioridad]);
            }

            if (ordenTickets.value === "estado") {
                const orden = { Pendiente: 1, "En proceso": 2, Resuelto: 3 };
                ticketsOrdenados.sort((a, b) => orden[a.estado] - orden[b.estado]);
            }

            paginaActual = 1;
            renderTickets();
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

        ordenTickets.addEventListener("change", ordenarTickets);

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

        ordenarTickets();
    </script>

</body>
</html>
