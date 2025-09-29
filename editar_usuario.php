<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ADMIN") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

if (!isset($_GET['id'])) {
    header("Location: gestion_usuarios.php");
    exit();
}

$id = (int)$_GET['id'];

// --- OBTENER DATOS DEL USUARIO ---
$stmt = $conexion->prepare("
    SELECT u.*, e.nombre_establecimiento 
    FROM usuarios u
    LEFT JOIN establecimientos e ON u.id_establecimiento = e.id_establecimiento
    WHERE u.id_usuario = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header("Location: gestion_usuarios.php?error=notfound");
    exit();
}

// --- ACTUALIZAR USUARIO ---
if (isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $tipo_encargado = ($rol === "USUARIO") ? $_POST['tipo_encargado'] : null;
    $id_establecimiento = $_POST['id_establecimiento'];
    $pass = !empty($_POST['pass']) ? password_hash($_POST['pass'], PASSWORD_DEFAULT) : $usuario['pass'];

    $stmt = $conexion->prepare("
        UPDATE usuarios 
        SET nombre=?, correo=?, pass=?, rol=?, id_establecimiento=?, tipo_encargado=? 
        WHERE id_usuario=?
    ");
    $stmt->bind_param("ssssisi", $nombre, $correo, $pass, $rol, $id_establecimiento, $tipo_encargado, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: gestionUsuarios.php?msg=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body class="container py-4">
    <h1><i class="fas fa-edit"></i> Editar Usuario</h1>
    <a href="gestionUsuarios.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Volver</a>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nueva contraseña (opcional)</label>
            <input type="password" name="pass" class="form-control" placeholder="Dejar vacío para no cambiar">
        </div>
        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="rol" class="form-select" required>
                <option value="ADMIN" <?= $usuario['rol']==='ADMIN'?'selected':'' ?>>Administrador</option>
                <option value="ENCARGADO" <?= $usuario['rol']==='ENCARGADO'?'selected':'' ?>>Encargado Informático</option>
                <option value="USUARIO" <?= $usuario['rol']==='USUARIO'?'selected':'' ?>>Usuario</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Establecimiento</label>
            <select name="id_establecimiento" class="form-select" required>
                <?php
                $escuelas = $conexion->query("SELECT id_establecimiento, nombre_establecimiento FROM establecimientos");
                while ($row = $escuelas->fetch_assoc()) {
                    $selected = $usuario['id_establecimiento'] == $row['id_establecimiento'] ? 'selected' : '';
                    echo "<option value='{$row['id_establecimiento']}' $selected>{$row['nombre_establecimiento']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo Encargado</label>
            <select name="tipo_encargado" class="form-select" <?= $usuario['rol']!=='USUARIO'?'disabled':'' ?>>
                <option value="" <?= empty($usuario['tipo_encargado'])?'selected':'' ?>>Ninguno</option>
                <option value="INFORMATICA" <?= $usuario['tipo_encargado']==='INFORMATICA'?'selected':'' ?>>Informática</option>
                <option value="ACADEMICA" <?= $usuario['tipo_encargado']==='ACADEMICA'?'selected':'' ?>>Académica</option>
                <option value="ADMINISTRATIVA" <?= $usuario['tipo_encargado']==='ADMINISTRATIVA'?'selected':'' ?>>Administrativa</option>
                <option value="DIRECCION" <?= $usuario['tipo_encargado']==='DIRECCION'?'selected':'' ?>>Dirección</option>
                <option value="CONVIVENCIA" <?= $usuario['tipo_encargado']==='CONVIVENCIA'?'selected':'' ?>>Convivencia Escolar</option>
            </select>
        </div>
        <button type="submit" name="actualizar" class="btn btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
    </form>
</body>
</html>
