<?php

function escaparInicioDocente(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function claseEstadoInicioDocente(string $estado): string
{
    return match ($estado) {
        "Pendiente" => "pendiente",
        "En proceso" => "proceso",
        "Completada" => "completada",
        default => "neutro"
    };
}

function fechaInicioDocente(?string $fecha): string
{
    $marcaTiempo = $fecha ? strtotime($fecha) : false;

    return $marcaTiempo === false ? "Sin fecha" : date("d/m/Y", $marcaTiempo);
}

$urlDocente = BASE_URL . "/app/controladores/DocenteController.php";
$nombreCompletoDocente = trim((string) ($_SESSION["nombre"] ?? "Docente"));
$partesNombreDocente = preg_split('/\s+/', $nombreCompletoDocente) ?: [];
$primerNombreDocente = $partesNombreDocente[0] ?? "Docente";
$tarjetasResumenDocente = [
    ["titulo" => "Solicitudes enviadas", "valor" => $resumenDocente["total"], "estado" => ""],
    ["titulo" => "Pendientes", "valor" => $resumenDocente["pendientes"], "estado" => "Pendiente"],
    ["titulo" => "En proceso", "valor" => $resumenDocente["en_proceso"], "estado" => "En proceso"],
    ["titulo" => "Completadas", "valor" => $resumenDocente["completadas"], "estado" => "Completada"]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Inicio del docente</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente.css?v=20260823-responsive-1">
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
            <a class="activo" href="<?php echo $urlDocente; ?>?pagina=inicio" aria-current="page">Inicio</a>
            <a href="<?php echo $urlDocente; ?>?pagina=llenar_planilla">Llenar planilla</a>
            <a href="<?php echo $urlDocente; ?>?pagina=mis_solicitudes">Solicitud</a>
            <a href="<?php echo $urlDocente; ?>?pagina=mi_perfil">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-docente" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            Cerrar sesión
        </a>
    </aside>

    <div class="pagina-docente">
        <header class="encabezado-docente">
            <div class="presentacion-docente">
                <span class="marca-docente">GesTIck</span>
                <h1>¡Bienvenido, <?php echo escaparInicioDocente($primerNombreDocente); ?>!</h1>
                <p>Aquí tienes un resumen general de la actividad de tus solicitudes.</p>
            </div>

            <a class="usuario-docente" href="<?php echo $urlDocente; ?>?pagina=mi_perfil" aria-label="Ir a mi perfil">
                <span class="avatar-docente" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-docente">
                    <strong>Docente</strong>
                    <span><?php echo escaparInicioDocente($nombreCompletoDocente); ?></span>
                </span>
            </a>
        </header>

        <main class="contenido-docente">
            <?php if ($errorInicioDocente): ?>
                <div class="alerta-docente" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar el resumen. Comprueba que MySQL continúe disponible.
                </div>
            <?php endif; ?>

            <section class="resumen-docente" aria-label="Resumen de solicitudes">
                <?php foreach ($tarjetasResumenDocente as $tarjeta): ?>
                    <?php
                    $parametrosResumen = ["pagina" => "mis_solicitudes"];
                    if ($tarjeta["estado"] !== "") {
                        $parametrosResumen["estado"] = $tarjeta["estado"];
                    }
                    ?>
                    <article class="tarjeta-resumen-docente">
                        <span><?php echo escaparInicioDocente($tarjeta["titulo"]); ?></span>
                        <strong><?php echo (int) $tarjeta["valor"]; ?></strong>
                        <a href="<?php echo escaparInicioDocente($urlDocente . "?" . http_build_query($parametrosResumen)); ?>">Ver todas</a>
                    </article>
                <?php endforeach; ?>
            </section>

            <div class="grid-inicio-docente">
                <section class="panel-docente panel-solicitudes-docente" id="solicitudesRecientes" aria-labelledby="tituloSolicitudesRecientes">
                    <header class="cabecera-panel-docente">
                        <div>
                            <span class="sobrelinea-docente">Actividad reciente</span>
                            <h2 id="tituloSolicitudesRecientes">Solicitudes recientes</h2>
                        </div>
                        <a href="<?php echo $urlDocente; ?>?pagina=mis_solicitudes">Ver todas</a>
                    </header>

                    <?php if (!$errorInicioDocente && !$solicitudesRecientesDocente): ?>
                        <div class="vacio-docente">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                            <h3>Aún no enviaste solicitudes</h3>
                            <p>Cuando crees una, podrás seguir su estado desde aquí.</p>
                            <a href="<?php echo $urlDocente; ?>?pagina=mis_solicitudes">Ver solicitudes</a>
                        </div>
                    <?php else: ?>
                        <div class="tabla-docente-contenedor">
                            <table class="tabla-solicitudes-docente">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Asunto</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th><span class="solo-lector-docente">Acción</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitudesRecientesDocente as $solicitud): ?>
                                        <tr>
                                            <td data-label="ID">#<?php echo (int) $solicitud["id_solicitud"]; ?></td>
                                            <td data-label="Asunto">
                                                <strong><?php echo escaparInicioDocente($solicitud["asunto"]); ?></strong>
                                                <small><?php echo escaparInicioDocente($solicitud["descripcion"]); ?></small>
                                            </td>
                                            <td data-label="Estado">
                                                <span class="estado-solicitud-docente estado-<?php echo claseEstadoInicioDocente($solicitud["estado"]); ?>">
                                                    <?php echo escaparInicioDocente($solicitud["estado"]); ?>
                                                </span>
                                            </td>
                                            <td data-label="Fecha"><?php echo fechaInicioDocente($solicitud["fecha_envio"]); ?></td>
                                            <td data-label="Acción">
                                                <a class="ver-solicitud-docente" href="<?php echo escaparInicioDocente($urlDocente . "?pagina=mis_solicitudes&solicitud=" . (int) $solicitud["id_solicitud"]); ?>">Ver más</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <aside class="panel-docente panel-avisos-docente" aria-labelledby="tituloAvisosDocente">
                    <header class="cabecera-panel-docente">
                        <div>
                            <span class="sobrelinea-docente">Seguimiento</span>
                            <h2 id="tituloAvisosDocente">Avisos y notificaciones</h2>
                        </div>
                    </header>

                    <?php if (!$avisosDocente): ?>
                        <div class="sin-avisos-docente">
                            <i class="bi bi-bell" aria-hidden="true"></i>
                            <strong>Todo al día</strong>
                            <p>No tienes avisos nuevos por el momento.</p>
                        </div>
                    <?php else: ?>
                        <div class="lista-avisos-docente">
                            <?php foreach ($avisosDocente as $aviso): ?>
                                <article class="aviso-docente aviso-<?php echo escaparInicioDocente($aviso["tipo"]); ?>">
                                    <span class="marca-aviso-docente" aria-hidden="true"></span>
                                    <div>
                                        <strong><?php echo escaparInicioDocente($aviso["titulo"]); ?></strong>
                                        <p><?php echo escaparInicioDocente($aviso["texto"]); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </main>

        <footer class="footer-docente">
            GesTIck · Sistema de gestión de recursos y soporte de informática
        </footer>
    </div>

    <script>
        const botonMenuDocente = document.getElementById("botonMenuDocente");
        const menuDocente = document.getElementById("menuDocente");
        const fondoMenuDocente = document.getElementById("fondoMenuDocente");

        function cambiarMenuDocente(abierto) {
            menuDocente.classList.toggle("abierto", abierto);
            fondoMenuDocente.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-docente-abierto", abierto);
            botonMenuDocente.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        botonMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(!menuDocente.classList.contains("abierto"));
        });

        fondoMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(false);
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuDocente(false);
            }
        });
    </script>
</body>
</html>
