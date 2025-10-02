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
    $id_usuario = (int)$_POST['id_usuario'];
    $fecha_inicio = trim($_POST['fecha_inicio']);
    $fecha_vencimiento = trim($_POST['fecha_vencimiento']);

    if (empty($fecha_inicio) || empty($fecha_vencimiento)) {
        header("Location: gestionLicencias.php?error=fechas");
        exit();
    }
    if ($fecha_inicio > $fecha_vencimiento) {
        header("Location: gestionLicencias.php?error=fechasinvalida");
        exit();
    }

    // Validaciones de establecimiento si es ENCARGADO
    if ($rol === "ENCARGADO") {
        // verificar equipo
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM equipos WHERE id_equipo = ? AND id_establecimiento = ?");
        $stmt->bind_param("ii", $id_equipo, $id_establecimiento);
        $stmt->execute(); $stmt->bind_result($cntEq); $stmt->fetch(); $stmt->close();
        if ($cntEq == 0) {
            header("Location: gestionLicencias.php?error=equipo_no_pertenece");
            exit();
        }

        // verificar software
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM software WHERE id_software = ? AND id_establecimiento = ?");
        $stmt->bind_param("ii", $id_software, $id_establecimiento);
        $stmt->execute(); $stmt->bind_result($cntSw); $stmt->fetch(); $stmt->close();
        if ($cntSw == 0) {
            header("Location: gestionLicencias.php?error=software_no_pertenece");
            exit();
        }

        // verificar usuario
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE id_usuario = ? AND id_establecimiento = ?");
        $stmt->bind_param("ii", $id_usuario, $id_establecimiento);
        $stmt->execute(); $stmt->bind_result($cntUs); $stmt->fetch(); $stmt->close();
        if ($cntUs == 0) {
            header("Location: gestionLicencias.php?error=usuario_no_pertenece");
            exit();
        }
    }

    // Insertar licencia
    $stmt = $conexion->prepare("
        INSERT INTO licencias (id_equipo, id_software, id_usuario, fecha_inicio, fecha_vencimiento) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiss", $id_equipo, $id_software, $id_usuario, $fecha_inicio, $fecha_vencimiento);
    $ok = $stmt->execute();
    $stmt->close();

    header("Location: gestionLicencias.php?".($ok ? "msg=created" : "error=db"));
    exit();
}

// --- ELIMINAR LICENCIA ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($rol === "ENCARGADO") {
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
        $stmt = $conexion->prepare("DELETE FROM licencias WHERE id_licencia = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
    }
    header("Location: gestionLicencias.php?".($affected > 0 ? "msg=deleted" : "error=delete_denegado"));
    exit();
}

// --- LISTADO DE EQUIPOS ---
if ($rol === "ENCARGADO") {
    $equipos = $conexion->query("SELECT id_equipo, nombre_equipo FROM equipos WHERE id_establecimiento = {$id_establecimiento} ORDER BY nombre_equipo");
} else {
    $equipos = $conexion->query("SELECT id_equipo, nombre_equipo FROM equipos ORDER BY nombre_equipo");
}

// --- LISTADO DE SOFTWARE ---
if ($rol === "ENCARGADO") {
    $software = $conexion->query("SELECT id_software, nombre_software, version FROM software WHERE id_establecimiento = {$id_establecimiento} ORDER BY nombre_software");
} else {
    $software = $conexion->query("SELECT id_software, nombre_software, version FROM software ORDER BY nombre_software");
}

// --- LISTADO DE USUARIOS ---
if ($rol === "ENCARGADO") {
    $usuarios = $conexion->query("SELECT id_usuario, nombre FROM usuarios WHERE id_establecimiento = {$id_establecimiento} ORDER BY nombre");
} else {
    $usuarios = $conexion->query("SELECT id_usuario, nombre FROM usuarios ORDER BY nombre");
}

