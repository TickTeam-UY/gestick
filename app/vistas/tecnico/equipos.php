<?php
$cantidadEquiposTecnico = array_sum(array_map(
    static fn (array $laboratorio): int => count($laboratorio["equipos"]),
    $laboratorios
));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Equipos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tecnico.css?v=20260824-equipo-detalle-1">
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
            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=equipos" class="activo">Equipos</a>
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
                <h1>Equipos</h1>
                <p>Equipos registrados por laboratorio</p>
            </div>

            <a href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mi_perfil" class="usuario-superior" aria-label="Ir al perfil del usuario">
                <div class="avatar"></div>

                <div class="datos-usuario">
                    <strong><?php echo htmlspecialchars($_SESSION["rol"] == "Tecnico" ? "Técnico" : $_SESSION["rol"]); ?></strong>
                    <span><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                </div>
            </a>
        </header>

        <main class="contenido-equipos">

            <section class="panel panel-equipos">
                <div class="titulo-equipos">
                    <span id="cantidadEquipos">
                        <?php echo $cantidadEquiposTecnico; ?>
                        <?php echo $cantidadEquiposTecnico === 1 ? "equipo" : "equipos"; ?>
                    </span>
                </div>

                <div class="grid-laboratorios" id="gridLaboratorios"></div>
            </section>

        </main>

        <!-- DETALLE DE EQUIPO -->
        <div class="modal-fondo" id="modalEquipo">
            <section class="modal-informe modal-equipo" role="dialog" aria-modal="true" aria-labelledby="tituloEquipo">
                <div class="modal-cabecera modal-cabecera-equipo">
                    <h2 id="tituloEquipo">
                        <span id="equipoCodigo"></span>
                        <small>(<span id="equipoLaboratorio"></span>)</small>
                    </h2>
                    <button type="button" class="cerrar-x" data-cerrar="modalEquipo" aria-label="Cerrar">×</button>
                </div>

                <div class="cuerpo-detalle-equipo">
                    <p class="resumen-detalle-equipo" id="equipoResumen"></p>

                    <section class="componentes-detalle-equipo" aria-label="Componentes del equipo">
                        <div class="grid-componentes-detalle" id="equipoComponentes"></div>
                    </section>

                    <section class="historial-detalle-equipo" aria-labelledby="tituloHistorialEquipo">
                        <h3 id="tituloHistorialEquipo" class="visually-hidden">Historial de cambios</h3>

                        <div class="tabla-historial-equipo">
                            <div class="cabecera-historial-equipo" aria-hidden="true">
                                <span>Fecha</span>
                                <span>Componente</span>
                                <span>Cambio</span>
                                <span>Técnico</span>
                            </div>
                            <div id="equipoHistorial"></div>
                        </div>
                    </section>

                    <div class="acciones-detalle-equipo">
                        <a
                            class="boton-anadir-cambio"
                            href="<?php echo BASE_URL; ?>/app/controladores/TecnicoController.php?pagina=mis_tickets"
                            title="Ir a Mis tickets para registrar el trabajo realizado">
                            Añadir cambio
                        </a>
                    </div>
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

        const laboratorios = <?php echo json_encode($laboratorios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const gridLaboratorios = document.getElementById("gridLaboratorios");
        const modalEquipo = document.getElementById("modalEquipo");

        function claseEstadoEquipo(estado) {
            if (estado === "Correcto") return "equipo-correcto";
            if (estado === "En reparación") return "equipo-reparacion";
            return "equipo-incidencia";
        }

        function renderLaboratorios() {
            gridLaboratorios.innerHTML = "";

            laboratorios.forEach(function (laboratorio) {
                const bloque = document.createElement("section");
                bloque.className = "laboratorio";

                const titulo = document.createElement("h3");
                titulo.textContent = laboratorio.nombre;
                bloque.appendChild(titulo);

                const grid = document.createElement("div");
                grid.className = "grid-equipos";

                laboratorio.equipos.forEach(function (equipo) {
                    const boton = document.createElement("button");
                    boton.type = "button";
                    boton.className = "equipo " + claseEstadoEquipo(equipo.estado);
                    const codigo = document.createElement("strong");
                    codigo.textContent = equipo.codigo;
                    const estado = document.createElement("span");
                    estado.textContent = equipo.estado;
                    boton.append(codigo, estado);

                    boton.addEventListener("click", function () {
                        abrirEquipo(laboratorio.nombre, equipo);
                    });

                    grid.appendChild(boton);
                });

                bloque.appendChild(grid);
                gridLaboratorios.appendChild(bloque);
            });
        }

        function abrirEquipo(laboratorio, equipo) {
            document.getElementById("equipoCodigo").textContent = equipo.codigo;
            document.getElementById("equipoLaboratorio").textContent = laboratorio;
            document.getElementById("equipoResumen").textContent = [
                equipo.tipo,
                equipo.estado,
                equipo.serie ? "Serie: " + equipo.serie : "Sin número de serie"
            ].join(" · ");
            renderComponentesEquipo(equipo.componentes || []);
            renderHistorialEquipo(equipo.historial || []);

            modalEquipo.classList.add("mostrar");
        }

        function renderComponentesEquipo(componentes) {
            const contenedor = document.getElementById("equipoComponentes");
            const componentesPorTipo = new Map(
                componentes.map(function (componente) {
                    return [componente.tipo, componente];
                })
            );

            contenedor.replaceChildren();

            ["Monitor", "Teclado", "Torre", "Mouse"].forEach(function (tipo) {
                const componente = componentesPorTipo.get(tipo);
                const tarjeta = document.createElement("article");
                const titulo = document.createElement("h4");
                const detalle = document.createElement("p");
                const estado = document.createElement("span");

                titulo.textContent = tipo;
                detalle.textContent = componente
                    ? [componente.marca, componente.serie].filter(Boolean).join(" · ")
                    : "Sin registrar";
                estado.textContent = componente ? componente.estado : "Sin datos";
                estado.className = "estado-componente estado-componente-" +
                    (componente ? componente.estado.toLowerCase().replaceAll("ñ", "n").replaceAll(" ", "-") : "sin-datos");

                tarjeta.append(titulo, detalle, estado);
                contenedor.appendChild(tarjeta);
            });
        }

        function renderHistorialEquipo(historial) {
            const contenedor = document.getElementById("equipoHistorial");
            contenedor.replaceChildren();

            if (!historial.length) {
                const vacio = document.createElement("p");
                vacio.className = "historial-equipo-vacio";
                vacio.textContent = "Este equipo todavía no tiene cambios registrados.";
                contenedor.appendChild(vacio);
                return;
            }

            historial.forEach(function (registro) {
                const fila = document.createElement("div");
                fila.className = "fila-historial-equipo";

                [registro.fecha, registro.componente, registro.cambio, registro.tecnico].forEach(function (valor) {
                    const celda = document.createElement("span");
                    celda.textContent = valor || "—";
                    fila.appendChild(celda);
                });

                contenedor.appendChild(fila);
            });
        }

        document.querySelectorAll("[data-cerrar]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.cerrar).classList.remove("mostrar");
            });
        });

        modalEquipo.addEventListener("click", function (evento) {
            if (evento.target === modalEquipo) {
                modalEquipo.classList.remove("mostrar");
            }
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                modalEquipo.classList.remove("mostrar");
            }
        });

        renderLaboratorios();
    </script>

</body>
</html>
