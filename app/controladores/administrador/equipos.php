<?php

/* Valida y coordina las altas, cambios y bajas del inventario. */
function validarDatosEquipoAdministrador(array $datos, Equipo $modeloEquipo): array
{
    $codigo = strtoupper(trim($datos["codigo"] ?? ""));
    $numeroSerie = trim($datos["numero_serie"] ?? "");
    $modelo = trim($datos["modelo"] ?? "");
    $idTipo = (int) ($datos["id_tipo_equipo"] ?? 0);
    $idEstado = (int) ($datos["id_estado_equipo"] ?? 0);
    $valorUbicacion = trim((string) ($datos["id_ubicacion"] ?? ""));
    $idUbicacion = $valorUbicacion === "" ? null : (int) $valorUbicacion;
    $esPrestable = isset($datos["es_prestable"]) ? 1 : 0;

    if ($codigo === "" || mb_strlen($codigo) > 30) {
        throw new DomainException("El código es obligatorio y puede tener hasta 30 caracteres.");
    }

    if ($numeroSerie !== "" && mb_strlen($numeroSerie) > 80) {
        throw new DomainException("El número de serie puede tener hasta 80 caracteres.");
    }

    if ($modelo !== "" && mb_strlen($modelo) > 80) {
        throw new DomainException("El modelo puede tener hasta 80 caracteres.");
    }

    if (!$modeloEquipo->tipoExiste($idTipo)) {
        throw new DomainException("Selecciona un tipo de equipo válido.");
    }

    if (!$modeloEquipo->estadoExiste($idEstado)) {
        throw new DomainException("Selecciona un estado de equipo válido.");
    }

    if ($idUbicacion !== null && !$modeloEquipo->ubicacionExiste($idUbicacion)) {
        throw new DomainException("Selecciona un laboratorio o salón válido.");
    }

    return [
        "codigo" => $codigo,
        "numero_serie" => $numeroSerie === "" ? null : $numeroSerie,
        "modelo" => $modelo === "" ? null : $modelo,
        "id_tipo_equipo" => $idTipo,
        "id_estado_equipo" => $idEstado,
        "id_ubicacion" => $idUbicacion,
        "es_prestable" => $esPrestable
    ];
}

if ($pagina === "equipos") {
    require_once __DIR__ . "/../../modelos/Equipo.php";
    $modeloEquipo = new Equipo();

    $csrfToken = tokenAdministrador();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $accion = $_POST["accion"] ?? "";

        if (!validarTokenAdministrador($_POST["csrf_token"] ?? "")) {
            guardarMensajeAdministrador(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirAdministrador("equipos");
        }

        try {
            if ($accion === "crear_ubicacion") {
                $nombre = trim($_POST["nombre_ubicacion"] ?? "");

                if ($nombre === "" || mb_strlen($nombre) > 60) {
                    throw new DomainException(
                        "El nombre del laboratorio o salón es obligatorio y puede tener hasta 60 caracteres."
                    );
                }

                $modeloEquipo->crearUbicacion($nombre);
                guardarMensajeAdministrador("exito", "Laboratorio o salón agregado correctamente.");
            } elseif ($accion === "actualizar_ubicacion") {
                $idUbicacion = (int) ($_POST["id_ubicacion"] ?? 0);
                $nombre = trim($_POST["nombre_ubicacion"] ?? "");

                if ($idUbicacion < 1 || $nombre === "" || mb_strlen($nombre) > 60) {
                    throw new DomainException("Los datos del laboratorio o salón no son válidos.");
                }

                $modeloEquipo->actualizarUbicacion($idUbicacion, $nombre);
                guardarMensajeAdministrador("exito", "Laboratorio o salón actualizado correctamente.");
            } elseif ($accion === "eliminar_ubicacion") {
                $idUbicacion = (int) ($_POST["id_ubicacion"] ?? 0);

                if ($idUbicacion < 1) {
                    throw new DomainException("El laboratorio o salón seleccionado no es válido.");
                }

                $modeloEquipo->eliminarUbicacion($idUbicacion);
                guardarMensajeAdministrador("exito", "Laboratorio o salón eliminado correctamente.");
            } elseif ($accion === "crear_equipo") {
                $equipo = validarDatosEquipoAdministrador($_POST, $modeloEquipo);
                $modeloEquipo->crear(
                    $equipo["codigo"],
                    $equipo["numero_serie"],
                    $equipo["modelo"],
                    $equipo["id_tipo_equipo"],
                    $equipo["id_estado_equipo"],
                    $equipo["id_ubicacion"],
                    $equipo["es_prestable"]
                );
                guardarMensajeAdministrador("exito", "Equipo agregado correctamente.");
            } elseif ($accion === "actualizar_equipo") {
                $idEquipo = (int) ($_POST["id_equipo"] ?? 0);

                if ($idEquipo < 1) {
                    throw new DomainException("El equipo seleccionado no es válido.");
                }

                $equipo = validarDatosEquipoAdministrador($_POST, $modeloEquipo);
                $modeloEquipo->actualizar(
                    $idEquipo,
                    $equipo["codigo"],
                    $equipo["numero_serie"],
                    $equipo["modelo"],
                    $equipo["id_tipo_equipo"],
                    $equipo["id_estado_equipo"],
                    $equipo["id_ubicacion"],
                    $equipo["es_prestable"]
                );
                guardarMensajeAdministrador("exito", "Equipo actualizado correctamente.");
            } elseif ($accion === "eliminar_equipo") {
                $idEquipo = (int) ($_POST["id_equipo"] ?? 0);

                if ($idEquipo < 1) {
                    throw new DomainException("El equipo seleccionado no es válido.");
                }

                $modeloEquipo->eliminar($idEquipo);
                guardarMensajeAdministrador("exito", "Equipo eliminado correctamente.");
            } else {
                throw new DomainException("La acción solicitada no es válida.");
            }
        } catch (DomainException $error) {
            guardarMensajeAdministrador("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al administrar equipos: " . $error->getMessage());

            if ($error->getCode() === 1062) {
                guardarMensajeAdministrador(
                    "error",
                    "Ya existe un registro con ese nombre, código o número de serie."
                );
            } elseif ($error->getCode() === 1451) {
                guardarMensajeAdministrador(
                    "error",
                    "No se puede eliminar porque el registro tiene información asociada."
                );
            } else {
                guardarMensajeAdministrador(
                    "error",
                    "No fue posible guardar los cambios en la base de datos."
                );
            }
        } catch (Throwable $error) {
            error_log("GesTIck - error al administrar equipos: " . $error->getMessage());
            guardarMensajeAdministrador(
                "error",
                "Ocurrió un error inesperado al procesar la solicitud."
            );
        }

        redirigirAdministrador("equipos");
    }

    $laboratorios = [];
    $tiposEquipo = [];
    $estadosEquipo = [];
    $errorEquipos = false;

    try {
        $laboratorios = $modeloEquipo->obtenerLaboratorios(true);
        $tiposEquipo = $modeloEquipo->obtenerTipos();
        $estadosEquipo = $modeloEquipo->obtenerEstados();
    } catch (Throwable $error) {
        $errorEquipos = true;
        error_log("GesTIck - error al cargar equipos: " . $error->getMessage());
    }

    $cantidadEquipos = array_sum(array_map(
        static fn (array $laboratorio): int => count($laboratorio["equipos"]),
        $laboratorios
    ));
    $mensajeAdministrador = consumirMensajeAdministrador();

    require __DIR__ . "/../../vistas/administrador/equipos.php";
    return;
}

