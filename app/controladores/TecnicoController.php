<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../include/proteccion.inc";
require_once __DIR__ . "/../../include/validaciones.php";

// Se admiten ambas grafías para mantener compatibilidad con datos ya existentes.
if ($_SESSION["rol"] != "Tecnico" &&
    $_SESSION["rol"] != "Técnico") {

    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form");
    exit;
}

function redirigirTecnico(string $pagina): never
{
    header(
        "Location: " . BASE_URL .
        "/app/controladores/TecnicoController.php?pagina=" . urlencode($pagina)
    );
    exit;
}

function guardarMensajeTecnico(string $tipo, string $texto): void
{
    // Mensaje flash: sobrevive a la redirección y luego se elimina al leerlo.
    $_SESSION["mensaje_tecnico"] = [
        "tipo" => $tipo,
        "texto" => $texto
    ];
}

function consumirMensajeTecnico(): ?array
{
    $mensaje = $_SESSION["mensaje_tecnico"] ?? null;
    unset($_SESSION["mensaje_tecnico"]);

    return is_array($mensaje) ? $mensaje : null;
}

function tokenTecnico(): string
{
    if (empty($_SESSION["csrf_tecnico"])) {
        $_SESSION["csrf_tecnico"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_tecnico"];
}

function validarTokenTecnico(string $token): bool
{
    $tokenSesion = $_SESSION["csrf_tecnico"] ?? "";

    return $tokenSesion !== "" && hash_equals($tokenSesion, $token);
}

$pagina = $_GET["pagina"] ?? "inicio";

// La lista blanca evita construir una ruta de archivo a partir de cualquier texto recibido.
$paginasPermitidas = [
    "inicio",
    "mis_tickets",
    "pendientes",
    "en_proceso",
    "equipos",
    "solicitudes",
    "mi_perfil"
];

if (!in_array($pagina, $paginasPermitidas)) {
    $pagina = "inicio";
}

require __DIR__ . "/tecnico/" . $pagina . ".php";


