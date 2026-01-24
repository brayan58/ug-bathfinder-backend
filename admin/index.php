<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - UG BathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #2196F3 0%, #1565C0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: #2196F3;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-control:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }
        .btn-login {
            background: #2196F3;
            color: white;
            padding: 12px;
            font-weight: 600;
            border: none;
        }
        .btn-login:hover {
            background: #1976D2;
            color: white;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-lock"></i>
            <h3 class="mb-0">Panel Administrativo</h3>
            <small>UG BathFinder</small>
        </div>
        <div class="login-body">
            <div id="alertContainer"></div>
            
            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" class="form-control" id="email" 
                           placeholder="admin@ug.edu.ec" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-key"></i> Contraseña
                    </label>
                    <input type="password" class="form-control" id="password" 
                           placeholder="••••••" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Recordar sesión
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login w-100" id="loginBtn">
                    <span id="loginText">INICIAR SESIÓN</span>
                    <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Solo administradores autorizados
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = 'http://localhost/ug-bathfinder/api/v1';
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');
            const loginSpinner = document.getElementById('loginSpinner');
            const alertContainer = document.getElementById('alertContainer');
            
            // Validar que sea email de admin
            if (!email.endsWith('@ug.edu.ec')) {
                showAlert('Debe usar un correo institucional @ug.edu.ec', 'danger');
                return;
            }
            
            // Deshabilitar botón y mostrar spinner
            loginBtn.disabled = true;
            loginText.classList.add('d-none');
            loginSpinner.classList.remove('d-none');
            alertContainer.innerHTML = '';
            
            try {
                const response = await fetch(`${API_URL}/auth/login.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success && data.user.rol === 'admin') {
                    // Guardar token y datos de usuario
                    sessionStorage.setItem('admin_token', data.token);
                    sessionStorage.setItem('admin_user', JSON.stringify(data.user));
                    
                    showAlert('¡Inicio de sesión exitoso! Redirigiendo...', 'success');
                    
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else if (data.success && data.user.rol !== 'admin') {
                    showAlert('Acceso denegado. Solo administradores pueden acceder.', 'danger');
                    loginBtn.disabled = false;
                    loginText.classList.remove('d-none');
                    loginSpinner.classList.add('d-none');
                } else {
                    showAlert(data.error || 'Credenciales incorrectas', 'danger');
                    loginBtn.disabled = false;
                    loginText.classList.remove('d-none');
                    loginSpinner.classList.add('d-none');
                }
            } catch (error) {
                showAlert('Error de conexión. Verifique que el servidor esté activo.', 'danger');
                loginBtn.disabled = false;
                loginText.classList.remove('d-none');
                loginSpinner.classList.add('d-none');
            }
        });
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
        
        // Verificar si ya hay sesión activa
        window.addEventListener('DOMContentLoaded', () => {
            const token = sessionStorage.getItem('admin_token');
            if (token) {
                window.location.href = 'dashboard.php';
            }
        });
    </script>
</body>
</html>