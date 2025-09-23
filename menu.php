<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ADMIN") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/stylemenu.css"> <!-- tu CSS moderno -->
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <img src="img/logo.png" alt="Logo" class="brand-logo">
                <div class="brand-text">
                    <strong>Panel Administrativo</strong>
                    <small>Ilustre Municipalidad de Ovalle</small>
                </div>
            </div>

            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="panel.php">📊 Ver estado de las licencias</a></li>
                    <li><a href="gestionLicencias.php">🗂 Gestión de licencias</a></li>
                    <li><a href="gestionUsuarios.php">👥 Gestión de usuarios</a></li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a class="logout" href="logout.php">🚪 Cerrar sesión</a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main">
            <header class="main-header">
                <div>
                    <h1>👑 Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                    <p class="subtitle">Rol: <strong>ADMIN</strong></p>
                </div>

                <div class="quick-actions">
                    <a class="btn btn-primary" href="gestionUsuarios.php">Crear usuario</a>
                    <a class="btn btn-outline" href="gestionLicencias.php">Nueva licencia</a>
                </div>
            </header>

            <!-- Resumen rápido -->
            <section class="cards">
                <article class="card">
                    <h3>Licencias activas</h3>
                    <div class="value" id="count-licencias">--</div>
                </article>

                <article class="card">
                    <h3>Equipos registrados</h3>
                    <div class="value" id="count-equipos">--</div>
                </article>

                <article class="card">
                    <h3>Usuarios</h3>
                    <div class="value" id="count-usuarios">--</div>
                </article>
            </section>

            <!-- Lugar para contenido principal -->
            <section class="content-panel">
                <h2>Estado general</h2>
                <p class="muted">Accede a los módulos de la izquierda para administrar el sistema.</p>

                <!-- Ejemplo: tabla resumen -->
                <div class="table-wrap">
                    <table class="table" aria-label="Resumen">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equipo</th>
                                <th>Software</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="resumen-body">
                            <!-- filas generadas desde PHP o AJAX -->
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="footer">
                <small>Departamento de Educación — Ilustre Municipalidad de Ovalle</small>
            </footer>
        </main>
    </div>

    <script>
    fetch('api/resumen.php').then(r=>r.json()).then(data=>{
    document.getElementById('count-licencias').textContent = data.licencias;
    document.getElementById('count-equipos').textContent = data.equipos;
    document.getElementById('count-usuarios').textContent = data.usuarios;
    });
    </script>
</body>
</html>
