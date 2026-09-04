<?php

/* Consulta el inventario compartido en modo de solo lectura para el técnico. */
if ($pagina == "equipos") {
    require_once __DIR__ . "/../../modelos/Equipo.php";
    $modeloEquipo = new Equipo();
    $laboratorios = $modeloEquipo->obtenerLaboratorios();
}

require __DIR__ . "/../../vistas/tecnico/" . $pagina . ".php";
