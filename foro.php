<?php
session_start();
if (!isset($_SESSION['correo']) || !isset($_SESSION['nombre'])) {
    header("Location: index.php"); // Redirige si no hay sesión
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro de Ayuda</title>
    <link rel="stylesheet" href="css/styleForo.css">
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="back-button-container">
        <a href="menu_informatico.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver al menú
        </a>    
    </div>
    
    <div class="container">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo Institución">
        </div>
        
        <div class="header">
            <h1>Foro de Ayuda</h1>
            <p>Complete el siguiente formulario y nos pondremos en contacto con usted lo antes posible. Estamos aquí para ayudarle con cualquier consulta o problema que pueda tener.</p>
        </div>
        
        <form id="contactForm" class="contact-form" method="POST" action="enviar.php">
           <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre <span class="required">*</span></label>
                    <input type="text" id="name" name="name" 
                        value="<?php echo htmlspecialchars($_SESSION['nombre']); ?>" 
                        readonly>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="email" name="email" 
                        value="<?php echo htmlspecialchars($_SESSION['correo']); ?>" 
                        readonly>
                </div>
            </div>

            
            <div class="form-group full-width">
                <label for="subject">Asunto <span class="required">*</span></label>
                <input type="text" id="subject" name="subject" required placeholder="Breve descripción de su consulta">
            </div>
            
            <div class="form-group full-width">
                <label for="message">Mensaje <span class="required">*</span></label>
                <textarea id="message" name="message" required placeholder="Describa su consulta o problema en detalle..."></textarea>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="btn-send" id="submitBtn">
                    <span id="btnText">Enviar Mensaje</span>
                </button>
            </div>
            
            <div id="statusMessage" class="status-message"></div>
        </form>
        
        <div class="contact-info">
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <p>informatica@demovalle.com</p>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-clock"></i></div>
                <div class="schedule">
                    <p>Lunes a jueves: 8:00 - 17:30</p>
                    <p>Viernes: 8:00 - 14:00</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <p>+56 9 1234 5678</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const statusMessage = document.getElementById('statusMessage');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            
            // Mostrar estado de carga
            btnText.innerHTML = '<div class="loading"></div> Enviando...';
            submitBtn.disabled = true;
            
            // Mostrar mensaje de carga
            statusMessage.textContent = 'Enviando mensaje...';
            statusMessage.className = 'status-message';
            statusMessage.style.display = 'block';
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('enviado')) {
                    statusMessage.textContent = '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.';
                    statusMessage.className = 'status-message success';
                    form.reset();
                } else {
                    statusMessage.textContent = 'Error al enviar el mensaje. Por favor, inténtelo de nuevo.';
                    statusMessage.className = 'status-message error';
                }
            })
            .catch(error => {
                statusMessage.textContent = 'Error de conexión. Por favor, verifique su conexión a Internet e inténtelo de nuevo.';
                statusMessage.className = 'status-message error';
                console.error('Error:', error);
            })
            .finally(() => {
                // Restaurar el botón
                btnText.textContent = 'Enviar Mensaje';
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>