<?php

/* Validación compartida por las pantallas de perfil de los tres roles. */
function validarDatosPerfil(array $datos): array
{
    $nombre = trim((string) ($datos["nombre"] ?? ""));
    $apellido = trim((string) ($datos["apellido"] ?? ""));
    $correo = strtolower(trim((string) ($datos["correo"] ?? "")));

    if ($nombre === "" || $apellido === "") {
        throw new DomainException("El nombre y el apellido son obligatorios.");
    }

    if (mb_strlen($nombre) > 60 || mb_strlen($apellido) > 60) {
        throw new DomainException("El nombre y el apellido pueden tener hasta 60 caracteres.");
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
        throw new DomainException("Ingresa un correo electrónico válido.");
    }

    return [
        "nombre" => $nombre,
        "apellido" => $apellido,
        "correo" => $correo
    ];
}
