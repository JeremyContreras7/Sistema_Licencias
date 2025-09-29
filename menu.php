<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ADMIN") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Obtener establecimientos
$establecimientos = $conexion->query("
    SELECT id_establecimiento, nombre_establecimiento, correo, telefono, tipo_escuela
    FROM establecimientos
    ORDER BY nombre_establecimiento ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Normalize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="css/stylemenu_admin.css">
    <link rel="icon" href="img/logo.png">
</head>
<body>
<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand">
            <img src="img/logo.png" alt="Logo" class="brand-logo">
            <div>
                <h2>Panel Administrativo</h2>
                <p>Municipalidad de Ovalle</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="panel.php">📊 Estado licencias</a></li>
                <li><a href="gestionLicencias.php">🗂 Gestión licencias</a></li>
                <li><a href="gestionUsuarios.php">👥 Gestión usuarios</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
    </aside>

    <!-- Contenido principal -->
    <main class="main">
        <header class="header">
            <div>
                <h1>👑 Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
                <span class="role">ADMIN</span>
            </div>
            <div class="actions">
                <a href="gestionUsuarios.php" class="btn primary">Crear usuario</a>
                <a href="gestionLicencias.php" class="btn outline">Nueva licencia</a>
            </div>
        </header>

        <section class="panel">
            <h2>🏫 Establecimientos registrados</h2>

            <!-- Buscador -->
            <input type="text" id="search" class="search" placeholder="Buscar establecimiento…">

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Tipo escuela</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($establecimientos && $establecimientos->num_rows): ?>
                        <?php while ($row = $establecimientos->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id_establecimiento'] ?></td>
                            <td><?= htmlspecialchars($row['nombre_establecimiento']) ?></td>
                            <td><?= htmlspecialchars($row['correo'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['telefono'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['tipo_escuela'] ?? '-') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">No hay establecimientos registrados</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="footer">
            <small>Departamento de Educación — Municipalidad de Ovalle</small>
        </footer>
    </main>
</div>

<script>
// Filtro de búsqueda de tabla
document.getElementById('search').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
</body>
</html>
