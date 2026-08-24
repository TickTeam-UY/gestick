<?php

function escaparAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function fechaAdministrador(?string $fecha, bool $incluirHora = false): string
{
    if (!$fecha) {
        return "Sin fecha";
    }

    $marcaTiempo = strtotime($fecha);

    if ($marcaTiempo === false) {
        return "Sin fecha";
    }

    return date($incluirHora ? "d/m/Y H:i" : "d/m/Y", $marcaTiempo);
}

function claseEtiquetaAdministrador(string $valor): string
{
    $normalizado = strtolower(str_replace(
        ["á", "é", "í", "ó", "ú", "ñ", " "],
        ["a", "e", "i", "o", "u", "n", "-"],
        $valor
    ));

    return preg_replace("/[^a-z0-9-]/", "", $normalizado) ?: "neutra";
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Inicio del administrador</title>

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
            <a class="activo" href="<?php echo $urlAdministrador; ?>?pagina=inicio">
                <span>Inicio</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=tickets">
                <span>Tickets</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=solicitudes">
                <span>Solicitudes</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=planillas">
                <span>Planillas</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=equipos">
                <span>Equipos</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=prestamos">
                <span>Préstamos</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=usuarios">
                <span>Usuarios</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=metricas">
                <span>Métricas</span>
            </a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=mi_perfil">
                <span>Mi perfil</span>
            </a>
        </nav>

        <a class="cerrar-sesion-admin" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            <span>Cerrar sesión</span>
        </a>
    </aside>

    <div class="pagina-admin">
        <header class="encabezado-admin">
            <div class="presentacion-admin">
                <h1>Inicio</h1>
                <p>Resumen general de la actividad del sistema.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true">
                    <i class="bi bi-person"></i>
                </span>
                <span class="identidad-admin">
                    <strong><?php echo escaparAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin">
            <?php if ($errorResumen): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar el resumen completo. Comprueba que Workbench continúe conectado.
                </div>
            <?php endif; ?>

            <section class="grid-resumen-admin" aria-label="Actividad reciente">
                <article class="panel-admin">
                    <div class="cabecera-panel-admin">
                        <div>
                            <span class="sobrelinea-panel">Actividad reciente</span>
                            <h2>Últimos tickets</h2>
                        </div>
                        <a href="<?php echo $urlAdministrador; ?>?pagina=tickets">Ver todos</a>
                    </div>

                    <div class="lista-resumen-admin">
                        <?php if (!$ticketsRecientes): ?>
                            <div class="estado-vacio-admin">
                                <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                                <p>No hay tickets para mostrar.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($ticketsRecientes as $ticket): ?>
                            <section class="item-resumen-admin">
                                <div class="meta-resumen-admin">
                                    <span class="etiqueta-admin prioridad-<?php echo claseEtiquetaAdministrador($ticket["prioridad"]); ?>">
                                        <?php echo escaparAdministrador($ticket["prioridad"]); ?>
                                    </span>
                                    <span class="etiqueta-admin estado-<?php echo claseEtiquetaAdministrador($ticket["estado"]); ?>">
                                        <?php echo escaparAdministrador($ticket["estado"]); ?>
                                    </span>
                                    <time datetime="<?php echo escaparAdministrador($ticket["fecha_generado"]); ?>">
                                        <?php echo fechaAdministrador($ticket["fecha_generado"]); ?>
                                    </time>
                                </div>
                                <h3>Ticket #<?php echo escaparAdministrador($ticket["id_ticket"]); ?> · <?php echo escaparAdministrador($ticket["equipo"]); ?></h3>
                                <p><?php echo escaparAdministrador($ticket["descripcion"]); ?></p>
                                <a class="ver-detalle-admin" href="<?php echo $urlAdministrador; ?>?pagina=tickets#ticket-<?php echo escaparAdministrador($ticket["id_ticket"]); ?>">
                                    Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel-admin">
                    <div class="cabecera-panel-admin">
                        <div>
                            <span class="sobrelinea-panel">Bandeja de entrada</span>
                            <h2>Últimas solicitudes</h2>
                        </div>
                        <a href="<?php echo $urlAdministrador; ?>?pagina=solicitudes">Ver todas</a>
                    </div>

                    <div class="lista-resumen-admin">
                        <?php if (!$solicitudesRecientes): ?>
                            <div class="estado-vacio-admin">
                                <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                                <p>No hay solicitudes para mostrar.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($solicitudesRecientes as $solicitud): ?>
                            <section class="item-resumen-admin">
                                <div class="meta-resumen-admin">
                                    <span class="etiqueta-admin estado-<?php echo claseEtiquetaAdministrador($solicitud["estado"]); ?>">
                                        <?php echo escaparAdministrador($solicitud["estado"]); ?>
                                    </span>
                                    <span class="remitente-admin">
                                        <?php echo escaparAdministrador($solicitud["docente"]); ?>
                                    </span>
                                    <time datetime="<?php echo escaparAdministrador($solicitud["fecha_envio"]); ?>">
                                        <?php echo fechaAdministrador($solicitud["fecha_envio"]); ?>
                                    </time>
                                </div>
                                <h3><?php echo escaparAdministrador($solicitud["asunto"]); ?></h3>
                                <p><?php echo escaparAdministrador($solicitud["descripcion"]); ?></p>
                                <a class="ver-detalle-admin" href="<?php echo $urlAdministrador; ?>?pagina=solicitudes#solicitud-<?php echo escaparAdministrador($solicitud["id_solicitud"]); ?>">
                                    Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <section class="grid-inferior-admin">
                <article class="panel-admin panel-planillas-admin">
                    <div class="cabecera-panel-admin">
                        <div>
                            <span class="sobrelinea-panel">Control de laboratorios</span>
                            <h2>Planillas recientes</h2>
                        </div>
                        <a href="<?php echo $urlAdministrador; ?>?pagina=planillas">Ver todas</a>
                    </div>

                    <div class="contenedor-tabla-admin">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Laboratorio</th>
                                    <th>Docente</th>
                                    <th>Fecha</th>
                                    <th><span class="solo-lector">Acción</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$planillasRecientes): ?>
                                    <tr>
                                        <td colspan="5" class="fila-vacia-admin">No hay planillas para mostrar.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($planillasRecientes as $planilla): ?>
                                    <tr>
                                        <td>#<?php echo escaparAdministrador($planilla["id_planilla"]); ?></td>
                                        <td><?php echo escaparAdministrador($planilla["laboratorio"]); ?></td>
                                        <td><?php echo escaparAdministrador($planilla["docente"]); ?></td>
                                        <td><?php echo fechaAdministrador($planilla["fecha"]); ?></td>
                                        <td>
                                            <a href="<?php echo $urlAdministrador; ?>?pagina=planillas#planilla-<?php echo escaparAdministrador($planilla["id_planilla"]); ?>">
                                                Ver más
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <aside class="panel-admin panel-avisos-admin">
                    <div class="cabecera-panel-admin">
                        <div>
                            <span class="sobrelinea-panel">Estado general</span>
                            <h2>Avisos y notificaciones</h2>
                        </div>
                    </div>

                    <div class="lista-avisos-admin">
                        <?php foreach ($avisos as $aviso): ?>
                            <article class="aviso-admin">
                                <span class="icono-aviso-admin" aria-hidden="true">
                                    <i class="bi <?php echo escaparAdministrador($aviso["icono"]); ?>"></i>
                                </span>
                                <div>
                                    <h3><?php echo escaparAdministrador($aviso["titulo"]); ?></h3>
                                    <p><?php echo escaparAdministrador($aviso["detalle"]); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </section>
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

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuAdmin(false);
            }
        });
    </script>
</body>
</html>
