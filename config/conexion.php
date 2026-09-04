<?php


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*
 * Mantiene una sola conexión compartida durante cada petición.
 * Los modelos acceden a ella mediante Modelo::conexion().
 */
class Conexion
{
    private static ?mysqli $instancia = null;

    public static function obtener(): mysqli
    {
        // Reutilizar la instancia evita abrir una conexión nueva por cada consulta.
        if (self::$instancia instanceof mysqli) {
            return self::$instancia;
        }

        $configuracion = self::configuracion();
        $conexion = mysqli_init();

        if ($conexion === false) {
            throw new RuntimeException("No fue posible preparar la conexión a la base de datos.");
        }

        $conexion->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        $conexion->real_connect(
            $configuracion["host"],
            $configuracion["username"],
            $configuracion["password"],
            $configuracion["database"],
            $configuracion["port"]
        );
        $conexion->set_charset("utf8mb4");

        self::$instancia = $conexion;

        return self::$instancia;
    }

    private static function configuracion(): array
    {
        static $configuracion = null;

        if ($configuracion !== null) {
            return $configuracion;
        }

        // El archivo local permite cambiar de servidor sin modificar esta clase.
        $archivoLocal = __DIR__ . "/database.local.php";
        $valoresLocales = is_file($archivoLocal) ? require $archivoLocal : [];

        $leerEntorno = static function (string $nombre, mixed $predeterminado): mixed {
            $valor = getenv($nombre);

            return ($valor === false || $valor === "") ? $predeterminado : $valor;
        };

        $configuracion = [
            "host" => (string) $leerEntorno(
                "GESTICK_DB_HOST",
                $valoresLocales["host"] ?? "127.0.0.1"
            ),
            "port" => (int) $leerEntorno(
                "GESTICK_DB_PORT",
                $valoresLocales["port"] ?? 3306
            ),
            "database" => (string) $leerEntorno(
                "GESTICK_DB_NAME",
                $valoresLocales["database"] ?? "gestick"
            ),
            "username" => (string) $leerEntorno(
                "GESTICK_DB_USER",
                $valoresLocales["username"] ?? "root"
            ),
            "password" => (string) $leerEntorno(
                "GESTICK_DB_PASSWORD",
                $valoresLocales["password"] ?? ""
            )
        ];

        return $configuracion;
    }
}


function obtenerConexion(): mysqli
{
    // Función conservada para módulos antiguos que todavía no usan la clase base Modelo.
    return Conexion::obtener();
}
