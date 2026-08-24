<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Inicio del técnico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tecnico.css?v=20260823-responsive-1">
</head>
<body>

    <!-- Botón para abrir/cerrar el menú en móvil -->
    <button class="boton-menu" id="botonMenu" aria-label="Abrir menú">
        ☰
    </button>

    <!-- MENÚ LATERAL -->
    <aside class="menu-lateral" id="menuLateral">

        <div class="logo-menu">
            <img src="<?php echo BASE_URL; ?>/public/imagenes/logoG.png" alt="Logo GesTIck">
        </div>

        <nav class="navegacion">
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=inicio" class="activo">Inicio</a>
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets">Mis tickets</a>
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

    <!-- CONTENIDO DE LA PÁGINA -->
    <div class="pagina">

        <header class="encabezado-principal">
            <div>
                <h1>Inicio del técnico</h1>
                <p>Resumen de tickets y solicitudes recientes</p>
            </div>

            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mi_perfil" class="usuario-superior" aria-label="Ir al perfil del usuario">
                <div class="avatar"></div>

                <div class="datos-usuario">
                    <strong><?php echo htmlspecialchars($_SESSION["rol"] == "Tecnico" ? "Técnico" : $_SESSION["rol"]); ?></strong>
                    <span><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                </div>
            </a>
        </header>

        <main class="contenido-principal">

            <!-- PANEL DE TICKETS -->
            <section class="panel">
                <div class="titulo-panel">
                    <h2>Tickets</h2>
                    <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets">Ver todos</a>
                </div>

                <?php foreach ($ticketsRecientes as $ticket): ?>
                    <?php
                        $clasePrioridad = "prioridad-baja";
                        if ($ticket["prioridad"] == "Alta") $clasePrioridad = "prioridad-alta";
                        if ($ticket["prioridad"] == "Media") $clasePrioridad = "prioridad-media";

                        $claseEstado = "estado-pendiente";
                        if ($ticket["estado"] == "En proceso") $claseEstado = "estado-proceso";
                        if ($ticket["estado"] == "Resuelto") $claseEstado = "estado-resuelto";
                    ?>

                    <article class="tarjeta ticket"
                        data-id="<?php echo htmlspecialchars($ticket["id"]); ?>"
                        data-prioridad="<?php echo htmlspecialchars($ticket["prioridad"]); ?>"
                        data-estado="<?php echo htmlspecialchars($ticket["estado"]); ?>"
                        data-equipo="<?php echo htmlspecialchars($ticket["equipo"]); ?>"
                        data-fecha="<?php echo htmlspecialchars($ticket["fechaFin"]); ?>"
                        data-descripcion="<?php echo htmlspecialchars($ticket["descripcion"]); ?>"
                        data-solucion="<?php echo htmlspecialchars($ticket["solucion"]); ?>">

                        <div class="tarjeta-superior">
                            <span class="badge <?php echo $clasePrioridad; ?>">
                                <?php echo htmlspecialchars($ticket["prioridad"]); ?>
                            </span>

                            <span class="badge <?php echo $claseEstado; ?>">
                                <?php echo htmlspecialchars($ticket["estado"]); ?>
                            </span>

                            <span class="fecha">
                                <?php echo date("d/m/Y", strtotime($ticket["fechaCreacion"])); ?>
                            </span>
                        </div>

                        <h3><?php echo htmlspecialchars($ticket["titulo"]); ?></h3>
                        <p><?php echo htmlspecialchars($ticket["equipo"]); ?></p>

                        <button type="button" class="ver-mas boton-ver-ticket">Ver más</button>
                    </article>
                <?php endforeach; ?>
            </section>

            <!-- PANEL DE SOLICITUDES -->
            <section class="panel">
                <div class="titulo-panel">
                    <h2>Solicitudes</h2>
                    <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=solicitudes">Ver todas</a>
                </div>

                <?php foreach ($solicitudesRecientes as $solicitud): ?>
                    <article class="tarjeta solicitud-card"
                        data-id="<?php echo htmlspecialchars($solicitud["id"]); ?>"
                        data-fecha="<?php echo date("d/m/Y", strtotime($solicitud["fecha"])); ?>"
                        data-estado="<?php echo htmlspecialchars($solicitud["estado"]); ?>"
                        data-asunto="<?php echo htmlspecialchars($solicitud["asunto"]); ?>"
                        data-remitente="<?php echo htmlspecialchars($solicitud["remitente"]); ?>"
                        data-finalizacion="<?php echo htmlspecialchars($solicitud["finalizacion"]); ?>"
                        data-descripcion="<?php echo htmlspecialchars($solicitud["descripcion"]); ?>"
                        data-trabajo="<?php echo htmlspecialchars($solicitud["trabajo"]); ?>">

                        <div class="tarjeta-superior">
                            <span class="badge solicitud">Solicitud</span>
                            <span class="fecha">
                                <?php echo date("d/m/Y", strtotime($solicitud["fecha"])); ?>
                            </span>
                        </div>

                        <h3><?php echo htmlspecialchars($solicitud["asunto"]); ?></h3>
                        <p><?php echo htmlspecialchars($solicitud["descripcion"]); ?></p>

                        <button type="button" class="ver-mas boton-ver-solicitud">Ver más</button>
                    </article>
                <?php endforeach; ?>
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
                    <a class="boton-guardar" href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets">Gestionar ticket</a>
                    <button type="button" class="boton-cerrar" data-cerrar="modalTicket">Cerrar ventana</button>
                </div>
            </section>
        </div>

        <!-- INFORME DE SOLICITUD -->
        <div class="modal-fondo" id="modalSolicitud">
            <section class="modal-informe modal-solicitud" role="dialog" aria-modal="true" aria-labelledby="tituloSolicitud">
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
                        <select id="solicitudEstado" disabled>
                            <option>Pendiente</option>
                            <option>En proceso</option>
                            <option>Completada</option>
                            <option>Cancelada</option>
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
                        <input type="date" id="solicitudFinalizacion" readonly>
                    </div>

                    <div class="campo-informe">
                        <label for="solicitudTrabajo">Trabajo realizado</label>
                        <textarea id="solicitudTrabajo" rows="4" readonly></textarea>
                    </div>
                </div>

                <div class="acciones-modal">
                    <a class="boton-guardar" href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=solicitudes">Gestionar solicitud</a>
                    <button type="button" class="boton-cerrar" data-cerrar="modalSolicitud">Cerrar ventana</button>
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

        const modalTicket = document.getElementById("modalTicket");
        const modalSolicitud = document.getElementById("modalSolicitud");

        document.querySelectorAll(".boton-ver-ticket").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const tarjeta = boton.closest(".ticket");

                document.getElementById("ticketId").textContent = tarjeta.dataset.id;
                document.getElementById("ticketPrioridad").value = tarjeta.dataset.prioridad;
                document.getElementById("ticketEquipo").value = tarjeta.dataset.equipo;
                document.getElementById("ticketEstado").value = tarjeta.dataset.estado;
                document.getElementById("ticketFecha").value = tarjeta.dataset.fecha;
                document.getElementById("ticketDescripcion").value = tarjeta.dataset.descripcion;
                document.getElementById("ticketSolucion").value = tarjeta.dataset.solucion;

                modalTicket.classList.add("mostrar");
            });
        });

        document.querySelectorAll(".boton-ver-solicitud").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const tarjeta = boton.closest(".solicitud-card");

                document.getElementById("solicitudId").textContent = tarjeta.dataset.id;
                document.getElementById("solicitudFecha").value = tarjeta.dataset.fecha;
                document.getElementById("solicitudEstado").value = tarjeta.dataset.estado;
                document.getElementById("solicitudRemitente").value = tarjeta.dataset.remitente;
                document.getElementById("solicitudAsunto").value = tarjeta.dataset.asunto;
                document.getElementById("solicitudDescripcion").value = tarjeta.dataset.descripcion;
                document.getElementById("solicitudFinalizacion").value = tarjeta.dataset.finalizacion;
                document.getElementById("solicitudTrabajo").value = tarjeta.dataset.trabajo;

                modalSolicitud.classList.add("mostrar");
            });
        });

        document.querySelectorAll("[data-cerrar]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.cerrar).classList.remove("mostrar");
            });
        });

        document.querySelectorAll(".modal-fondo").forEach(function (modal) {
            modal.addEventListener("click", function (evento) {
                if (evento.target === modal) {
                    modal.classList.remove("mostrar");
                }
            });
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                modalTicket.classList.remove("mostrar");
                modalSolicitud.classList.remove("mostrar");
            }
        });

    </script>

</body>
</html>
