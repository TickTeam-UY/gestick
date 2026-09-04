<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../include/proteccion.inc";
require_once __DIR__ . "/../../include/validaciones.php";

// La protección general valida la sesión; aquí se restringe además el rol.
if (($_SESSION["rol"] ?? "") !== "Administrador") {
    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form");
    exit;
}

function redirigirAdministrador(string $pagina): never
{
    header(
        "Location: " . BASE_URL .
        "/app/controladores/AdministradorController.php?pagina=" . urlencode($pagina)
    );
    exit;
}

function guardarMensajeAdministrador(string $tipo, string $texto): void
{
    // Mensaje flash: se guarda antes de redirigir y se muestra una sola vez.
    $_SESSION["mensaje_administrador"] = [
        "tipo" => $tipo,
        "texto" => $texto
    ];
}

function consumirMensajeAdministrador(): ?array
{
    $mensaje = $_SESSION["mensaje_administrador"] ?? null;
    unset($_SESSION["mensaje_administrador"]);

    return is_array($mensaje) ? $mensaje : null;
}

function tokenAdministrador(): string
{
    // Un único token protege todos los formularios de administración de la sesión.
    if (empty($_SESSION["csrf_administrador"])) {
        $_SESSION["csrf_administrador"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_administrador"];
}

function validarTokenAdministrador(string $token): bool
{
    $tokenSesion = $_SESSION["csrf_administrador"] ?? "";

    return $tokenSesion !== "" && hash_equals($tokenSesion, $token);
}

$pagina = $_GET["pagina"] ?? "inicio";

if ($pagina === "reportes") {
    $pagina = "metricas";
}

// Lista blanca: solo se incluyen archivos de páginas conocidas por el sistema.
$paginasDisponibles = ["inicio", "tickets", "solicitudes", "planillas", "equipos", "prestamos", "usuarios", "metricas", "mi_perfil"];

if (!in_array($pagina, $paginasDisponibles, true)) {
    $pagina = "inicio";
}

if ($pagina === "usuarios") {
    // Usuarios tiene un controlador propio porque incluye altas, modificaciones y bajas lógicas.
    require_once __DIR__ . "/UsuarioController.php";
    $controladorUsuarios = new UsuarioController();
    $controladorUsuarios->administrar();
    return;
}

require __DIR__ . "/administrador/" . $pagina . ".php";




