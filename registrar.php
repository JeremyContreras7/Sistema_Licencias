<?php
include("conexion.php");

if (isset($_POST['btnregistrar'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $id_establecimiento = $_POST['id_establecimiento']; // FK con tabla establecimientos
    $tipo_encargado = ($rol === "USUARIO") ? $_POST['tipo_encargado'] : null;

    // Verificar si el correo ya existe
    $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('❌ El correo ya está registrado.'); window.location='registrar_usuario.php';</script>";
    } else {
        // Insertar nuevo usuario
        $stmt = $conexion->prepare("INSERT INTO usuarios 
            (nombre, correo, pass, rol, id_establecimiento, tipo_encargado) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $nombre, $correo, $pass, $rol, $id_establecimiento, $tipo_encargado);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Usuario registrado correctamente.'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('❌ Error al registrar el usuario.'); window.location='registrar_usuario.php';</script>";
        }

        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="css/styleregistro.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
    <main class="registro-wrapper">
        <section class="registro-card">
            <header class="registro-header">
                <img src="img/logo.png" alt="Logo" class="registro-logo">
                <h1>Registrar Nuevo Usuario</h1>
                <p class="registro-sub">Crea cuentas para el sistema de gestión</p>
            </header>

            <form class="registro-form" method="POST" action="">
                <div class="grid-2">
                    <div class="field">
                        <label for="nombre">Nombre completo</label>
                        <input id="nombre" type="text" name="nombre" placeholder="Juan Pérez" required>
                    </div>

                    <div class="field">
                        <label for="correo">Correo institucional</label>
                        <input id="correo" type="email" name="correo" placeholder="usuario@dominio.cl" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label for="pass">Contraseña</label>
                        <input id="pass" type="password" name="pass" placeholder="Mínimo 8 caracteres" required>
                    </div>

                    <div class="field">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" required onchange="toggleEncargado()">
                            <option disabled selected value="">Seleccionar rol</option>
                            <option value="ADMIN">Administrador</option>
                            <option value="ENCARGADO">Encargado Informático</option>
                            <option value="USUARIO">Personal Escolar</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label for="id_establecimiento">Establecimiento</label>
                        <select id="id_establecimiento" name="id_establecimiento" required>
                            <option disabled selected value="">Seleccionar establecimiento</option>
                            <?php
                            $escuelas = $conexion->query("SELECT id_establecimiento, nombre_establecimiento FROM establecimientos");
                            while ($row = $escuelas->fetch_assoc()) {
                                echo "<option value='".$row['id_establecimiento']."'>".$row['nombre_establecimiento']."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="tipo_encargado">Tipo de encargado</label>
                        <select id="tipo_encargado" name="tipo_encargado" disabled>
                            <option disabled selected value="">Tipo de encargado</option>
                            <option value="INFORMATICA">Informática</option>
                            <option value="ACADEMICA">Académica</option>
                            <option value="ADMINISTRATIVA">Administrativa</option>
                            <option value="DIRECCION">Dirección</option>
                            <option value="CONVIVENCIA">Convivencia Escolar</option>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" name="btnregistrar" class="btn-primary">Registrar Usuario</button>
                    <a href="index.php" class="btn-secondary">Volver</a>
                </div>
            </form>

            <footer class="registro-footer">
                <small>Al registrar acepta las políticas internas de uso.</small>
            </footer>
        </section>
    </main>

    <script>
        function toggleEncargado() {
            let rol = document.getElementById("rol").value;
            let encargadoSelect = document.getElementById("tipo_encargado");

            if (rol === "USUARIO") {
                encargadoSelect.disabled = false;
                encargadoSelect.required = true;
            } else {
                encargadoSelect.disabled = true;
                encargadoSelect.required = false;
                encargadoSelect.selectedIndex = 0;
            }
        }
    </script>
</body>

</html>
