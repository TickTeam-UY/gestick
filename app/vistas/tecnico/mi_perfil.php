<?php

function escaparPerfilTecnico(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function inicialesPerfilTecnico(string $nombre, string $apellido): string
{
    $inicialNombre = $nombre !== "" ? mb_substr($nombre, 0, 1) : "T";
    $inicialApellido = $apellido !== "" ? mb_substr($apellido, 0, 1) : "";

    return mb_strtoupper($inicialNombre . $inicialApellido);
}

function fechaPerfilTecnico(?string $fecha): string
{
    if (!$fecha) {
        return "Sin fecha disponible";
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? "Sin fecha disponible" : date("d/m/Y", $marcaTiempo);
}

$urlTecnico = BASE_URL . "/app/controladores/TecnicoController.php";
$nombre = $perfilTecnico["nombre"] ?? "";
$apellido = $perfilTecnico["apellido"] ?? "";
$correo = $perfilTecnico["correo"] ?? ($_SESSION["correo"] ?? "");
$rol = $perfilTecnico["rol"] ?? ($_SESSION["rol"] ?? "Tecnico");
$rolVisible = in_array($rol, ["Tecnico", "Técnico"], true) ? "Técnico" : $rol;
$nombreCompleto = trim($nombre . " " . $apellido);

if ($nombreCompleto === "") {
    $nombreCompleto = $_SESSION["nombre"] ?? "Técnico";
}

$iniciales = inicialesPerfilTecnico($nombre, $apellido);
$fechaRegistro = fechaPerfilTecnico($perfilTecnico["fecha_registro"] ?? null);
$camposDeshabilitados = $perfilTecnico ? "" : " disabled";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Mi perfil del técnico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tecnico.css?v=20260823-responsive-1">
</head>
<body>
    <button class="boton-menu" id="botonMenu" aria-label="Abrir menú" aria-expanded="false">☰</button>

    <aside class="menu-lateral" id="menuLateral">
        <div class="logo-menu">
            <img src="<?php echo BASE_URL; ?>/public/imagenes/logoG.png" alt="Logo GesTIck">
        </div>

        <nav class="navegacion">
            <a href="<?php echo $urlTecnico; ?>?pagina=inicio">Inicio</a>
            <a href="<?php echo $urlTecnico; ?>?pagina=mis_tickets">Mis tickets</a>
            <a href="<?php echo $urlTecnico; ?>?pagina=pendientes">Pendientes</a>
            <a href="<?php echo $urlTecnico; ?>?pagina=en_proceso">En proceso</a>
            <a href="<?php echo $urlTecnico; ?>?pagina=equipos">Equipos</a>
            <a href="<?php echo $urlTecnico; ?>?pagina=solicitudes">Solicitudes</a>
            <a href="<?php echo $urlTecnico; ?>?pagina=mi_perfil" class="activo">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-tecnico" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina">
        <header class="encabezado-principal">
            <div>
                <h1>Mi perfil</h1>
                <p>Información y configuración de la cuenta</p>
            </div>

            <a href="<?php echo $urlTecnico; ?>?pagina=mi_perfil" class="usuario-superior" aria-label="Ir al perfil del usuario">
                <div class="avatar"></div>
                <div class="datos-usuario">
                    <strong><?php echo escaparPerfilTecnico($rolVisible); ?></strong>
                    <span><?php echo escaparPerfilTecnico($nombreCompleto); ?></span>
                </div>
            </a>
        </header>

        <main class="contenido-perfil-compacto-tecnico">
            <?php if ($mensajeTecnico): ?>
                <div class="mensaje-perfil-tecnico mensaje-perfil-<?php echo escaparPerfilTecnico($mensajeTecnico["tipo"]); ?>" role="status">
                    <?php echo escaparPerfilTecnico($mensajeTecnico["texto"]); ?>
                </div>
            <?php endif; ?>

            <?php if ($errorPerfilTecnico): ?>
                <div class="mensaje-perfil-tecnico mensaje-perfil-error" role="alert">
                    No fue posible cargar toda la información del perfil. Comprueba la conexión e inténtalo nuevamente.
                </div>
            <?php endif; ?>

            <section class="perfil-compacto-tecnico" aria-labelledby="tituloPerfilTecnico">
                <header class="titulo-perfil-compacto-tecnico">
                    <span>Cuenta técnica</span>
                    <h2 id="tituloPerfilTecnico">Mi perfil</h2>
                </header>

                <div class="cuerpo-perfil-compacto-tecnico">
                    <section class="informacion-perfil-compacto-tecnico" aria-labelledby="tituloInformacionTecnico">
                        <h3 id="tituloInformacionTecnico">Información personal</h3>

                        <div class="grid-resumen-perfil-tecnico">
                            <label class="campo-resumen-perfil-tecnico">
                                <span>Nombre completo</span>
                                <input type="text" value="<?php echo escaparPerfilTecnico($nombreCompleto); ?>" readonly>
                            </label>

                            <label class="campo-resumen-perfil-tecnico">
                                <span>Rol</span>
                                <input type="text" value="<?php echo escaparPerfilTecnico($rolVisible); ?>" readonly>
                            </label>

                            <label class="campo-resumen-perfil-tecnico">
                                <span>Correo electrónico</span>
                                <input type="email" value="<?php echo escaparPerfilTecnico($correo); ?>" readonly>
                            </label>

                            <label class="campo-resumen-perfil-tecnico">
                                <span>Contraseña</span>
                                <input type="password" value="********" readonly aria-label="Contraseña protegida">
                            </label>
                        </div>
                    </section>

                    <aside class="avatar-compacto-tecnico" aria-label="Información de la cuenta">
                        <h3>Foto de perfil</h3>
                        <div class="circulo-avatar-compacto-tecnico" aria-hidden="true">
                            <?php echo escaparPerfilTecnico($iniciales); ?>
                        </div>
                        <span class="estado-perfil-compacto-tecnico">Cuenta activa</span>
                        <small>Registrada el <?php echo escaparPerfilTecnico($fechaRegistro); ?></small>
                    </aside>

                    <div class="acciones-perfil-compacto-tecnico">
                        <button class="boton-perfil boton-modificar" type="button" data-abrir-dialog-tecnico="dialogEditarPerfilTecnico"<?php echo $camposDeshabilitados; ?>>
                            Modificar datos
                        </button>
                        <button class="boton-perfil boton-secundario" type="button" data-abrir-dialog-tecnico="dialogContrasenaTecnico"<?php echo $camposDeshabilitados; ?>>
                            Restablecer contraseña
                        </button>
                    </div>
                </div>
            </section>

            <dialog class="dialog-perfil-tecnico" id="dialogEditarPerfilTecnico">
                <form method="post" action="<?php echo $urlTecnico; ?>?pagina=mi_perfil">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparPerfilTecnico($csrfToken); ?>">
                    <input type="hidden" name="accion" value="actualizar_perfil">

                    <div class="cabecera-dialog-perfil-tecnico">
                        <div>
                            <span>Mi perfil</span>
                            <h2>Modificar datos</h2>
                        </div>
                        <button type="button" data-cerrar-dialog-tecnico aria-label="Cerrar">×</button>
                    </div>

                    <div class="cuerpo-dialog-perfil-tecnico grid-dialog-perfil-tecnico">
                        <label class="campo-dialog-perfil-tecnico">
                            <span>Nombre</span>
                            <input type="text" name="nombre" value="<?php echo escaparPerfilTecnico($nombre); ?>" maxlength="60" autocomplete="given-name" required>
                        </label>
                        <label class="campo-dialog-perfil-tecnico">
                            <span>Apellido</span>
                            <input type="text" name="apellido" value="<?php echo escaparPerfilTecnico($apellido); ?>" maxlength="60" autocomplete="family-name" required>
                        </label>
                        <label class="campo-dialog-perfil-tecnico campo-dialog-tecnico-ancho">
                            <span>Correo electrónico</span>
                            <input type="email" name="correo" value="<?php echo escaparPerfilTecnico($correo); ?>" maxlength="120" autocomplete="email" required>
                        </label>
                    </div>

                    <div class="acciones-dialog-perfil-tecnico">
                        <button class="boton-perfil boton-secundario" type="button" data-cerrar-dialog-tecnico>Cancelar</button>
                        <button class="boton-perfil boton-modificar" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </dialog>

            <dialog class="dialog-perfil-tecnico" id="dialogContrasenaTecnico">
                <form id="formContrasenaTecnico" method="post" action="<?php echo $urlTecnico; ?>?pagina=mi_perfil">
                    <input type="hidden" name="csrf_token" value="<?php echo escaparPerfilTecnico($csrfToken); ?>">
                    <input type="hidden" name="accion" value="actualizar_contrasena">

                    <div class="cabecera-dialog-perfil-tecnico">
                        <div>
                            <span>Seguridad</span>
                            <h2>Restablecer contraseña</h2>
                        </div>
                        <button type="button" data-cerrar-dialog-tecnico aria-label="Cerrar">×</button>
                    </div>

                    <div class="cuerpo-dialog-perfil-tecnico grid-dialog-perfil-tecnico">
                        <label class="campo-dialog-perfil-tecnico campo-dialog-tecnico-ancho">
                            <span>Contraseña actual</span>
                            <input type="password" name="contrasena_actual" autocomplete="current-password" required>
                        </label>
                        <label class="campo-dialog-perfil-tecnico">
                            <span>Nueva contraseña</span>
                            <input type="password" id="contrasenaNuevaTecnico" name="contrasena_nueva" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </label>
                        <label class="campo-dialog-perfil-tecnico">
                            <span>Repetir contraseña</span>
                            <input type="password" id="repetirContrasenaTecnico" name="repetir_contrasena" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </label>
                        <p class="ayuda-dialog-perfil-tecnico">La nueva contraseña debe tener entre 8 y 72 caracteres.</p>
                    </div>

                    <div class="acciones-dialog-perfil-tecnico">
                        <button class="boton-perfil boton-secundario" type="button" data-cerrar-dialog-tecnico>Cancelar</button>
                        <button class="boton-perfil boton-modificar" type="submit">Actualizar contraseña</button>
                    </div>
                </form>
            </dialog>
        </main>

        <footer>
            <p>GesTIck - Sistema de Gestión de Recursos y Soporte de Informática</p>
        </footer>
    </div>

    <script>
        const botonMenu = document.getElementById("botonMenu");
        const menuLateral = document.getElementById("menuLateral");

        botonMenu.addEventListener("click", function () {
            const abierto = menuLateral.classList.toggle("abierto");
            botonMenu.setAttribute("aria-expanded", abierto ? "true" : "false");
        });

        document.querySelectorAll("[data-abrir-dialog-tecnico]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                document.getElementById(boton.dataset.abrirDialogTecnico).showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-dialog-tecnico]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                boton.closest("dialog").close();
            });
        });

        const formularioContrasena = document.getElementById("formContrasenaTecnico");
        const contrasenaNueva = document.getElementById("contrasenaNuevaTecnico");
        const repetirContrasena = document.getElementById("repetirContrasenaTecnico");

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
    </script>
</body>
</html>
