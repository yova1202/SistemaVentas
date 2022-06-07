<?php
session_start();
	if ($_SESSION['rol'] != 1) 
	{
		header("location: ./");
	} 

include "../conexion.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Sisteme Ventas</title>
</head>
<body>
	<?php include "includes/header.php";?>
	<section id="container">

		<h1><i class="fas fa-users"></i> Listade usuarios</h1>
		<a href="#" class="btn_new" id="nuevoUsuario"><i class="fas fa-user-plus"></i> Crear Usuario</a>

		<form action="" method="post" class="form_search">
			<input type="text" name="busquedaUsuario" id="busquedaUsuario" placeholder="Buscar">
		</form>
		<div class="containerTable" id="listaUsuario">
			<!--CONTENIDO AJAX-->
		</div>
		<div class="paginador" id="paginadorUsuario">
			<!--CONTENIDO AJAX-->
		</div>
	</section>


		<?php include "includes/footer.php"?>

</body>
</html>