<?php
session_start();
$page_title = "Registro";
$no_sidebar = true;

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-body p-4">

                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.jpeg" 
                             alt="Logo" 
                             style="width:70px; border-radius:12px;"
                             onerror="this.style.display='none'">
                        <h4 class="mt-2 fw-bold">Crear Cuenta</h4>
                        <p class="text-muted small">Consultoría CM — Sistema de Vacantes</p>
                    </div>

                    <!-- Mensajes -->
                    <?php if (!empty($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            ❌ <?= htmlspecialchars($_GET['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            ✅ <?= htmlspecialchars($_GET['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="procesar_registro.php" autocomplete="off" id="formRegistro">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nombre" 
                                   class="form-control" 
                                   placeholder="Nombre"
                                   maxlength="100"
                                   required>
                        </div>

                        <!-- Apellido -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="apellido" 
                                   class="form-control" 
                                   placeholder="Apellido"
                                   maxlength="100"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="correo@ejemplo.com"
                                   required>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password"
                                       class="form-control" 
                                       placeholder="Mínimo 6 caracteres"
                                       minlength="6"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password', 'iconPass')">
                                    👁️
                                </button>
                            </div>
                            <!-- Barra de seguridad -->
                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div id="barraSeguridad" 
                                         class="progress-bar" 
                                         style="width: 0%; transition: 0.3s;">
                                    </div>
                                </div>
                                <small id="textoSeguridad" class="text-muted"></small>
                            </div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       name="confirmar_password" 
                                       id="confirmar_password"
                                       class="form-control" 
                                       placeholder="Repite tu contraseña"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('confirmar_password')">
                                    👁️
                                </button>
                            </div>
                            <small id="msgConfirmar" class="text-muted"></small>
                        </div>

                        <!-- Rol OCULTO — siempre empleado desde registro público -->
                        <input type="hidden" name="rol" value="empleado">

                        <!-- Botón -->
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mt-2">
                            🚀 Crear Cuenta
                        </button>

                    </form>

                    <hr>

                    <!-- Link a login -->
                    <div class="text-center">
                        <p class="mb-0 text-muted small">
                            ¿Ya tienes cuenta? 
                            <a href="login.php" class="fw-semibold">Iniciar Sesión</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Mostrar/Ocultar contraseña
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Barra de seguridad contraseña
document.getElementById('password').addEventListener('input', function () {
    const val    = this.value;
    const barra  = document.getElementById('barraSeguridad');
    const texto  = document.getElementById('textoSeguridad');
    let fuerza   = 0;
    let color    = '';
    let label    = '';

    if (val.length >= 6)                        fuerza += 25;
    if (/[A-Z]/.test(val))                      fuerza += 25;
    if (/[0-9]/.test(val))                      fuerza += 25;
    if (/[^A-Za-z0-9]/.test(val))              fuerza += 25;

    if (fuerza <= 25)      { color = 'bg-danger';  label = '🔴 Muy débil';  }
    else if (fuerza <= 50) { color = 'bg-warning'; label = '🟡 Débil';      }
    else if (fuerza <= 75) { color = 'bg-info';    label = '🔵 Moderada';   }
    else                   { color = 'bg-success'; label = '🟢 Fuerte';     }

    barra.className  = 'progress-bar ' + color;
    barra.style.width = fuerza + '%';
    texto.textContent = val.length > 0 ? label : '';
});

// Validar contraseñas coinciden
document.getElementById('confirmar_password').addEventListener('input', function () {
    const pass     = document.getElementById('password').value;
    const msg      = document.getElementById('msgConfirmar');
    if (this.value === pass) {
        msg.textContent = '✅ Las contraseñas coinciden';
        msg.className   = 'text-success small';
    } else {
        msg.textContent = '❌ Las contraseñas no coinciden';
        msg.className   = 'text-danger small';
    }
});

// Validar antes de enviar
document.getElementById('formRegistro').addEventListener('submit', function (e) {
    const pass     = document.getElementById('password').value;
    const confirmar = document.getElementById('confirmar_password').value;
    if (pass !== confirmar) {
        e.preventDefault();
        alert('❌ Las contraseñas no coinciden');
    }
});
</script>

<?php include 'includes/footer.php'; ?>