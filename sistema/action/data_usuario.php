<?php 

	include "../../conexion.php";
	session_start();

//print_r($_POST);exit;
	 //Extraer datos del detalle_temp
	   	$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM usuario WHERE status = 1 ");
			$result_register = mysqli_fetch_array($sql_registe);
			$total_registro = $result_register['total_registro'];

			$por_pagina = 10;

			if(empty($_POST['pagina']))
			{
				$pagina = 1;
			}else{
				$pagina = $_POST['pagina'];
			}

			$desde = ($pagina-1) * $por_pagina;
			$total_pagina = ceil($total_registro / $por_pagina);

				$query = mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol WHERE status = 1 ORDER BY u.idusuario DESC LIMIT $desde,$por_pagina");

			//Buscador en tirmpo real
			if (isset($_POST['busqueda'])) {
					$busqueda = mysqli_escape_string($conection,$_POST['busqueda']);

					$rol = '';
			if ($busqueda == 'administrador')
			 {
				$rol = " OR rol LIKE '%1%' ";

			}else if ($busqueda == 'supervisor') {

				$rol = " OR rol LIKE '%2%' ";

			}else if ($busqueda == 'vendedor') {

				$rol = " OR rol LIKE '%3%' ";
			

			}

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM usuario 
																WHERE (idusuario LIKE '%$busqueda%' OR
																		nombre LIKE '%$busqueda%' OR 
																		correo LIKE '%$busqueda%' OR
																		usuario LIKE '%$busqueda%'
																		$rol) 
																AND status = 1 ");
			$result_register = mysqli_fetch_array($sql_registe);
			$total_registro = $result_register['total_registro'];

			$por_pagina = 10;

			if(empty($_POST['pagina']))
			{
				$pagina = 1;
			}else{
				$pagina = $_POST['pagina'];
			}

			$desde = ($pagina-1) * $por_pagina;
			$total_pagina = ceil($total_registro / $por_pagina);

				$query = mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol 
												WHERE 
												(u.idusuario LIKE '%$busqueda%' OR
													u.nombre LIKE '%$busqueda%' OR 
													u.correo LIKE '%$busqueda%' OR
													u.usuario LIKE '%$busqueda%' OR
													r.rol     LIKE  '%$busqueda%' ) 
													AND
												status = 1 ORDER BY u.idusuario DESC LIMIT $desde,$por_pagina");


				}
				
				$result = mysqli_num_rows($query);
				$lista = '';
				$detalleTabla = '';
				$arrayData    = array();

				$detalleTabla.='
								<table>
									<tr>
										<th>ID</th>
										<th>Nombre</th>
										<th>Correo</th>
										<th>usuario</th>
										<th>Rol</th>
										<th>Acciones</th>
									</tr>';

				if ($result > 0) {
				  
				while ($data = mysqli_fetch_assoc($query)){
					
					$detalleTabla .= '<tr>
						                <td>'.$data['idusuario'].'</td>
						                <td colspan="">'.$data['nombre'].'</td>
						                <td class="">'.$data['correo'].'</td>
						                <td class="">'.$data['usuario'].'</td>
						                <td class="">'.$data['rol'].'</td>
						                <td class="">
							                <a class="link_edit" id="editarCliente" href="javascript:editarUsuario('.$data['idusuario'].');"><i class="fas fa-edit"></i> Editar</a>';
							         if($data['idusuario'] != 1){
										$detalleTabla.='	|	
											<a class="link_delete" id="eliminarCliente" href="javascript:infoEliminarUsuario('.$data['idusuario'].');"><i class="fas fa-trash-alt"></i> Eliminar</a>';
										}
						                
						             
				}
				$detalleTabla.='</td></tr></table>';

				$lista.='<ul>';

				if ($pagina > 1) {
					$lista.= '<li><a href="javascript:listaUsuario();"><i class="fas fa-step-backward"></i></a></li>
				<li><a href="javascript:listaUsuario('.($pagina-1).');"><i class="fas fa-caret-left"></i></a></li>';
				}

				for ($i=1; $i <= $total_pagina; $i++) { 

						if ($i == $pagina) 
						{
							$lista.= '<li class="pageSelected">'.$i.'</a></li>';	
						}else{
							$lista.= '<li><a href="javascript:listaUsuario('.$i.');">'.$i.'</a></li>';
						}
					}

				if ($pagina < $total_pagina) {
					$lista.= '<li><a href="javascript:listaUsuario('.($pagina+1).');"><i class="fas fa-caret-right"></i></a></li>
				<li><a href="javascript:listaUsuario('.($pagina=$total_pagina).');"><i class="fas fa-step-forward"></i></a></li>';
				}
				$lista.='</ul>';

				$arrayData['detalle'] = $detalleTabla;
				$arrayData['totales'] = $lista;

				echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				echo 'error';
			}
			mysqli_close($conection);
		
		exit;
	   

			

	?>