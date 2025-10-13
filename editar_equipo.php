<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$id_establecimiento = $_SESSION['id_establecimiento'];

// Validar ID recibido
if (!isset($_GET['id'])) {
    header("Location: gestionEquipos.php");
    exit();
}
$id_equipo = (int)$_GET['id'];

// Obtener datos del equipo
$stmt = $conexion->prepare("SELECT * FROM equipos WHERE id_equipo = ? AND id_establecimiento = ?");
$stmt->bind_param("ii", $id_equipo, $id_establecimiento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('❌ Equipo no encontrado.'); window.location='gestionEquipos.php';</script>";
    exit();
}

$equipo = $result->fetch_assoc();

// Variable para mensajes
$mensaje = '';

// Guardar cambios
if (isset($_POST['guardar'])) {
    $nombre_equipo = $conexion->real_escape_string($_POST['nombre_equipo']);
    $sistema_operativo = $conexion->real_escape_string($_POST['sistema_operativo']);
    $Modelo = $conexion->real_escape_string($_POST['Modelo']);
    $Numero_serial = $conexion->real_escape_string($_POST['Numero_serial']);

    // Verificar si el número serial ya existe en OTRO equipo
    $check_sql = "SELECT id_equipo FROM equipos WHERE Numero_serial = ? AND id_equipo != ?";
    $check_stmt = $conexion->prepare($check_sql);
    $check_stmt->bind_param("si", $Numero_serial, $id_equipo);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Error: El número serial "' . htmlspecialchars($Numero_serial) . '" ya está registrado en otro equipo.</div>';
    } else {
        $update = $conexion->prepare("UPDATE equipos SET nombre_equipo = ?, sistema_operativo = ?, Modelo = ?, Numero_serial = ? WHERE id_equipo = ? AND id_establecimiento = ?");
        $update->bind_param("ssssii", $nombre_equipo, $sistema_operativo, $Modelo, $Numero_serial, $id_equipo, $id_establecimiento);

        if ($update->execute()) {
            echo "<script>
                alert('✅ Equipo actualizado correctamente.');
                window.location='gestionEquipos.php?success=1';
            </script>";
            exit();
        } else {
            $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Error al actualizar el equipo: ' . $conexion->error . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Equipo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styleequipos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/img/logo.png">
    <style>
        /* Estilos para los mensajes de alerta */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert i {
            font-size: 18px;
        }

        .message-container {
            max-width: 800px;
            margin: 0 auto 20px auto;
            padding: 0 20px;
        }

        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .edit-header {
            background: linear-gradient(135deg, var(--primary), #2980b9);
            color: white;
            padding: 25px 30px;
            border-radius: 12px 12px 0 0;
            margin-bottom: 0;
        }

        .edit-header h1 {
            margin: 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .edit-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .equipo-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid var(--primary);
        }

        .equipo-info strong {
            color: var(--primary);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-save {
            background: var(--success);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-save:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .field-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .field-group {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header Mejorado -->
        <header class="header">
            <div class="header-content">
                <div class="header-text">
                    <h1><i class="fas fa-edit"></i> Editar Equipo</h1>
                    <p>Modifica la información del equipo seleccionado</p>
                </div>
                <div class="header-actions">
                    <a class="back-btn" href="gestionEquipos.php">
                        <i class="fas fa-arrow-left"></i> Volver a Equipos
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

        <div class="edit-container">
            <!-- Información del equipo -->
            <div class="equipo-info">
                <i class="fas fa-info-circle"></i> 
                Editando equipo <strong>#<?php echo $equipo['id_equipo']; ?></strong> 
                del establecimiento <strong><?php echo htmlspecialchars($_SESSION['establecimiento'] ?? '—'); ?></strong>
            </div>

            <!-- Formulario de Edición -->
            <section class="form-card">
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="field">
                            <label for="nombre_equipo">
                                <i class="fas fa-desktop"></i> Nombre del equipo
                            </label>
                            <input id="nombre_equipo" type="text" name="nombre_equipo" 
                                   value="<?php echo htmlspecialchars($equipo['nombre_equipo']); ?>" 
                                   placeholder="Ej: Aula-Comp-01, Laboratorio-PC-02" required>
                        </div>

                        <div class="field">
                            <label for="sistema_operativo">
                                <i class="fas fa-cog"></i> Sistema operativo
                            </label>
                            <textarea id="sistema_operativo" name="sistema_operativo" 
                                      placeholder="Windows 10 Pro, Ubuntu 22.04, etc." 
                                      rows="3"><?php echo htmlspecialchars($equipo['sistema_operativo']); ?></textarea>
                        </div>

                        <div class="field-group">
                            <div class="field">
                                <label for="Modelo">
                                    <i class="fas fa-laptop"></i> Modelo
                                </label>
                                <input id="Modelo" type="text" name="Modelo" 
                                       value="<?php echo htmlspecialchars($equipo['Modelo']); ?>" 
                                       placeholder="Expertbook, ThinkPad, etc.">
                            </div>
                            
                            <div class="field">
                                <label for="Numero_serial">
                                    <i class="fas fa-barcode"></i> Número Serial
                                </label>
                                <input id="Numero_serial" type="text" name="Numero_serial" 
                                       value="<?php echo htmlspecialchars($equipo['Numero_serial']); ?>" 
                                       placeholder="3CMN8G21B" required>
                                <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                    <i class="fas fa-info-circle"></i> Este número debe ser único para cada equipo
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="gestionEquipos.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" name="guardar" class="btn-save">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        // Efectos de interacción mejorados
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar efecto de carga al botón de guardar
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('btn-loading');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                }
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

            // Validación en tiempo real del número serial
            const serialInput = document.getElementById('Numero_serial');
            if (serialInput) {
                serialInput.addEventListener('input', function() {
                    const value = this.value.trim();
                    if (value.length > 0) {
                        this.style.borderColor = '#28a745';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }
        });
    </script>
</body>
</html>