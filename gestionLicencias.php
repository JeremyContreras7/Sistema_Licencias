<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$rol = $_SESSION['rol'];
$id_establecimiento = $_SESSION['id_establecimiento'] ?? null;
$nombre_usuario = htmlspecialchars($_SESSION['nombre'] ?? '');

// --- CREAR LICENCIA ---
if (isset($_POST['crear'])) {
    $id_equipo = (int)$_POST['id_equipo'];
    $id_software = (int)$_POST['id_software'];
    $fecha_inicio = trim($_POST['fecha_inicio']);
    $fecha_vencimiento = trim($_POST['fecha_vencimiento']);

    // Validaciones básicas
    if (empty($fecha_inicio) || empty($fecha_vencimiento)) {
        header("Location: gestionLicencias.php?error=fechas");
        exit();
    }
    if ($fecha_inicio > $fecha_vencimiento) {
        header("Location: gestionLicencias.php?error=fechasinvalida");
        exit();
    }

    // Si el usuario es ENCARGADO, verificar que el equipo y software pertenecen a su establecimiento
    if ($rol === "ENCARGADO") {
        // verificar equipo
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM equipos WHERE id_equipo = ? AND id_establecimiento = ?");
        $stmt->bind_param("ii", $id_equipo, $id_establecimiento);
        $stmt->execute();
        $stmt->bind_result($cntEq);
        $stmt->fetch();
        $stmt->close();
        if ($cntEq == 0) {
            header("Location: gestionLicencias.php?error=equipo_no_pertenece");
            exit();
        }

        // verificar software
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM software WHERE id_software = ? AND id_establecimiento = ?");
        $stmt->bind_param("ii", $id_software, $id_establecimiento);
        $stmt->execute();
        $stmt->bind_result($cntSw);
        $stmt->fetch();
        $stmt->close();
        if ($cntSw == 0) {
            header("Location: gestionLicencias.php?error=software_no_pertenece");
            exit();
        }
    }

    // Insertar licenca (uso prepared statement)
    $stmt = $conexion->prepare("INSERT INTO licencias (id_equipo, id_software, fecha_inicio, fecha_vencimiento) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_equipo, $id_software, $fecha_inicio, $fecha_vencimiento);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        header("Location: gestionLicencias.php?msg=created");
    } else {
        header("Location: gestionLicencias.php?error=db");
    }
    exit();
}

