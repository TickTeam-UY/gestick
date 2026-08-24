<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesTIck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5 text-center">
        <h1>Inicio de sesión correcto</h1>
        <p>El panel para el rol <strong><?php echo htmlspecialchars($_SESSION["rol"]); ?></strong> todavía no fue desarrollado.</p>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=logout">Cerrar sesión</a>
    </main>
</body>
</html>
