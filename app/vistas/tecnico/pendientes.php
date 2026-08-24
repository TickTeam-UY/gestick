<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Pendientes</title>

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
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=pendientes" class="activo">Pendientes</a>
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
                <h1>Pendientes</h1>
                <p>Revisa tus tickets pendientes o toma uno que todavía esté sin asignar</p>
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
                    <span><?php echo htmlspecialchars($mensajeTecnico["texto"], ENT_QUOTES, "UTF-8"); ?></span>
                </div>
            <?php endif; ?>

            <section class="panel panel-tickets-completo">
                <div class="barra-pendientes">

                    <div class="controles-pendientes">
                        <div class="ordenar">
                            <label for="ordenPendientes">Ordenar por</label>
                            <select id="ordenPendientes">
                                <option value="recientes">Más recientes</option>
                                <option value="antiguos">Más antiguos</option>
                                <option value="prioridad">Prioridad</option>
                            </select>
                        </div>

                        <div class="ordenar">
                            <label for="mostrarPendientes">Mostrar solo</label>
                            <select id="mostrarPendientes">
                                <option value="todos">Todos</option>
                                <option value="Alta">Prioridad alta</option>
                                <option value="Media">Prioridad media</option>
                                <option value="Baja">Prioridad baja</option>
                            </select>
                        </div>
                    </div>

                    <h2>Pendientes y disponibles</h2>

                    <div class="contador-tickets">
                        <span id="cantidadPendientes"></span>
                    </div>
                </div>

                <div class="grid-tickets" id="listaPendientes"></div>

                <nav class="paginacion" id="paginacionPendientes" aria-label="Paginación de tickets pendientes"></nav>
            </section>

        </main>

        <!-- INFORME DE TICKET -->
        <div class="modal-fondo" id="modalTicket">
            <section class="modal-informe" role="dialog" aria-modal="true" aria-labelledby="tituloTicket">
                <div class="modal-cabecera">
                    <h2 id="tituloTicket">Ticket <span id="ticketId"></span></h2>
                    <button type="button" class="cerrar-x" data-cerrar="modalTicket" aria-label="Cerrar">×</button>
                </div>

                <div class="formulario-informe">
                    <div class="campo-informe">
                        <label for="ticketPrioridad">Prioridad</label>
                        <select id="ticketPrioridad" disabled>
                            <option>Alta</option>
                            <option>Media</option>
                            <option>Baja</option>
                        </select>
                    </div>

                    <div class="campo-informe">
                        <label for="ticketEquipo">Equipo</label>
                        <input type="text" id="ticketEquipo" readonly>
                    </div>

                    <div class="campo-informe">
                        <label for="ticketEstado">Estado</label>
                        <select id="ticketEstado" disabled>
                            <option>Pendiente</option>
                            <option>En proceso</option>
                            <option>Resuelto</option>
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
                        <textarea id="ticketSolucion" rows="4" readonly></textarea>
                    </div>
                </div>

                <div class="acciones-modal">
                    <button type="button" class="boton-cerrar" data-cerrar="modalTicket">Cerrar ventana</button>
                </div>
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

        const pendientes = <?php echo json_encode($pendientes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const urlPendientes = <?php echo json_encode(BASE_URL . "/app/controladores/TecnicoController.php?pagina=pendientes", JSON_UNESCAPED_SLASHES); ?>;
        const csrfTecnico = <?php echo json_encode($csrfToken); ?>;

        const porPagina = 8;
        let paginaActual = 1;
        let pendientesVisibles = [...pendientes];

        const listaPendientes = document.getElementById("listaPendientes");
        const paginacionPendientes = document.getElementById("paginacionPendientes");
        const ordenPendientes = document.getElementById("ordenPendientes");
        const mostrarPendientes = document.getElementById("mostrarPendientes");
        const cantidadPendientes = document.getElementById("cantidadPendientes");
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

        function actualizarPendientes() {
            pendientesVisibles = [...pendientes];

            /* Filtro "Mostrar solo" */
            if (mostrarPendientes.value !== "todos") {
                pendientesVisibles = pendientesVisibles.filter(function (ticket) {
                    return ticket.prioridad === mostrarPendientes.value;
                });
            }

            /* Orden */
            if (ordenPendientes.value === "recientes") {
                pendientesVisibles.sort(function (a, b) {
                    return b.fechaCreacion.localeCompare(a.fechaCreacion);
                });
            }

            if (ordenPendientes.value === "antiguos") {
                pendientesVisibles.sort(function (a, b) {
                    return a.fechaCreacion.localeCompare(b.fechaCreacion);
                });
            }

            if (ordenPendientes.value === "prioridad") {
                const orden = { Alta: 3, Media: 2, Baja: 1 };

                pendientesVisibles.sort(function (a, b) {
                    return orden[b.prioridad] - orden[a.prioridad];
                });
            }

            paginaActual = 1;
            renderPendientes();
        }

        function renderPendientes() {
            listaPendientes.innerHTML = "";

            const inicio = (paginaActual - 1) * porPagina;
            const fin = inicio + porPagina;
            const pagina = pendientesVisibles.slice(inicio, fin);

            pagina.forEach(function (ticket) {
                const tarjeta = document.createElement("article");
                tarjeta.className = "tarjeta tarjeta-mis-ticket";

                tarjeta.innerHTML = `
                    <div class="tarjeta-superior">
                        <span class="badge ${clasePrioridad(ticket.prioridad)}">${escaparHtml(ticket.prioridad)}</span>
                        <span class="badge estado-pendiente">Pendiente</span>
                        ${ticket.disponible ? '<span class="badge estado-disponible-ticket">Disponible</span>' : '<span class="badge estado-asignado-ticket">Asignado a ti</span>'}
                        <span class="fecha">${fechaVisible(ticket.fechaCreacion)}</span>
                    </div>

                    <h3>${escaparHtml(ticket.titulo)}</h3>
                    <p>${escaparHtml(ticket.equipo)}</p>

                    <div class="acciones-ticket-tecnico">
                        <button type="button" class="ver-mas">Ver más</button>
                    </div>
                `;

                tarjeta.querySelector(".ver-mas").addEventListener("click", function () {
                    abrirTicket(ticket);
                });

                if (ticket.disponible) {
                    const formulario = document.createElement("form");
                    formulario.method = "POST";
                    formulario.action = urlPendientes;
                    formulario.addEventListener("submit", function (evento) {
                        if (!window.confirm("¿Quieres asignarte este ticket?")) {
                            evento.preventDefault();
                        }
                    });

                    const token = document.createElement("input");
                    token.type = "hidden";
                    token.name = "csrf_token";
                    token.value = csrfTecnico;
                    const accion = document.createElement("input");
                    accion.type = "hidden";
                    accion.name = "accion";
                    accion.value = "tomar_ticket";
                    const idTicket = document.createElement("input");
                    idTicket.type = "hidden";
                    idTicket.name = "id_ticket";
                    idTicket.value = ticket.idNumerico;
                    const botonTomar = document.createElement("button");
                    botonTomar.type = "submit";
                    botonTomar.className = "boton-tomar-ticket";
                    botonTomar.textContent = "Asignarme";
                    formulario.append(token, accion, idTicket, botonTomar);
                    tarjeta.querySelector(".acciones-ticket-tecnico").appendChild(formulario);
                }

                listaPendientes.appendChild(tarjeta);
            });

            cantidadPendientes.textContent =
                pendientesVisibles.length + (pendientesVisibles.length === 1 ? " pendiente" : " pendientes");

            renderPaginacion();
        }

        function renderPaginacion() {
            paginacionPendientes.innerHTML = "";

            const totalPaginas = Math.max(1, Math.ceil(pendientesVisibles.length / porPagina));

            const anterior = document.createElement("button");
            anterior.textContent = "← Anterior";
            anterior.disabled = paginaActual === 1;

            anterior.addEventListener("click", function () {
                paginaActual--;
                renderPendientes();
            });

            paginacionPendientes.appendChild(anterior);

            for (let i = 1; i <= totalPaginas; i++) {
                const boton = document.createElement("button");
                boton.textContent = i;

                if (i === paginaActual) {
                    boton.classList.add("pagina-activa");
                }

                boton.addEventListener("click", function () {
                    paginaActual = i;
                    renderPendientes();
                });

                paginacionPendientes.appendChild(boton);
            }

            const siguiente = document.createElement("button");
            siguiente.textContent = "Siguiente →";
            siguiente.disabled = paginaActual === totalPaginas;

            siguiente.addEventListener("click", function () {
                paginaActual++;
                renderPendientes();
            });

            paginacionPendientes.appendChild(siguiente);
        }

        function abrirTicket(ticket) {
            document.getElementById("ticketId").textContent = ticket.id;
            document.getElementById("ticketPrioridad").value = ticket.prioridad;
            document.getElementById("ticketEquipo").value = ticket.equipo;
            document.getElementById("ticketEstado").value = ticket.estado;
            document.getElementById("ticketFecha").value = ticket.fechaFin;
            document.getElementById("ticketDescripcion").value = ticket.descripcion;
            document.getElementById("ticketSolucion").value = ticket.solucion;

            modalTicket.classList.add("mostrar");
        }

        ordenPendientes.addEventListener("change", actualizarPendientes);
        mostrarPendientes.addEventListener("change", actualizarPendientes);

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

        actualizarPendientes();
    </script>

</body>
</html>
