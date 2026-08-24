<?php

function escaparUsuariosAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function nombreRolUsuariosAdministrador(string $rol): string
{
    return $rol === "Tecnico" ? "Técnico" : $rol;
}

function inicialesUsuariosAdministrador(string $nombre, string $apellido): string
{
    $primera = mb_substr(trim($nombre), 0, 1);
    $segunda = mb_substr(trim($apellido), 0, 1);

    return mb_strtoupper($primera . $segunda);
}

function fechaUsuariosAdministrador(string $fecha): string
{
    $valor = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $fecha);

    return $valor ? $valor->format("d/m/Y") : $fecha;
}

function urlPaginaUsuariosAdministrador(int $pagina, array $filtros): string
{
    $parametros = ["pagina" => "usuarios"];

    if (($filtros["buscar"] ?? "") !== "") {
        $parametros["buscar"] = $filtros["buscar"];
    }

    if (($filtros["rol"] ?? "") !== "") {
        $parametros["rol"] = $filtros["rol"];
    }

    if (($filtros["estado"] ?? "activos") !== "activos") {
        $parametros["estado"] = $filtros["estado"];
    }

    if ($pagina > 1) {
        $parametros["p"] = $pagina;
    }

    return BASE_URL . "/app/controladores/AdministradorController.php?" . http_build_query($parametros);
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$urlUsuariosAdministrador = $urlAdministrador . "?pagina=usuarios";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
$usuarioSesionId = (int) ($_SESSION["usuario_id"] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Administración de usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-usuarios.css?v=20260824-responsive-1">
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
            <a href="<?php echo $urlAdministrador; ?>?pagina=prestamos"><span>Préstamos</span></a>
            <a class="activo" href="<?php echo $urlUsuariosAdministrador; ?>"><span>Usuarios</span></a>
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
                <h1>Usuarios</h1>
                <p>Administra las cuentas y permisos de acceso al sistema.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparUsuariosAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparUsuariosAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-usuarios-admin">
            <?php if ($mensajeAdministrador): ?>
                <div class="mensaje-admin mensaje-<?php echo escaparUsuariosAdministrador($mensajeAdministrador["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeAdministrador["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparUsuariosAdministrador($mensajeAdministrador["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorUsuariosAdministrador): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar los usuarios. Comprueba que el servidor de base de datos esté disponible.
                </div>
            <?php endif; ?>

            <section class="resumen-usuarios-admin" aria-label="Resumen de usuarios">
                <article>
                    <span class="icono-resumen-usuario icono-total-usuarios"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparUsuariosAdministrador($resumenUsuariosAdministrador["total"]); ?></strong><span>Usuarios registrados</span></div>
                </article>
                <article>
                    <span class="icono-resumen-usuario icono-activos-usuarios"><i class="bi bi-person-check" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparUsuariosAdministrador($resumenUsuariosAdministrador["activos"]); ?></strong><span>Cuentas activas</span></div>
                </article>
                <article>
                    <span class="icono-resumen-usuario icono-tecnicos-usuarios"><i class="bi bi-tools" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparUsuariosAdministrador($resumenUsuariosAdministrador["tecnicos"]); ?></strong><span>Técnicos activos</span></div>
                </article>
                <article>
                    <span class="icono-resumen-usuario icono-docentes-usuarios"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                    <div><strong><?php echo escaparUsuariosAdministrador($resumenUsuariosAdministrador["docentes"]); ?></strong><span>Docentes activos</span></div>
                </article>
            </section>

            <section class="panel-usuarios-admin" aria-labelledby="tituloPanelUsuarios">
                <header class="cabecera-panel-usuarios-admin">
                    <div>
                        <span class="sobrelinea-panel">Control de acceso</span>
                        <h2 id="tituloPanelUsuarios">Usuarios del sistema</h2>
                        <p><?php echo escaparUsuariosAdministrador($totalUsuariosAdministrador); ?> resultados en esta vista</p>
                    </div>

                    <button class="boton-admin boton-admin-principal" type="button" id="abrirNuevoUsuario">
                        <i class="bi bi-person-plus" aria-hidden="true"></i>
                        Nuevo usuario
                    </button>
                </header>

                <form class="filtros-usuarios-admin" method="GET" action="<?php echo $urlAdministrador; ?>">
                    <input type="hidden" name="pagina" value="usuarios">

                    <label class="buscador-usuarios-admin">
                        <span class="sr-only">Buscar usuarios</span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input
                            type="search"
                            name="buscar"
                            maxlength="100"
                            value="<?php echo escaparUsuariosAdministrador($filtrosUsuarios["buscar"]); ?>"
                            placeholder="Buscar por nombre, correo o ID">
                    </label>

                    <label>
                        <span class="sr-only">Filtrar por rol</span>
                        <select name="rol">
                            <option value="">Todos los roles</option>
                            <?php foreach (["Administrador", "Tecnico", "Docente"] as $rol): ?>
                                <option value="<?php echo $rol; ?>"<?php echo $filtrosUsuarios["rol"] === $rol ? " selected" : ""; ?>>
                                    <?php echo escaparUsuariosAdministrador(nombreRolUsuariosAdministrador($rol)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span class="sr-only">Filtrar por estado</span>
                        <select name="estado">
                            <option value="activos"<?php echo $filtrosUsuarios["estado"] === "activos" ? " selected" : ""; ?>>Activos</option>
                            <option value="inactivos"<?php echo $filtrosUsuarios["estado"] === "inactivos" ? " selected" : ""; ?>>Inactivos</option>
                            <option value="todos"<?php echo $filtrosUsuarios["estado"] === "todos" ? " selected" : ""; ?>>Todos los estados</option>
                        </select>
                    </label>

                    <button class="boton-filtrar-usuarios" type="submit">Aplicar filtros</button>

                    <?php if ($filtrosUsuarios["buscar"] !== "" || $filtrosUsuarios["rol"] !== "" || $filtrosUsuarios["estado"] !== "activos"): ?>
                        <a class="limpiar-filtros-usuarios" href="<?php echo $urlUsuariosAdministrador; ?>">Limpiar</a>
                    <?php endif; ?>
                </form>

                <div class="tabla-usuarios-contenedor">
                    <table class="tabla-usuarios-admin">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Correo electrónico</th>
                                <th>Turno</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuariosAdministrador as $usuario): ?>
                                <tr class="<?php echo (int) $usuario["activo"] === 1 ? "" : "usuario-inactivo-admin"; ?>">
                                    <td data-label="Usuario">
                                        <div class="identidad-tabla-usuario">
                                            <span class="iniciales-tabla-usuario"><?php echo escaparUsuariosAdministrador(inicialesUsuariosAdministrador($usuario["nombre"], $usuario["apellido"])); ?></span>
                                            <div>
                                                <strong><?php echo escaparUsuariosAdministrador($usuario["nombre"] . " " . $usuario["apellido"]); ?></strong>
                                                <span>ID <?php echo escaparUsuariosAdministrador($usuario["id_usuario"]); ?><?php echo (int) $usuario["id_usuario"] === $usuarioSesionId ? " · Tu cuenta" : ""; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Rol">
                                        <span class="etiqueta-rol-usuario rol-<?php echo strtolower(escaparUsuariosAdministrador($usuario["rol"])); ?>">
                                            <?php echo escaparUsuariosAdministrador(nombreRolUsuariosAdministrador($usuario["rol"])); ?>
                                        </span>
                                    </td>
                                    <td data-label="Correo"><?php echo escaparUsuariosAdministrador($usuario["correo"]); ?></td>
                                    <td data-label="Turno"><?php echo escaparUsuariosAdministrador($usuario["nombre_turno"] ?: "—"); ?></td>
                                    <td data-label="Estado">
                                        <span class="estado-cuenta-usuario <?php echo (int) $usuario["activo"] === 1 ? "estado-activo-usuario" : "estado-inactivo-usuario"; ?>">
                                            <i aria-hidden="true"></i>
                                            <?php echo (int) $usuario["activo"] === 1 ? "Activo" : "Inactivo"; ?>
                                        </span>
                                    </td>
                                    <td data-label="Registro"><?php echo escaparUsuariosAdministrador(fechaUsuariosAdministrador($usuario["fecha_registro"])); ?></td>
                                    <td data-label="Acciones">
                                        <div class="acciones-usuario-admin">
                                            <button
                                                type="button"
                                                class="boton-accion-usuario"
                                                data-editar-usuario
                                                data-id="<?php echo escaparUsuariosAdministrador($usuario["id_usuario"]); ?>"
                                                data-nombre="<?php echo escaparUsuariosAdministrador($usuario["nombre"]); ?>"
                                                data-apellido="<?php echo escaparUsuariosAdministrador($usuario["apellido"]); ?>"
                                                data-correo="<?php echo escaparUsuariosAdministrador($usuario["correo"]); ?>"
                                                data-rol="<?php echo escaparUsuariosAdministrador($usuario["rol"]); ?>"
                                                data-turno="<?php echo escaparUsuariosAdministrador($usuario["id_turno"] ?? ""); ?>"
                                                data-propio="<?php echo (int) $usuario["id_usuario"] === $usuarioSesionId ? "1" : "0"; ?>"
                                                aria-label="Editar a <?php echo escaparUsuariosAdministrador($usuario["nombre"] . " " . $usuario["apellido"]); ?>">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </button>

                                            <?php if ((int) $usuario["activo"] === 1): ?>
                                                <form method="POST" action="<?php echo $urlUsuariosAdministrador; ?>" data-confirmar="¿Desactivar esta cuenta? El usuario ya no podrá iniciar sesión, pero su historial se conservará.">
                                                    <input type="hidden" name="csrf_token" value="<?php echo escaparUsuariosAdministrador($csrfToken); ?>">
                                                    <input type="hidden" name="accion" value="desactivar_usuario">
                                                    <input type="hidden" name="id_usuario" value="<?php echo escaparUsuariosAdministrador($usuario["id_usuario"]); ?>">
                                                    <button
                                                        type="submit"
                                                        class="boton-accion-usuario boton-desactivar-usuario"
                                                        <?php echo (int) $usuario["id_usuario"] === $usuarioSesionId ? "disabled" : ""; ?>
                                                        aria-label="Desactivar a <?php echo escaparUsuariosAdministrador($usuario["nombre"] . " " . $usuario["apellido"]); ?>"
                                                        title="<?php echo (int) $usuario["id_usuario"] === $usuarioSesionId ? "No puedes desactivar tu propia cuenta" : "Desactivar usuario"; ?>">
                                                        <i class="bi bi-person-x" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="<?php echo $urlUsuariosAdministrador; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo escaparUsuariosAdministrador($csrfToken); ?>">
                                                    <input type="hidden" name="accion" value="reactivar_usuario">
                                                    <input type="hidden" name="id_usuario" value="<?php echo escaparUsuariosAdministrador($usuario["id_usuario"]); ?>">
                                                    <button type="submit" class="boton-accion-usuario boton-reactivar-usuario" aria-label="Reactivar a <?php echo escaparUsuariosAdministrador($usuario["nombre"] . " " . $usuario["apellido"]); ?>" title="Reactivar usuario">
                                                        <i class="bi bi-person-check" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (!$usuariosAdministrador && !$errorUsuariosAdministrador): ?>
                        <div class="estado-vacio-usuarios-admin">
                            <i class="bi bi-person-search" aria-hidden="true"></i>
                            <h3>No encontramos usuarios</h3>
                            <p>Prueba con otros filtros o crea una cuenta nueva.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($totalPaginasUsuariosAdministrador > 1): ?>
                    <nav class="paginacion-usuarios-admin" aria-label="Paginación de usuarios">
                        <?php if ($paginaActualUsuariosAdministrador > 1): ?>
                            <a href="<?php echo escaparUsuariosAdministrador(urlPaginaUsuariosAdministrador($paginaActualUsuariosAdministrador - 1, $filtrosUsuarios)); ?>">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i> Anterior
                            </a>
                        <?php else: ?>
                            <span class="pagina-deshabilitada"><i class="bi bi-chevron-left" aria-hidden="true"></i> Anterior</span>
                        <?php endif; ?>

                        <span>Página <?php echo escaparUsuariosAdministrador($paginaActualUsuariosAdministrador); ?> de <?php echo escaparUsuariosAdministrador($totalPaginasUsuariosAdministrador); ?></span>

                        <?php if ($paginaActualUsuariosAdministrador < $totalPaginasUsuariosAdministrador): ?>
                            <a href="<?php echo escaparUsuariosAdministrador(urlPaginaUsuariosAdministrador($paginaActualUsuariosAdministrador + 1, $filtrosUsuarios)); ?>">
                                Siguiente <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </a>
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

    <dialog class="dialog-admin dialog-usuario-admin" id="dialogNuevoUsuario">
        <form method="POST" action="<?php echo $urlUsuariosAdministrador; ?>" class="formulario-dialog-admin" data-formulario-usuario>
            <input type="hidden" name="csrf_token" value="<?php echo escaparUsuariosAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="crear_usuario">

            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Control de acceso</span><h2>Crear usuario</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin grid-formulario-usuario-admin">
                <label class="campo-dialog-admin"><span>Nombre</span><input type="text" name="nombre" maxlength="60" autocomplete="given-name" required></label>
                <label class="campo-dialog-admin"><span>Apellido</span><input type="text" name="apellido" maxlength="60" autocomplete="family-name" required></label>
                <label class="campo-dialog-admin campo-completo-dialog"><span>Correo electrónico</span><input type="email" name="correo" maxlength="120" autocomplete="email" placeholder="usuario@ejemplo.com" required></label>
                <label class="campo-dialog-admin"><span>Rol</span><select name="rol" data-selector-rol required><option value="Docente">Docente</option><option value="Tecnico">Técnico</option><option value="Administrador">Administrador</option></select></label>
                <label class="campo-dialog-admin campo-turno-usuario" data-campo-turno hidden><span>Turno</span><select name="id_turno"><option value="">Seleccionar turno</option><?php foreach ($turnosUsuariosAdministrador as $turno): ?><option value="<?php echo escaparUsuariosAdministrador($turno["id_turno"]); ?>"><?php echo escaparUsuariosAdministrador($turno["nombre_turno"]); ?></option><?php endforeach; ?></select></label>
                <label class="campo-dialog-admin campo-completo-dialog"><span>Contraseña inicial</span><input type="password" name="contrasena" minlength="8" maxlength="72" autocomplete="new-password" required><small>Entre 8 y 72 caracteres.</small></label>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Crear usuario</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog-admin dialog-usuario-admin" id="dialogEditarUsuario">
        <form method="POST" action="<?php echo $urlUsuariosAdministrador; ?>" class="formulario-dialog-admin" data-formulario-usuario>
            <input type="hidden" name="csrf_token" value="<?php echo escaparUsuariosAdministrador($csrfToken); ?>">
            <input type="hidden" name="accion" value="actualizar_usuario">
            <input type="hidden" name="id_usuario" id="editarUsuarioId">

            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Datos de la cuenta</span><h2>Modificar usuario</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-dialog-admin grid-formulario-usuario-admin">
                <label class="campo-dialog-admin"><span>Nombre</span><input type="text" name="nombre" id="editarUsuarioNombre" maxlength="60" required></label>
                <label class="campo-dialog-admin"><span>Apellido</span><input type="text" name="apellido" id="editarUsuarioApellido" maxlength="60" required></label>
                <label class="campo-dialog-admin campo-completo-dialog"><span>Correo electrónico</span><input type="email" name="correo" id="editarUsuarioCorreo" maxlength="120" required></label>
                <label class="campo-dialog-admin"><span>Rol</span><select name="rol" id="editarUsuarioRol" data-selector-rol required><option value="Docente">Docente</option><option value="Tecnico">Técnico</option><option value="Administrador">Administrador</option></select><small id="avisoRolPropio" hidden>Tu propia cuenta debe conservar el rol Administrador.</small></label>
                <label class="campo-dialog-admin campo-turno-usuario" data-campo-turno hidden><span>Turno</span><select name="id_turno" id="editarUsuarioTurno"><option value="">Seleccionar turno</option><?php foreach ($turnosUsuariosAdministrador as $turno): ?><option value="<?php echo escaparUsuariosAdministrador($turno["id_turno"]); ?>"><?php echo escaparUsuariosAdministrador($turno["nombre_turno"]); ?></option><?php endforeach; ?></select></label>
                <label class="campo-dialog-admin campo-completo-dialog"><span>Nueva contraseña <small>(opcional)</small></span><input type="password" name="contrasena" id="editarUsuarioContrasena" minlength="8" maxlength="72" autocomplete="new-password"><small>Déjala vacía para conservar la contraseña actual.</small></label>
            </div>

            <footer class="acciones-dialog-admin">
                <button type="button" class="boton-admin boton-admin-secundario" data-cerrar-dialog>Cancelar</button>
                <button type="submit" class="boton-admin boton-admin-principal">Guardar cambios</button>
            </footer>
        </form>
    </dialog>

    <script>
        const botonMenuAdmin = document.getElementById("botonMenuAdmin");
        const menuAdmin = document.getElementById("menuAdmin");
        const fondoMenuAdmin = document.getElementById("fondoMenuAdmin");

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

        function actualizarTurnoFormulario(formulario) {
            const rol = formulario.querySelector("[data-selector-rol]");
            const campoTurno = formulario.querySelector("[data-campo-turno]");
            const selectorTurno = campoTurno.querySelector("select");
            const esTecnico = rol.value === "Tecnico";

            campoTurno.hidden = !esTecnico;
            selectorTurno.required = esTecnico;

            if (!esTecnico) {
                selectorTurno.value = "";
            }
        }

        document.querySelectorAll("[data-formulario-usuario]").forEach(function (formulario) {
            const selectorRol = formulario.querySelector("[data-selector-rol]");
            selectorRol.addEventListener("change", function () {
                actualizarTurnoFormulario(formulario);
            });
            actualizarTurnoFormulario(formulario);
        });

        const dialogNuevoUsuario = document.getElementById("dialogNuevoUsuario");
        document.getElementById("abrirNuevoUsuario").addEventListener("click", function () {
            const formulario = dialogNuevoUsuario.querySelector("form");
            formulario.reset();
            actualizarTurnoFormulario(formulario);
            dialogNuevoUsuario.showModal();
        });

        const dialogEditarUsuario = document.getElementById("dialogEditarUsuario");
        document.querySelectorAll("[data-editar-usuario]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const formulario = dialogEditarUsuario.querySelector("form");
                document.getElementById("editarUsuarioId").value = boton.dataset.id;
                document.getElementById("editarUsuarioNombre").value = boton.dataset.nombre;
                document.getElementById("editarUsuarioApellido").value = boton.dataset.apellido;
                document.getElementById("editarUsuarioCorreo").value = boton.dataset.correo;
                document.getElementById("editarUsuarioRol").value = boton.dataset.rol;
                document.getElementById("editarUsuarioTurno").value = boton.dataset.turno || "";
                document.getElementById("editarUsuarioContrasena").value = "";
                document.getElementById("avisoRolPropio").hidden = boton.dataset.propio !== "1";
                actualizarTurnoFormulario(formulario);
                dialogEditarUsuario.showModal();
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
