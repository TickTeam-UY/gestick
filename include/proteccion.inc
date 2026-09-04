<?php

require_once __DIR__ . "/sesion.php";

/*
 * Guardia común de las rutas privadas. Cada controlador comprueba después
 * el rol concreto que puede acceder a su sección.
 */
Sesion::iniciar();

if (($_SESSION["autenticado"] ?? false) !== true) {
    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form");
    exit;
}

if (Sesion::estaInactiva()) {
    // Una sesión vencida se destruye antes de volver al formulario de acceso.
    Sesion::cerrar();
    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form&sesion=expirada");
    exit;
}

Sesion::actualizarActividad();
