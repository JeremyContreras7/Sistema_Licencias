<?php
// Evitar caching para mayor seguridad
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
session_start();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <title>Gestión de Licencias - Inicio de Sesión</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="css/stylelogin.css" />
</head>

<body class="homepage is-preload">
    <div id="page-wrapper">
        <center>
            <br><br>
            <img src="img/logo.png" alt="Logo Institución" width="120">
        </center>

        <center>
            <!-- Formulario de inicio de sesión -->
            <form id="frmlogin" class="grupo-entradas" method="POST" action="validar.php" autocomplete="off">
                <h1>Sistema de Gestión de Licencias</h1>   

                <input type="email" class="cajaentradatexto" placeholder="&#128273; Correo Institucional" name="txtusuario" required>

                <input type="password" class="cajaentradatexto" placeholder="🔑 Contraseña" name="txtpassword" id="txtpassword" required>

                <label style="display:block; margin:10px 0;">
                    <input type="checkbox" onclick="verpassword()"> Mostrar contraseña
                </label>

                <select name="rol" required>
                    <option disabled selected value="">Seleccionar Rol</option>
                    <option value="ADMIN">Administrador</option>
                    <option value="ENCARGADO">Encargado Informático</option>
                    <option value="USUARIO">Personal Escolar</option>
                </select>

                <button type="submit" class="botonenviar" name="btnloginx">Iniciar Sesión</button>
            </form>

            <!-- Botón para ir a Registro -->
            <p style="margin-top: 15px;">¿No tienes cuenta?</p>
            <a href="registrar.php" class="botonregistro">Crear una cuenta</a>
        </center>
    </div>

    <script>
        function verpassword() {
            let x = document.getElementById("txtpassword");
            x.type = (x.type === "password") ? "text" : "password";
        }
    </script>
</body>
</html>
