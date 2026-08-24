<?php

require_once __DIR__ . "/config/config.php";

header("Location: " . BASE_URL . "/app/controladores/AuthController.php?accion=login_form");
exit;

?>
