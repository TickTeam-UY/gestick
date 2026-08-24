<?php

require_once __DIR__ . "/sesion.php";

Sesion::iniciar();

if (($_SESSION["autenticado"] ?? false) !== true) {
    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form");
    exit;
}

if (Sesion::estaInactiva()) {
    Sesion::cerrar();
    header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form&sesion=expirada");
    exit;
}

Sesion::actualizarActividad();
