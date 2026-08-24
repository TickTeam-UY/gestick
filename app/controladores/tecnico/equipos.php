<?php

if ($pagina == "equipos") {
    require_once __DIR__ . "/../../modelos/Equipo.php";
    $modeloEquipo = new Equipo();
    $laboratorios = $modeloEquipo->obtenerLaboratorios();
}

require __DIR__ . "/../../vistas/tecnico/" . $pagina . ".php";
