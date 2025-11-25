<?php
session_start();
if($_SESSION['rol']!=='admin') exit;

$conn = new mysqli("localhost","root","root","sistema_vacantes");
if($conn->connect_error) die(json_encode(['status'=>'error','message'=>'Error de conexión']));

// ==================
// OBTENER USUARIO (EDIT)
if(isset($_GET['get_user'])){
    $id = intval($_GET['get_user']);
    $res = $conn->query("SELECT id,nombre,apellido,email,rol FROM usuarios WHERE id=$id");
    echo json_encode($res->fetch_assoc());
    exit;
}

// ==================
// CREAR USUARIO
if(isset($_POST['nombre']) && isset($_POST['password']) && isset($_POST['rol'])){
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    if(strlen($password)<6) die(json_encode(['status'=>'error','message'=>'La contraseña debe tener al menos 6 caracteres.']));

    // Verificar email
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email=?");
    $check->bind_param("s",$email);
    $check->execute();
    $check->store_result();
    if($check->num_rows>0) die(json_encode(['status'=>'error','message'=>'El email ya está registrado.']));
    $check->close();

    $hashed = password_hash($password,PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios(nombre,apellido,email,password,rol) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssss",$nombre,$apellido,$email,$hashed,$rol);
    if($stmt->execute()){
        $id = $stmt->insert_id;
        $html = "<tr id='usuario-$id'><td>$id</td><td>".htmlspecialchars($nombre.' '.$apellido)."</td><td>".htmlspecialchars($email)."</td><td>$rol</td><td>".date('Y-m-d H:i:s')."</td><td><button class='btn btn-warning btn-sm' onclick='abrirEditarModal($id)'>Editar</button> <button class='btn btn-danger btn-sm' onclick='abrirEliminarModal($id,\"$nombre $apellido\")'>Eliminar</button></td></tr>";
        echo json_encode(['status'=>'success','message'=>'Usuario creado exitosamente.','html'=>$html]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Error al crear usuario.']);
    }
    exit;
}

// ==================
// EDITAR USUARIO
if(isset($_POST['id']) && isset($_POST['nombre'])){
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?,apellido=?,email=?,rol=? WHERE id=?");
    $stmt->bind_param("ssssi",$nombre,$apellido,$email,$rol,$id);
    if($stmt->execute()){
        $html = "<tr id='usuario-$id'><td>$id</td><td>".htmlspecialchars($nombre.' '.$apellido)."</td><td>".htmlspecialchars($email)."</td><td>$rol</td><td>".date('Y-m-d H:i:s')."</td><td><button class='btn btn-warning btn-sm' onclick='abrirEditarModal($id)'>Editar</button> <button class='btn btn-danger btn-sm' onclick='abrirEliminarModal($id,\"$nombre $apellido\")'>Eliminar</button></td></tr>";
        echo json_encode(['status'=>'success','message'=>'Usuario editado correctamente.','html'=>$html,'id'=>$id]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Error al editar usuario.']);
    }
    exit;
}

// ==================
// ELIMINAR USUARIO
if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->bind_param("i",$id);
    if($stmt->execute()){
        echo json_encode(['status'=>'success','message'=>'Usuario eliminado correctamente.','id'=>$id]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Error al eliminar usuario.']);
    }
    exit;
}
