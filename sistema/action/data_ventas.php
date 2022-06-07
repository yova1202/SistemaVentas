<?php 

	include "../../conexion.php";
	session_start();
	//print_r($_POST);
	 //Extraer datos del detalle_temp
		$query_conf = mysqli_query($conection,"SELECT moneda FROM configuracion");
		$result_conf = mysqli_num_rows($query_conf);
		$usuario = $_SESSION['idUser'];


				if ($result_conf > 0) {
					$info_conf = mysqli_fetch_assoc($query_conf);
					$moned = $info_conf['moneda'];
				}
		if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 ){
	   	$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM venta WHERE status != 10 ");
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

				$query = mysqli_query($conection,"SELECT f.noventa,f.fecha,f.totalventa,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM venta f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE f.status != 10 
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina ");
			//Buscador en tirmpo real
			if (isset($_POST['busqueda'])) {
					$busqueda = mysqli_escape_string($conection,$_POST['busqueda']);

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM venta 
																WHERE (noventa LIKE '%$busqueda%' OR
																       codcliente LIKE '%$busqueda%' OR
																       usuario LIKE '%$busqueda%' OR
																       fecha LIKE '%$busqueda%') 
																AND status != 10 ");
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

				$query = mysqli_query($conection,"SELECT f.noventa,f.fecha,f.totalventa,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM venta f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE (
																f.noventa LIKE '%$busqueda%' OR
																cl.nombre LIKE '%$busqueda%' OR
																u.nombre LIKE '%$busqueda%' OR
																f.fecha LIKE '%$busqueda%')
														 AND f.status != 10
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
				}

				//Busqueda por rango de fecha
				if (isset($_POST['fecha_de']) && isset($_POST['fecha_a'])){
					//print_r($_POST);
					if (empty($_POST['busqueda'])) {
						$busqueda = '';
					}else{
						$busqueda = $_POST['busqueda'];
					}

					$fecha_de = mysqli_escape_string($conection,$_POST['fecha_de']);
					$fecha_a = mysqli_escape_string($conection,$_POST['fecha_a']);
					$f_de = $fecha_de.' 00:00:00';
					$f_a = $fecha_a.' 23:59:59';
					//$where = "fecha BETWEEN '$f_de' AND '$f_a'";

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM venta 
																WHERE fecha BETWEEN '{$f_de} ' AND '{$f_a}'
																AND status != 10");
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

				$query = mysqli_query($conection,"SELECT f.noventa,f.fecha,f.totalventa,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM venta f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE f.fecha BETWEEN '{$f_de} ' AND '{$f_a}'
														 AND f.status != 10 AND (u.nombre LIKE '%$busqueda%' OR
														 						cl.nombre like '%$busqueda%')
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
				}
			}else{
//////////////////////////Ventas de vendedor////////////////////////////////				
				$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM venta WHERE status != 10 AND usuario = $usuario AND fecha > CURDATE()");
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

				$query = mysqli_query($conection,"SELECT f.noventa,f.fecha,f.totalventa,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM venta f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE f.status != 10 AND f.usuario = $usuario AND f.fecha > CURDATE()
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina ");
			//Buscador en tirmpo real
			if (isset($_POST['busqueda'])) {
					$busqueda = mysqli_escape_string($conection,$_POST['busqueda']);

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM venta 
																WHERE usuario = $usuario AND status != 10  ");
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

				$query = mysqli_query($conection,"SELECT f.noventa,f.fecha,f.totalventa,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM venta f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE (
																f.noventa LIKE '%$busqueda%' OR
																cl.nombre LIKE '%$busqueda%' OR
																f.fecha LIKE '%$busqueda%')
														 AND f.usuario = $usuario 
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
				}

				//Busqueda por rango de fecha
				if (isset($_POST['fecha_de']) && isset($_POST['fecha_a'])){
					//print_r($_POST);
					$fecha_de = mysqli_escape_string($conection,$_POST['fecha_de']);
					$fecha_a = mysqli_escape_string($conection,$_POST['fecha_a']);
					$f_de = $fecha_de.' 00:00:00';
					$f_a = $fecha_a.' 23:59:59';
					//$where = "fecha BETWEEN '$f_de' AND '$f_a'";

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM venta 
																WHERE fecha BETWEEN '{$f_de} ' AND '{$f_a}'
																AND usuario = $usuario ");
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

				$query = mysqli_query($conection,"SELECT f.noventa,f.fecha,f.totalventa,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM venta f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE f.fecha BETWEEN '{$f_de} ' AND '{$f_a}'
														 AND f.usuario = $usuario
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
				}
			}
				//Fin de la busqueda por rango de fecha
				
				$result = mysqli_num_rows($query);
				$lista = '';
				$detalleTabla = '';
				$arrayData    = array();

				$detalleTabla.='
								<table>
										<tr>
											<th>No.</th>
											<th>Fecha / Hora</th>
											<th>Cliente</th>
											<th>Vendedor</th>
											<th>Estado</th>
											<th class="">Total Venta</th>
											<th class="textcenter">Acciones</th>
										</tr>';

				if ($result > 0) {
					while ($data = mysqli_fetch_array($query)) {
						if ($data['status'] == 1) {
							$estatus = '<span class="pagada">Pagada</span>';
						}else if ($data['status'] == 2){
							$estatus = '<span class="anulada">Anulada</span>';
						}else{
							$estatus = '<span class="credito">Crédito</span>';
						}
						$totalventa= number_format($data["totalventa"],2);
						$detalleTabla.='<tr id="row_'.$data['noventa'].'">
							<td>'.$data["noventa"].'</td>
							<td>'.$data["fecha"].'</td>
							<td>'.$data["cliente"].'</td>
							<td>'.$data["vendedor"].'</td>
							<td class="estado">'.$estatus.'</td>
							<td class="totalventa">'.$moned.' '.$totalventa.'</td>
							<td>
								<div class="div_acciones">
									<div>
										<a href class="btn_view view_ticket" title="Ver ticket" onclick="event.preventDefault(); verTicket('.$data['codcliente'].','.$data['noventa'].');"><i class="fas fa-clipboard-list"></i></a>
									</div>';
								if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
								if ($data["status"] == 1 || $data['status'] == 3)
								 {
									$detalleTabla.= '<div class="div_factura">
										<a href="#" class="btn_anular anular_factura" title="Anular venta" onclick="event.preventDefault();
								                  infoAnularFactura('.$data['noventa'].');"><i class="fas fa-ban"></i></a>
								    </div>';
								 }else{
										$detalleTabla.= '<div class="div_factura">
											<a href="#" class="btn_anular inactive"><i class="fas fa-ban"></i></a>
										</div></td></tr>';
									}
								}
				}
				$detalleTabla.='</table>';

				$lista.='<ul>';

				if ($pagina > 1) {
					$lista.= '<li><a href="javascript:listaVentas();"><i class="fas fa-step-backward"></i></a></li>
				<li><a href="javascript:listaVentas('.($pagina-1).');"><i class="fas fa-caret-left"></i></a></li>';
				}
				//for($i=max(1, min($pagina-3,$total_pagina-7)); $i < max(8, min($pagina+4,$total_pagina+1)); $i++)
				for ($i=1; $i <= $total_pagina; $i++) 
				{ 

						if ($i == $pagina) 
						{
							$lista.= '<li class="pageSelected">'.$i.'</a></li>';	
						}else{
							$lista.= '<li><a href="javascript:listaVentas('.$i.');">'.$i.'</a></li>';
						}
					}

				if ($pagina < $total_pagina) {
					$lista.= '<li><a href="javascript:listaVentas('.($pagina+1).');"><i class="fas fa-caret-right"></i></a></li>
				<li><a href="javascript:listaVentas('.($pagina=$total_pagina).');"><i class="fas fa-step-forward"></i></a></li>';
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