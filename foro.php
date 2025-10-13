<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro de Ayuda - Soporte Técnico</title>
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
            <h1><i class="fas fa-headset"></i> Foro de Ayuda</h1>
            <p>Complete el siguiente formulario y nos pondremos en contacto con usted lo antes posible. Estamos aquí para ayudarle con cualquier consulta o problema que pueda tener.</p>
        </div>
        
        <form id="contactForm" class="contact-form" method="POST" action="enviar.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Nombre <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name" required placeholder="Ingrese su nombre completo">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Correo Electrónico <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="subject">
                    <i class="fas fa-tag"></i> Asunto <span class="required">*</span>
                </label>
                <input type="text" id="subject" name="subject" required placeholder="Breve descripción de su consulta">
            </div>
            
            <div class="form-group full-width">
                <label for="message">
                    <i class="fas fa-comment-dots"></i> Mensaje <span class="required">*</span>
                </label>
                <textarea id="message" name="message" required placeholder="Describa su consulta o problema en detalle..."></textarea>
                <small style="color: var(--gray); font-size: 0.85rem; margin-top: 5px; display: block;">
                    <i class="fas fa-info-circle"></i> Sea lo más específico posible para poder ayudarle mejor
                </small>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="btn-send pulse" id="submitBtn">
                    <i class="fas fa-paper-plane"></i>
                    <span id="btnText">Enviar Mensaje</span>
                </button>
            </div>
            
            <div id="statusMessage" class="status-message"></div>
        </form>
        
        <div class="contact-info">
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <p><strong>Correo Electrónico</strong></p>
                    <p>informatica@demovalle.com</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p><strong>Horario de Atención</strong></p>
                    <div class="schedule">
                        <p>Lunes a jueves: 8:00 - 17:30</p>
                        <p>Viernes: 8:00 - 14:00</p>
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    <p><strong>Teléfono de Contacto</strong></p>
                    <p>+56 9 1234 5678</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const statusMessage = document.getElementById('statusMessage');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // Mostrar estado de carga
                btnText.innerHTML = '<div class="loading"></div> Enviando...';
                submitBtn.disabled = true;
                submitBtn.classList.remove('pulse');
                
                // Mostrar mensaje de carga
                statusMessage.textContent = 'Enviando mensaje...';
                statusMessage.className = 'status-message';
                statusMessage.style.display = 'block';
                
                // Simular envío (reemplaza con tu endpoint real)
                setTimeout(() => {
                    fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes('enviado') || data.includes('success')) {
                            statusMessage.textContent = '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.';
                            statusMessage.className = 'status-message success';
                            form.reset();
                            
                            // Mostrar confeti visual
                            showSuccessAnimation();
                        } else {
                            throw new Error('Error en el servidor');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        statusMessage.textContent = 'Error al enviar el mensaje. Por favor, inténtelo de nuevo o contacte directamente por teléfono.';
                        statusMessage.className = 'status-message error';
                    })
                    .finally(() => {
                        // Restaurar el botón
                        btnText.textContent = 'Enviar Mensaje';
                        submitBtn.disabled = false;
                        submitBtn.classList.add('pulse');
                    });
                }, 1500);
            });

            // Efectos de validación en tiempo real
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.style.borderColor = '#28a745';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            });

            function showSuccessAnimation() {
                // Efecto visual simple de éxito
                submitBtn.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
                setTimeout(() => {
                    submitBtn.style.background = 'linear-gradient(135deg, var(--primary), var(--primary-dark))';
                }, 2000);
            }

            // Efecto de aparición gradual para los elementos
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Aplicar a los elementos del formulario
            const formElements = document.querySelectorAll('.form-group, .btn-container');
            formElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>