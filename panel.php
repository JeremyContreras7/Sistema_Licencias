<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

include("conexion.php"); // aquí defines $conexion con mysqli

$rol = $_SESSION['rol'];
$ses_establecimiento = $_SESSION['id_establecimiento'] ?? null;

// filtros
$selected_est = $_GET['establecimiento'] ?? '';
$q = trim($_GET['q'] ?? '');

// si es ENCARGADO, forzar su establecimiento
if ($rol === 'ENCARGADO') {
    $selected_est = $ses_establecimiento;
}

// lista de establecimientos
$establecimientos = [];
$res = $conexion->query("SELECT id_establecimiento, nombre_establecimiento FROM establecimientos ORDER BY nombre_establecimiento");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $establecimientos[] = $row;
    }
}

// construir WHERE dinámico
$whereClauses = ["1=1"];
$params = [];
$types = "";

if (!empty($selected_est)) {
    $whereClauses[] = "e.id_establecimiento = ?";
    $params[] = $selected_est;
    $types .= "i";
}

if (!empty($q)) {
    $whereClauses[] = "(e.nombre_equipo LIKE ? OR s.nombre_software LIKE ?)";
    $params[] = "%" . $q . "%";
    $params[] = "%" . $q . "%";
    $types .= "ss";
}

$where = implode(" AND ", $whereClauses);

// consultas
$sql_vencidas = "
SELECT e.id_equipo, e.nombre_equipo, est.nombre_establecimiento AS establecimiento,
       s.nombre_software, s.version, l.fecha_vencimiento
FROM licencias l
JOIN equipos e ON e.id_equipo = l.id_equipo
JOIN software s ON s.id_software = l.id_software
JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
WHERE l.fecha_vencimiento < CURDATE()
AND $where
ORDER BY l.fecha_vencimiento ASC
LIMIT 200
";

$sql_proximas = "
SELECT e.id_equipo, e.nombre_equipo, est.nombre_establecimiento AS establecimiento,
       s.nombre_software, s.version, l.fecha_vencimiento,
       DATEDIFF(l.fecha_vencimiento, CURDATE()) AS dias_restantes
FROM licencias l
JOIN equipos e ON e.id_equipo = l.id_equipo
JOIN software s ON s.id_software = l.id_software
JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
WHERE l.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
AND $where
ORDER BY l.fecha_vencimiento ASC
LIMIT 200
";

$sql_criticos = "
SELECT DISTINCT e.id_equipo, e.nombre_equipo, est.nombre_establecimiento AS establecimiento,
       s.nombre_software, s.version,
       (SELECT l2.fecha_vencimiento
        FROM licencias l2
        WHERE l2.id_equipo = e.id_equipo AND l2.id_software = s.id_software
        ORDER BY l2.fecha_vencimiento DESC LIMIT 1) AS fecha_ult_venc
FROM software s
JOIN licencias l ON l.id_software = s.id_software
JOIN equipos e ON l.id_equipo = e.id_equipo
JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
WHERE s.es_critico = 1
AND (
      (SELECT l3.fecha_vencimiento
       FROM licencias l3
       WHERE l3.id_equipo = e.id_equipo AND l3.id_software = s.id_software
       ORDER BY l3.fecha_vencimiento DESC LIMIT 1) IS NULL
   OR
      (SELECT l3.fecha_vencimiento
       FROM licencias l3
       WHERE l3.id_equipo = e.id_equipo AND l3.id_software = s.id_software
       ORDER BY l3.fecha_vencimiento DESC LIMIT 1) < CURDATE()
)
AND $where
LIMIT 200
";


