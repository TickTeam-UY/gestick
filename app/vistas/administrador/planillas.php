<?php

function escaparPlanillasAdministrador(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function fechaPlanillasAdministrador(string $fecha): string
{
    $valor = DateTimeImmutable::createFromFormat("Y-m-d", $fecha);

    return $valor ? $valor->format("d/m/Y") : $fecha;
}

function horaPlanillasAdministrador(?string $hora): string
{
    if (!$hora) {
        return "Sin registrar";
    }

    return mb_substr($hora, 0, 5);
}

function urlPaginaPlanillasAdministrador(int $pagina, array $filtros): string
{
    $parametros = ["pagina" => "planillas"];

    foreach (["buscar", "orden", "docente", "ubicacion", "turno", "asignatura"] as $campo) {
        $valor = $filtros[$campo] ?? "";

        if ($valor !== "" && $valor !== 0 && !($campo === "orden" && $valor === "fecha")) {
            $parametros[$campo] = $valor;
        }
    }

    if ($pagina > 1) {
        $parametros["p"] = $pagina;
    }

    return BASE_URL . "/app/controladores/AdministradorController.php?" . http_build_query($parametros);
}

function datosDetallePlanillaAdministrador(array $planilla): string
{
    $datos = [
        "id" => (int) $planilla["id_planilla"],
        "fecha" => fechaPlanillasAdministrador($planilla["fecha"]),
        "horario" => horaPlanillasAdministrador($planilla["hora_inicio"]) . " a " . horaPlanillasAdministrador($planilla["hora_fin"]),
        "docente" => $planilla["docente"],
        "correo" => $planilla["correo_docente"],
        "grupo" => $planilla["grupo"],
        "asignatura" => $planilla["asignatura"],
        "turno" => $planilla["turno"],
        "ubicacion" => $planilla["ubicacion"],
        "total" => (int) $planilla["total_equipos"],
        "correctos" => (int) $planilla["equipos_correctos"],
        "faltantes" => (int) $planilla["equipos_faltantes"],
        "danados" => (int) $planilla["equipos_danados"],
        "detalles" => array_map(
            static fn (array $detalle): array => [
                "codigo" => $detalle["codigo"],
                "tipo" => $detalle["tipo_equipo"],
                "modelo" => $detalle["modelo"] ?: "Sin modelo",
                "alumno" => $detalle["alumno"] ?: "Sin alumno asignado",
                "estado" => $detalle["estado_registrado"],
                "observacion" => $detalle["observacion"] ?: "Sin observaciones"
            ],
            $planilla["detalles"] ?? []
        )
    ];

    return escaparPlanillasAdministrador(json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ));
}

