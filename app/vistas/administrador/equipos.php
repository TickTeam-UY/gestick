<?php

function escaparEquiposAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function claseEstadoEquipoAdministrador(string $estado): string
{
    $normalizado = strtolower(str_replace(
        ["á", "é", "í", "ó", "ú", "ñ", " "],
        ["a", "e", "i", "o", "u", "n", "-"],
        $estado
    ));

    return preg_replace("/[^a-z0-9-]/", "", $normalizado) ?: "desconocido";
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$urlEquiposAdministrador = $urlAdministrador . "?pagina=equipos";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Administración de equipos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
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
            <a class="activo" href="<?php echo $urlEquiposAdministrador; ?>"><span>Equipos</span></a>
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
        <header class="encabezado-admin encabezado-equipos-admin">
            <div class="presentacion-admin">
                <h1>Equipos</h1>
                <p>Administra los laboratorios, salones y equipos visibles para el personal técnico.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparEquiposAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparEquiposAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-equipos-admin">
            <?php if ($mensajeAdministrador): ?>
                <div class="mensaje-admin mensaje-<?php echo escaparEquiposAdministrador($mensajeAdministrador["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeAdministrador["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparEquiposAdministrador($mensajeAdministrador["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorEquipos): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar el inventario. Comprueba que Workbench continúe conectado.
                </div>
            <?php endif; ?>

            <section class="barra-gestion-equipos-admin" aria-labelledby="tituloGestionEquipos">
                <div>
                    <span class="sobrelinea-panel">Inventario compartido</span>
                    <h2 id="tituloGestionEquipos">Laboratorios y salones</h2>
                    <p><?php echo escaparEquiposAdministrador($cantidadEquipos); ?> equipos registrados</p>
                </div>

                <div class="acciones-gestion-equipos-admin">
                    <button class="boton-admin boton-admin-secundario" type="button" data-abrir-dialog="dialogNuevaUbicacion">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Nuevo laboratorio/salón
                    </button>
                    <button class="boton-admin boton-admin-principal" type="button" data-nuevo-equipo data-ubicacion="">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Nuevo equipo
                    </button>
                </div>
            </section>

            <section class="grid-ubicaciones-admin" aria-label="Laboratorios, salones y equipos">
                <?php if (!$laboratorios && !$errorEquipos): ?>
                    <div class="estado-vacio-ubicaciones-admin">
                        <i class="bi bi-building" aria-hidden="true"></i>
                        <h2>Todavía no hay laboratorios o salones</h2>
                        <p>Agrega la primera ubicación para comenzar a organizar los equipos.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($laboratorios as $laboratorio): ?>
                    <article class="ubicacion-admin<?php echo $laboratorio["es_sin_ubicacion"] ? " ubicacion-sin-asignar" : ""; ?>">
                        <header class="cabecera-ubicacion-admin">
                            <div>
                                <h2><?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?></h2>
                                <span>
                                    <?php echo count($laboratorio["equipos"]); ?>
                                    <?php echo count($laboratorio["equipos"]) === 1 ? "equipo" : "equipos"; ?>
                                </span>
                            </div>

                            <div class="acciones-ubicacion-admin">
                                <?php if (!$laboratorio["es_sin_ubicacion"]): ?>
                                    <button
                                        type="button"
                                        class="boton-icono-admin"
                                        data-editar-ubicacion
                                        data-id="<?php echo escaparEquiposAdministrador($laboratorio["id"]); ?>"
                                        data-nombre="<?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?>"
                                        aria-label="Editar <?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?>">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </button>

                                    <form method="POST" action="<?php echo $urlEquiposAdministrador; ?>" data-confirmar="¿Eliminar esta ubicación? Solo será posible si no tiene equipos ni planillas asociadas.">
                                        <input type="hidden" name="csrf_token" value="<?php echo escaparEquiposAdministrador($csrfToken); ?>">
                                        <input type="hidden" name="accion" value="eliminar_ubicacion">
                                        <input type="hidden" name="id_ubicacion" value="<?php echo escaparEquiposAdministrador($laboratorio["id"]); ?>">
                                        <button type="submit" class="boton-icono-admin boton-peligro-admin" aria-label="Eliminar <?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?>">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <button
                                    type="button"
                                    class="boton-icono-admin boton-agregar-equipo-admin"
                                    data-nuevo-equipo
                                    data-ubicacion="<?php echo escaparEquiposAdministrador($laboratorio["id"] ?? ""); ?>"
                                    aria-label="Agregar equipo en <?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?>">
                                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </header>

                        <div class="grid-tarjetas-equipo-admin">
                            <?php if (!$laboratorio["equipos"]): ?>
                                <div class="ubicacion-vacia-admin">
                                    <i class="bi bi-pc-display" aria-hidden="true"></i>
                                    <span>Sin equipos asignados</span>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($laboratorio["equipos"] as $equipo): ?>
                                <button
                                    type="button"
                                    class="tarjeta-equipo-admin estado-equipo-<?php echo claseEstadoEquipoAdministrador($equipo["estado"]); ?>"
                                    data-ver-equipo
                                    data-id="<?php echo escaparEquiposAdministrador($equipo["id"]); ?>"
                                    data-codigo="<?php echo escaparEquiposAdministrador($equipo["codigo"]); ?>"
                                    data-serie="<?php echo escaparEquiposAdministrador($equipo["serie"]); ?>"
                                    data-modelo="<?php echo escaparEquiposAdministrador($equipo["modelo"]); ?>"
                                    data-tipo="<?php echo escaparEquiposAdministrador($equipo["id_tipo"]); ?>"
                                    data-estado="<?php echo escaparEquiposAdministrador($equipo["id_estado"]); ?>"
                                    data-ubicacion="<?php echo escaparEquiposAdministrador($equipo["id_ubicacion"] ?? ""); ?>"
                                    data-prestable="<?php echo $equipo["prestable"] ? "1" : "0"; ?>">
                                    <strong><?php echo escaparEquiposAdministrador($equipo["codigo"]); ?></strong>
                                    <span><?php echo escaparEquiposAdministrador($equipo["tipo"]); ?></span>
                                    <small><?php echo escaparEquiposAdministrador($equipo["estado"]); ?></small>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>

        <footer class="footer-admin">
            <p>GesTIck · Sistema de gestión de recursos y soporte de informática</p>
        </footer>
    </div>

    <dialog class="dialog-admin dialog-detalle-equipo-admin" id="dialogDetalleEquipo">
        <header class="cabecera-dialog-admin cabecera-detalle-equipo-admin">
            <div>
                <span class="sobrelinea-panel">Inventario</span>
                <h2 id="detalleEquipoTitulo"></h2>
            </div>
            <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
        </header>

        <div class="cuerpo-detalle-equipo-admin">
            <p class="resumen-detalle-equipo-admin" id="detalleEquipoResumen"></p>

            <section class="componentes-detalle-equipo-admin" aria-label="Componentes del equipo">
                <div class="grid-componentes-detalle-admin" id="detalleEquipoComponentes"></div>
            </section>

            <section class="historial-detalle-equipo-admin" aria-labelledby="tituloHistorialEquipoAdmin">
                <h3 id="tituloHistorialEquipoAdmin" class="texto-oculto-admin">Historial de cambios</h3>

                <div class="tabla-historial-equipo-admin">
                    <div class="cabecera-historial-equipo-admin" aria-hidden="true">
                        <span>Fecha</span>
                        <span>Componente</span>
                        <span>Cambio</span>
                        <span>Técnico</span>
                    </div>
                    <div id="detalleEquipoHistorial"></div>
                </div>
            </section>
        </div>

        <footer class="acciones-dialog-admin acciones-detalle-equipo-admin">
            <a class="boton-admin boton-admin-secundario" id="detalleEquipoTickets" href="#">
                Ver tickets
            </a>
            <button class="boton-admin boton-admin-principal" id="modificarEquipoDetalle" type="button">
                Modificar equipo
            </button>
        </footer>
    </dialog>

    <dialog class="dialog-admin" id="dialogNuevaUbicacion">
        <form method="POST" action="<?php echo $urlEquiposAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparEquiposAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="crear_ubicacion">

            <header class="cabecera-dialog-admin">
                <div>
                    <span class="sobrelinea-panel">Nueva ubicación</span>
                    <h2>Agregar laboratorio o salón</h2>
                </div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin">
                <label class="campo-dialog-admin campo-completo-dialog">
                    <span>Nombre</span>
                    <input type="text" name="nombre_ubicacion" maxlength="60" placeholder="Ej.: Laboratorio 6 o Salón multimedia" required>
                </label>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Agregar ubicación</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-admin" id="dialogEditarUbicacion">
        <form method="POST" action="<?php echo $urlEquiposAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparEquiposAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="actualizar_ubicacion">
            <input type="hidden" name="id_ubicacion" id="editarUbicacionId">

            <header class="cabecera-dialog-admin">
                <div>
                    <span class="sobrelinea-panel">Editar ubicación</span>
                    <h2>Modificar laboratorio o salón</h2>
                </div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin">
                <label class="campo-dialog-admin campo-completo-dialog">
                    <span>Nombre</span>
                    <input type="text" name="nombre_ubicacion" id="editarUbicacionNombre" maxlength="60" required>
                </label>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Guardar cambios</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-admin dialog-equipo-admin" id="dialogNuevoEquipo">
        <form method="POST" action="<?php echo $urlEquiposAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparEquiposAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="crear_equipo">

            <header class="cabecera-dialog-admin">
                <div>
                    <span class="sobrelinea-panel">Inventario</span>
                    <h2>Agregar equipo</h2>
                </div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin grid-formulario-equipo-admin">
                <label class="campo-dialog-admin">
                    <span>Código</span>
                    <input type="text" name="codigo" maxlength="30" placeholder="Ej.: LAB1-PC05" required>
                </label>
                <label class="campo-dialog-admin">
                    <span>N.º de serie</span>
                    <input type="text" name="numero_serie" maxlength="80">
                </label>
                <label class="campo-dialog-admin">
                    <span>Modelo</span>
                    <input type="text" name="modelo" maxlength="80">
                </label>
                <label class="campo-dialog-admin">
                    <span>Tipo</span>
                    <select name="id_tipo_equipo" required>
                        <?php foreach ($tiposEquipo as $tipo): ?>
                            <option value="<?php echo escaparEquiposAdministrador($tipo["id"]); ?>"><?php echo escaparEquiposAdministrador($tipo["nombre"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-dialog-admin">
                    <span>Estado</span>
                    <select name="id_estado_equipo" required>
                        <?php foreach ($estadosEquipo as $estado): ?>
                            <option value="<?php echo escaparEquiposAdministrador($estado["id"]); ?>"><?php echo escaparEquiposAdministrador($estado["nombre"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-dialog-admin">
                    <span>Laboratorio o salón</span>
                    <select name="id_ubicacion" id="nuevoEquipoUbicacion">
                        <option value="">Sin ubicación asignada</option>
                        <?php foreach ($laboratorios as $laboratorio): ?>
                            <?php if (!$laboratorio["es_sin_ubicacion"]): ?>
                                <option value="<?php echo escaparEquiposAdministrador($laboratorio["id"]); ?>"><?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-check-dialog campo-completo-dialog">
                    <input type="checkbox" name="es_prestable" value="1">
                    <span>Este equipo puede asignarse a préstamos</span>
                </label>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Agregar equipo</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-admin dialog-equipo-admin" id="dialogEditarEquipo">
        <form method="POST" action="<?php echo $urlEquiposAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparEquiposAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="actualizar_equipo">
            <input type="hidden" name="id_equipo" id="editarEquipoId">

            <header class="cabecera-dialog-admin">
                <div>
                    <span class="sobrelinea-panel">Inventario</span>
                    <h2>Modificar equipo</h2>
                </div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin grid-formulario-equipo-admin">
                <label class="campo-dialog-admin">
                    <span>Código</span>
                    <input type="text" name="codigo" id="editarEquipoCodigo" maxlength="30" required>
                </label>
                <label class="campo-dialog-admin">
                    <span>N.º de serie</span>
                    <input type="text" name="numero_serie" id="editarEquipoSerie" maxlength="80">
                </label>
                <label class="campo-dialog-admin">
                    <span>Modelo</span>
                    <input type="text" name="modelo" id="editarEquipoModelo" maxlength="80">
                </label>
                <label class="campo-dialog-admin">
                    <span>Tipo</span>
                    <select name="id_tipo_equipo" id="editarEquipoTipo" required>
                        <?php foreach ($tiposEquipo as $tipo): ?>
                            <option value="<?php echo escaparEquiposAdministrador($tipo["id"]); ?>"><?php echo escaparEquiposAdministrador($tipo["nombre"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-dialog-admin">
                    <span>Estado</span>
                    <select name="id_estado_equipo" id="editarEquipoEstado" required>
                        <?php foreach ($estadosEquipo as $estado): ?>
                            <option value="<?php echo escaparEquiposAdministrador($estado["id"]); ?>"><?php echo escaparEquiposAdministrador($estado["nombre"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-dialog-admin">
                    <span>Laboratorio o salón</span>
                    <select name="id_ubicacion" id="editarEquipoUbicacion">
                        <option value="">Sin ubicación asignada</option>
                        <?php foreach ($laboratorios as $laboratorio): ?>
                            <?php if (!$laboratorio["es_sin_ubicacion"]): ?>
                                <option value="<?php echo escaparEquiposAdministrador($laboratorio["id"]); ?>"><?php echo escaparEquiposAdministrador($laboratorio["nombre"]); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-check-dialog campo-completo-dialog">
                    <input type="checkbox" name="es_prestable" value="1" id="editarEquipoPrestable">
                    <span>Este equipo puede asignarse a préstamos</span>
                </label>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Guardar cambios</button>
            </footer>
        </form>

        <form method="POST" action="<?php echo $urlEquiposAdministrador; ?>" class="zona-eliminar-equipo-admin" data-confirmar="¿Eliminar este equipo? Solo será posible si no tiene historial asociado.">
            <input type="hidden" name="csrf_token" value="<?php echo escaparEquiposAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="eliminar_equipo">
            <input type="hidden" name="id_equipo" id="eliminarEquipoId">
            <button type="submit" class="boton-eliminar-equipo-admin">
                <i class="bi bi-trash3" aria-hidden="true"></i>
                Eliminar equipo
            </button>
        </form>
    </dialog>

    <script>
        const botonMenuAdmin = document.getElementById("botonMenuAdmin");
        const menuAdmin = document.getElementById("menuAdmin");
        const fondoMenuAdmin = document.getElementById("fondoMenuAdmin");
        const inventarioEquiposAdmin = <?php echo json_encode($laboratorios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const urlTicketsAdministrador = <?php echo json_encode($urlAdministrador . "?pagina=tickets&buscar=", JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

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

        document.querySelectorAll("[data-abrir-dialog]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.abrirDialog).showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-dialog]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                boton.closest("dialog").close();
            });
        });

        const dialogNuevoEquipo = document.getElementById("dialogNuevoEquipo");
        const nuevoEquipoUbicacion = document.getElementById("nuevoEquipoUbicacion");

        document.querySelectorAll("[data-nuevo-equipo]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                dialogNuevoEquipo.querySelector("form").reset();
                nuevoEquipoUbicacion.value = boton.dataset.ubicacion || "";
                dialogNuevoEquipo.showModal();
            });
        });

        const dialogEditarUbicacion = document.getElementById("dialogEditarUbicacion");

        document.querySelectorAll("[data-editar-ubicacion]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById("editarUbicacionId").value = boton.dataset.id;
                document.getElementById("editarUbicacionNombre").value = boton.dataset.nombre;
                dialogEditarUbicacion.showModal();
            });
        });

        const dialogDetalleEquipo = document.getElementById("dialogDetalleEquipo");
        const dialogEditarEquipo = document.getElementById("dialogEditarEquipo");
        let botonEquipoSeleccionado = null;

        function buscarEquipoAdministrador(idEquipo) {
            for (const laboratorio of inventarioEquiposAdmin) {
                const equipo = laboratorio.equipos.find(function (elemento) {
                    return String(elemento.id) === String(idEquipo);
                });

                if (equipo) {
                    return { laboratorio, equipo };
                }
            }

            return null;
        }

        function renderComponentesEquipoAdministrador(componentes) {
            const contenedor = document.getElementById("detalleEquipoComponentes");
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
                estado.className = "estado-componente-admin estado-componente-admin-" +
                    (componente ? componente.estado.toLowerCase().replaceAll("ñ", "n").replaceAll(" ", "-") : "sin-datos");

                tarjeta.append(titulo, detalle, estado);
                contenedor.appendChild(tarjeta);
            });
        }

        function renderHistorialEquipoAdministrador(historial) {
            const contenedor = document.getElementById("detalleEquipoHistorial");
            contenedor.replaceChildren();

            if (!historial.length) {
                const vacio = document.createElement("p");
                vacio.className = "historial-equipo-vacio-admin";
                vacio.textContent = "Este equipo todavía no tiene cambios registrados.";
                contenedor.appendChild(vacio);
                return;
            }

            historial.forEach(function (registro) {
                const fila = document.createElement("div");
                fila.className = "fila-historial-equipo-admin";

                [registro.fecha, registro.componente, registro.cambio, registro.tecnico].forEach(function (valor) {
                    const celda = document.createElement("span");
                    celda.textContent = valor || "—";
                    fila.appendChild(celda);
                });

                contenedor.appendChild(fila);
            });
        }

        function abrirDetalleEquipoAdministrador(boton) {
            const resultado = buscarEquipoAdministrador(boton.dataset.id);

            if (!resultado) {
                return;
            }

            botonEquipoSeleccionado = boton;
            document.getElementById("detalleEquipoTitulo").textContent =
                resultado.equipo.codigo + " (" + resultado.laboratorio.nombre + ")";
            document.getElementById("detalleEquipoResumen").textContent = [
                resultado.equipo.tipo,
                resultado.equipo.estado,
                resultado.equipo.serie ? "Serie: " + resultado.equipo.serie : "Sin número de serie",
                resultado.equipo.prestable ? "Prestable" : "No prestable"
            ].join(" · ");
            document.getElementById("detalleEquipoTickets").href =
                urlTicketsAdministrador + encodeURIComponent(resultado.equipo.codigo);
            renderComponentesEquipoAdministrador(resultado.equipo.componentes || []);
            renderHistorialEquipoAdministrador(resultado.equipo.historial || []);
            dialogDetalleEquipo.showModal();
        }

        function abrirEdicionEquipoAdministrador(boton) {
            document.getElementById("editarEquipoId").value = boton.dataset.id;
            document.getElementById("eliminarEquipoId").value = boton.dataset.id;
            document.getElementById("editarEquipoCodigo").value = boton.dataset.codigo;
            document.getElementById("editarEquipoSerie").value = boton.dataset.serie;
            document.getElementById("editarEquipoModelo").value = boton.dataset.modelo;
            document.getElementById("editarEquipoTipo").value = boton.dataset.tipo;
            document.getElementById("editarEquipoEstado").value = boton.dataset.estado;
            document.getElementById("editarEquipoUbicacion").value = boton.dataset.ubicacion;
            document.getElementById("editarEquipoPrestable").checked = boton.dataset.prestable === "1";
            dialogEditarEquipo.showModal();
        }

        document.querySelectorAll("[data-ver-equipo]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                abrirDetalleEquipoAdministrador(boton);
            });
        });

        document.getElementById("modificarEquipoDetalle").addEventListener("click", function () {
            if (!botonEquipoSeleccionado) {
                return;
            }

            dialogDetalleEquipo.close();
            abrirEdicionEquipoAdministrador(botonEquipoSeleccionado);
        });

        document.querySelectorAll("[data-confirmar]").forEach(function (formulario) {
            formulario.addEventListener("submit", function (evento) {
                if (!window.confirm(formulario.dataset.confirmar)) {
                    evento.preventDefault();
                }
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