// función auxiliar para ejecutar consultas con parámetros
function ejecutarConsulta($conexion, $sql, $types, $params) {
    $stmt = $conexion->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

try {
    $vencidas = ejecutarConsulta($conexion, $sql_vencidas, $types, $params);
    $proximas = ejecutarConsulta($conexion, $sql_proximas, $types, $params);
    $criticos = ejecutarConsulta($conexion, $sql_criticos, $types, $params);
} catch (Exception $e) {
    $errorMsg = "Error al consultar la base de datos: " . $e->getMessage();
    $vencidas = $proximas = $criticos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel de Control - Licencias</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="/img/logo.png">
<style>
body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; color:#222; }
.container { max-width:1100px; margin:0 auto; }
.header { display:flex; align-items:center; justify-content:space-between; gap:10px; }
h1 { margin:0 0 10px 0; }
.card { background:#fff; border-radius:8px; padding:15px; box-shadow:0 4px 10px rgba(0,0,0,0.06); margin-bottom:15px; }
.table { width:100%; border-collapse:collapse; }
.table th, .table td { padding:8px; border-bottom:1px solid #eee; text-align:left; }
.small { font-size:0.9rem; color:#666; }
.empty { color:#666; font-style:italic; }
.badge { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:bold; font-size:0.9rem; }
.badge.red { background:#dc3545; color:#fff; }
.badge.yellow { background:#ffc107; color:#333; }
.badge.orange { background:#fd7e14; color:#fff; }
.info-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
.info { padding:8px 12px; background:#f8f9fa; border-radius:8px; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>📊 Panel de Control - Licencias</h1>
            <p class="small">Usuario: <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?> — Rol: <?= htmlspecialchars($rol) ?></p>
        </div>
        <div>
            <a href="menu.php"><button>← Volver</button></a>
            <a href="logout.php"><button>Cerrar sesión</button></a>
        </div>
    </div>
    <div class="container">

    <!-- 🔎 Filtros y buscador -->
    <div class="card">
        <form method="get" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            
            <!-- Buscar por nombre equipo o software -->
            <input type="text" name="q" placeholder="Buscar equipo o software"
                   value="<?= htmlspecialchars($q) ?>"
                   style="padding:8px; flex:1; border:1px solid #ccc; border-radius:6px;">
            
            <!-- Filtro por establecimiento (solo admin puede elegir) -->
            <?php if ($rol === 'ADMIN'): ?>
            <select name="establecimiento" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                <option value="">-- Todos los establecimientos --</option>
                <?php foreach ($establecimientos as $est): ?>
                    <option value="<?= $est['id_establecimiento'] ?>" 
                        <?= ($selected_est == $est['id_establecimiento']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($est['nombre_establecimiento']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <button type="submit" style="padding:8px 14px; border:none; border-radius:6px; background:#007BFF; color:#fff; cursor:pointer;">🔍 Buscar</button>
            <a href="panel_control.php" style="text-decoration:none;">
                <button type="button" style="padding:8px 14px; border:none; border-radius:6px; background:#6c757d; color:#fff; cursor:pointer;">
                    ❌ Limpiar
                </button>
            </a>
        </form>
    </div>


    <?php if (!empty($errorMsg)): ?>
        <div class="card"><strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card info-row">
        <div class="info"><span class="badge red"><?= count($vencidas) ?></span> Licencias vencidas</div>
        <div class="info"><span class="badge yellow"><?= count($proximas) ?></span> Próximas (30 días)</div>
        <div class="info"><span class="badge orange"><?= count($criticos) ?></span> Crítico sin licencia</div>
    </div>

    <div class="card">
        <h3>🚨 Licencias vencidas</h3>
        <?php if ($vencidas): ?>
            <table class="table">
                <tr><th>Equipo</th><th>Software</th><th>Versión</th><th>Vencimiento</th><th>Escuela</th></tr>
                <?php foreach ($vencidas as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre_equipo']) ?></td>
                    <td><?= htmlspecialchars($r['nombre_software']) ?></td>
                    <td><?= htmlspecialchars($r['version']) ?></td>
                    <td><?= htmlspecialchars($r['fecha_vencimiento']) ?></td>
                    <td><?= htmlspecialchars($r['establecimiento']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?><p class="empty">No hay licencias vencidas.</p><?php endif; ?>
    </div>

    <div class="card">
        <h3>⚠️ Próximas a vencer</h3>
        <?php if ($proximas): ?>
            <table class="table">
                <tr><th>Equipo</th><th>Software</th><th>Versión</th><th>Días</th><th>Fecha</th><th>Escuela</th></tr>
                <?php foreach ($proximas as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre_equipo']) ?></td>
                    <td><?= htmlspecialchars($r['nombre_software']) ?></td>
                    <td><?= htmlspecialchars($r['version']) ?></td>
                    <td><?= htmlspecialchars($r['dias_restantes']) ?></td>
                    <td><?= htmlspecialchars($r['fecha_vencimiento']) ?></td>
                    <td><?= htmlspecialchars($r['establecimiento']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?><p class="empty">No hay licencias próximas a vencer.</p><?php endif; ?>
    </div>

    <div class="card">
        <h3>🔴 Críticos sin licencia válida</h3>
        <?php if ($criticos): ?>
            <table class="table">
                <tr><th>Equipo</th><th>Software</th><th>Versión</th><th>Últ. vencimiento</th><th>Escuela</th></tr>
                <?php foreach ($criticos as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre_equipo']) ?></td>
                    <td><?= htmlspecialchars($r['nombre_software']) ?></td>
                    <td><?= htmlspecialchars($r['version']) ?></td>
                    <td><?= htmlspecialchars($r['fecha_ult_venc'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($r['establecimiento']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?><p class="empty">Todo el software crítico tiene licencias válidas.</p><?php endif; ?>
    </div>
</div>
</body>
</html>
