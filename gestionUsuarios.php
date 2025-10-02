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
    $tipo_encargado = ($_POST['rol'] === "USUARIO") ? $_POST['tipo_encargado'] : null;
    $id_establecimiento = $_POST['id_establecimiento'];
    
    $sql = "INSERT INTO usuarios (nombre, correo, pass, rol, id_establecimiento, tipo_encargado) 
            VALUES ('$nombre','$correo','$pass','$rol','$id_establecimiento','$tipo_encargado')";

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
$usuarios = $conexion->query("
    SELECT u.*, e.nombre_establecimiento 
    FROM usuarios u
    LEFT JOIN establecimientos e ON u.id_establecimiento = e.id_establecimiento
    ORDER BY u.id_usuario DESC
");

// Contar usuarios por rol para estadísticas
$total_usuarios = $usuarios->num_rows;
$admin_count = $conexion->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'ADMIN'")->fetch_assoc()['count'];
$encargado_count = $conexion->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'ENCARGADO'")->fetch_assoc()['count'];
$usuario_count = $conexion->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'USUARIO'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styleGusuario.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-text">
                    <h1><i class="fas fa-users-cog"></i> Gesti&oacute;n de Usuarios</h1>
                    <p class="subtitle">Administra los usuarios del sistema de licencias</p>
                </div>
                <div class="header-actions">
                    <a href="menu.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Volver al Men&uacute;
                    </a>
                </div>
            </div>
        </header>

        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= $total_usuarios ?></div>
                <div class="stat-label">
                    <i class="fas fa-users"></i>
                    Total Usuarios
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $admin_count ?></div>
                <div class="stat-label">
                    <i class="fas fa-crown"></i>
                    Administradores
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $encargado_count ?></div>
                <div class="stat-label">
                    <i class="fas fa-laptop-code"></i>
                    Encargados
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $usuario_count ?></div>
                <div class="stat-label">
                    <i class="fas fa-user"></i>
                    Usuarios
                </div>
            </div>
        </div>

        <!-- Formulario de creación -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-plus"></i> Registrar Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="userForm">
                    <div class="form-grid">
                        <div class="field">
                            <label for="nombre">
                                <i class="fas fa-user"></i>
                                Nombre completo
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   placeholder="Ingresa el nombre completo" required>
                        </div>

                        <div class="field">
                            <label for="correo">
                                <i class="fas fa-envelope"></i>
                                Correo electrónico
                            </label>
                            <input type="email" class="form-control" id="correo" name="correo" 
                                   placeholder="usuario@institucion.edu" required>
                        </div>

                        <div class="field">
                            <label for="pass">
                                <i class="fas fa-lock"></i>
                                Contraseña
                            </label>
                            <input type="password" class="form-control" id="pass" name="pass" 
                                   placeholder="Crear una contraseña segura" required>
                            <div class="password-strength">
                                <div class="strength-bar" id="passwordStrength"></div>
                            </div>
                        </div>

                        <div class="field">
                            <label for="rol">
                                <i class="fas fa-user-tag"></i>
                                Rol del usuario
                            </label>
                            <div class="select-wrapper">
                                <select class="form-control form-select" id="rol" name="rol" required onchange="toggleEncargado()">
                                    <option value="" selected disabled>Seleccionar rol</option>
                                    <option value="ADMIN">Administrador del Sistema</option>
                                    <option value="ENCARGADO">Encargado Informático</option>
                                    <option value="USUARIO">Personal Escolar</option>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label for="id_establecimiento">
                                <i class="fas fa-school"></i>
                                Establecimiento
                            </label>
                            <div class="select-wrapper">
                                <select class="form-control form-select" id="id_establecimiento" name="id_establecimiento" required>
                                    <option value="" selected disabled>Seleccionar establecimiento</option>
                                    <?php
                                    $escuelas = $conexion->query("SELECT id_establecimiento, nombre_establecimiento FROM establecimientos ORDER BY nombre_establecimiento");
                                    while ($row = $escuelas->fetch_assoc()) {
                                        echo "<option value='".$row['id_establecimiento']."'>".htmlspecialchars($row['nombre_establecimiento'])."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label for="tipo_encargado">
                                <i class="fas fa-user-cog"></i>
                                Tipo de Encargado
                            </label>
                            <div class="select-wrapper">
                                <select class="form-control form-select" id="tipo_encargado" name="tipo_encargado" disabled>
                                    <option value="" selected disabled>Seleccionar tipo</option>
                                    <option value="INFORMATICA">Informática</option>
                                    <option value="ACADEMICA">Académica</option>
                                    <option value="ADMINISTRATIVA">Administrativa</option>
                                    <option value="DIRECCION">Dirección</option>
                                    <option value="CONVIVENCIA">Convivencia Escolar</option>
                                </select>
                            </div>
                            <small style="color: var(--gray-600); margin-top: 8px; display: block;">
                                Solo aplica para usuarios con rol "Personal Escolar"
                            </small>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" name="crear" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i>
                            <span class="btn-text">Registrar Usuario</span>
                        </button>
                        <a href="gestion_usuarios.php" class="btn btn-outline">
                            <i class="fas fa-broom"></i>
                            Limpiar Formulario
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Usuarios Registrados
                </h2>
                <div class="count-badge">
                    <i class="fas fa-users"></i>
                    <?= $total_usuarios ?> usuarios
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
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
                                $badge_icon = '';
                                if ($row['rol'] === 'ADMIN') {
                                    $badge_class = 'badge-admin';
                                    $badge_icon = 'fa-crown';
                                } elseif ($row['rol'] === 'ENCARGADO') {
                                    $badge_class = 'badge-encargado';
                                    $badge_icon = 'fa-laptop-code';
                                } else {
                                    $badge_class = 'badge-usuario';
                                    $badge_icon = 'fa-user';
                                }
                        ?>
                        <tr>
                            <td><strong>#<?= $row['id_usuario'] ?></strong></td>
                            <td>
                                <i class="fas fa-user" style="color: var(--primary); margin-right: 8px;"></i>
                                <?= htmlspecialchars($row['nombre']) ?>
                            </td>
                            <td>
                                <i class="fas fa-envelope" style="color: var(--secondary); margin-right: 8px;"></i>
                                <?= htmlspecialchars($row['correo']) ?>
                            </td>
                            <td>
                                <span class="badge <?= $badge_class ?>">
                                    <i class="fas <?= $badge_icon ?>"></i>
                                    <?= $row['rol'] ?>
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-school" style="color: var(--info); margin-right: 8px;"></i>
                                <?= htmlspecialchars($row['nombre_establecimiento'] ?? 'Sin asignar') ?>
                            </td>
                            <td>
                                <?php if ($row['tipo_encargado']): ?>
                                    <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: var(--primary);">
                                        <?= $row['tipo_encargado'] ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray-500);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small style="color: var(--gray-600);">
                                    <?= date('d/m/Y', strtotime($row['fecha_registro'])) ?>
                                </small>
                            </td>
                            <td class="actions">
                                <a href="editar_usuario.php?id=<?= $row['id_usuario'] ?>" class="action-btn btn-edit" title="Editar usuario">
                                    <i class="fas fa-edit"></i>
                                    Editar
                                </a>
                                <a href="gestion_usuarios.php?eliminar=<?= $row['id_usuario'] ?>" 
                                   class="action-btn btn-delete" 
                                   title="Eliminar usuario"
                                   onclick="return confirm('¿Estás seguro de eliminar al usuario <?= htmlspecialchars($row['nombre']) ?>?\n\nEsta acción no se puede deshacer.')">
                                    <i class="fas fa-trash"></i>
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                        ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <p>No hay usuarios registrados</p>
                                <p class="subtext">Comienza agregando el primer usuario utilizando el formulario superior.</p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleEncargado() {
            let rol = document.getElementById("rol").value;
            let encargadoSelect = document.getElementById("tipo_encargado");
            let encargadoLabel = encargadoSelect.previousElementSibling;

            if (rol === "USUARIO") {
                encargadoSelect.disabled = false;
                encargadoSelect.required = true;
                encargadoLabel.style.opacity = "1";
            } else {
                encargadoSelect.disabled = true;
                encargadoSelect.required = false;
                encargadoSelect.selectedIndex = 0;
                encargadoLabel.style.opacity = "0.6";
            }
        }

        // Password strength indicator
        document.getElementById('pass').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            if (password.length > 6) strength++;
            if (password.length > 10) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'strength-bar ' + 
                (strength < 2 ? 'strength-weak' : 
                 strength < 4 ? 'strength-medium' : 'strength-strong');
        });

        // Form submission handling
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnIcon = btn.querySelector('.fa-save');
            
            btnText.textContent = 'Registrando...';
            btnIcon.className = 'fas fa-spinner fa-spin';
            btn.classList.add('btn-loading');
        });

        // Real-time form validation
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = 'var(--success)';
                } else {
                    this.style.borderColor = 'var(--danger)';
                }
            });
            
            input.addEventListener('input', function() {
                this.style.borderColor = 'var(--gray-300)';
            });
        });

        // Initialize tooltips and effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats counters
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 30;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        stat.textContent = Math.ceil(current);
                        setTimeout(updateCounter, 50);
                    } else {
                        stat.textContent = target;
                    }
                };
                
                updateCounter();
            });
        });
    </script>
</body>
</html>