<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ADMIN") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// --- CREAR USUARIO ---
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $establecimiento = $_POST['establecimiento'];
    $tipo_encargado = ($_POST['rol'] === "USUARIO") ? $_POST['tipo_encargado'] : null;

    $sql = "INSERT INTO usuarios (nombre, correo, pass, rol, establecimiento, tipo_encargado) 
            VALUES ('$nombre','$correo','$pass','$rol','$establecimiento','$tipo_encargado')";
    $conexion->query($sql);
}

// --- ELIMINAR USUARIO ---
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conexion->query("DELETE FROM usuarios WHERE id_usuario=$id");
    header("Location: gestion_usuarios.php");
    exit();
}

// --- LISTAR USUARIOS ---
// Consulta corregida para usar id_usuario en lugar de id
$usuarios = $conexion->query("SELECT * FROM usuarios ORDER BY id_usuario DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styleGusuario.css" />
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i> Gestión de Usuarios</h1>
            <a href="menu.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al menú
            </a>
        </div>
        
        <!-- Formulario de creación -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Crear Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pass" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="pass" name="pass" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required onchange="toggleEncargado()">
                                <option value="" selected disabled>Seleccionar Rol</option>
                                <option value="ADMIN">Administrador</option>
                                <option value="ENCARGADO">Encargado Informático</option>
                                <option value="USUARIO">Usuario</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="establecimiento" class="form-label">Establecimiento</label>
                            <input type="text" class="form-control" id="establecimiento" name="establecimiento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_encargado" class="form-label">Tipo de Encargado</label>
                            <select class="form-select" id="tipo_encargado" name="tipo_encargado" disabled>
                                <option value="" selected disabled>Seleccionar Tipo</option>
                                <option value="INFORMATICA">Informática</option>
                                <option value="ACADEMICA">Académica</option>
                                <option value="ADMINISTRATIVA">Administrativa</option>
                                <option value="DIRECCION">Dirección</option>
                                <option value="CONVIVENCIA">Convivencia Escolar</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="crear" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Usuario
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Lista de Usuarios</h5>
                <span class="badge bg-primary"><?php echo $usuarios->num_rows; ?> usuarios</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Establecimiento</th>
                                <th>Tipo Encargado</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($usuarios->num_rows > 0) {
                                while($row = $usuarios->fetch_assoc()): 
                                    // Determinar la clase del badge según el rol
                                    $badge_class = '';
                                    if ($row['rol'] === 'ADMIN') $badge_class = 'badge-admin';
                                    if ($row['rol'] === 'ENCARGADO') $badge_class = 'badge-encargado';
                                    if ($row['rol'] === 'USUARIO') $badge_class = 'badge-usuario';
                            ?>
                            <tr>
                                <td><?= $row['id_usuario'] ?></td>
                                <td><?= $row['nombre'] ?></td>
                                <td><?= $row['correo'] ?></td>
                                <td><span class="badge <?= $badge_class ?>"><?= $row['rol'] ?></span></td>
                                <td><?= $row['id_establecimiento'] ?></td>
                                <td><?= $row['tipo_encargado'] ?? '-' ?></td>
                                <td><?= $row['fecha_registro'] ?></td>
                                <td>
                                    <a href="editar_usuario.php?id=<?= $row['id_usuario'] ?>" class="btn btn-sm btn-outline-primary action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="gestion_usuarios.php?eliminar=<?= $row['id_usuario'] ?>" class="btn btn-sm btn-outline-danger action-btn" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="8" class="text-center">No hay usuarios registrados</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>