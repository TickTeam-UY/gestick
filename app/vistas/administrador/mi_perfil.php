<?php

function escaparPerfilAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function inicialesPerfilAdministrador(string $nombre, string $apellido): string
{
    $inicialNombre = $nombre !== "" ? mb_substr($nombre, 0, 1) : "A";
    $inicialApellido = $apellido !== "" ? mb_substr($apellido, 0, 1) : "";

    return mb_strtoupper($inicialNombre . $inicialApellido);
}

function fechaPerfilAdministrador(?string $fecha): string
{
    if (!$fecha) {
        return "Sin fecha disponible";
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? "Sin fecha disponible" : date("d/m/Y", $marcaTiempo);
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$nombre = $perfilAdministrador["nombre"] ?? "";
$apellido = $perfilAdministrador["apellido"] ?? "";
$correo = $perfilAdministrador["correo"] ?? ($_SESSION["correo"] ?? "");
$rol = $perfilAdministrador["rol"] ?? "Administrador";
$nombreCompleto = trim($nombre . " " . $apellido);

if ($nombreCompleto === "") {
    $nombreCompleto = $_SESSION["nombre"] ?? "Administrador";
}

$iniciales = inicialesPerfilAdministrador($nombre, $apellido);
$fechaRegistro = fechaPerfilAdministrador($perfilAdministrador["fecha_registro"] ?? null);
$camposDeshabilitados = $perfilAdministrador ? "" : " disabled";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Mi perfil de administrador</title>

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
            <a href="<?php echo $urlAdministrador; ?>?pagina=equipos"><span>Equipos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=prestamos"><span>Préstamos</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=usuarios"><span>Usuarios</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=metricas"><span>Métricas</span></a>
            <a class="activo" href="<?php echo $urlAdministrador; ?>?pagina=mi_perfil"><span>Mi perfil</span></a>
        </nav>

        <a class="cerrar-sesion-admin" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina-admin">
        <header class="encabezado-admin encabezado-perfil-admin">
            <div class="presentacion-admin">
                <h1>Mi perfil</h1>
                <p>Consulta y actualiza la información de tu cuenta de administrador.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparPerfilAdministrador($rol); ?></strong>
                    <span><?php echo escaparPerfilAdministrador($nombreCompleto); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-perfil-prototipo-admin">
            <?php if ($mensajeAdministrador): ?>
                <div class="mensaje-admin mensaje-<?php echo escaparPerfilAdministrador($mensajeAdministrador["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeAdministrador["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparPerfilAdministrador($mensajeAdministrador["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorPerfil): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar toda la información del perfil. Comprueba la conexión e inténtalo nuevamente.
                </div>
            <?php endif; ?>

            <section class="perfil-prototipo-admin" aria-labelledby="tituloPerfilAdministrador">
                <header class="titulo-perfil-prototipo-admin">
                    <span class="sobrelinea-panel">Cuenta administrativa</span>
                    <h2 id="tituloPerfilAdministrador">Mi perfil</h2>
                </header>

                <div class="cuerpo-perfil-prototipo-admin">
                    <section class="informacion-perfil-prototipo-admin" aria-labelledby="tituloInformacionPersonal">
                        <h3 id="tituloInformacionPersonal">Información personal</h3>

                        <div class="grid-resumen-perfil-admin">
                            <label class="campo-resumen-perfil-admin">
                                <span>Nombre completo</span>
                                <input type="text" value="<?php echo escaparPerfilAdministrador($nombreCompleto); ?>" readonly>
                            </label>

                            <label class="campo-resumen-perfil-admin">
                                <span>Rol</span>
                                <input type="text" value="<?php echo escaparPerfilAdministrador($rol); ?>" readonly>
                            </label>

                            <label class="campo-resumen-perfil-admin">
                                <span>Correo electrónico</span>
                                <input type="email" value="<?php echo escaparPerfilAdministrador($correo); ?>" readonly>
                            </label>

                            <label class="campo-resumen-perfil-admin">
                                <span>Contraseña</span>
                                <input type="password" value="********" readonly aria-label="Contraseña protegida">
                            </label>
                        </div>
                    </section>

                    <aside class="avatar-prototipo-admin" aria-label="Información de la cuenta">
                        <h3>Foto de perfil</h3>
                        <div class="circulo-avatar-prototipo-admin" aria-hidden="true">
                            <?php echo escaparPerfilAdministrador($iniciales); ?>
                        </div>
                        <span class="estado-perfil-prototipo-admin">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            Cuenta activa
                        </span>
                        <small>Registrada el <?php echo escaparPerfilAdministrador($fechaRegistro); ?></small>
                    </aside>

                    <div class="acciones-prototipo-admin">
                        <button class="boton-admin boton-admin-principal" type="button" data-abrir-dialog-perfil="dialogEditarPerfilAdmin"<?php echo $camposDeshabilitados; ?>>
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                            Modificar datos
                        </button>
                        <button class="boton-admin boton-admin-secundario" type="button" data-abrir-dialog-perfil="dialogContrasenaAdmin"<?php echo $camposDeshabilitados; ?>>
                            <i class="bi bi-key" aria-hidden="true"></i>
                            Restablecer contraseña
                        </button>
                    </div>
                </div>
            </section>

            <dialog class="dialog-admin dialog-perfil-admin" id="dialogEditarPerfilAdmin">
                <form class="formulario-dialog-admin" method="post" action="<?php echo $urlAdministrador; ?>?pagina=mi_perfil">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparPerfilAdministrador($csrfToken); ?>">
                    <input type="hidden" name="accion" value="actualizar_perfil">

                    <div class="cabecera-dialog-admin">
                        <div>
                            <span class="sobrelinea-panel">Mi perfil</span>
                            <h2>Modificar datos</h2>
                        </div>
                        <button class="cerrar-dialog-admin" type="button" data-cerrar-dialog-perfil aria-label="Cerrar">×</button>
                    </div>

                    <div class="cuerpo-dialog-admin grid-formulario-perfil-admin">
                        <label class="campo-dialog-admin">
                            <span>Nombre</span>
                            <input type="text" name="nombre" value="<?php echo escaparPerfilAdministrador($nombre); ?>" maxlength="60" autocomplete="given-name" required>
                        </label>
                        <label class="campo-dialog-admin">
                            <span>Apellido</span>
                            <input type="text" name="apellido" value="<?php echo escaparPerfilAdministrador($apellido); ?>" maxlength="60" autocomplete="family-name" required>
                        </label>
                        <label class="campo-dialog-admin campo-dialog-perfil-ancho">
                            <span>Correo electrónico</span>
                            <input type="email" name="correo" value="<?php echo escaparPerfilAdministrador($correo); ?>" maxlength="120" autocomplete="email" required>
                        </label>
                    </div>

                    <div class="acciones-dialog-admin">
                        <button class="boton-admin boton-admin-secundario" type="button" data-cerrar-dialog-perfil>Cancelar</button>
                        <button class="boton-admin boton-admin-principal" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </dialog>

            <dialog class="dialog-admin dialog-perfil-admin" id="dialogContrasenaAdmin">
                <form class="formulario-dialog-admin" id="formContrasenaAdministrador" method="post" action="<?php echo $urlAdministrador; ?>?pagina=mi_perfil">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparPerfilAdministrador($csrfToken); ?>">
                    <input type="hidden" name="accion" value="actualizar_contrasena">

                    <div class="cabecera-dialog-admin">
                        <div>
                            <span class="sobrelinea-panel">Seguridad</span>
                            <h2>Restablecer contraseña</h2>
                        </div>
                        <button class="cerrar-dialog-admin" type="button" data-cerrar-dialog-perfil aria-label="Cerrar">×</button>
                    </div>

                    <div class="cuerpo-dialog-admin grid-formulario-perfil-admin">
                        <label class="campo-dialog-admin campo-dialog-perfil-ancho">
                            <span>Contraseña actual</span>
                            <input type="password" name="contrasena_actual" autocomplete="current-password" required>
                        </label>
                        <label class="campo-dialog-admin">
                            <span>Nueva contraseña</span>
                            <input type="password" id="contrasenaNuevaAdmin" name="contrasena_nueva" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </label>
                        <label class="campo-dialog-admin">
                            <span>Repetir contraseña</span>
                            <input type="password" id="repetirContrasenaAdmin" name="repetir_contrasena" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </label>
                        <p class="ayuda-dialog-perfil-admin">La nueva contraseña debe tener entre 8 y 72 caracteres.</p>
                    </div>

                    <div class="acciones-dialog-admin">
                        <button class="boton-admin boton-admin-secundario" type="button" data-cerrar-dialog-perfil>Cancelar</button>
                        <button class="boton-admin boton-admin-principal" type="submit">Actualizar contraseña</button>
                    </div>
                </form>
            </dialog>
        </main>

        <footer class="footer-admin">
            <p>GesTIck · Sistema de gestión de recursos y soporte de informática</p>
        </footer>
    </div>

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

        document.querySelectorAll("[data-abrir-dialog-perfil]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.abrirDialogPerfil).showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-dialog-perfil]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                boton.closest("dialog").close();
            });
        });

        const formularioContrasena = document.getElementById("formContrasenaAdministrador");
        const contrasenaNueva = document.getElementById("contrasenaNuevaAdmin");
        const repetirContrasena = document.getElementById("repetirContrasenaAdmin");

        formularioContrasena.addEventListener("submit", function (evento) {
            repetirContrasena.setCustomValidity("");

            if (contrasenaNueva.value !== repetirContrasena.value) {
                evento.preventDefault();
                repetirContrasena.setCustomValidity("Las contraseñas no coinciden.");
                repetirContrasena.reportValidity();
            }
        });

        repetirContrasena.addEventListener("input", function () {
            repetirContrasena.setCustomValidity("");
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuAdmin(false);
            }
        });
    </script>
</body>
</html>
