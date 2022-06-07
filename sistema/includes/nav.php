<nav>
			<ul>
				<li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
				<?php 
				if ($_SESSION['rol'] == 1) {
				
				?>
				<li class="principal">
					<a href="lista_usuarios.php"><i class="fas fa-users"></i> Usuarios</a>
				</li>
			<?php  } ?>
				<li class="principal">
					<a href="lista_cliente.php"><i class="fas fa-user"></i> Clientes</a>
				</li>
				<?php 
				if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
				
				?>
				<li class="principal">
					<a href="lista_proveedor.php"><i class="far fa-building"></i> Proveedores</a>
				</li>
				<?php  } ?>

				
				<li class="principal">
					<a href="lista_producto.php"><i class="fas fa-cubes"></i> Productos</a>				
				</li>
			

				<li class="principal">
					<a href="#"><i class="far fa-file-alt"></i> Ventas</a>
					<ul>
						<li><a href="ventas.php"><i class="far fa-file-alt"></i> Venta</a></li>
						<li><a href="nueva_venta.php"><i class="fas fa-plus"></i> Nueva Venta</a></li>
					</ul>
				</li>
			</ul>
		</nav>