// --- LISTADO DE LICENCIAS ---
if ($rol === "ENCARGADO") {
    $licencias = $conexion->query("
        SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version, 
               l.fecha_inicio, l.fecha_vencimiento, u.nombre AS usuario
        FROM licencias l
        JOIN equipos e ON e.id_equipo = l.id_equipo
        JOIN software s ON s.id_software = l.id_software
        JOIN usuarios u ON u.id_usuario = l.id_usuario
        WHERE e.id_establecimiento = {$id_establecimiento}
        ORDER BY l.fecha_vencimiento ASC
    ");
} else {
    $licencias = $conexion->query("
        SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version, 
               l.fecha_inicio, l.fecha_vencimiento, u.nombre AS usuario, est.nombre_establecimiento
        FROM licencias l
        JOIN equipos e ON e.id_equipo = l.id_equipo
        JOIN software s ON s.id_software = l.id_software
        JOIN usuarios u ON u.id_usuario = l.id_usuario
        JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
        ORDER BY l.fecha_vencimiento ASC
    ");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Licencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styleGlicencias.css">
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-file-contract me-2"></i>Gestión de Licencias</h1>
                    <p class="mb-0 mt-1">Administra las licencias de software de tu organización</p>
                </div>
                <div class="text-end">
                    <div class="user-info">
                        <i class="fas fa-user me-1"></i> <?= $nombre_usuario ?> — <?= htmlspecialchars($rol) ?>
                    </div>
                    <a href="menu.php" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Menú
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <?php
        // Calcular estadísticas
        $hoy = date('Y-m-d');
        $total_licencias = $licencias->num_rows;
        
        // Reiniciar el puntero para contar estados
        $licencias->data_seek(0);
        $vencidas = 0;
        $proximas = 0;
        $vigentes = 0;
        
        while($row = $licencias->fetch_assoc()) {
            $venc = $row['fecha_vencimiento'];
            $dias = floor((strtotime($venc)-strtotime($hoy))/(60*60*24));
            
            if ($venc < $hoy) {
                $vencidas++;
            } elseif ($dias <= 30) {
                $proximas++;
            } else {
                $vigentes++;
            }
        }
        
        // Volver al inicio para mostrar la tabla
        $licencias->data_seek(0);
        ?>
        <div class="stats-container">
            <div class="stat-card">
                <div class="number"><?= $total_licencias ?></div>
                <div class="label">Total Licencias</div>
            </div>
            <div class="stat-card">
                <div class="number text-success"><?= $vigentes ?></div>
                <div class="label">Vigentes</div>
            </div>
            <div class="stat-card">
                <div class="number text-warning"><?= $proximas ?></div>
                <div class="label">Próximas a Vencer</div>
            </div>
            <div class="stat-card">
                <div class="number text-danger"><?= $vencidas ?></div>
                <div class="label">Vencidas</div>
            </div>
        </div>

        <!-- Formulario de creación -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Registrar Nueva Licencia</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="field">
                            <label for="id_equipo"><i class="fas fa-desktop me-1"></i> Equipo</label>
                            <select class="form-select" name="id_equipo" id="id_equipo" required>
                                <option value="" selected disabled>Seleccione un equipo</option>
                                <?php while($eq = $equipos->fetch_assoc()): ?>
                                    <option value="<?= $eq['id_equipo'] ?>"><?= htmlspecialchars($eq['nombre_equipo']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="id_software"><i class="fas fa-cube me-1"></i> Software</label>
                            <select class="form-select" name="id_software" id="id_software" required>
                                <option value="" selected disabled>Seleccione un software</option>
                                <?php while($sw = $software->fetch_assoc()): ?>
                                    <option value="<?= $sw['id_software'] ?>"><?= htmlspecialchars($sw['nombre_software'].' '.$sw['version']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="id_usuario"><i class="fas fa-user me-1"></i> Usuario asignado</label>
                            <select class="form-select" name="id_usuario" id="id_usuario" required>
                                <option value="" selected disabled>Seleccione un usuario</option>
                                <?php while($us = $usuarios->fetch_assoc()): ?>
                                    <option value="<?= $us['id_usuario'] ?>"><?= htmlspecialchars($us['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="fecha_inicio"><i class="fas fa-calendar-alt me-1"></i> Fecha de inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="field">
                            <label for="fecha_vencimiento"><i class="fas fa-calendar-times me-1"></i> Fecha de vencimiento</label>
                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                        </div>
                    </div>
                    <button type="submit" name="crear" class="btn btn-primary mt-3">
                        <i class="fas fa-save me-1"></i> Registrar Licencia
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de licencias -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Licencias Registradas</h5>
                <span class="badge bg-primary"><?= $total_licencias ?> licencias</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Software</th>
                                <th>Versión</th>
                                <th>Usuario asignado</th>
                                <th>Fecha inicio</th>
                                <th>Fecha vencimiento</th>
                                <th>Estado</th>
                                <?php if ($rol === "ADMIN"): ?><th>Establecimiento</th><?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if ($licencias->num_rows > 0) {
                            while($row = $licencias->fetch_assoc()): 
                                $hoy = date('Y-m-d');
                                $venc = $row['fecha_vencimiento'];
                                $dias = floor((strtotime($venc)-strtotime($hoy))/(60*60*24));
                                
                                if ($venc < $hoy) {
                                    $estado_class = "estado-vencida";
                                    $txt = "Vencida";
                                } elseif ($dias <= 30) {
                                    $estado_class = "estado-proxima";
                                    $txt = "Próxima";
                                } else {
                                    $estado_class = "estado-vigente";
                                    $txt = "Vigente";
                                }
                        ?>
                            <tr>
                                <td><?= $row['nombre_equipo'] ?></td>
                                <td><?= $row['nombre_software'] ?></td>
                                <td><?= $row['version'] ?></td>
                                <td><?= $row['usuario'] ?></td>
                                <td><?= $row['fecha_inicio'] ?></td>
                                <td><?= $row['fecha_vencimiento'] ?></td>
                                <td><span class="<?= $estado_class ?>"><?= $txt ?></span></td>
                                <?php if ($rol === "ADMIN"): ?><td><?= $row['nombre_establecimiento'] ?></td><?php endif; ?>
                                <td>
                                    <a href="editar_licencia.php?id=<?= $row['id_licencia'] ?>" class="btn btn-sm btn-outline-primary action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="gestionLicencias.php?eliminar=<?= $row['id_licencia'] ?>" class="btn btn-sm btn-outline-danger action-btn" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta licencia?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        } else {
                            $colspan = $rol === "ADMIN" ? 8 : 7;
                            echo "<tr><td colspan='$colspan' class='text-center py-4 text-muted'><i class='fas fa-info-circle me-2'></i>No hay licencias registradas</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>