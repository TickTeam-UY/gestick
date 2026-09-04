<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../include/proteccion.inc";
require_once __DIR__ . "/../../include/validaciones.php";

// La protección general valida la sesión; aquí se restringe además el rol.
if (($_SESSION["rol"] ?? "") !== "Docente") {
    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form");
    exit;
}

function redirigirDocente(string $pagina): never
{
    header(
        "Location: " . BASE_URL .
        "/app/controladores/DocenteController.php?pagina=" . urlencode($pagina)
    );
    exit;
}

function tokenDocente(): string
{
    if (empty($_SESSION["csrf_docente"])) {
        $_SESSION["csrf_docente"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_docente"];
}

function validarTokenDocente(string $token): bool
{
    $tokenSesion = (string) ($_SESSION["csrf_docente"] ?? "");

    return $tokenSesion !== "" && hash_equals($tokenSesion, $token);
}

function guardarMensajeDocente(string $tipo, string $texto): void
{
    // Mensaje flash para informar el resultado después de una redirección.
    $_SESSION["mensaje_docente"] = ["tipo" => $tipo, "texto" => $texto];
}

function consumirMensajeDocente(): ?array
{
    $mensaje = $_SESSION["mensaje_docente"] ?? null;
    unset($_SESSION["mensaje_docente"]);

    return is_array($mensaje) ? $mensaje : null;
}

$pagina = (string) ($_GET["pagina"] ?? "inicio");

// La ruta anterior se conserva como compatibilidad, pero ahora la creación
// se inicia únicamente desde el botón "Nueva solicitud" de la bandeja.
if ($pagina === "crear_solicitud") {
    $pagina = "mis_solicitudes";
}

// Solo estas páginas pueden cargarse desde el parámetro de la URL.
$paginasPermitidas = [
    "inicio",
    "llenar_planilla",
    "mis_solicitudes",
    "mi_perfil"
];

if (!in_array($pagina, $paginasPermitidas, true)) {
    $pagina = "inicio";
}

$idDocente = (int) ($_SESSION["usuario_id"] ?? 0);

require __DIR__ . "/docente/" . $pagina . ".php";