// --- ELIMINAR LICENCIA ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];

    if ($rol === "ENCARGADO") {
        // eliminar solo si la licencia pertenece a un equipo del establecimiento del encargado
        $stmt = $conexion->prepare("
            DELETE l FROM licencias l
            JOIN equipos e ON l.id_equipo = e.id_equipo
            WHERE l.id_licencia = ? AND e.id_establecimiento = ?
        ");
        $stmt->bind_param("ii", $id, $id_establecimiento);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
    } else {
        // ADMIN puede eliminar cualquiera
        $stmt = $conexion->prepare("DELETE FROM licencias WHERE id_licencia = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
    }

    if ($affected > 0) {
        header("Location: gestionLicencias.php?msg=deleted");
    } else {
        header("Location: gestionLicencias.php?error=delete_denegado");
    }
    exit();
}

// --- LISTADO DE EQUIPOS para el select ---
if ($rol === "ENCARGADO") {
    $equipos = $conexion->query("SELECT id_equipo, nombre_equipo FROM equipos WHERE id_establecimiento = {$id_establecimiento} ORDER BY nombre_equipo");
} else {
    $equipos = $conexion->query("SELECT id_equipo, nombre_equipo FROM equipos ORDER BY nombre_equipo");
}

// --- LISTADO DE SOFTWARE para el select ---
if ($rol === "ENCARGADO") {
    $software = $conexion->query("SELECT id_software, nombre_software, version FROM software WHERE id_establecimiento = {$id_establecimiento} ORDER BY nombre_software");
} else {
    $software = $conexion->query("SELECT id_software, nombre_software, version FROM software ORDER BY nombre_software");
}

// --- LISTADO DE LICENCIAS para la tabla ---
if ($rol === "ENCARGADO") {
    $licencias = $conexion->query("
        SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version, l.fecha_inicio, l.fecha_vencimiento
        FROM licencias l
        JOIN equipos e ON e.id_equipo = l.id_equipo
        JOIN software s ON s.id_software = l.id_software
        WHERE e.id_establecimiento = {$id_establecimiento}
        ORDER BY l.fecha_vencimiento ASC
    ");
} else {
    $licencias = $conexion->query("
        SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version, l.fecha_inicio, l.fecha_vencimiento, est.nombre_establecimiento
        FROM licencias l
        JOIN equipos e ON e.id_equipo = l.id_equipo
        JOIN software s ON s.id_software = l.id_software
        JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
        ORDER BY l.fecha_vencimiento ASC
    ");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Licencias</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styleGlicencias.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="page">
    <header>
        <div class="header-content">
            <h1><i class="fas fa-file-contract"></i> Gestión de Licencias</h1>
            <a href="menu.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Menú
            </a>
        </div>
        <div class="user-info">
            <i class="fas fa-user"></i>
            <span><?= $nombre_usuario ?> — <?= htmlspecialchars($rol) ?></span>
        </div>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <div class="notice success">
            <i class="fas fa-check-circle"></i>
            <?php if ($_GET['msg'] === 'created') echo "Licencia registrada correctamente."; 
                  if ($_GET['msg'] === 'deleted') echo "Licencia eliminada correctamente."; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice error">
            <i class="fas fa-exclamation-circle"></i>
            <?php
                $err = $_GET['error'];
                if ($err === 'fechas') echo "Por favor complete las fechas de inicio y vencimiento.";
                elseif ($err === 'fechasinvalida') echo "La fecha de inicio no puede ser posterior a la fecha de vencimiento.";
                elseif ($err === 'equipo_no_pertenece') echo "El equipo seleccionado no pertenece a tu establecimiento.";
                elseif ($err === 'software_no_pertenece') echo "El software seleccionado no pertenece a tu establecimiento.";
                elseif ($err === 'delete_denegado') echo "No se pudo eliminar la licencia (permiso denegado o inexistente).";
                else echo "Error en la operación.";
            ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO CREAR LICENCIA -->
    <section class="form-card">
        <h2><i class="fas fa-plus-circle"></i> Registrar Nueva Licencia</h2>
        <form method="POST" action="">
            <div class="form-grid">
                <div class="field">
                    <label for="id_equipo"><i class="fas fa-desktop"></i> Equipo</label>
                    <select name="id_equipo" id="id_equipo" required>
                        <option disabled selected value="">Seleccione un equipo</option>
                        <?php while($eq = $equipos->fetch_assoc()): ?>
                            <option value="<?= $eq['id_equipo'] ?>"><?= htmlspecialchars($eq['nombre_equipo']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="id_software"><i class="fas fa-cube"></i> Software</label>
                    <select name="id_software" id="id_software" required>
                        <option disabled selected value="">Seleccione un software</option>
                        <?php while($sw = $software->fetch_assoc()): ?>
                            <option value="<?= $sw['id_software'] ?>"><?= htmlspecialchars($sw['nombre_software'] . ' ' . $sw['version']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha de inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="field">
                    <label for="fecha_vencimiento"><i class="fas fa-calendar-times"></i> Fecha de vencimiento</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required>
                </div>
            </div>
            <button type="submit" name="crear">
                <i class="fas fa-save"></i> Registrar Licencia
            </button>
        </form>
    </section>

    <!-- LISTADO DE LICENCIAS -->
    <section class="table-container">
        <h2><i class="fas fa-list"></i> Licencias Registradas</h2>
        <?php if ($licencias && $licencias->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Software</th>
                        <th>Versión</th>
                        <th>Fecha inicio</th>
                        <th>Fecha vencimiento</th>
                        <th>Estado</th>
                        <?php if ($rol === "ADMIN"): ?><th>Establecimiento</th><?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $licencias->fetch_assoc()): 
                    $hoy = date('Y-m-d');
                    $vencimiento = $row['fecha_vencimiento'];
                    $dias_restantes = floor((strtotime($vencimiento) - strtotime($hoy)) / (60 * 60 * 24));
                    
                    if ($vencimiento < $hoy) {
                        $estado = 'vencida';
                        $icono = 'fas fa-exclamation-triangle';
                        $texto = 'Vencida';
                    } elseif ($dias_restantes <= 30) {
                        $estado = 'proxima';
                        $icono = 'fas fa-clock';
                        $texto = 'Próxima a vencer';
                    } else {
                        $estado = 'vigente';
                        $icono = 'fas fa-check-circle';
                        $texto = 'Vigente';
                    }
                ?>
                    <tr>
                        <td><i class="fas fa-desktop"></i> <?= htmlspecialchars($row['nombre_equipo']) ?></td>
                        <td><i class="fas fa-cube"></i> <?= htmlspecialchars($row['nombre_software']) ?></td>
                        <td><?= htmlspecialchars($row['version']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_vencimiento']) ?></td>
                        <td>
                            <span class="estado-licencia <?= $estado ?>">
                                <i class="<?= $icono ?>"></i> <?= $texto ?>
                            </span>
                        </td>
                        <?php if ($rol === "ADMIN"): ?>
                            <td><i class="fas fa-building"></i> <?= htmlspecialchars($row['nombre_establecimiento']) ?></td>
                        <?php endif; ?>
                        <td>
                            <div class="acciones">
                                <a href="editar_licencia.php?id=<?= $row['id_licencia'] ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="gestionLicencias.php?eliminar=<?= $row['id_licencia'] ?>" 
                                   class="btn-eliminar" 
                                   onclick="return confirm('¿Está seguro de eliminar esta licencia?')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-contract"></i>
                <h3>No hay licencias registradas</h3>
                <p>Comience registrando una nueva licencia usando el formulario superior.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    // Validación de fechas en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaVencimiento = document.getElementById('fecha_vencimiento');
        
        fechaInicio.addEventListener('change', function() {
            if (fechaVencimiento.value && this.value > fechaVencimiento.value) {
                fechaVencimiento.value = this.value;
            }
        });
        
        fechaVencimiento.addEventListener('change', function() {
            if (fechaInicio.value && this.value < fechaInicio.value) {
                alert('La fecha de vencimiento no puede ser anterior a la fecha de inicio');
                this.value = fechaInicio.value;
            }
        });
    });
</script>
</body>
</html>
<?php
// ======================
// NOTIFICACIÓN DE LICENCIAS PRÓXIMAS A VENCER
// ======================
if ($rol === "ENCARGADO" && $licencias && $licencias->num_rows > 0) {
    $licencias->data_seek(0); // reiniciar puntero del resultset
    $proximas = [];

    while ($row = $licencias->fetch_assoc()) {
        $hoy = date('Y-m-d');
        $vencimiento = $row['fecha_vencimiento'];
        $dias_restantes = floor((strtotime($vencimiento) - strtotime($hoy)) / (60 * 60 * 24));

        if ($dias_restantes >= 0 && $dias_restantes <= 30) {
            $proximas[] = $row;
        }
    }

    if (count($proximas) > 0) {
        require 'phpmailer/Exception.php';
        require 'phpmailer/PHPMailer.php';
        require 'phpmailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '50615979dcf445'; // usuario Mailtrap
            $mail->Password = '084d022f9ec7c1'; // contraseña Mailtrap
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('notificaciones@sistema-licencias.cl', 'Sistema de Licencias');
            $mail->addAddress('jeremytortuguita@gmail.com', 'Jeremy'); // Destinatario principal

            $mail->isHTML(true);
            $mail->Subject = "Notificacion: Licencias proximas a vencer";

            $body = "<h2>Estimado/a</h2>";
            $body .= "<p>Se han detectado las siguientes licencias de su establecimiento que vencen en los proximos 30 dias:</p>";
            $body .= "<table border='1' cellspacing='0' cellpadding='5'>
                        <tr>
                            <th>Equipo</th>
                            <th>Software</th>
                            <th>Version</th>
                            <th>Fecha de Vencimiento</th>
                        </tr>";
            foreach ($proximas as $lic) {
                $body .= "<tr>
                            <td>{$lic['nombre_equipo']}</td>
                            <td>{$lic['nombre_software']}</td>
                            <td>{$lic['version']}</td>
                            <td>{$lic['fecha_vencimiento']}</td>
                          </tr>";
            }
            $body .= "</table><p>Por favor, gestione la renovacion a la brevedad.</p>";

            $mail->Body = $body;
            $mail->AltBody = "Tiene licencias próximas a vencer. Revise el sistema para más detalles.";

            $mail->send();
            // Puedes mostrar un aviso en pantalla si quieres:
            echo "<div class='notice success'><i class='fas fa-envelope'></i> Se ha enviado una notificación de licencias próximas a vencer a su correo.</div>";
        } catch (Exception $e) {
            echo "<div class='notice error'>❌ Error al enviar notificación: {$mail->ErrorInfo}</div>";
        }
    }
}
?>
