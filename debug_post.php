<?php
// ARCHIVO DE DEBUG ULTRA DETALLADO
echo "<h2>🔍 Debug Ultra Detallado del POST</h2>";
echo "<hr>";
 
echo "<h3>1. Método de envío:</h3>";
echo "Método: <strong>" . $_SERVER['REQUEST_METHOD'] . "</strong><br>";
echo "<hr>";
 
echo "<h3>2. Datos RAW de POST:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "<hr>";
 
echo "<h3>3. Análisis detallado de cada campo:</h3>";
 
// EMAIL
echo "<div style='padding:10px; background:#e7f3ff; border-left:4px solid #007bff; margin:10px 0;'>";
echo "<strong>Campo EMAIL:</strong><br>";
echo "- Existe en POST: " . (isset($_POST['email']) ? '✓ SÍ' : '✗ NO') . "<br>";
echo "- Valor: " . (isset($_POST['email']) ? "'" . $_POST['email'] . "'" : 'NO EXISTE') . "<br>";
echo "- Tipo: " . (isset($_POST['email']) ? gettype($_POST['email']) : 'N/A') . "<br>";
echo "- Longitud: " . (isset($_POST['email']) ? strlen($_POST['email']) : 0) . " caracteres<br>";
echo "- Está vacío: " . (empty($_POST['email']) ? 'SÍ' : 'NO') . "<br>";
echo "</div>";
 
// PASSWORD
echo "<div style='padding:10px; background:#fff3e0; border-left:4px solid #ff9800; margin:10px 0;'>";
echo "<strong>Campo PASSWORD:</strong><br>";
echo "- Existe en POST: " . (isset($_POST['password']) ? '✓ SÍ' : '✗ NO') . "<br>";
echo "- Valor: " . (isset($_POST['password']) ? "(oculto por seguridad, longitud: " . strlen($_POST['password']) . ")" : 'NO EXISTE') . "<br>";
echo "- Tipo: " . (isset($_POST['password']) ? gettype($_POST['password']) : 'N/A') . "<br>";
echo "- Longitud: " . (isset($_POST['password']) ? strlen($_POST['password']) : 0) . " caracteres<br>";
echo "- Está vacío: " . (empty($_POST['password']) ? '✗ SÍ ← ESTE ES EL PROBLEMA' : '✓ NO') . "<br>";
 
if (isset($_POST['password'])) {
    echo "- Es NULL: " . (is_null($_POST['password']) ? 'SÍ' : 'NO') . "<br>";
    echo "- Valor exacto: '" . $_POST['password'] . "'<br>";
    echo "- En hexadecimal: " . bin2hex($_POST['password']) . "<br>";
}
echo "</div>";
 
echo "<hr>";
 
echo "<h3>4. Todos los datos recibidos vía POST:</h3>";
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
var_dump($_POST);
echo "</pre>";
 
echo "<hr>";
 
echo "<h3>5. Información del servidor:</h3>";
echo "Content-Type: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'No definido') . "<br>";
echo "Content-Length: " . (isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'No definido') . "<br>";
 
echo "<hr>";
 
echo "<h3>6. Input RAW (PHP://input):</h3>";
$raw_post = file_get_contents('php://input');
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
echo htmlspecialchars($raw_post);
echo "</pre>";
 
if ($raw_post) {
    echo "<br><strong>Parseado:</strong><br>";
    parse_str($raw_post, $parsed);
    echo "<pre>";
    print_r($parsed);
    echo "</pre>";
}
 
echo "<hr>";
 
if (empty($_POST)) {
    echo "<div style='color:red; padding:15px; background:#ffebee; border:2px solid #f44336;'>";
    echo "<h3>⚠️ PROBLEMA DETECTADO</h3>";
    echo "<p>No se recibieron datos POST. Posibles causas:</p>";
    echo "<ul>";
    echo "<li>El formulario no tiene method='POST'</li>";
    echo "<li>Los campos no tienen el atributo 'name'</li>";
    echo "<li>Hay un problema con el envío del formulario</li>";
    echo "<li>El servidor no está procesando POST correctamente</li>";
    echo "</ul>";
    echo "</div>";
} elseif (isset($_POST['email']) && !isset($_POST['password'])) {
    echo "<div style='color:orange; padding:15px; background:#fff3e0; border:2px solid #ff9800;'>";
    echo "<h3>⚠️ PROBLEMA IDENTIFICADO</h3>";
    echo "<p>El email se recibe correctamente, pero el campo PASSWORD no existe o no se está enviando.</p>";
    echo "<p><strong>Soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verifica que el input tenga: <code>name='password'</code></li>";
    echo "<li>Limpia la caché del navegador</li>";
    echo "<li>Prueba en modo incógnito</li>";
    echo "<li>Verifica que no haya JavaScript interfiriendo</li>";
    echo "</ul>";
    echo "</div>";
} elseif (isset($_POST['password']) && empty($_POST['password'])) {
    echo "<div style='color:orange; padding:15px; background:#fff3e0; border:2px solid #ff9800;'>";
    echo "<h3>⚠️ PROBLEMA IDENTIFICADO</h3>";
    echo "<p>El campo password existe pero está vacío (string vacío).</p>";
    echo "<p>El usuario no está escribiendo nada en el campo de contraseña, o hay algo borrando el valor.</p>";
    echo "</div>";
}
 
echo "<hr>";
echo "<h3>7. Test del formulario:</h3>";
echo '<a href="login.php" class="btn btn-primary" style="display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">← Volver al Login</a>';
?>