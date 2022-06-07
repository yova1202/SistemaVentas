<?php
session_start(); 

include "../conexion.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php"?>
	<title>Lista de productos</title>
</head>
<body>
	<?php include "includes/header.php"?>
	<section id="container">

		<h1><i class="fas fa-cube"></i> Lista de productos</h1>
		<a href="#" class="btn_new btnNewProducto" id="nuevoProducto"><i class="fas fa-plus"></i> Registrar producto</a>

		<form action="#" method="post" class="form_search">
			<input type="text" name="busquedaProducto" id="busquedaProducto" placeholder="Buscar">
		</form>
		<div class="containerTable" id="listaProducto">
			<!--CONTENIDO AJAX-->
		</div>
		<div class="paginador" id="paginadorProducto">
			<!--CONTENIDO AJAX-->
		</div>



	</section>


		<?php include "includes/footer.php"?>

</body>
</html>