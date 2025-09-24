<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro de Ayuda</title>
    <link rel="stylesheet" href="css/styleForo.css">
    <link rel="icon" href="img/logo.png">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo Institución">
        </div>
        
        <div class="header">
            <h1>Foro de Ayuda</h1>
            <p>Complete el siguiente formulario y nos pondremos en contacto con usted lo antes posible. Estamos aquí para ayudarle con cualquier consulta o problema que pueda tener.</p>
        </div>
        
        <form id="contactForm" class="contact-form" method="POST" action="enviar.php">
            <div class="form-group">
                <label for="name">Nombre <span class="required">*</span></label>
                <input type="text" id="name" name="name" required placeholder="Ingrese su nombre completo">
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico <span class="required">*</span></label>
                <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
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
                <button type="submit" class="btn" id="submitBtn">
                    <span id="btnText">Enviar Mensaje</span>
                </button>
            </div>
            
            <div id="statusMessage" class="status-message"></div>
        </form>
        
        <div class="contact-info">
            <div class="info-item">
                <div class="info-icon">📧</div>
                <p>informatica@demovalle.com</p>
            </div>
            <div class="info-item">
                <div class="info-icon">📞</div>
                <p>+56 9 1234 5678</p>
            </div>
            <div class="info-item">
                <div class="info-icon">⏰</div>
                <p>Lun-Vie: 8:00 - 17:30</p>
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