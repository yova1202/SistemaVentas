<?php 

	include "../../conexion.php";
	session_start();

	 //Extraer datos del detalle_temp
		$query_conf = mysqli_query($conection,"SELECT moneda FROM configuracion");
		$result_conf = mysqli_num_rows($query_conf);

				if ($result_conf > 0) {
					$info_conf = mysqli_fetch_assoc($query_conf);
					$moned = $info_conf['moneda'];
				}

	   	$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM producto WHERE status = 1 ");
			$result_register = mysqli_fetch_array($sql_registe);
			$total_registro = $result_register['total_registro'];

			$por_pagina = 5;

			if(empty($_POST['pagina']))
			{
				$pagina = 1;
			}else{
				$pagina = $_POST['pagina'];
			}

			$desde = ($pagina-1) * $por_pagina;
			$total_pagina = ceil($total_registro / $por_pagina);

			$query = mysqli_query($conection,"SELECT * FROM producto
														 WHERE status = 1 ORDER BY descripcion ASC LIMIT $desde,$por_pagina ");
			//Buscador en tiempo real
			if (isset($_POST['busquedaProd'])) {

					$busqueda = mysqli_escape_string($conection,$_POST['busquedaProd']);

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM producto 
																WHERE (
																		codproducto LIKE '%$busqueda%' OR 
																		descripcion LIKE '%$busqueda%') 
																AND status = 1 ");
			$result_register = mysqli_fetch_array($sql_registe);
			$total_registro = $result_register['total_registro'];

			$por_pagina = 5;

			if(empty($_POST['pagina']))
			{
				$pagina = 1;
			}else{
				$pagina = $_POST['pagina'];
			}

			$desde = ($pagina-1) * $por_pagina;
			$total_pagina = ceil($total_registro / $por_pagina);

				$query = mysqli_query($conection,"SELECT * FROM producto WHERE
										(
											codproducto LIKE '%$busqueda%' OR 
											descripcion LIKE '%$busqueda%'
											 ) 
											AND
										status = 1 ORDER BY descripcion ASC LIMIT $desde,$por_pagina ");


				}
				
				$result = mysqli_num_rows($query);
				$lista = '';
				$tabla = '';
				$arrayData    = array();

				$tabla.='<table>
									<tr>
										<th>Código</th>
										<th>Descripción</th>
										<th>Existencia</th>
										<th>Precio</th>
										<th>Foto</th>
										<th>Cantidad</th>
										<th>Acción</th>
									</tr>';

				if ($result > 0) {
				  
				while ($data = mysqli_fetch_assoc($query)){
					if ($data['foto'] != 'img_producto.png') {
							$foto = './img/uploads/'.$data['foto'];
						}else{
							$foto = './img/'.$data['foto'];
						}
						$precio = number_format($data['precio'],2);

					$tabla .= '<tr>
						                <td>'.$data['codigo'].'</td>
						                <td colspan="">'.$data['descripcion'].'</td>
						                <td class=""><input class="textright" type="number" style="width:70px;" id="existencia_2'.$data['codproducto'].'"value="'.$data['existencia'].'" disabled></td>
						                <td class="">'.$moned.' '.$precio.'</td>
						                <td class="img_producto"><img src="'.$foto.'" alt="'.$data['descripcion'].'"></td>
						                <td class=""><input class="textright" step="any" style="width:70px;" type="number" name="txt_cant_producto_2'.$data['codproducto'].'" id="txt_cant_producto_2'.$data['codproducto'].'" value="1" min"1"></td>
						                <td class="">
							                <a class="link_edit" id="add_product_venta_2" href="#" onclick="event.preventDefault(); agregarProducto('.$data['codproducto'].');"><i class="fas fa-plus"></i></a></td></tr>';
				}

				$lista.='<ul>';

				if ($pagina > 1) {
					$lista.= '<li><a href="javascript:serchForDetalleProd();"><i class="fas fa-step-backward"></i></a></li>
				<li><a href="javascript:serchForDetalleProd('.($pagina-1).');"><i class="fas fa-caret-left"></i></a></li>';
				}

				for ($i=1; $i <= $total_pagina; $i++) { 

						if ($i == $pagina) 
						{
							$lista.= '<li class="pageSelected">'.$i.'</a></li>';	
						}else{
							$lista.= '<li><a href="javascript:serchForDetalleProd('.$i.');">'.$i.'</a></li>';
						}
					}

				if ($pagina < $total_pagina) {
					$lista.= '<li><a href="javascript:serchForDetalleProd('.($pagina+1).');"><i class="fas fa-caret-right"></i></a></li>
				<li><a href="javascript:serchForDetalleProd('.($pagina=$total_pagina).');"><i class="fas fa-step-forward"></i></a></li>';
				}
				$lista.='</ul>';

				$arrayData['detalle'] = $tabla;
				$arrayData['totales'] = $lista;

				echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				echo 'error';
			}
			mysqli_close($conection);
		
		exit;

	?>