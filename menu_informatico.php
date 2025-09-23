<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Encargado Informático</title>
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
                    <strong>Panel Encargado</strong>
                    <small>Departamento de Educación</small>
                </div>
            </div>

            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="registrar.php">👥 Crear cuentas</a></li>
                    <li><a href="gestionEquipos.php">🖥 Registro de equipos</a></li>
                    <li><a href="gestionLicencias.php">📑 Registro de licencias</a></li>
                    <li><a href="asociar_software.php">🔗 Asociar software</a></li>
                    <li><a href="foro.php">💬 Foro</a></li>
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
                    <h1>💻 Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                    <p class="subtitle">Rol: <strong>ENCARGADO</strong></p>
                </div>

                <div class="quick-actions">
                    <a class="btn btn-primary" href="registrar.php">Crear usuario</a>
                    <a class="btn btn-outline" href="gestionEquipos.php">Nuevo equipo</a>
                </div>
            </header>

            <!-- Resumen rápido -->
            <section class="cards">
                <article class="card">
                    <h3>Equipos registrados</h3>
                    <div class="value" id="count-equipos">--</div>
                </article>

                <article class="card">
                    <h3>Licencias activas</h3>
                    <div class="value" id="count-licencias">--</div>
                </article>

                <article class="card">
                    <h3>Software asociados</h3>
                    <div class="value" id="count-software">--</div>
                </article>
            </section>

            <!-- Contenido principal -->
            <section class="content-panel">
                <h2>Acciones recientes</h2>
                <p class="muted">Revisa y administra los registros desde las opciones del menú.</p>

                <div class="table-wrap">
                    <table class="table" aria-label="Resumen rápido">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="recientes-body">
                            <!-- filas generadas por PHP o AJAX -->
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
   
    fetch('api/resumen_encargado.php').then(r=>r.json()).then(data=>{
    document.getElementById('count-equipos').textContent = data.equipos;
    document.getElementById('count-licencias').textContent = data.licencias;
    document.getElementById('count-software').textContent = data.software;
     });
    </script>
</body>
</html>
