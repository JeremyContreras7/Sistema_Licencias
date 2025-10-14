    
        function toggleTipoEncargado() {
            const rol = document.getElementById('rol').value;
            const tipoEncargadoSelect = document.getElementById('tipo_encargado');
            
            if (rol === 'USUARIO') {
                tipoEncargadoSelect.disabled = false;
                tipoEncargadoSelect.required = true;
            } else {
                tipoEncargadoSelect.disabled = true;
                tipoEncargadoSelect.required = false;
                tipoEncargadoSelect.value = '';
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('pass');
            const toggleButton = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleButton.className = 'fas fa-eye';
            }
        }

        // Inicializar el estado del campo tipo_encargado
        document.addEventListener('DOMContentLoaded', function() {
            toggleTipoEncargado();
            
            // Validación de contraseña
            const form = document.getElementById('userForm');
            const passwordInput = document.getElementById('pass');
            
            form.addEventListener('submit', function(e) {
                if (passwordInput.value && passwordInput.value.length < 8) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 8 caracteres.');
                    passwordInput.focus();
                }
            });
        });

        // Efectos de interacción
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    