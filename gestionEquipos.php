<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Obtener el id_establecimiento directo de la sesión
$id_establecimiento = $_SESSION['id_establecimiento'];

// --- CREAR EQUIPO ---
if (isset($_POST['crear'])) {
    $nombre_equipo = $conexion->real_escape_string($_POST['nombre_equipo']);
    $sistema_operativo = $conexion->real_escape_string($_POST['sistema_operativo']);

    $sql = "INSERT INTO equipos (nombre_equipo, sistema_operativo, id_establecimiento) 
            VALUES ('$nombre_equipo', '$sistema_operativo', '$id_establecimiento')";
    $conexion->query($sql);
    header("Location: gestionEquipos.php");
    exit();
}

// --- ELIMINAR EQUIPO ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM equipos WHERE id_equipo=$id AND id_establecimiento=$id_establecimiento");
    header("Location: gestionEquipos.php");
    exit();
}

// --- LISTAR EQUIPOS SOLO DEL ESTABLECIMIENTO ---
$equipos = $conexion->query("
    SELECT e.*, est.nombre_establecimiento 
    FROM equipos e
    LEFT JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
    WHERE e.id_establecimiento = $id_establecimiento
    ORDER BY e.id_equipo DESC
");

// Contar equipos para estadísticas
$total_equipos = $equipos->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Equipos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styleequipos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
    <div class="page">
        <!-- Header Mejorado -->
        <header class="header">
            <div class="header-content">
                <div class="header-text">
                    <h1><i class="fas fa-laptop-code"></i> Gesti&oacute;n de Equipos</h1>
                    <p>Registra y administra los equipos del establecimiento</p>
                </div>
                <div class="header-actions">
                    <a class="back-btn" href="menu_informatico.php">
                        <i class="fas fa-arrow-left"></i> Volver al men&uacute;
                    </a>
                    <div class="badge">
                        <i class="fas fa-building"></i>
                        Establecimiento: <?php echo htmlspecialchars($_SESSION['establecimiento'] ?? '—'); ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Tarjetas de Estadísticas -->
        <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
            <div class="stat-card" style="background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid var(--primary);">
                <div class="number" style="font-size: 2rem; font-weight: bold; color: var(--primary);"><?php echo $total_equipos; ?></div>
                <div class="label" style="color: var(--gray-600);">Equipos Registrados</div>
            </div>
        </div>

        <!-- Formulario de Registro -->
        <section class="form-card">
            <h2><i class="fas fa-plus-circle"></i> Registrar Nuevo Equipo</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="field">
                        <label for="nombre_equipo">
                            <i class="fas fa-desktop"></i> Nombre del equipo
                        </label>
                        <input id="nombre_equipo" type="text" name="nombre_equipo" placeholder="Ej: Aula-Comp-01, Laboratorio-PC-02" required>
                    </div>

                    <div class="field">
                        <label for="sistema_operativo">
                            <i class="fas fa-cog"></i> Sistema operativo / Descripción
                        </label>
                        <textarea id="sistema_operativo" name="sistema_operativo" placeholder="Windows 10 Pro, 8GB RAM, 256GB SSD - Aula de informática principal" rows="3"></textarea>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" name="crear" class="btn-primary">
                        <i class="fas fa-save"></i> Registrar Equipo
                    </button>
                    <a href="gestionEquipos.php" class="btn-outline">
                        <i class="fas fa-broom"></i> Limpiar Formulario
                    </a>
                </div>
            </form>
        </section>

        <!-- Lista de Equipos -->
        <section class="table-container">
            <div class="table-header">
                <h2 class="section-title">
                    <i class="fas fa-list"></i> Equipos Registrados
                </h2>
                <div class="badge" style="background: var(--primary); color: white;">
                    <i class="fas fa-laptop"></i> <?php echo $total_equipos; ?> equipos
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Equipo</th>
                            <th>Sistema Operativo / Descripción</th>
                            <th>Establecimiento</th>
                            <th class="center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($equipos && $equipos->num_rows > 0): ?>
                            <?php while($row = $equipos->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $row['id_equipo']; ?></strong></td>
                                <td>
                                    <i class="fas fa-desktop" style="color: var(--primary); margin-right: 8px;"></i>
                                    <?php echo htmlspecialchars($row['nombre_equipo']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['sistema_operativo'] ?: '—'); ?></td>
                                <td>
                                    <i class="fas fa-building" style="color: var(--secondary); margin-right: 8px;"></i>
                                    <?php echo htmlspecialchars($row['nombre_establecimiento'] ?? $_SESSION['establecimiento']); ?>
                                </td>
                                <td class="center actions">
                                    <a href="editar_equipo.php?id=<?php echo $row['id_equipo']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="gestionEquipos.php?eliminar=<?php echo $row['id_equipo']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar este equipo?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-laptop" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 15px;"></i>
                                    <p>No hay equipos registrados en este establecimiento</p>
                                    <p style="font-size: 0.9rem; color: var(--gray-500); margin-top: 8px;">
                                        Utiliza el formulario superior para registrar el primer equipo.
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        // Efectos de interacción mejorados
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar efecto de carga a los botones de envío
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('btn-loading');
                        submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Procesando...';
                    }
                });
            });
            
            // Confirmación mejorada para eliminación
            const deleteLinks = document.querySelectorAll('.btn-delete');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('⚠️ ¿Estás seguro de que deseas eliminar este equipo?\n\nEsta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>