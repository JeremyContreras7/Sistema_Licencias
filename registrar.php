<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$id_establecimiento = $_SESSION['id_establecimiento']; // viene del login del encargado
$nombre_establecimiento = "";

// Obtener el nombre del establecimiento del encargado
$stmt = $conexion->prepare("SELECT nombre_establecimiento FROM establecimientos WHERE id_establecimiento = ?");
$stmt->bind_param("i", $id_establecimiento);
$stmt->execute();
$stmt->bind_result($nombre_establecimiento);
$stmt->fetch();
$stmt->close();

if (isset($_POST['btnregistrar'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $rol = "USUARIO"; // fijo solo funcionario escolar
    $tipo_encargado = $_POST['tipo_encargado'] ?? null;

    // Verificar si el correo ya existe
    $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('❌ El correo ya está registrado.'); window.location='registrar.php';</script>";
    } else {
        // Insertar nuevo usuario
        $stmt = $conexion->prepare("INSERT INTO usuarios 
            (nombre, correo, pass, rol, id_establecimiento, tipo_encargado) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $nombre, $correo, $pass, $rol, $id_establecimiento, $tipo_encargado);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Usuario registrado correctamente.'); window.location='registrar.php';</script>";
        } else {
            echo "<script>alert('❌ Error al registrar el usuario.'); window.location='registrar.php';</script>";
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
                <h1>Registrar Funcionario Escolar</h1>
                <p class="registro-sub">Crea cuentas de funcionarios para tu establecimiento</p>
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

                <!-- El rol ya está fijo como USUARIO -->
                <input type="hidden" name="rol" value="USUARIO">

                <div class="grid-2">
                    <div class="field">
                        <label for="id_establecimiento">Establecimiento</label>
                        <input type="text" value="<?= htmlspecialchars($nombre_establecimiento) ?>" disabled>
                        <input type="hidden" name="id_establecimiento" value="<?= $id_establecimiento ?>">
                    </div>

                    <div class="field">
                        <label for="tipo_encargado">Tipo de funcionario</label>
                        <select id="tipo_encargado" name="tipo_encargado" required>
                            <option disabled selected value="">Seleccionar tipo</option>
                            <option value="ACADEMICA">Académica</option>
                            <option value="ADMINISTRATIVA">Administrativa</option>
                            <option value="DIRECCION">Dirección</option>
                            <option value="CONVIVENCIA">Convivencia Escolar</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="pass">Contraseña</label>
                    <input id="pass" type="password" name="pass" placeholder="Mínimo 8 caracteres" required>
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
</body>
</html>
