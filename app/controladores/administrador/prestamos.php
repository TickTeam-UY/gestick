<?php

/* Valida y coordina el ciclo de alta, edición y devolución de préstamos. */
function validarFechaPrestamoAdministrador(string $fecha, string $etiqueta): string
{
    $fecha = trim($fecha);
    $valor = DateTimeImmutable::createFromFormat("!Y-m-d", $fecha);

    if (!$valor || $valor->format("Y-m-d") !== $fecha) {
        throw new DomainException("Selecciona una {$etiqueta} válida.");
    }

    if ($valor > new DateTimeImmutable("today")) {
        throw new DomainException("La {$etiqueta} no puede ser posterior a hoy.");
    }

    return $fecha;
}

function validarDatosPrestamoAdministrador(array $datos): array
{
    $idAlumno = (int) ($datos["id_alumno"] ?? 0);
    $idsEquipos = array_values(array_unique(array_filter(array_map(
        "intval",
        is_array($datos["equipos"] ?? null) ? $datos["equipos"] : []
    ))));

    if ($idAlumno < 1) {
        throw new DomainException("Selecciona el alumno que recibe el préstamo.");
    }

    if (!$idsEquipos) {
        throw new DomainException("Selecciona al menos un equipo para el préstamo.");
    }

    return [
        "id_alumno" => $idAlumno,
        "fecha_prestamo" => validarFechaPrestamoAdministrador(
            (string) ($datos["fecha_prestamo"] ?? ""),
            "fecha de préstamo"
        ),
        "equipos" => $idsEquipos
    ];
}

if ($pagina === "prestamos") {
    require_once __DIR__ . "/../../modelos/PrestamoAdministrador.php";
    $modeloPrestamoAdministrador = new PrestamoAdministrador();

    $csrfToken = tokenAdministrador();

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        if (!validarTokenAdministrador($_POST["csrf_token"] ?? "")) {
            guardarMensajeAdministrador(
                "error",
                "La sesión del formulario venció. Vuelve a intentarlo."
            );
            redirigirAdministrador("prestamos");
        }

        try {
            $accion = (string) ($_POST["accion"] ?? "");
            $idPrestamo = (int) ($_POST["id_prestamo"] ?? 0);

            if ($accion === "crear_prestamo") {
                $prestamo = validarDatosPrestamoAdministrador($_POST);
                $idCreado = $modeloPrestamoAdministrador->crear(
                    $prestamo["id_alumno"],
                    $prestamo["fecha_prestamo"],
                    $prestamo["equipos"]
                );
                guardarMensajeAdministrador(
                    "exito",
                    "Préstamo #{$idCreado} registrado correctamente."
                );
            } elseif ($accion === "actualizar_prestamo") {
                if ($idPrestamo < 1) {
                    throw new DomainException("El préstamo seleccionado no es válido.");
                }

                $prestamo = validarDatosPrestamoAdministrador($_POST);
                $modeloPrestamoAdministrador->actualizar(
                    $idPrestamo,
                    $prestamo["id_alumno"],
                    $prestamo["fecha_prestamo"],
                    $prestamo["equipos"]
                );
                guardarMensajeAdministrador("exito", "Préstamo actualizado correctamente.");
            } elseif ($accion === "marcar_atrasado") {
                if ($idPrestamo < 1) {
                    throw new DomainException("El préstamo seleccionado no es válido.");
                }

                $modeloPrestamoAdministrador->marcarAtrasado($idPrestamo);
                guardarMensajeAdministrador("exito", "Préstamo marcado como atrasado.");
            } elseif ($accion === "devolver_prestamo") {
                if ($idPrestamo < 1) {
                    throw new DomainException("El préstamo seleccionado no es válido.");
                }

                $fechaDevolucion = validarFechaPrestamoAdministrador(
                    (string) ($_POST["fecha_devolucion"] ?? ""),
                    "fecha de devolución"
                );
                $modeloPrestamoAdministrador->devolver($idPrestamo, $fechaDevolucion);
                guardarMensajeAdministrador(
                    "exito",
                    "Devolución registrada y equipos liberados correctamente."
                );
            } else {
                throw new DomainException("La acción solicitada no es válida.");
            }
        } catch (DomainException $error) {
            guardarMensajeAdministrador("error", $error->getMessage());
        } catch (mysqli_sql_exception $error) {
            error_log("GesTIck - error al administrar préstamos: " . $error->getMessage());

            if ($error->getCode() === 1062) {
                guardarMensajeAdministrador(
                    "error",
                    "Uno de los equipos ya está asociado a este préstamo."
                );
            } elseif ($error->getCode() === 1452) {
                guardarMensajeAdministrador(
                    "error",
                    "El alumno, equipo o estado seleccionado ya no está disponible."
                );
            } else {
                guardarMensajeAdministrador(
                    "error",
                    "No fue posible guardar los cambios en la base de datos."
                );
            }
        } catch (Throwable $error) {
            error_log("GesTIck - error inesperado al administrar préstamos: " . $error->getMessage());
            guardarMensajeAdministrador(
                "error",
                "Ocurrió un error inesperado al procesar el préstamo."
            );
        }

        redirigirAdministrador("prestamos");
    }

    $filtrosPrestamos = [
        "buscar" => mb_substr(trim((string) ($_GET["buscar"] ?? "")), 0, 100),
        "estado" => max(0, (int) ($_GET["estado"] ?? 0)),
        "pagina" => max(1, (int) ($_GET["p"] ?? 1))
    ];
    $prestamosAdministrador = [];
    $estadosPrestamosAdministrador = [];
    $alumnosPrestamosAdministrador = [];
    $equiposDisponiblesPrestamosAdministrador = [];
    $resumenPrestamosAdministrador = [
        "total" => 0,
        "activos" => 0,
        "atrasados" => 0,
        "devueltos" => 0,
        "equipos_prestados" => 0
    ];
    $totalPrestamosAdministrador = 0;
    $totalPaginasPrestamosAdministrador = 1;
    $paginaActualPrestamosAdministrador = 1;
    $errorPrestamosAdministrador = false;

    try {
        $resultadoPrestamos = $modeloPrestamoAdministrador->obtener($filtrosPrestamos, 10);
        $prestamosAdministrador = $resultadoPrestamos["prestamos"];
        $totalPrestamosAdministrador = $resultadoPrestamos["total"];
        $totalPaginasPrestamosAdministrador = $resultadoPrestamos["total_paginas"];
        $paginaActualPrestamosAdministrador = $resultadoPrestamos["pagina_actual"];
        $filtrosPrestamos["pagina"] = $paginaActualPrestamosAdministrador;
        $estadosPrestamosAdministrador = $modeloPrestamoAdministrador->obtenerEstados();
        $alumnosPrestamosAdministrador = $modeloPrestamoAdministrador->obtenerAlumnos();
        $equiposDisponiblesPrestamosAdministrador = $modeloPrestamoAdministrador->obtenerEquiposDisponibles();
        $resumenPrestamosAdministrador = array_replace(
            $resumenPrestamosAdministrador,
            $modeloPrestamoAdministrador->obtenerResumen()
        );
    } catch (Throwable $error) {
        $errorPrestamosAdministrador = true;
        error_log("GesTIck - error al cargar préstamos: " . $error->getMessage());
    }

    $mensajeAdministrador = consumirMensajeAdministrador();

    require __DIR__ . "/../../vistas/administrador/prestamos.php";
    return;
}

