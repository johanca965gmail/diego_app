<?php
include "db/parametros.php";
function permisos() {  
  if (isset($_SERVER['HTTP_ORIGIN'])){
      header("Access-Control-Allow-Origin: *");
      header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
      header("Access-Control-Allow-Headers: Origin, Authorization, X-Requested-With, Content-Type, Accept");
      header('Access-Control-Allow-Credentials: true');      
  }  
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))          
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: Origin, Authorization, X-Requested-With, Content-Type, Accept");
    exit(0);
  }
}
permisos();
$conexion =  Conectar($db);
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    if (isset($_GET['id'])) {      
      $sql = $conexion->prepare("SELECT t1.*, t2.nombre as tipodocumento, t3.nombre as residencia FROM personas t1 INNER JOIN tipodocumentos t2 ON t1.idtipodocumento = t2.id INNER JOIN ciudades t3 ON t1.lugarresidencia = t3.id where t1.id=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode($sql->fetch(PDO::FETCH_ASSOC));
      exit();
    }
    else{      
      $sql = $conexion->prepare("SELECT t1.*, t2.nombre as tipodocumento, t3.nombre as residencia FROM personas t1 INNER JOIN tipodocumentos t2 ON t1.idtipodocumento = t2.id INNER JOIN ciudades t3 ON t1.lugarresidencia = t3.id ");
      $sql->execute();
      $sql->setFetchMode(PDO::FETCH_ASSOC);
      header("HTTP/1.1 200 OK");
      echo json_encode( $sql->fetchAll());
      exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $input = $_POST;
    // buscamos los datos del documento
    $sql_tipodocumento = $conexion->prepare("SELECT * FROM tipodocumentos where nombre=:nombre");
    $sql_tipodocumento->bindValue(':nombre', $input['idtipodocumento']);
    $sql_tipodocumento->execute();  
    $tipodocumento = $sql_tipodocumento->fetch(PDO::FETCH_ASSOC);
    // buscamos los datos del documento
    $sql_lugarresidencia = $conexion->prepare("SELECT * FROM ciudades where nombre=:nombre");
    $sql_lugarresidencia->bindValue(':nombre', $input['lugarresidencia']);
    $sql_lugarresidencia->execute();  
    $lugarresidencia = $sql_lugarresidencia->fetch(PDO::FETCH_ASSOC);
    // agregamos el tipo de documento
    $input['idtipodocumento'] = $tipodocumento['id'];
    // agregamos el lugar de residencia
    $input['lugarresidencia'] = $lugarresidencia['id'];
    $sql = "INSERT INTO personas (nombres, apellidos, idtipodocumento, documento, lugarresidencia, fechanacimiento, email, telefono, usuario, password) VALUES (:nombres, :apellidos, :idtipodocumento, :documento, :lugarresidencia, :fechanacimiento, :email, :telefono, :usuario, :password)";		  
    $resultado = $conexion->prepare($sql);
    bindAllValues($resultado, $input);
    $resultado->execute();
    $id = $conexion->lastInsertId();
    if($id){
      $input['id'] = $id;
      header("HTTP/1.1 200 OK");
      echo json_encode($input);
      exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
    $input = $_GET;	
    $id = $input['id'];
    $campos = getParams($input);
    $sql = "UPDATE personas SET $campos WHERE id='$id'";
    $resultado = $conexion->prepare($sql);
    bindAllValues($resultado, $input);
    $resultado->execute();
    header("HTTP/1.1 200 OK");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'DELETE'){
  $id = $_GET['id'];
  $resultado = $conexion->prepare("DELETE FROM personas where id=:id");
  $resultado->bindValue(':id', $id);
  $resultado->execute();
  header("HTTP/1.1 200 OK");
  exit();
}
header("HTTP/1.1 400 Peticion HTTP inexistente");
?>