$urlAdministrador = BASE_URL . "/app/controladores/AdministradorController.php";
$urlPlanillasAdministrador = $urlAdministrador . "?pagina=planillas";
$nombreAdministrador = $_SESSION["nombre"] ?? "Administrador";
$rolAdministrador = $_SESSION["rol"] ?? "Administrador";
$cantidadFiltrosActivos = count(array_filter([
    $filtrosPlanillas["docente"],
    $filtrosPlanillas["ubicacion"],
    $filtrosPlanillas["turno"],
    $filtrosPlanillas["asignatura"]
]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Planillas del administrador</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador.css?v=20260824-a11y-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/administrador-planillas.css?v=20260822-1">
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
            <a class="activo" href="<?php echo $urlPlanillasAdministrador; ?>"><span>Planillas</span></a>
            <a href="<?php echo $urlAdministrador; ?>?pagina=equipos"><span>Equipos</span></a>
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
        <header class="encabezado-admin">
            <div class="presentacion-admin">
                <h1>Planillas</h1>
                <p>Consulta el uso de laboratorios, grupos y equipos registrado por los docentes.</p>
            </div>

            <div class="usuario-admin">
                <span class="avatar-admin" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-admin">
                    <strong><?php echo escaparPlanillasAdministrador($rolAdministrador); ?></strong>
                    <span><?php echo escaparPlanillasAdministrador($nombreAdministrador); ?></span>
                </span>
            </div>
        </header>

        <main class="contenido-admin contenido-planillas-admin">
            <?php if ($errorPlanillasAdministrador): ?>
                <div class="alerta-carga-admin" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    No fue posible cargar las planillas. Comprueba que Workbench continúe conectado.
                </div>
            <?php endif; ?>

            <section class="panel-planillas-listado" aria-labelledby="tituloTodasPlanillas">
                <form class="barra-planillas-admin" method="GET" action="<?php echo $urlAdministrador; ?>">
                    <input type="hidden" name="pagina" value="planillas">

                    <label class="orden-planillas-admin">
                        <span class="sr-only-planillas">Ordenar planillas</span>
                        <select name="orden" onchange="this.form.submit()">
                            <option value="fecha"<?php echo $filtrosPlanillas["orden"] === "fecha" ? " selected" : ""; ?>>Más recientes</option>
                            <option value="antiguas"<?php echo $filtrosPlanillas["orden"] === "antiguas" ? " selected" : ""; ?>>Más antiguas</option>
                            <option value="id"<?php echo $filtrosPlanillas["orden"] === "id" ? " selected" : ""; ?>>ID</option>
                            <option value="docente"<?php echo $filtrosPlanillas["orden"] === "docente" ? " selected" : ""; ?>>Docente</option>
                        </select>
                    </label>

                    <details class="filtros-desplegables-planillas"<?php echo $cantidadFiltrosActivos > 0 ? " open" : ""; ?>>
                        <summary>
                            Filtrar por
                            <?php if ($cantidadFiltrosActivos > 0): ?><span><?php echo $cantidadFiltrosActivos; ?></span><?php endif; ?>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </summary>

                        <div class="panel-filtros-planillas">
                            <label><span>Docente</span><select name="docente"><option value="0">Todos</option><?php foreach ($opcionesFiltrosPlanillas["docentes"] as $opcion): ?><option value="<?php echo escaparPlanillasAdministrador($opcion["id"]); ?>"<?php echo $filtrosPlanillas["docente"] === (int) $opcion["id"] ? " selected" : ""; ?>><?php echo escaparPlanillasAdministrador($opcion["nombre"]); ?></option><?php endforeach; ?></select></label>
                            <label><span>Laboratorio o salón</span><select name="ubicacion"><option value="0">Todos</option><?php foreach ($opcionesFiltrosPlanillas["ubicaciones"] as $opcion): ?><option value="<?php echo escaparPlanillasAdministrador($opcion["id"]); ?>"<?php echo $filtrosPlanillas["ubicacion"] === (int) $opcion["id"] ? " selected" : ""; ?>><?php echo escaparPlanillasAdministrador($opcion["nombre"]); ?></option><?php endforeach; ?></select></label>
                            <label><span>Turno</span><select name="turno"><option value="0">Todos</option><?php foreach ($opcionesFiltrosPlanillas["turnos"] as $opcion): ?><option value="<?php echo escaparPlanillasAdministrador($opcion["id"]); ?>"<?php echo $filtrosPlanillas["turno"] === (int) $opcion["id"] ? " selected" : ""; ?>><?php echo escaparPlanillasAdministrador($opcion["nombre"]); ?></option><?php endforeach; ?></select></label>
                            <label><span>Asignatura</span><select name="asignatura"><option value="0">Todas</option><?php foreach ($opcionesFiltrosPlanillas["asignaturas"] as $opcion): ?><option value="<?php echo escaparPlanillasAdministrador($opcion["id"]); ?>"<?php echo $filtrosPlanillas["asignatura"] === (int) $opcion["id"] ? " selected" : ""; ?>><?php echo escaparPlanillasAdministrador($opcion["nombre"]); ?></option><?php endforeach; ?></select></label>
                            <div class="acciones-filtros-planillas">
                                <?php if ($cantidadFiltrosActivos > 0): ?><a href="<?php echo $urlPlanillasAdministrador; ?>">Limpiar</a><?php endif; ?>
                                <button type="submit">Aplicar filtros</button>
                            </div>
                        </div>
                    </details>

                    <div class="titulo-barra-planillas">
                        <h2 id="tituloTodasPlanillas">Todas las planillas</h2>
                        <span><?php echo escaparPlanillasAdministrador($totalPlanillasAdministrador); ?> registradas</span>
                    </div>

                    <label class="buscador-planillas-admin">
                        <span class="sr-only-planillas">Buscar planillas</span>
                        <input type="search" name="buscar" maxlength="100" value="<?php echo escaparPlanillasAdministrador($filtrosPlanillas["buscar"]); ?>" placeholder="Buscar planilla...">
                        <button type="submit" aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
                    </label>
                </form>

                <div class="grid-planillas-admin">
                    <?php foreach ($planillasAdministrador as $planilla): ?>
                        <article class="tarjeta-planilla-admin" id="planilla-<?php echo escaparPlanillasAdministrador($planilla["id_planilla"]); ?>">
                            <header>
                                <div>
                                    <span class="id-planilla-admin">Planilla #<?php echo escaparPlanillasAdministrador($planilla["id_planilla"]); ?></span>
                                    <h3><?php echo escaparPlanillasAdministrador($planilla["ubicacion"]); ?></h3>
                                </div>
                                <time datetime="<?php echo escaparPlanillasAdministrador($planilla["fecha"]); ?>"><?php echo fechaPlanillasAdministrador($planilla["fecha"]); ?></time>
                            </header>

                            <div class="datos-planilla-admin">
                                <p><i class="bi bi-person" aria-hidden="true"></i><span><?php echo escaparPlanillasAdministrador($planilla["docente"]); ?></span></p>
                                <p><i class="bi bi-book" aria-hidden="true"></i><span><?php echo escaparPlanillasAdministrador($planilla["asignatura"]); ?></span></p>
                                <p><i class="bi bi-people" aria-hidden="true"></i><span><?php echo escaparPlanillasAdministrador($planilla["grupo"]); ?> · <?php echo escaparPlanillasAdministrador($planilla["turno"]); ?></span></p>
                                <p><i class="bi bi-clock" aria-hidden="true"></i><span><?php echo horaPlanillasAdministrador($planilla["hora_inicio"]); ?>–<?php echo horaPlanillasAdministrador($planilla["hora_fin"]); ?></span></p>
                            </div>

                            <footer>
                                <div class="resumen-equipos-planilla">
                                    <span><strong><?php echo escaparPlanillasAdministrador($planilla["total_equipos"]); ?></strong> equipos</span>
                                    <?php if ((int) $planilla["equipos_faltantes"] > 0): ?><span class="resumen-alerta-planilla"><?php echo escaparPlanillasAdministrador($planilla["equipos_faltantes"]); ?> faltantes</span><?php endif; ?>
                                    <?php if ((int) $planilla["equipos_danados"] > 0): ?><span class="resumen-peligro-planilla"><?php echo escaparPlanillasAdministrador($planilla["equipos_danados"]); ?> dañados</span><?php endif; ?>
                                </div>
                                <button type="button" data-ver-planilla data-planilla="<?php echo datosDetallePlanillaAdministrador($planilla); ?>">Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                            </footer>
                        </article>
                    <?php endforeach; ?>

                    <?php if (!$planillasAdministrador && !$errorPlanillasAdministrador): ?>
                        <div class="estado-vacio-planillas">
                            <i class="bi bi-clipboard2-data" aria-hidden="true"></i>
                            <h3>No encontramos planillas</h3>
                            <p>Prueba con otros términos o elimina los filtros aplicados.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($totalPaginasPlanillasAdministrador > 1): ?>
                    <nav class="paginacion-planillas-admin" aria-label="Paginación de planillas">
                        <?php if ($paginaActualPlanillasAdministrador > 1): ?><a href="<?php echo escaparPlanillasAdministrador(urlPaginaPlanillasAdministrador($paginaActualPlanillasAdministrador - 1, $filtrosPlanillas)); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</a><?php else: ?><span class="deshabilitado-planillas"><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</span><?php endif; ?>
                        <span>Página <?php echo escaparPlanillasAdministrador($paginaActualPlanillasAdministrador); ?> de <?php echo escaparPlanillasAdministrador($totalPaginasPlanillasAdministrador); ?></span>
                        <?php if ($paginaActualPlanillasAdministrador < $totalPaginasPlanillasAdministrador): ?><a href="<?php echo escaparPlanillasAdministrador(urlPaginaPlanillasAdministrador($paginaActualPlanillasAdministrador + 1, $filtrosPlanillas)); ?>">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></a><?php else: ?><span class="deshabilitado-planillas">Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i></span><?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>
        </main>

        <footer class="footer-admin"><p>GesTIck · Sistema de gestión de recursos y soporte de informática</p></footer>
    </div>

    <dialog class="dialog-admin dialog-detalle-planilla" id="dialogDetallePlanilla">
        <div class="contenido-dialog-planilla">
            <header class="cabecera-dialog-admin">
                <div><span class="sobrelinea-panel">Registro de uso</span><h2 id="tituloDetallePlanilla">Detalle de planilla</h2></div>
                <button type="button" class="cerrar-dialog-admin" data-cerrar-dialog aria-label="Cerrar">×</button>
            </header>

            <div class="cuerpo-detalle-planilla">
                <dl class="resumen-detalle-planilla">
                    <div><dt>Fecha</dt><dd id="detalleFechaPlanilla"></dd></div>
                    <div><dt>Horario</dt><dd id="detalleHorarioPlanilla"></dd></div>
                    <div><dt>Docente</dt><dd id="detalleDocentePlanilla"></dd></div>
                    <div><dt>Correo</dt><dd id="detalleCorreoPlanilla"></dd></div>
                    <div><dt>Grupo</dt><dd id="detalleGrupoPlanilla"></dd></div>
                    <div><dt>Asignatura</dt><dd id="detalleAsignaturaPlanilla"></dd></div>
                    <div><dt>Turno</dt><dd id="detalleTurnoPlanilla"></dd></div>
                    <div><dt>Laboratorio o salón</dt><dd id="detalleUbicacionPlanilla"></dd></div>
                </dl>

                <div class="conteos-detalle-planilla" id="conteosDetallePlanilla"></div>

                <section class="equipos-detalle-planilla" aria-labelledby="tituloEquiposDetallePlanilla">
                    <h3 id="tituloEquiposDetallePlanilla">Equipos registrados</h3>
                    <div class="tabla-detalle-planilla">
                        <table>
                            <thead><tr><th>Equipo</th><th>Alumno</th><th>Estado</th><th>Observación</th></tr></thead>
                            <tbody id="filasEquiposPlanilla"></tbody>
                        </table>
                        <p class="sin-equipos-planilla" id="sinEquiposPlanilla" hidden>No se registraron equipos en esta planilla.</p>
                    </div>
                </section>
            </div>
        </div>
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
        fondoMenuAdmin.addEventListener("click", function () { cambiarMenuAdmin(false); });

        const dialogDetallePlanilla = document.getElementById("dialogDetallePlanilla");

        function crearCelda(texto) {
            const celda = document.createElement("td");
            celda.textContent = texto;
            return celda;
        }

        document.querySelectorAll("[data-ver-planilla]").forEach(function (boton) {
            boton.addEventListener("click", function () {
                const planilla = JSON.parse(boton.dataset.planilla);
                document.getElementById("tituloDetallePlanilla").textContent = "Planilla #" + planilla.id;
                document.getElementById("detalleFechaPlanilla").textContent = planilla.fecha;
                document.getElementById("detalleHorarioPlanilla").textContent = planilla.horario;
                document.getElementById("detalleDocentePlanilla").textContent = planilla.docente;
                document.getElementById("detalleCorreoPlanilla").textContent = planilla.correo;
                document.getElementById("detalleGrupoPlanilla").textContent = planilla.grupo;
                document.getElementById("detalleAsignaturaPlanilla").textContent = planilla.asignatura;
                document.getElementById("detalleTurnoPlanilla").textContent = planilla.turno;
                document.getElementById("detalleUbicacionPlanilla").textContent = planilla.ubicacion;

                const conteos = document.getElementById("conteosDetallePlanilla");
                conteos.replaceChildren();
                [["Total", planilla.total, ""], ["Correctos", planilla.correctos, "correcto"], ["Faltantes", planilla.faltantes, "faltante"], ["Dañados", planilla.danados, "danado"]].forEach(function (dato) {
                    const elemento = document.createElement("span");
                    elemento.className = dato[2] ? "conteo-" + dato[2] : "";
                    const valor = document.createElement("strong");
                    valor.textContent = dato[1];
                    elemento.append(valor, document.createTextNode(dato[0]));
                    conteos.appendChild(elemento);
                });

                const filas = document.getElementById("filasEquiposPlanilla");
                filas.replaceChildren();
                document.getElementById("sinEquiposPlanilla").hidden = planilla.detalles.length > 0;

                planilla.detalles.forEach(function (detalle) {
                    const fila = document.createElement("tr");
                    const equipo = document.createElement("td");
                    const codigo = document.createElement("strong");
                    codigo.textContent = detalle.codigo;
                    const descripcion = document.createElement("span");
                    descripcion.textContent = detalle.tipo + " · " + detalle.modelo;
                    equipo.append(codigo, descripcion);
                    fila.append(equipo, crearCelda(detalle.alumno));
                    const estado = crearCelda(detalle.estado);
                    estado.className = "estado-detalle-" + detalle.estado.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    fila.append(estado, crearCelda(detalle.observacion));
                    filas.appendChild(fila);
                });

                dialogDetallePlanilla.showModal();
            });
        });

        document.querySelectorAll("[data-cerrar-dialog]").forEach(function (boton) {
            boton.addEventListener("click", function () { boton.closest("dialog").close(); });
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") { cambiarMenuAdmin(false); }
        });
    </script>
</body>
</html>
