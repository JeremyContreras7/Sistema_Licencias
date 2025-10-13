<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$id_establecimiento = $_SESSION['id_establecimiento'];
$nombre_establecimiento = "";

// Obtener el nombre del establecimiento del encargado
$stmt = $conexion->prepare("SELECT nombre_establecimiento FROM establecimientos WHERE id_establecimiento = ?");
$stmt->bind_param("i", $id_establecimiento);
$stmt->execute();
$stmt->bind_result($nombre_establecimiento);
$stmt->fetch();
$stmt->close();

// Variable para mensajes
$mensaje = '';

if (isset($_POST['btnregistrar'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $pass = $_POST['pass'];
    $rol = "USUARIO";
    $tipo_encargado = $_POST['tipo_encargado'] ?? null;

    // Validaciones
    if (strlen($pass) < 8) {
        $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> La contraseña debe tener al menos 8 caracteres.</div>';
    } else {
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

        // Verificar si el correo ya existe
        $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> El correo electrónico ya está registrado en el sistema.</div>';
        } else {
            // Insertar nuevo usuario
            $stmt = $conexion->prepare("INSERT INTO usuarios 
                (nombre, correo, pass, rol, id_establecimiento, tipo_encargado) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $nombre, $correo, $pass_hash, $rol, $id_establecimiento, $tipo_encargado);

            if ($stmt->execute()) {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Usuario registrado correctamente. El funcionario puede iniciar sesión con sus credenciales.</div>';
                // Limpiar formulario después de registro exitoso
                echo '<script>document.getElementById("registroForm").reset();</script>';
            } else {
                $mensaje = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Error al registrar el usuario. Por favor, intente nuevamente.</div>';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Funcionario - <?= htmlspecialchars($nombre_establecimiento) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styleregistro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
    <div class="registro-wrapper">
        <section class="registro-card">
            <header class="registro-header">
                <img src="img/logo.png" alt="Logo Municipalidad" class="registro-logo">
                <h1><i class="fas fa-user-plus"></i> Registrar Funcionario</h1>
                <p class="registro-sub">Crea cuentas de funcionarios para tu establecimiento educativo</p>
            </header>

            <!-- Mostrar mensajes -->
            <?php if (!empty($mensaje)): ?>
                <?php echo $mensaje; ?>
            <?php endif; ?>

            <form class="registro-form" method="POST" action="" id="registroForm">
                <div class="grid-2">
                    <div class="field">
                        <label for="nombre">
                            <i class="fas fa-user"></i> Nombre completo
                        </label>
                        <input id="nombre" type="text" name="nombre" placeholder="Ej: Juan Pérez González" required 
                               pattern="[A-Za-záéíóúñÁÉÍÓÚÑ\s]{2,}" 
                               title="Ingrese un nombre válido (mínimo 2 caracteres, solo letras y espacios)">
                    </div>

                    <div class="field">
                        <label for="correo">
                            <i class="fas fa-envelope"></i> Correo institucional
                        </label>
                        <input id="correo" type="email" name="correo" placeholder="usuario@establecimiento.cl" required>
                    </div>
                </div>

                <!-- El rol ya está fijo como USUARIO -->
                <input type="hidden" name="rol" value="USUARIO">

                <div class="grid-2">
                    <div class="field">
                        <label for="id_establecimiento">
                            <i class="fas fa-building"></i> Establecimiento
                        </label>
                        <input type="text" value="<?= htmlspecialchars($nombre_establecimiento) ?>" disabled>
                        <input type="hidden" name="id_establecimiento" value="<?= $id_establecimiento ?>">
                    </div>

                    <div class="field">
                        <label for="tipo_encargado">
                            <i class="fas fa-briefcase"></i> Tipo de funcionario
                        </label>
                        <select id="tipo_encargado" name="tipo_encargado" required>
                            <option disabled selected value="">Seleccionar tipo de funcionario</option>
                            <option value="ACADEMICA">👨‍🏫 Académica</option>
                            <option value="ADMINISTRATIVA">📊 Administrativa</option>
                            <option value="DIRECCION">👨‍💼 Dirección</option>
                            <option value="CONVIVENCIA">🤝 Convivencia Escolar</option>
                            <option value="APOYO">🛠️ Apoyo Técnico</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="pass">
                        <i class="fas fa-lock"></i> Contraseña
                        <span class="tooltip">
                            <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                            <span class="tooltiptext">La contraseña debe tener al menos 8 caracteres, incluyendo letras y números</span>
                        </span>
                    </label>
                    <input id="pass" type="password" name="pass" placeholder="Mínimo 8 caracteres" required 
                           minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$">
                    <div class="password-strength">
                        <span>Seguridad:</span>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span id="strengthText">Débil</span>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" name="btnregistrar" class="btn-primary pulse">
                        <i class="fas fa-user-plus"></i> Registrar Funcionario
                    </button>
                    <a href="menu_informatico.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </form>

            <footer class="registro-footer">
                <small>
                    <i class="fas fa-shield-alt"></i>
                    Al registrar acepta las políticas internas de uso y protección de datos.
                </small>
            </footer>
        </section>
    </div>

    <script>
        // Validación de fortaleza de contraseña en tiempo real
        document.getElementById('pass').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            // Validar longitud
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Validar caracteres diversos
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Actualizar interfaz
            strengthFill.className = 'strength-fill';
            if (password.length === 0) {
                strengthFill.style.width = '0%';
                strengthText.textContent = 'Ingrese contraseña';
            } else if (strength <= 2) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Débil';
            } else if (strength <= 4) {
                strengthFill.classList.add('strength-medium');
                strengthText.textContent = 'Media';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Fuerte';
            }
        });

        // Validación de formulario mejorada
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            const password = document.getElementById('pass').value;
            const tipoFuncionario = document.getElementById('tipo_encargado').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('❌ La contraseña debe tener al menos 8 caracteres.');
                return;
            }
            
            if (!tipoFuncionario) {
                e.preventDefault();
                alert('❌ Por favor, seleccione el tipo de funcionario.');
                return;
            }
            
            // Mostrar loading en el botón
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
                submitBtn.disabled = true;
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
                        alert.parentElement.removeChild(alert);
                    }
                }, 500);
            }, 5000);
        });

        // Efecto de foco en campos
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>