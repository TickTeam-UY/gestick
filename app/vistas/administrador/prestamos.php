<?php

function escaparPrestamosAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function fechaPrestamosAdministrador(?string $fecha): string
{
    if (!$fecha) {
        return "—";
    }

    $valor = DateTimeImmutable::createFromFormat("Y-m-d", $fecha);

    return $valor ? $valor->format("d/m/Y") : $fecha;
}

function claseEstadoPrestamosAdministrador(string $estado): string
{
    return match ($estado) {
        "Activo" => "estado-prestamo-activo",
        "Atrasado" => "estado-prestamo-atrasado",
        "Devuelto" => "estado-prestamo-devuelto",
        default => "estado-prestamo-neutro"
    };
}

function urlPaginaPrestamosAdministrador(int $pagina, array $filtros): string
{
    $parametros = ["pagina" => "prestamos"];

    if (($filtros["buscar"] ?? "") !== "") {
        $parametros["buscar"] = $filtros["buscar"];
    }

    if ((int) ($filtros["estado"] ?? 0) > 0) {
        $parametros["estado"] = (int) $filtros["estado"];
    }

    if ($pagina > 1) {
        $parametros["p"] = $pagina;
    }

    return BASE_URL . "/app/controladores/AdministradorController.php?" . http_build_query($parametros);
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$urlPrestamosAdministrador = $urlAdministrador . "?pagina=prestamos";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
$fechaHoyPrestamo = (new DateTimeImmutable("today"))->format("Y-m-d");
$equiposDisponiblesJson = json_encode(
    $equiposDisponiblesPrestamosAdministrador,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?: "[]";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Administración de préstamos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-prestamos.css?v=20260822-1">
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
            <a href="<?php echo $urlAdministrador; ?>?pagina=equipos"><span>Equipos</span></a>
            <a class="activo" href="<?php echo $urlPrestamosAdministrador; ?>"><span>Préstamos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=usuarios"><span>Usuarios</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=metricas"><span>Métricas</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=mi_perfil"><span>Mi perfil</span></a>
        </nav>

        <a class="cerrar-sesion-admin" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina-admin">
        <header class="encabezado-admin">
            <div class="presentacion-admin">
                <h1>Préstamos</h1>
                <p>Registra la entrega y devolución de equipos solicitados por los alumnos.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparPrestamosAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparPrestamosAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-prestamos-admin">
            <?php if ($mensajeAdministrador): ?>
                <div class="mensaje-admin mensaje-<?php echo escaparPrestamosAdministrador($mensajeAdministrador["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeAdministrador["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparPrestamosAdministrador($mensajeAdministrador["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorPrestamosAdministrador): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar los préstamos. Comprueba que la conexión con la base de datos siga disponible.
                </div>
            <?php endif; ?>

            <section class="resumen-prestamos-admin" aria-label="Resumen de préstamos">
                <article>
                    <span class="icono-resumen-prestamo resumen-activos-prestamo"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparPrestamosAdministrador($resumenPrestamosAdministrador["activos"]); ?></strong><span>Préstamos activos</span></div>
                </article>
                <article>
                    <span class="icono-resumen-prestamo resumen-atrasados-prestamo"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparPrestamosAdministrador($resumenPrestamosAdministrador["atrasados"]); ?></strong><span>Préstamos atrasados</span></div>
                </article>
                <article>
                    <span class="icono-resumen-prestamo resumen-equipos-prestamo"><i class="bi bi-laptop" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparPrestamosAdministrador($resumenPrestamosAdministrador["equipos_prestados"]); ?></strong><span>Equipos entregados</span></div>
                </article>
                <article>
                    <span class="icono-resumen-prestamo resumen-devueltos-prestamo"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparPrestamosAdministrador($resumenPrestamosAdministrador["devueltos"]); ?></strong><span>Préstamos devueltos</span></div>
                </article>
            </section>

            <section class="panel-prestamos-admin" aria-labelledby="tituloPanelPrestamos">
                <header class="cabecera-panel-prestamos-admin">
                    <div>
                        <span class="sobrelinea-panel">Control de inventario</span>
                        <h2 id="tituloPanelPrestamos">Préstamos a alumnos</h2>
                        <p><?php echo escaparPrestamosAdministrador($totalPrestamosAdministrador); ?> registros encontrados</p>
                    </div>

                    <button class="boton-admin boton-admin-principal" id="abrirNuevoPrestamo" type="button">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Nuevo préstamo
                    </button>
                </header>

                <form class="filtros-prestamos-admin" method="GET" action="<?php echo $urlAdministrador; ?>">
                    <input type="hidden" name="pagina" value="prestamos">

                    <label class="buscador-prestamos-admin">
                        <span class="solo-lector">Buscar préstamos</span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input
                            type="search"
                            name="buscar"
                            maxlength="100"
                            value="<?php echo escaparPrestamosAdministrador($filtrosPrestamos["buscar"]); ?>"
                            placeholder="Buscar por alumno, grupo, equipo o ID">
                    </label>

                    <label>
                        <span class="solo-lector">Filtrar por estado</span>
                        <select name="estado">
                            <option value="0">Todos los estados</option>
                            <?php foreach ($estadosPrestamosAdministrador as $estadoPrestamo): ?>
                                <option
                                    value="<?php echo escaparPrestamosAdministrador($estadoPrestamo["id"]); ?>"
                                    <?php echo (int) $filtrosPrestamos["estado"] === (int) $estadoPrestamo["id"] ? " selected" : ""; ?>>
                                    <?php echo escaparPrestamosAdministrador($estadoPrestamo["nombre"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <button class="boton-filtro-prestamos" type="submit">Aplicar filtros</button>

                    <?php if ($filtrosPrestamos["buscar"] !== "" || (int) $filtrosPrestamos["estado"] > 0): ?>
                        <a class="limpiar-filtros-prestamos" href="<?php echo $urlPrestamosAdministrador; ?>">Limpiar</a>
                    <?php endif; ?>
                </form>

                <div class="tabla-prestamos-contenedor">
                    <table class="tabla-prestamos-admin">
                        <thead>
                            <tr>
                                <th>Préstamo</th>
                                <th>Alumno</th>
                                <th>Fecha de entrega</th>
                                <th>Equipos</th>
                                <th>Estado</th>
                                <th>Devolución</th>
                                <th><span class="solo-lector">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prestamosAdministrador as $prestamo): ?>
                                <?php
                                $datosPrestamo = [
                                    "id" => (int) $prestamo["id_prestamo"],
                                    "id_alumno" => (int) $prestamo["id_alumno"],
                                    "alumno" => $prestamo["alumno"],
                                    "grupo" => $prestamo["grupo"],
                                    "fecha_prestamo" => $prestamo["fecha_prestamo"],
                                    "fecha_devolucion" => $prestamo["fecha_devolucion"],
                                    "estado" => $prestamo["estado"],
                                    "equipos" => $prestamo["equipos"]
                                ];
                                $datosJson = json_encode(
                                    $datosPrestamo,
                                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                ) ?: "{}";
                                ?>
                                <tr>
                                    <td data-label="Préstamo"><strong class="numero-prestamo-admin">#<?php echo escaparPrestamosAdministrador($prestamo["id_prestamo"]); ?></strong></td>
                                    <td data-label="Alumno">
                                        <div class="alumno-prestamo-admin">
                                            <strong><?php echo escaparPrestamosAdministrador($prestamo["alumno"]); ?></strong>
                                            <span><?php echo escaparPrestamosAdministrador($prestamo["grupo"]); ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Entrega"><?php echo escaparPrestamosAdministrador(fechaPrestamosAdministrador($prestamo["fecha_prestamo"])); ?></td>
                                    <td data-label="Equipos">
                                        <div class="equipos-prestamo-admin">
                                            <?php foreach (array_slice($prestamo["equipos"], 0, 2) as $equipo): ?>
                                                <span><?php echo escaparPrestamosAdministrador($equipo["codigo"]); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($prestamo["equipos"]) > 2): ?>
                                                <span>+<?php echo count($prestamo["equipos"]) - 2; ?></span>
                                            <?php endif; ?>
                                            <?php if (!$prestamo["equipos"]): ?><small>Sin equipos</small><?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Estado">
                                        <span class="estado-prestamo-admin <?php echo claseEstadoPrestamosAdministrador($prestamo["estado"]); ?>">
                                            <i aria-hidden="true"></i>
                                            <?php echo escaparPrestamosAdministrador($prestamo["estado"]); ?>
                                        </span>
                                    </td>
                                    <td data-label="Devolución"><?php echo escaparPrestamosAdministrador(fechaPrestamosAdministrador($prestamo["fecha_devolucion"])); ?></td>
                                    <td data-label="Acciones">
                                        <div class="acciones-prestamo-admin">
                                            <button
                                                class="boton-accion-prestamo"
                                                type="button"
                                                data-ver-prestamo="<?php echo escaparPrestamosAdministrador($datosJson); ?>"
                                                aria-label="Ver detalle del préstamo <?php echo escaparPrestamosAdministrador($prestamo["id_prestamo"]); ?>"
                                                title="Ver detalle">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </button>

                                            <?php if ($prestamo["estado"] !== "Devuelto"): ?>
                                                <button
                                                    class="boton-accion-prestamo"
                                                    type="button"
                                                    data-editar-prestamo="<?php echo escaparPrestamosAdministrador($datosJson); ?>"
                                                    aria-label="Editar el préstamo <?php echo escaparPrestamosAdministrador($prestamo["id_prestamo"]); ?>"
                                                    title="Editar préstamo">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                                </button>

                                                <?php if ($prestamo["estado"] === "Activo"): ?>
                                                    <form method="POST" action="<?php echo $urlPrestamosAdministrador; ?>" data-confirmar="¿Marcar este préstamo como atrasado?">
                                                        <input type="hidden" name="csrf_token" value="<?php echo escaparPrestamosAdministrador($csrfToken); ?>">
                                                        <input type="hidden" name="accion" value="marcar_atrasado">
                                                        <input type="hidden" name="id_prestamo" value="<?php echo escaparPrestamosAdministrador($prestamo["id_prestamo"]); ?>">
                                                        <button class="boton-accion-prestamo boton-atrasar-prestamo" type="submit" aria-label="Marcar préstamo como atrasado" title="Marcar como atrasado">
                                                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <button
                                                    class="boton-accion-prestamo boton-devolver-prestamo"
                                                    type="button"
                                                    data-devolver-prestamo="<?php echo escaparPrestamosAdministrador($datosJson); ?>"
                                                    aria-label="Registrar devolución del préstamo <?php echo escaparPrestamosAdministrador($prestamo["id_prestamo"]); ?>"
                                                    title="Registrar devolución">
                                                    <i class="bi bi-box-arrow-in-left" aria-hidden="true"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (!$prestamosAdministrador && !$errorPrestamosAdministrador): ?>
                        <div class="estado-vacio-prestamos-admin">
                            <i class="bi bi-inboxes" aria-hidden="true"></i>
                            <h3>No hay préstamos para mostrar</h3>
                            <p>Prueba con otros filtros o registra un préstamo nuevo.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($totalPaginasPrestamosAdministrador > 1): ?>
                    <nav class="paginacion-prestamos-admin" aria-label="Paginación de préstamos">
                        <?php if ($paginaActualPrestamosAdministrador > 1): ?>
                            <a href="<?php echo escaparPrestamosAdministrador(urlPaginaPrestamosAdministrador($paginaActualPrestamosAdministrador - 1, $filtrosPrestamos)); ?>"><i class="bi bi-chevron-left" aria-hidden="true"></i> Anterior</a>
                        <?php else: ?>
                            <span class="pagina-deshabilitada"><i class="bi bi-chevron-left" aria-hidden="true"></i> Anterior</span>
                        <?php endif; ?>

                        <span>Página <?php echo escaparPrestamosAdministrador($paginaActualPrestamosAdministrador); ?> de <?php echo escaparPrestamosAdministrador($totalPaginasPrestamosAdministrador); ?></span>

                        <?php if ($paginaActualPrestamosAdministrador < $totalPaginasPrestamosAdministrador): ?>
                            <a href="<?php echo escaparPrestamosAdministrador(urlPaginaPrestamosAdministrador($paginaActualPrestamosAdministrador + 1, $filtrosPrestamos)); ?>">Siguiente <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <?php else: ?>
                            <span class="pagina-deshabilitada">Siguiente <i class="bi bi-chevron-right" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>
        </main>

        <footer class="footer-admin">
            <p>GesTIck · Sistema de gestión de recursos y soporte de informática</p>
        </footer>
    </div>

    <dialog class="dialog-admin dialog-prestamo-admin" id="dialogNuevoPrestamo">
        <form method="POST" action="<?php echo $urlPrestamosAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparPrestamosAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="crear_prestamo">

            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Entrega de inventario</span><h2>Nuevo préstamo</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin cuerpo-prestamo-dialog">
                <div class="grid-datos-prestamo-dialog">
                    <label class="campo-dialog-admin">
                        <span>Alumno</span>
                        <select name="id_alumno" required>
                            <option value="">Seleccionar alumno</option>
                            <?php foreach ($alumnosPrestamosAdministrador as $alumno): ?>
                                <option value="<?php echo escaparPrestamosAdministrador($alumno["id"]); ?>">
                                    <?php echo escaparPrestamosAdministrador($alumno["apellido"] . ", " . $alumno["nombre"] . " · " . $alumno["grupo"] . " · ID " . $alumno["id"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="campo-dialog-admin">
                        <span>Fecha de préstamo</span>
                        <input type="date" name="fecha_prestamo" value="<?php echo $fechaHoyPrestamo; ?>" max="<?php echo $fechaHoyPrestamo; ?>" required>
                    </label>
                </div>

                <fieldset class="selector-equipos-prestamo-admin">
                    <legend>Equipos disponibles</legend>
                    <p>Selecciona uno o varios equipos prestables.</p>
                    <div class="grid-selector-equipos-admin">
                        <?php foreach ($equiposDisponiblesPrestamosAdministrador as $equipo): ?>
                            <label class="tarjeta-selector-equipo">
                                <input type="checkbox" name="equipos[]" value="<?php echo escaparPrestamosAdministrador($equipo["id"]); ?>">
                                <span class="marca-selector-equipo"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                                <strong><?php echo escaparPrestamosAdministrador($equipo["codigo"]); ?></strong>
                                <small><?php echo escaparPrestamosAdministrador($equipo["tipo"] . " · " . $equipo["ubicacion"]); ?></small>
                            </label>
                        <?php endforeach; ?>

                        <?php if (!$equiposDisponiblesPrestamosAdministrador): ?>
                            <div class="sin-equipos-prestamo">No hay equipos prestables disponibles en este momento.</div>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal"<?php echo !$equiposDisponiblesPrestamosAdministrador ? " disabled" : ""; ?>>Registrar préstamo</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-admin dialog-prestamo-admin" id="dialogEditarPrestamo">
        <form method="POST" action="<?php echo $urlPrestamosAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparPrestamosAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="actualizar_prestamo">
            <input type="hidden" name="id_prestamo" id="editarPrestamoId">

            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Préstamo abierto</span><h2 id="editarPrestamoTitulo">Modificar préstamo</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin cuerpo-prestamo-dialog">
                <div class="grid-datos-prestamo-dialog">
                    <label class="campo-dialog-admin">
                        <span>Alumno</span>
                        <select name="id_alumno" id="editarPrestamoAlumno" required>
                            <?php foreach ($alumnosPrestamosAdministrador as $alumno): ?>
                                <option value="<?php echo escaparPrestamosAdministrador($alumno["id"]); ?>">
                                    <?php echo escaparPrestamosAdministrador($alumno["apellido"] . ", " . $alumno["nombre"] . " · " . $alumno["grupo"] . " · ID " . $alumno["id"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="campo-dialog-admin">
                        <span>Fecha de préstamo</span>
                        <input type="date" name="fecha_prestamo" id="editarPrestamoFecha" max="<?php echo $fechaHoyPrestamo; ?>" required>
                    </label>
                </div>

                <fieldset class="selector-equipos-prestamo-admin">
                    <legend>Equipos del préstamo</legend>
                    <p>Puedes retirar equipos de la entrega o agregar otros disponibles.</p>
                    <div class="grid-selector-equipos-admin" id="equiposEditarPrestamo"></div>
                </fieldset>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Guardar cambios</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-admin dialog-detalle-prestamo-admin" id="dialogDetallePrestamo">
        <header class="cabecera-dialog-admin">
            <div><span class="sobrelinea-panel">Detalle del movimiento</span><h2 id="detallePrestamoTitulo">Préstamo</h2></div>
            <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
        </header>
        <div class="cuerpo-dialog-admin">
            <dl class="datos-detalle-prestamo">
                <div><dt>Alumno</dt><dd id="detallePrestamoAlumno"></dd></div>
                <div><dt>Grupo</dt><dd id="detallePrestamoGrupo"></dd></div>
                <div><dt>Fecha de entrega</dt><dd id="detallePrestamoFecha"></dd></div>
                <div><dt>Estado</dt><dd id="detallePrestamoEstado"></dd></div>
                <div><dt>Fecha de devolución</dt><dd id="detallePrestamoDevolucion"></dd></div>
            </dl>
            <div class="detalle-equipos-prestamo">
                <h3>Equipos asociados</h3>
                <div id="detallePrestamoEquipos"></div>
            </div>
        </div>
        <footer class="acciones-dialog-admin">
            <button type="button" class="boton-admin boton-admin-principal" data-cerrar-dialog>Cerrar</button>
        </footer>
    </dialog>

    <dialog class="dialog-admin dialog-devolucion-prestamo-admin" id="dialogDevolverPrestamo">
        <form method="POST" action="<?php echo $urlPrestamosAdministrador; ?>" class="formulario-dialog-admin">
            <input type="hidden" name="csrf_token" value="<?php echo escaparPrestamosAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="devolver_prestamo">
            <input type="hidden" name="id_prestamo" id="devolverPrestamoId">

            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Cierre del préstamo</span><h2 id="devolverPrestamoTitulo">Registrar devolución</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>
            <div class="cuerpo-dialog-admin cuerpo-devolucion-prestamo">
                <span class="icono-devolucion-prestamo"><i class="bi bi-box-arrow-in-left" aria-hidden="true"></i></span>
                <p>Al confirmar, el préstamo quedará como devuelto y los equipos prestados volverán a estar disponibles.</p>
                <label class="campo-dialog-admin">
                    <span>Fecha de devolución</span>
                    <input type="date" name="fecha_devolucion" id="devolverPrestamoFecha" value="<?php echo $fechaHoyPrestamo; ?>" max="<?php echo $fechaHoyPrestamo; ?>" required>
                </label>
            </div>
            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Confirmar devolución</button>
            </footer>
        </form>
    </dialog>

    <script>
        const botonMenuAdmin = document.getElementById("botonMenuAdmin");
        const menuAdmin = document.getElementById("menuAdmin");
        const fondoMenuAdmin = document.getElementById("fondoMenuAdmin");
        const equiposDisponiblesPrestamos = <?php echo $equiposDisponiblesJson; ?>;

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

        function convertirFechaPrestamo(fecha) {
            if (!fecha) {
                return "—";
            }

            const partes = fecha.split("-");
            return partes.length === 3 ? partes[2] + "/" + partes[1] + "/" + partes[0] : fecha;
        }

        function crearTarjetaSelectorEquipo(equipo, seleccionado) {
            const etiqueta = document.createElement("label");
            etiqueta.className = "tarjeta-selector-equipo";

            const casilla = document.createElement("input");
            casilla.type = "checkbox";
            casilla.name = "equipos[]";
            casilla.value = equipo.id;
            casilla.checked = seleccionado;

            const marca = document.createElement("span");
            marca.className = "marca-selector-equipo";
            const icono = document.createElement("i");
            icono.className = "bi bi-check-lg";
            icono.setAttribute("aria-hidden", "true");
            marca.appendChild(icono);

            const codigo = document.createElement("strong");
            codigo.textContent = equipo.codigo;
            const detalle = document.createElement("small");
            detalle.textContent = (equipo.tipo || "Equipo") + " · " + (equipo.ubicacion || "Sin ubicación");

            etiqueta.append(casilla, marca, codigo, detalle);
            return etiqueta;
        }

        const dialogNuevoPrestamo = document.getElementById("dialogNuevoPrestamo");
        document.getElementById("abrirNuevoPrestamo").addEventListener("click", function () {
            dialogNuevoPrestamo.querySelector("form").reset();
            dialogNuevoPrestamo.showModal();
        });

        const dialogEditarPrestamo = document.getElementById("dialogEditarPrestamo");
        document.querySelectorAll("[data-editar-prestamo]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const prestamo = JSON.parse(boton.dataset.editarPrestamo);
                const equiposActuales = new Map();

                prestamo.equipos.forEach(function (equipo) {
                    equiposActuales.set(Number(equipo.id_equipo), {
                        id: Number(equipo.id_equipo),
                        codigo: equipo.codigo,
                        tipo: equipo.tipo,
                        ubicacion: equipo.ubicacion
                    });
                });

                equiposDisponiblesPrestamos.forEach(function (equipo) {
                    if (!equiposActuales.has(Number(equipo.id))) {
                        equiposActuales.set(Number(equipo.id), equipo);
                    }
                });

                document.getElementById("editarPrestamoId").value = prestamo.id;
                document.getElementById("editarPrestamoTitulo").textContent = "Modificar préstamo #" + prestamo.id;
                document.getElementById("editarPrestamoAlumno").value = prestamo.id_alumno;
                document.getElementById("editarPrestamoFecha").value = prestamo.fecha_prestamo;

                const contenedor = document.getElementById("equiposEditarPrestamo");
                contenedor.replaceChildren();
                equiposActuales.forEach(function (equipo, id) {
                    const seleccionado = prestamo.equipos.some(function (actual) {
                        return Number(actual.id_equipo) === Number(id);
                    });
                    contenedor.appendChild(crearTarjetaSelectorEquipo(equipo, seleccionado));
                });

                dialogEditarPrestamo.showModal();
            });
        });

        const dialogDetallePrestamo = document.getElementById("dialogDetallePrestamo");
        document.querySelectorAll("[data-ver-prestamo]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const prestamo = JSON.parse(boton.dataset.verPrestamo);
                document.getElementById("detallePrestamoTitulo").textContent = "Préstamo #" + prestamo.id;
                document.getElementById("detallePrestamoAlumno").textContent = prestamo.alumno;
                document.getElementById("detallePrestamoGrupo").textContent = prestamo.grupo;
                document.getElementById("detallePrestamoFecha").textContent = convertirFechaPrestamo(prestamo.fecha_prestamo);
                document.getElementById("detallePrestamoEstado").textContent = prestamo.estado;
                document.getElementById("detallePrestamoDevolucion").textContent = convertirFechaPrestamo(prestamo.fecha_devolucion);

                const contenedor = document.getElementById("detallePrestamoEquipos");
                contenedor.replaceChildren();

                if (!prestamo.equipos.length) {
                    const aviso = document.createElement("p");
                    aviso.className = "sin-detalle-equipos-prestamo";
                    aviso.textContent = "Este registro no tiene equipos asociados.";
                    contenedor.appendChild(aviso);
                } else {
                    prestamo.equipos.forEach(function (equipo) {
                        const tarjeta = document.createElement("article");
                        const datos = document.createElement("div");
                        const codigo = document.createElement("strong");
                        const descripcion = document.createElement("span");
                        const ubicacion = document.createElement("small");
                        codigo.textContent = equipo.codigo;
                        descripcion.textContent = equipo.tipo + (equipo.modelo ? " · " + equipo.modelo : "");
                        ubicacion.textContent = equipo.ubicacion;
                        datos.append(codigo, descripcion, ubicacion);
                        tarjeta.appendChild(datos);
                        contenedor.appendChild(tarjeta);
                    });
                }

                dialogDetallePrestamo.showModal();
            });
        });

        const dialogDevolverPrestamo = document.getElementById("dialogDevolverPrestamo");
        document.querySelectorAll("[data-devolver-prestamo]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const prestamo = JSON.parse(boton.dataset.devolverPrestamo);
                document.getElementById("devolverPrestamoId").value = prestamo.id;
                document.getElementById("devolverPrestamoTitulo").textContent = "Devolver préstamo #" + prestamo.id;
                document.getElementById("devolverPrestamoFecha").min = prestamo.fecha_prestamo;
                document.getElementById("devolverPrestamoFecha").value = "<?php echo $fechaHoyPrestamo; ?>";
                dialogDevolverPrestamo.showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-dialog]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                boton.closest("dialog").close();
            });
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
