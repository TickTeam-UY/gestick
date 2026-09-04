<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../include/sesion.php";
require_once __DIR__ . "/../modelos/Usuario.php";

/* Coordina el formulario de acceso, la autenticación y el cierre de sesión. */
class AuthController
{
    public function ejecutar(): void
    {
        Sesion::iniciar();
        $accion = (string) ($_GET["accion"] ?? "login_form");

        // Punto de entrada único para las acciones públicas de autenticación.
        switch ($accion) {
            case "login_form":
                $csrfLogin = $this->tokenLogin();
                require __DIR__ . "/../vistas/auth/login.php";
                return;

            case "login":
                $this->login();
                return;

            case "logout":
                Sesion::cerrar();
                $this->redirigir($this->url("login_form"));

            default:
                $this->redirigir($this->url("login_form"));
        }
    }

    private function login(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->redirigir($this->url("login_form"));
        }

        $correo = strtolower(trim((string) ($_POST["correo"] ?? "")));
        $contrasena = (string) ($_POST["contrasenia"] ?? "");
        $csrfRecibido = (string) ($_POST["csrf_token"] ?? "");

        if (!$this->validarTokenLogin($csrfRecibido)) {
            $this->alerta(
                "La sesión del formulario venció. Vuelva a intentarlo.",
                $this->url("login_form")
            );
        }

        if ($correo === "" || $contrasena === "") {
            $this->alerta("Complete el correo y la contraseña.", $this->url("login_form"));
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
            $this->alerta("Ingrese un correo electrónico válido.", $this->url("login_form"));
        }

        if (strlen($contrasena) > 72) {
            $this->alerta("La contraseña ingresada no es válida.", $this->url("login_form"));
        }

        // El modelo decide si la credencial es válida; el controlador solo dirige el flujo.
        try {
            $usuario = Usuario::autenticar($correo, $contrasena);
        } catch (Throwable $error) {
            error_log("GesTIck - error al iniciar sesión: " . $error->getMessage());
            $this->alerta(
                "No fue posible conectar con la base de datos. Inténtelo nuevamente.",
                $this->url("login_form")
            );
        }

        if ($usuario === false) {
            $this->alerta("Correo o contraseña incorrectos.", $this->url("login_form"));
        }

        Sesion::registrarUsuario($usuario);
        unset($_SESSION["csrf_login"]);
        $this->redirigirAlPanel((string) $usuario["rol"]);
    }

    private function tokenLogin(): string
    {
        // El token vincula el formulario con la sesión que lo generó.
        if (empty($_SESSION["csrf_login"])) {
            $_SESSION["csrf_login"] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION["csrf_login"];
    }

    private function validarTokenLogin(string $token): bool
    {
        $tokenSesion = (string) ($_SESSION["csrf_login"] ?? "");

        return $tokenSesion !== "" && hash_equals($tokenSesion, $token);
    }

    private function redirigirAlPanel(string $rol): never
    {
        // La pantalla inicial se elige en el servidor, no desde un dato enviado por el cliente.
        $controladores = [
            "Administrador" => "AdministradorController.php",
            "Tecnico" => "TecnicoController.php",
            "Docente" => "DocenteController.php"
        ];

        if (!isset($controladores[$rol])) {
            require __DIR__ . "/../vistas/auth/sin_panel.php";
            exit;
        }

        $url = BASE_URL . "/app/controladores/" . $controladores[$rol] . "?pagina=inicio";
        $this->redirigir($url);
    }

    private function url(string $accion): string
    {
        return BASE_URL . "/app/controladores/AuthController.php?accion=" . urlencode($accion);
    }

    private function redirigir(string $url): never
    {
        header("Location: " . $url);
        exit;
    }

    private function alerta(string $mensaje, string $url): never
    {
        // json_encode impide que el mensaje o la URL rompan el JavaScript de respuesta.
        $mensajeSeguro = json_encode(
            $mensaje,
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        $urlSegura = json_encode(
            $url,
            JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        echo "<script>alert($mensajeSeguro); window.location.href=$urlSegura;</script>";
        exit;
    }
}

(new AuthController())->ejecutar();
