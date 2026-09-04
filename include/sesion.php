<?php

/* Centraliza el ciclo de vida y los datos de la sesión autenticada. */
class Sesion
{
    // Después de 30 minutos sin actividad se exige iniciar sesión nuevamente.
    private const TIEMPO_INACTIVIDAD = 1800;

    public static function iniciar(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $conexionSegura = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

        // La cookie no es accesible desde JavaScript y se limita al mismo sitio.
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "secure" => $conexionSegura,
            "httponly" => true,
            "samesite" => "Lax"
        ]);

        session_start();
    }

    public static function cerrar(): void
    {
        // Se eliminan tanto los datos del servidor como la cookie del navegador.
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $parametros = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires" => time() - 42000,
                "path" => $parametros["path"],
                "domain" => $parametros["domain"],
                "secure" => $parametros["secure"],
                "httponly" => $parametros["httponly"],
                "samesite" => $parametros["samesite"] ?? "Lax"
            ]);
        }

        session_destroy();
    }

    public static function estaInactiva(): bool
    {
        $ultimoAcceso = (int) ($_SESSION["ultimo_acceso"] ?? 0);

        return $ultimoAcceso > 0
            && (time() - $ultimoAcceso) > self::TIEMPO_INACTIVIDAD;
    }

    public static function registrarUsuario(array $usuario): void
    {
        // Regenerar el identificador evita reutilizar la sesión previa al login.
        session_regenerate_id(true);

        $_SESSION["usuario_id"] = (int) $usuario["id_usuario"];
        $_SESSION["nombre"] = trim($usuario["nombre"] . " " . $usuario["apellido"]);
        $_SESSION["correo"] = $usuario["correo"];
        $_SESSION["rol"] = $usuario["rol"];
        $_SESSION["autenticado"] = true;
        $_SESSION["ultimo_acceso"] = time();
    }

    public static function actualizarActividad(): void
    {
        $_SESSION["ultimo_acceso"] = time();
    }
}

/* Compatibilidad con los módulos existentes. */
function iniciarSesionSegura(): void
{
    Sesion::iniciar();
}

function cerrarSesionActual(): void
{
    Sesion::cerrar();
}

function sesionInactiva(): bool
{
    return Sesion::estaInactiva();
}
