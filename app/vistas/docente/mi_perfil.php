<?php

function escaparPerfilDocente(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function inicialesPerfilDocente(string $nombre, string $apellido): string
{
    $inicialNombre = $nombre !== "" ? mb_substr($nombre, 0, 1) : "D";
    $inicialApellido = $apellido !== "" ? mb_substr($apellido, 0, 1) : "";

    return mb_strtoupper($inicialNombre . $inicialApellido);
}

function fechaPerfilDocente(?string $fecha): string
{
    if (!$fecha) {
        return "sin fecha disponible";
    }

    $marca = strtotime($fecha);

    return $marca === false ? "sin fecha disponible" : date("d/m/Y", $marca);
}

$urlDocente = BASE_URL . "/app/controladores/DocenteController.php";
$nombre = (string) ($perfilDocente["nombre"] ?? "");
$apellido = (string) ($perfilDocente["apellido"] ?? "");
$correo = (string) ($perfilDocente["correo"] ?? ($_SESSION["correo"] ?? ""));
$rol = (string) ($perfilDocente["rol"] ?? "Docente");
$nombreCompleto = trim($nombre . " " . $apellido);
$nombreVisible = $nombreCompleto !== "" ? $nombreCompleto : "Docente";
$iniciales = inicialesPerfilDocente($nombre, $apellido);
$fechaRegistro = fechaPerfilDocente($perfilDocente["fecha_registro"] ?? null);
$camposDeshabilitados = $perfilDocente ? "" : " disabled";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Mi perfil docente</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente.css?v=20260823-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente-perfil.css?v=20260823-1">
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
            <a href="<?php echo $urlDocente; ?>?pagina=mis_solicitudes">Solicitud</a>
            <a class="activo" href="<?php echo $urlDocente; ?>?pagina=mi_perfil">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-docente" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            Cerrar sesión
        </a>
    </aside>

    <div class="pagina-docente">
        <header class="encabezado-docente encabezado-perfil-docente">
            <div class="presentacion-docente">
                <span class="marca-docente">GesTIck</span>
                <h1>Mi perfil</h1>
                <p>Consulta tus grupos y administra la información de tu cuenta.</p>
            </div>

            <a class="usuario-docente" href="<?php echo $urlDocente; ?>?pagina=mi_perfil" aria-label="Ir a mi perfil">
                <span class="avatar-docente" aria-hidden="true"><?php echo escaparPerfilDocente($iniciales); ?></span>
                <span class="identidad-docente">
                    <strong>Docente</strong>
                    <span><?php echo escaparPerfilDocente($nombreVisible); ?></span>
                </span>
            </a>
        </header>

        <main class="contenido-perfil-docente">
            <?php if ($mensajeDocente): ?>
                <div class="mensaje-perfil-docente mensaje-<?php echo escaparPerfilDocente($mensajeDocente["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeDocente["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparPerfilDocente($mensajeDocente["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorPerfilDocente): ?>
                <div class="mensaje-perfil-docente mensaje-error" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>No fue posible cargar toda la información del perfil.</span>
                </div>
            <?php endif; ?>

            <div class="paneles-perfil-docente">
                <section class="panel-grupos-docente" aria-labelledby="tituloGruposDocente">
                    <header class="cabecera-panel-perfil-docente">
                        <div>
                            <span>Actividad académica</span>
                            <h2 id="tituloGruposDocente">Mis grupos</h2>
                        </div>
                        <strong><?php echo count($gruposPerfilDocente); ?> grupos</strong>
                    </header>

                    <div class="tabla-grupos-docente">
                        <div class="encabezados-grupos-docente" aria-hidden="true">
                            <span>Turno</span>
                            <span>Grupo</span>
                            <span>Estudiantes</span>
                            <span>Acción</span>
                        </div>

                        <?php if (!$gruposPerfilDocente): ?>
                            <div class="sin-grupos-docente">
                                <i class="bi bi-people" aria-hidden="true"></i>
                                <h3>No tienes grupos asignados</h3>
                                <p>Cuando Administración asigne un grupo, aparecerá aquí.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($gruposPerfilDocente as $grupo): ?>
                            <?php
                            $detalleGrupo = json_encode(
                                $grupo,
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES |
                                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                            );
                            ?>
                            <article class="fila-grupo-docente">
                                <div data-etiqueta="Turno">
                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                    <span><?php echo escaparPerfilDocente($grupo["turno"]); ?></span>
                                </div>
                                <div data-etiqueta="Grupo">
                                    <strong><?php echo escaparPerfilDocente($grupo["grupo"]); ?></strong>
                                </div>
                                <div data-etiqueta="Estudiantes">
                                    <span><?php echo count($grupo["estudiantes"]); ?> estudiantes</span>
                                </div>
                                <div data-etiqueta="Acción">
                                    <button type="button" data-ver-grupo data-grupo="<?php echo escaparPerfilDocente($detalleGrupo ?: "{}"); ?>">
                                        Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="panel-cuenta-docente" aria-labelledby="tituloCuentaDocente">
                    <header class="cabecera-panel-perfil-docente">
                        <div>
                            <span>Cuenta docente</span>
                            <h2 id="tituloCuentaDocente">Mi perfil</h2>
                        </div>
                        <span class="cuenta-activa-docente"><i class="bi bi-check-circle" aria-hidden="true"></i> Activa</span>
                    </header>

                    <div class="avatar-perfil-docente">
                        <div aria-hidden="true"><?php echo escaparPerfilDocente($iniciales); ?></div>
                        <small>Cuenta registrada el <?php echo escaparPerfilDocente($fechaRegistro); ?></small>
                    </div>

                    <div class="datos-perfil-docente">
                        <label>
                            <span>Nombre completo</span>
                            <input type="text" value="<?php echo escaparPerfilDocente($nombreVisible); ?>" readonly>
                        </label>
                        <label>
                            <span>Rol</span>
                            <input type="text" value="<?php echo escaparPerfilDocente($rol); ?>" readonly>
                        </label>
                        <label>
                            <span>Correo electrónico</span>
                            <input type="email" value="<?php echo escaparPerfilDocente($correo); ?>" readonly>
                        </label>
                        <label>
                            <span>Contraseña</span>
                            <input type="password" value="********" readonly aria-label="Contraseña protegida">
                        </label>
                    </div>

                    <div class="acciones-cuenta-docente">
                        <button class="boton-perfil-docente boton-principal-docente" type="button" data-abrir-perfil="dialogEditarPerfilDocente"<?php echo $camposDeshabilitados; ?>>
                            Modificar datos
                        </button>
                        <button class="boton-perfil-docente boton-secundario-docente" type="button" data-abrir-perfil="dialogContrasenaDocente"<?php echo $camposDeshabilitados; ?>>
                            Restablecer contraseña
                        </button>
                    </div>
                </section>
            </div>

            <dialog class="dialog-perfil-docente" id="dialogEditarPerfilDocente">
                <form method="POST" action="<?php echo $urlDocente; ?>?pagina=mi_perfil">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparPerfilDocente($csrfToken); ?>">
                    <input type="hidden" name="accion" value="actualizar_perfil">
                    <header>
                        <div><span>Mi perfil</span><h2>Modificar datos</h2></div>
                        <button type="button" data-cerrar-perfil aria-label="Cerrar">×</button>
                    </header>
                    <div class="campos-dialog-perfil-docente">
                        <label><span>Nombre</span><input type="text" name="nombre" value="<?php echo escaparPerfilDocente($nombre); ?>" maxlength="60" autocomplete="given-name" required></label>
                        <label><span>Apellido</span><input type="text" name="apellido" value="<?php echo escaparPerfilDocente($apellido); ?>" maxlength="60" autocomplete="family-name" required></label>
                        <label class="campo-ancho-dialog-docente"><span>Correo electrónico</span><input type="email" name="correo" value="<?php echo escaparPerfilDocente($correo); ?>" maxlength="120" autocomplete="email" required></label>
                    </div>
                    <footer>
                        <button class="boton-perfil-docente boton-secundario-docente" type="button" data-cerrar-perfil>Cancelar</button>
                        <button class="boton-perfil-docente boton-principal-docente" type="submit">Guardar cambios</button>
                    </footer>
                </form>
            </dialog>

            <dialog class="dialog-perfil-docente" id="dialogContrasenaDocente">
                <form id="formContrasenaDocente" method="POST" action="<?php echo $urlDocente; ?>?pagina=mi_perfil">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparPerfilDocente($csrfToken); ?>">
                    <input type="hidden" name="accion" value="actualizar_contrasena">
                    <header>
                        <div><span>Seguridad</span><h2>Restablecer contraseña</h2></div>
                        <button type="button" data-cerrar-perfil aria-label="Cerrar">×</button>
                    </header>
                    <div class="campos-dialog-perfil-docente">
                        <label class="campo-ancho-dialog-docente"><span>Contraseña actual</span><input type="password" name="contrasena_actual" autocomplete="current-password" required></label>
                        <label><span>Nueva contraseña</span><input type="password" id="contrasenaNuevaDocente" name="contrasena_nueva" minlength="8" maxlength="72" autocomplete="new-password" required></label>
                        <label><span>Repetir contraseña</span><input type="password" id="repetirContrasenaDocente" name="repetir_contrasena" minlength="8" maxlength="72" autocomplete="new-password" required></label>
                        <p class="ayuda-contrasena-docente">La nueva contraseña debe tener entre 8 y 72 caracteres.</p>
                    </div>
                    <footer>
                        <button class="boton-perfil-docente boton-secundario-docente" type="button" data-cerrar-perfil>Cancelar</button>
                        <button class="boton-perfil-docente boton-principal-docente" type="submit">Actualizar contraseña</button>
                    </footer>
                </form>
            </dialog>

            <dialog class="dialog-perfil-docente dialog-grupo-docente" id="dialogGrupoDocente">
                <div>
                    <header>
                        <div><span id="turnoDetalleGrupo">Turno</span><h2 id="tituloDetalleGrupo">Grupo</h2></div>
                        <button type="button" data-cerrar-grupo aria-label="Cerrar">×</button>
                    </header>
                    <div class="contenido-detalle-grupo-docente">
                        <div class="resumen-detalle-grupo"><i class="bi bi-people" aria-hidden="true"></i><span id="totalDetalleGrupo"></span></div>
                        <ol id="listaDetalleGrupo"></ol>
                    </div>
                    <footer><button class="boton-perfil-docente boton-secundario-docente" type="button" data-cerrar-grupo>Cerrar</button></footer>
                </div>
            </dialog>
        </main>

        <footer class="footer-docente">GesTIck · Sistema de gestión de recursos y soporte de informática</footer>
    </div>

    <script>
        const botonMenuDocente = document.getElementById("botonMenuDocente");
        const menuDocente = document.getElementById("menuDocente");
        const fondoMenuDocente = document.getElementById("fondoMenuDocente");
        const dialogGrupoDocente = document.getElementById("dialogGrupoDocente");

        function cambiarMenuDocente(abierto) {
            menuDocente.classList.toggle("abierto", abierto);
            fondoMenuDocente.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-docente-abierto", abierto);
            botonMenuDocente.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        botonMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(!menuDocente.classList.contains("abierto"));
        });
        fondoMenuDocente.addEventListener("click", function () { cambiarMenuDocente(false); });

        document.querySelectorAll("[data-abrir-perfil]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.abrirPerfil).showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-perfil]").forEach(function (boton) {
            boton.addEventListener("click", function () { boton.closest("dialog").close(); });
        });

        document.querySelectorAll(".dialog-perfil-docente").forEach(function (dialogo) {
            dialogo.addEventListener("click", function (evento) {
                if (evento.target === dialogo) { dialogo.close(); }
            });
        });

        document.querySelectorAll("[data-ver-grupo]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const grupo = JSON.parse(boton.dataset.grupo);
                const lista = document.getElementById("listaDetalleGrupo");
                document.getElementById("tituloDetalleGrupo").textContent = "Grupo " + grupo.grupo;
                document.getElementById("turnoDetalleGrupo").textContent = grupo.turno;
                document.getElementById("totalDetalleGrupo").textContent = grupo.estudiantes.length + (grupo.estudiantes.length === 1 ? " estudiante" : " estudiantes");
                lista.replaceChildren();
                grupo.estudiantes.forEach(function (estudiante) {
                    const elemento = document.createElement("li");
                    elemento.textContent = estudiante.nombre;
                    lista.appendChild(elemento);
                });
                if (grupo.estudiantes.length === 0) {
                    const elemento = document.createElement("li");
                    elemento.textContent = "Este grupo todavía no tiene estudiantes registrados.";
                    lista.appendChild(elemento);
                }
                dialogGrupoDocente.showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-grupo]").forEach(function (boton) {
            boton.addEventListener("click", function () { dialogGrupoDocente.close(); });
        });

        document.getElementById("formContrasenaDocente").addEventListener("submit", function (evento) {
            const nueva = document.getElementById("contrasenaNuevaDocente");
            const repetir = document.getElementById("repetirContrasenaDocente");
            repetir.setCustomValidity(nueva.value === repetir.value ? "" : "Las contraseñas no coinciden.");
            if (!this.checkValidity()) { evento.preventDefault(); this.reportValidity(); }
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") { cambiarMenuDocente(false); }
        });
    </script>
</body>
</html>
