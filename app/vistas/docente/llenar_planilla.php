<?php

function escaparPlanillaDocente(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function valorAnteriorPlanillaDocente(array $datos, string $clave, mixed $predeterminado = ""): mixed
{
    return $datos[$clave] ?? $predeterminado;
}

$urlDocente = BASE_URL . "/app/controladores/DocenteController.php";
$nombreDocente = trim((string) ($_SESSION["nombre"] ?? "Docente"));
$asignaturaPredeterminada = count($opcionesPlanillaDocente["asignaturas"]) === 1
    ? $opcionesPlanillaDocente["asignaturas"][0]["id"]
    : "";
$turnoPredeterminado = count($opcionesPlanillaDocente["turnos"]) === 1
    ? $opcionesPlanillaDocente["turnos"][0]["id"]
    : "";
$fechaPredeterminada = date("Y-m-d");
$horaPredeterminada = date("H:i");
$detallesAnteriores = is_array($datosAnterioresPlanilla["detalles"] ?? null)
    ? array_values($datosAnterioresPlanilla["detalles"])
    : [];
$formularioDisponible = !$errorCargaPlanillaDocente
    && $opcionesPlanillaDocente["grupos"]
    && $opcionesPlanillaDocente["asignaturas"]
    && $opcionesPlanillaDocente["turnos"]
    && $opcionesPlanillaDocente["ubicaciones"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck - Llenar planilla</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente.css?v=20260823-responsive-1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/docente-planilla.css?v=20260822-1">
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
            <a class="activo" href="<?php echo $urlDocente; ?>?pagina=llenar_planilla" aria-current="page">Llenar planilla</a>
            <a href="<?php echo $urlDocente; ?>?pagina=mis_solicitudes">Solicitud</a>
            <a href="<?php echo $urlDocente; ?>?pagina=mi_perfil">Mi perfil</a>
        </nav>

        <a class="cerrar-sesion-docente" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">
            Cerrar sesión
        </a>
    </aside>

    <div class="pagina-docente">
        <header class="encabezado-docente encabezado-planilla-docente">
            <div class="presentacion-docente">
                <span class="marca-docente">GesTIck</span>
                <h1>Llenar planilla</h1>
                <p>Registra el uso del aula y el estado de cada equipo al finalizar la clase.</p>
            </div>

            <a class="usuario-docente" href="<?php echo $urlDocente; ?>?pagina=mi_perfil" aria-label="Ir a mi perfil">
                <span class="avatar-docente" aria-hidden="true"><i class="bi bi-person"></i></span>
                <span class="identidad-docente">
                    <strong>Docente</strong>
                    <span><?php echo escaparPlanillaDocente($nombreDocente); ?></span>
                </span>
            </a>
        </header>

        <main class="contenido-planilla-docente">
            <?php if ($mensajeDocente): ?>
                <div class="mensaje-planilla-docente mensaje-<?php echo escaparPlanillaDocente($mensajeDocente["tipo"]); ?>" role="status">
                    <i class="bi <?php echo $mensajeDocente["tipo"] === "exito" ? "bi-check-circle" : "bi-exclamation-circle"; ?>" aria-hidden="true"></i>
                    <span><?php echo escaparPlanillaDocente($mensajeDocente["texto"]); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$formularioDisponible): ?>
                <div class="mensaje-planilla-docente mensaje-error" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>No es posible completar una planilla: revisa que el docente tenga grupo, asignatura, turno y aulas con equipos asignados.</span>
                </div>
            <?php endif; ?>

            <form class="formulario-planilla-docente" id="formularioPlanillaDocente" method="POST" action="<?php echo $urlDocente; ?>?pagina=llenar_planilla">
                <input type="hidden" name="csrf_token" value="<?php echo escaparPlanillaDocente($csrfToken); ?>">
                <input type="hidden" name="accion" value="guardar_planilla">

                <section class="panel-formulario-planilla" aria-labelledby="tituloNuevaPlanilla">
                    <header class="titulo-planilla-docente">
                        <div>
                            <span class="sobrelinea-docente">Nuevo registro de clase</span>
                            <h2 id="tituloNuevaPlanilla">Planilla N°<?php echo str_pad((string) $opcionesPlanillaDocente["siguiente_id"], 3, "0", STR_PAD_LEFT); ?></h2>
                        </div>
                        <span class="estado-borrador-planilla"><i class="bi bi-pencil" aria-hidden="true"></i> Sin guardar</span>
                    </header>

                    <div class="datos-generales-planilla">
                        <label class="campo-planilla campo-docente-planilla">
                            <span>Docente</span>
                            <input type="text" value="<?php echo escaparPlanillaDocente($nombreDocente); ?>" readonly>
                        </label>

                        <label class="campo-planilla">
                            <span>Fecha</span>
                            <input type="date" name="fecha" max="<?php echo $fechaPredeterminada; ?>" value="<?php echo escaparPlanillaDocente(valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "fecha", $fechaPredeterminada)); ?>" required>
                        </label>

                        <label class="campo-planilla">
                            <span>Hora de entrada</span>
                            <input type="time" name="hora_inicio" value="<?php echo escaparPlanillaDocente(valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "hora_inicio", $horaPredeterminada)); ?>" required>
                        </label>

                        <label class="campo-planilla">
                            <span>Hora de salida</span>
                            <input type="time" name="hora_fin" value="<?php echo escaparPlanillaDocente(valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "hora_fin")); ?>" required>
                        </label>

                        <label class="campo-planilla">
                            <span>Grupo</span>
                            <select name="id_grupo" id="grupoPlanillaDocente" required>
                                <option value="">Selecciona un grupo</option>
                                <?php foreach ($opcionesPlanillaDocente["grupos"] as $grupo): ?>
                                    <option value="<?php echo escaparPlanillaDocente($grupo["id"]); ?>"<?php echo (string) valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "id_grupo") === (string) $grupo["id"] ? " selected" : ""; ?>><?php echo escaparPlanillaDocente($grupo["nombre"]); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="campo-planilla">
                            <span>Turno</span>
                            <select name="id_turno" required>
                                <option value="">Selecciona un turno</option>
                                <?php foreach ($opcionesPlanillaDocente["turnos"] as $turno): ?>
                                    <?php $turnoSeleccionado = valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "id_turno", $turnoPredeterminado); ?>
                                    <option value="<?php echo escaparPlanillaDocente($turno["id"]); ?>"<?php echo (string) $turnoSeleccionado === (string) $turno["id"] ? " selected" : ""; ?>><?php echo escaparPlanillaDocente($turno["nombre"]); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="campo-planilla campo-asignatura-planilla">
                            <span>Asignatura</span>
                            <select name="id_asignatura" required>
                                <option value="">Selecciona una asignatura</option>
                                <?php foreach ($opcionesPlanillaDocente["asignaturas"] as $asignatura): ?>
                                    <?php $asignaturaSeleccionada = valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "id_asignatura", $asignaturaPredeterminada); ?>
                                    <option value="<?php echo escaparPlanillaDocente($asignatura["id"]); ?>"<?php echo (string) $asignaturaSeleccionada === (string) $asignatura["id"] ? " selected" : ""; ?>><?php echo escaparPlanillaDocente($asignatura["nombre"]); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="campo-planilla">
                            <span>Aula o laboratorio</span>
                            <select name="id_ubicacion" id="ubicacionPlanillaDocente" required>
                                <option value="">Selecciona un aula</option>
                                <?php foreach ($opcionesPlanillaDocente["ubicaciones"] as $ubicacion): ?>
                                    <option value="<?php echo escaparPlanillaDocente($ubicacion["id"]); ?>"<?php echo (string) valorAnteriorPlanillaDocente($datosAnterioresPlanilla, "id_ubicacion") === (string) $ubicacion["id"] ? " selected" : ""; ?>>
                                        <?php echo escaparPlanillaDocente($ubicacion["nombre"]); ?> · <?php echo (int) $ubicacion["cantidad_equipos"]; ?> equipos
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                </section>

                <section class="panel-formulario-planilla panel-equipos-planilla" aria-labelledby="tituloEquiposPlanilla">
                    <header class="cabecera-equipos-planilla">
                        <div>
                            <span class="sobrelinea-docente">Control del aula</span>
                            <h2 id="tituloEquiposPlanilla">Estado de los equipos</h2>
                        </div>
                        <span id="contadorEquiposPlanilla">Selecciona un aula</span>
                    </header>

                    <div class="aviso-tickets-planilla">
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                        <p><strong>Incidencias automáticas:</strong> los equipos marcados como dañados o faltantes generarán un ticket pendiente para Administración y los técnicos.</p>
                    </div>

                    <div class="estado-sin-aula-planilla" id="estadoSinAulaPlanilla">
                        <i class="bi bi-pc-display" aria-hidden="true"></i>
                        <h3>Selecciona el aula</h3>
                        <p>Los equipos registrados en esa ubicación aparecerán automáticamente.</p>
                    </div>

                    <div class="tabla-planilla-contenedor" id="contenedorTablaPlanilla" hidden>
                        <table class="tabla-equipos-planilla">
                            <thead>
                                <tr>
                                    <th>Equipo</th>
                                    <th>Alumno</th>
                                    <th>Estado del equipo</th>
                                    <th>Descripción del problema</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoEquiposPlanilla"></tbody>
                        </table>
                    </div>
                </section>

                <div class="acciones-planilla-docente">
                    <a href="<?php echo $urlDocente; ?>?pagina=inicio">Cancelar</a>
                    <button type="submit" id="guardarPlanillaDocente"<?php echo $formularioDisponible ? "" : " disabled"; ?>>
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        Guardar planilla
                    </button>
                </div>
            </form>
        </main>

        <footer class="footer-docente">
            GesTIck · Sistema de gestión de recursos y soporte de informática
        </footer>
    </div>

    <script>
        const botonMenuDocente = document.getElementById("botonMenuDocente");
        const menuDocente = document.getElementById("menuDocente");
        const fondoMenuDocente = document.getElementById("fondoMenuDocente");
        const grupoPlanilla = document.getElementById("grupoPlanillaDocente");
        const ubicacionPlanilla = document.getElementById("ubicacionPlanillaDocente");
        const cuerpoEquipos = document.getElementById("cuerpoEquiposPlanilla");
        const contenedorTabla = document.getElementById("contenedorTablaPlanilla");
        const estadoSinAula = document.getElementById("estadoSinAulaPlanilla");
        const contadorEquipos = document.getElementById("contadorEquiposPlanilla");
        const botonGuardar = document.getElementById("guardarPlanillaDocente");
        const formularioPlanilla = document.getElementById("formularioPlanillaDocente");
        const formularioDisponible = <?php echo $formularioDisponible ? "true" : "false"; ?>;
        const equiposPlanilla = <?php echo json_encode($opcionesPlanillaDocente["equipos"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const alumnosPlanilla = <?php echo json_encode($opcionesPlanillaDocente["alumnos"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const detallesAnteriores = <?php echo json_encode($detallesAnteriores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        function cambiarMenuDocente(abierto) {
            menuDocente.classList.toggle("abierto", abierto);
            fondoMenuDocente.classList.toggle("visible", abierto);
            document.body.classList.toggle("menu-docente-abierto", abierto);
            botonMenuDocente.setAttribute("aria-expanded", abierto ? "true" : "false");
        }

        function escaparHtml(valor) {
            const elemento = document.createElement("span");
            elemento.textContent = String(valor ?? "");
            return elemento.innerHTML;
        }

        function detalleAnteriorParaEquipo(idEquipo) {
            return detallesAnteriores.find(function (detalle) {
                return Number(detalle.id_equipo) === Number(idEquipo);
            }) || {};
        }

        function opcionesAlumnos(idSeleccionado) {
            const grupo = Number(grupoPlanilla.value);
            const alumnos = alumnosPlanilla.filter(function (alumno) {
                return Number(alumno.id_grupo) === grupo;
            });
            let opciones = '<option value="">Sin alumno asignado</option>';

            alumnos.forEach(function (alumno) {
                const seleccionado = Number(idSeleccionado) === Number(alumno.id) ? " selected" : "";
                opciones += `<option value="${Number(alumno.id)}"${seleccionado}>${escaparHtml(alumno.nombre)} · #${Number(alumno.id)}</option>`;
            });

            return opciones;
        }

        function actualizarFilaEquipo(fila) {
            const estado = fila.querySelector("[data-estado-equipo]");
            const observacion = fila.querySelector("[data-observacion-equipo]");
            const hayProblema = estado.value !== "Correcto";

            fila.dataset.estado = estado.value.toLocaleLowerCase("es")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");
            observacion.required = hayProblema;
            observacion.placeholder = hayProblema
                ? "Describe qué ocurrió"
                : "Sin observaciones";
        }

        function renderEquiposPlanilla() {
            const idUbicacion = Number(ubicacionPlanilla.value);
            const equipos = equiposPlanilla.filter(function (equipo) {
                return Number(equipo.id_ubicacion) === idUbicacion;
            });

            cuerpoEquipos.innerHTML = "";
            contenedorTabla.hidden = equipos.length === 0;
            estadoSinAula.hidden = equipos.length > 0;
            botonGuardar.disabled = !formularioDisponible || equipos.length === 0;
            contadorEquipos.textContent = equipos.length === 0
                ? "Selecciona un aula"
                : equipos.length + (equipos.length === 1 ? " equipo" : " equipos");

            equipos.forEach(function (equipo, indice) {
                const anterior = detalleAnteriorParaEquipo(equipo.id);
                const estadoAnterior = ["Correcto", "Faltante", "Dañado"].includes(anterior.estado)
                    ? anterior.estado
                    : "Correcto";
                const fila = document.createElement("tr");

                fila.innerHTML = `
                    <td data-label="Equipo">
                        <input type="hidden" name="detalles[${indice}][id_equipo]" value="${Number(equipo.id)}">
                        <strong>${escaparHtml(equipo.codigo)}</strong>
                        <small>${escaparHtml(equipo.tipo)} · ${escaparHtml(equipo.modelo)}</small>
                        <span>Estado actual: ${escaparHtml(equipo.estado_actual)}</span>
                    </td>
                    <td data-label="Alumno">
                        <select name="detalles[${indice}][id_alumno]" data-alumno-equipo>
                            ${opcionesAlumnos(anterior.id_alumno || "")}
                        </select>
                    </td>
                    <td data-label="Estado del equipo">
                        <select name="detalles[${indice}][estado]" data-estado-equipo required>
                            <option value="Correcto"${estadoAnterior === "Correcto" ? " selected" : ""}>Correcto</option>
                            <option value="Faltante"${estadoAnterior === "Faltante" ? " selected" : ""}>Faltante</option>
                            <option value="Dañado"${estadoAnterior === "Dañado" ? " selected" : ""}>Dañado</option>
                        </select>
                    </td>
                    <td data-label="Descripción del problema">
                        <textarea name="detalles[${indice}][observacion]" maxlength="255" rows="2" data-observacion-equipo>${escaparHtml(anterior.observacion || "")}</textarea>
                    </td>
                `;

                const selectorEstado = fila.querySelector("[data-estado-equipo]");
                selectorEstado.addEventListener("change", function () {
                    actualizarFilaEquipo(fila);
                });
                actualizarFilaEquipo(fila);
                cuerpoEquipos.appendChild(fila);
            });
        }

        botonMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(!menuDocente.classList.contains("abierto"));
        });

        fondoMenuDocente.addEventListener("click", function () {
            cambiarMenuDocente(false);
        });

        ubicacionPlanilla.addEventListener("change", renderEquiposPlanilla);

        grupoPlanilla.addEventListener("change", function () {
            document.querySelectorAll("[data-alumno-equipo]").forEach(function (selector) {
                const valorAnterior = selector.value;
                selector.innerHTML = opcionesAlumnos(valorAnterior);
            });
        });

        formularioPlanilla.addEventListener("submit", function (evento) {
            const alumnosSeleccionados = Array.from(
                document.querySelectorAll("[data-alumno-equipo]")
            ).map(function (selector) {
                return selector.value;
            }).filter(Boolean);
            const alumnosUnicos = new Set(alumnosSeleccionados);

            if (alumnosUnicos.size !== alumnosSeleccionados.length) {
                evento.preventDefault();
                window.alert("Un alumno no puede estar asignado a más de un equipo.");
                return;
            }

            if (!window.confirm("¿Quieres guardar esta planilla? Después no podrá editarse desde este formulario.")) {
                evento.preventDefault();
            }
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cambiarMenuDocente(false);
            }
        });

        renderEquiposPlanilla();
    </script>
</body>
</html>
