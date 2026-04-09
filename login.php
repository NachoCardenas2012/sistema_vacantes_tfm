<?php
session_start();
$page_title = "Login";
$no_sidebar = true;

include 'includes/header.php';
?>

<div class="login-wrapper">
    <div class="login-box">

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

        <!-- Título -->
        <h3 class="login-titulo">Iniciar Sesión</h3>

        <!-- Formulario -->
        <form method="POST" action="procesar_login.php" autocomplete="off" id="formLogin">

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       placeholder="correo@ejemplo.com"
                       required
                       autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">
                    Contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           placeholder="Ingresa tu contraseña"
                           required>
                    <button class="btn btn-outline-secondary"
                            type="button"
                            onclick="togglePassword()"
                            id="btnToggle">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" id="btnSubmit">
                Entrar
            </button>

        </form>

        <!-- ✅ Link registro DENTRO del card -->
        <hr class="my-3">
        <div class="auth-footer">
            ¿No tienes cuenta?
            <a href="register.php">Regístrate aquí</a>
        </div>

    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.getElementById('btnToggle');
    input.type      = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
}

document.getElementById('formLogin').addEventListener('submit', function (e) {
    const btn    = document.getElementById('btnSubmit');
    btn.innerHTML = '⏳ Ingresando...';
    btn.disabled  = true;
});
</script>

<?php include 'includes/footer.php'; ?>