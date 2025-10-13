<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Obtener el id_establecimiento directo de la sesión
$id_establecimiento = $_SESSION['id_establecimiento'];

// Variable para mensajes
$mensaje = '';

// --- CREAR EQUIPO ---
if (isset($_POST['crear'])) {
    $nombre_equipo = $conexion->real_escape_string($_POST['nombre_equipo']);
    $sistema_operativo = $conexion->real_escape_string($_POST['sistema_operativo']);
    $Modelo = $conexion->real_escape_string($_POST['Modelo']);
    $Numero_serial = $conexion->real_escape_string($_POST['Numero_serial']);

    // Verificar si el número serial ya existe
    $check_sql = "SELECT id_equipo FROM equipos WHERE Numero_serial = '$Numero_serial'";
    $result = $conexion->query($check_sql);
    
    if ($result->num_rows > 0) {
        $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Error: El número serial "' . htmlspecialchars($Numero_serial) . '" ya está registrado en el sistema.</div>';
    } else {
        $sql = "INSERT INTO equipos (nombre_equipo, sistema_operativo, Modelo, numero_serial, id_establecimiento) 
                VALUES ('$nombre_equipo', '$sistema_operativo', '$Modelo', '$Numero_serial', '$id_establecimiento')";
        
        if ($conexion->query($sql)) {
            header("Location: gestionEquipos.php?success=1");
            exit();
        } else {
            $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Error al registrar el equipo: ' . $conexion->error . '</div>';
        }
    }
}

// --- MOSTRAR MENSAJE DE ÉXITO ---
if (isset($_GET['success'])) {
    $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Equipo registrado exitosamente.</div>';
}

// --- ELIMINAR EQUIPO ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM equipos WHERE id_equipo=$id AND id_establecimiento=$id_establecimiento");
    header("Location: gestionEquipos.php?deleted=1");
    exit();
}

// --- MOSTRAR MENSAJE DE ELIMINACIÓN ---
if (isset($_GET['deleted'])) {
    $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Equipo eliminado exitosamente.</div>';
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
    <style>

    </style>
</head>
<body>
    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Confirmar Eliminación</h3>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este equipo?</p>
                <div class="equipo-info">
                    <strong id="equipoNombre"></strong><br>
                    <span id="equipoDetalles"></span>
                </div>
                <p style="color: #e74c3c; font-weight: 500;">
                    <i class="fas fa-info-circle"></i>
                    Esta acción no se puede deshacer
                </p>
                <div class="modal-actions">
                    <button class="btn-cancel" id="cancelDelete">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <a href="#" class="btn-confirm-delete" id="confirmDelete">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>

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

        <!-- Mostrar mensajes -->
        <?php if (!empty($mensaje)): ?>
            <div class="message-container">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de Estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="number"><?php echo $total_equipos; ?></div>
                <div class="label">Equipos Registrados</div>
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
                            <i class="fas fa-cog"></i> Sistema operativo
                        </label>
                        <textarea id="sistema_operativo" name="sistema_operativo" placeholder="Windows 10 Pro" rows="3"></textarea>
                    </div>

                    <div class="field">
                        <label for="Modelo">
                            <i class="fas fa-laptop"></i> Modelo
                        </label>
                        <input id="Modelo" type="text" name="Modelo" placeholder="Expertbook">
                    </div>
                    
                    <div class="field">
                        <label for="Numero_serial">
                            <i class="fas fa-barcode"></i> Número Serial
                        </label>
                        <input id="Numero_serial" type="text" name="Numero_serial" placeholder="3CMN8G21B" required>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Este número debe ser único para cada equipo
                        </small>
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
                <div class="badge">
                    <i class="fas fa-laptop"></i> <?php echo $total_equipos; ?> equipos
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Equipo</th>
                            <th>Sistema Operativo</th>
                            <th>Modelo</th>
                            <th>Número Serial</th>
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
                                <td><?php echo htmlspecialchars($row['Modelo'] ?: '—'); ?></td>
                                <td>
                                    <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-family: monospace;">
                                        <?php echo htmlspecialchars($row['Numero_serial'] ?: '—'); ?>
                                    </code>
                                </td>
                                <td>
                                    <i class="fas fa-building" style="color: var(--secondary); margin-right: 8px;"></i>
                                    <?php echo htmlspecialchars($row['nombre_establecimiento'] ?? $_SESSION['establecimiento']); ?>
                                </td>
                                <td class="center actions">
                                    <a href="editar_equipo.php?id=<?php echo $row['id_equipo']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="#" class="btn-delete" 
                                       data-id="<?php echo $row['id_equipo']; ?>"
                                       data-nombre="<?php echo htmlspecialchars($row['nombre_equipo']); ?>"
                                       data-serial="<?php echo htmlspecialchars($row['Numero_serial']); ?>"
                                       data-modelo="<?php echo htmlspecialchars($row['Modelo']); ?>">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-laptop"></i>
                                    <p>No hay equipos registrados en este establecimiento</p>
                                    <p>Utiliza el formulario superior para registrar el primer equipo.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        // Modal de Confirmación para Eliminar
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        const equipoNombre = document.getElementById('equipoNombre');
        const equipoDetalles = document.getElementById('equipoDetalles');

        let currentDeleteUrl = '';

        // Abrir modal cuando se hace clic en eliminar
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const serial = this.getAttribute('data-serial');
                const modelo = this.getAttribute('data-modelo');
                
                // Actualizar información en el modal
                equipoNombre.textContent = nombre;
                equipoDetalles.innerHTML = `
                    Serial: <strong>${serial}</strong><br>
                    Modelo: ${modelo || '—'}
                `;
                
                // Configurar URL de eliminación
                currentDeleteUrl = `gestionEquipos.php?eliminar=${id}`;
                
                // Mostrar modal
                deleteModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        });

        // Cerrar modal al cancelar
        cancelDelete.addEventListener('click', function() {
            deleteModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Confirmar eliminación
        confirmDelete.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = currentDeleteUrl;
        });

        // Cerrar modal al hacer clic fuera
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteModal.style.display === 'block') {
                deleteModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Efectos de interacción mejorados
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar efecto de carga a los botones de envío
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('btn-loading');
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    }
                });
            });

            // Auto-ocultar mensajes después de 5 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        if (alert.parentElement) {
                            alert.parentElement.remove();
                        }
                    }, 500);
                }, 5000);
            });

            // Efectos hover para las filas de la tabla
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>