<?php

/* session_start();
 //Si existe la sesión "cliente"..., la guardamos en una variable.
 if (isset($_SESSION['usuarios'])){
	 $cliente = $_SESSION['usuarios'];
	 session_destroy();
 }else{
header('Location: ../index.php');//Aqui lo redireccionas al lugar que quieras.
  die() ;

 } */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferencia de archivos Excel</title>
    <link rel="stylesheet" href="./resources/styles/header.css">
    <link rel="stylesheet" href="./resources/styles/style.css">

</head>
<header>
<!-- aquí comienza nuestro menu -->
	<div class="ancho">
		<div class="logo">
			<a href="./transferencia.php"><img src="./resources/images/logo.png" class="imaa"></a>
		</div>
	</div>
</header>
<body>
<img src="./resources/images/logo.png" class="pimaa">
    <div class="container">
        <div class="row" id="prin">
            <div class="col-12">
                <h1>Transferir archivos</h1>
                <br>
                <input webkitdirectory mozdirectory msdirectory odirectory directory multiple type="file"  id="inputArchivos" accept=".xlsx" >
                <div class="form-group">
                 
                    <br>
                       
                       <br><br>
                    <button id="btnEnviar" class="btn btn-success">Enviar</button>
                </div><br><br>
                <div class="alert alert-info" id="estado" >Se procesaran unicamente archivos excel(".xlsx",".xls")</div>
            </div>
        </div>
    </div>
    <script src="./resources/scripts/recuperaArchivos.js"></script>
</body>
</html>