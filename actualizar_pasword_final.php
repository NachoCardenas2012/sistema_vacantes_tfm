<?php

include "conexion.php";
 
echo "<style>

    body { font-family: Arial, sans-serif; padding: 30px; background: #f5f5f5; }

    .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }

    .success { background: #d4edda; border: 1px solid #28a745; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }

    .info { background: #e7f3ff; border: 1px solid #007bff; color: #004085; padding: 15px; border-radius: 5px; margin: 20px 0; }

    .btn { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }

    .btn:hover { background: #218838; }

    table { width: 100%; border-collapse: collapse; margin-top: 20px; }

    th { background: #007bff; color: white; padding: 12px; text-align: left; }

    td { padding: 10px; border-bottom: 1px solid #ddd; }

    tr:hover { background: #f8f9fa; }

    h2 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
</style>";
 
echo "<div class='container'>";

echo "<h2>🔐 Actualizar Contraseña del Usuario</h2>";
 
// CONFIGURACIÓN - CAMBIA ESTOS VALORES

$email_usuario = "admin@xyz.com";  // Email del usuario a actualizar

$nueva_password = "123456";         // La nueva contraseña que quieres usar
 
echo "<div class='info'>";

echo "<strong>📋 Configuración actual:</strong><br>";

echo "Usuario: <strong>$email_usuario</strong><br>";

echo "Nueva contraseña: <strong>$nueva_password</strong><br>";

echo "</div>";
 
// Generar el hash de la nueva contraseña

$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
 
echo "<h3>Paso 1: Verificar usuario existente</h3>";
 
$check = $conexion->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE email = ?");

$check->bind_param("s", $email_usuario);

$check->execute();

$result = $check->get_result();
 
if ($result->num_rows === 0) {

    echo "<div style='background: #f8d7da; border: 1px solid #dc3545; color: #721c24; padding: 15px; border-radius: 5px;'>";

    echo "❌ <strong>Error:</strong> No existe ningún usuario con el email: <strong>$email_usuario</strong><br>";

    echo "Verifica que el email sea correcto.";

    echo "</div>";

} else {

    $usuario = $result->fetch_assoc();

    echo "<div style='background: #d4edda; border: 1px solid #28a745; padding: 10px; border-radius: 5px; margin: 10px 0;'>";

    echo "✅ Usuario encontrado:<br>";

    echo "- ID: " . $usuario['id'] . "<br>";

    echo "- Nombre: <strong>" . $usuario['nombre'] . "</strong><br>";

    echo "- Rol: " . $usuario['rol'] . "<br>";

    echo "- Hash actual: " . substr($usuario['password'], 0, 30) . "...";

    echo "</div>";

    echo "<h3>Paso 2: Actualizar contraseña</h3>";

    // Actualizar la contraseña

    $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE email = ?");

    $stmt->bind_param("ss", $password_hash, $email_usuario);

    if ($stmt->execute()) {

        echo "<div class='success'>";

        echo "<h3 style='margin-top: 0;'>🎉 ¡Contraseña actualizada exitosamente!</h3>";

        echo "<strong>Nuevas credenciales de acceso:</strong><br><br>";

        echo "📧 <strong>Email:</strong> <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px; font-size: 16px;'>$email_usuario</code><br><br>";

        echo "🔑 <strong>Password:</strong> <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px; font-size: 16px;'>$nueva_password</code>";

        echo "</div>";

        echo "<h3>Paso 3: Verificar que el hash funciona</h3>";

        // Verificar que el nuevo hash funciona correctamente

        if (password_verify($nueva_password, $password_hash)) {

            echo "<div style='background: #d4edda; border: 1px solid #28a745; padding: 10px; border-radius: 5px; margin: 10px 0;'>";

            echo "✅ <strong>Verificación exitosa:</strong> El hash se creó correctamente y la contraseña coincide.";

            echo "</div>";

        } else {

            echo "<div style='background: #f8d7da; border: 1px solid #dc3545; padding: 10px; border-radius: 5px;'>";

            echo "❌ Error en la verificación del hash (esto no debería pasar)";

            echo "</div>";

        }

        echo "<h3>Paso 4: Probar el login</h3>";

        echo "<a href='login.php' class='btn'>🚀 Ir al Login y Probar</a>";

    } else {

        echo "<div style='background: #f8d7da; border: 1px solid #dc3545; color: #721c24; padding: 15px; border-radius: 5px;'>";

        echo "❌ <strong>Error al actualizar:</strong> " . $conexion->error;

        echo "</div>";

    }

}
 
// Mostrar todos los usuarios

echo "<hr>";

echo "<h3>📊 Usuarios en la base de datos:</h3>";

$todos = $conexion->query("SELECT id, nombre, email, rol FROM usuarios ORDER BY id");
 
if ($todos && $todos->num_rows > 0) {

    echo "<table>";

    echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>";

    while ($u = $todos->fetch_assoc()) {

        $highlight = ($u['email'] === $email_usuario) ? "style='background: #fff3cd; font-weight: bold;'" : "";

        echo "<tr $highlight>";

        echo "<td>" . $u['id'] . "</td>";

        echo "<td>" . htmlspecialchars($u['nombre']) . "</td>";

        echo "<td>" . htmlspecialchars($u['email']) . "</td>";

        echo "<td>" . htmlspecialchars($u['rol']) . "</td>";

        echo "</tr>";

    }

    echo "</table>";

} else {

    echo "<p>No hay usuarios en la base de datos.</p>";

}
 
echo "</div>";

?>
 