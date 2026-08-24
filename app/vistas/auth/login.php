<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>GesTIck - Inicio de sesión</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/login.css?v=20260823-responsive-1"> 
    </head>
    <body>
        <header>
                <img src="<?php echo BASE_URL; ?>/public/imagenes/logo-mejorado.png" alt="Logo GesTIck"> 
        </header>
        <main>
                <section>
                    <form action="<?php echo BASE_URL; ?>/app/controladores/AuthController.php?accion=login" method="POST" name="datos">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfLogin, ENT_QUOTES, 'UTF-8'); ?>">
                        <table class="tabla-login" border="1" cellpadding="10">
                            <tr>
                                <td colspan="2" align="center">
                                    <b>Iniciar sesión</b>
                                </td>
                            </tr>
                            <tr>
                                <td>Correo electrónico</td>
                                <td><input type="email" name="correo" size="25" maxlength="254" required></td>
                            </tr>
                            <tr>
                                <td>Contraseña</td>
                                <td><input type="password" name="contrasenia" size="25" maxlength="72" required></td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center"><input type="submit" value="Ingresar"></td>
                            </tr>
                        </table>
                    </form>
                    
                </section>
        </main>
        
        <footer>
            <p>GesTIck - Sistema de Gestión de Recursos y Soporte de Informática</p>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
