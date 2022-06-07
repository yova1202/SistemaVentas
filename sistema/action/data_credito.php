<?php 

	include "../../conexion.php";
	session_start();
	//print_r($_POST);
	 //Extraer datos del detalle_temp
		$query_conf = mysqli_query($conection,"SELECT moneda FROM configuracion");
		$result_conf = mysqli_num_rows($query_conf);

				if ($result_conf > 0) {
					$info_conf = mysqli_fetch_assoc($query_conf);
					$moned = $info_conf['moneda'];
				}

	   	$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM factura WHERE status = 3 ");
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

				$query = mysqli_query($conection,"SELECT f.nofactura,MAX(f.fecha) as fecha,SUM(f.totalfactura) as saldo,SUM(f.abono) as abono, f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM factura f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE f.status = 3 OR f.status = 4
														 GROUP BY f.codcliente
														 ORDER BY MAX(f.fecha) DESC LIMIT $desde,$por_pagina ");
			//Buscador en tirmpo real
			if (isset($_POST['busqueda'])) {
					$busqueda = mysqli_escape_string($conection,$_POST['busqueda']);

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM factura 
																WHERE (nofactura LIKE '%$busqueda%' OR
																       codcliente LIKE '%$busqueda%' OR
																       usuario LIKE '%$busqueda%' OR
																       fecha LIKE '%$busqueda%') 
																AND status = 3 ");
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

				$query = mysqli_query($conection,"SELECT f.nofactura,f.fecha,SUM(f.totalfactura) as saldo,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM factura f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE (
																f.nofactura LIKE '%$busqueda%' OR
																cl.nombre LIKE '%$busqueda%' OR
																u.nombre LIKE '%$busqueda%' OR
																f.fecha LIKE '%$busqueda%')
														 AND f.status = 3 
														 GROUP BY f.codcliente
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
				}

				//Busqueda por rango de fecha
				/*if (isset($_POST['fecha_de']) && isset($_POST['fecha_a'])){
					//print_r($_POST);
					$fecha_de = mysqli_escape_string($conection,$_POST['fecha_de']);
					$fecha_a = mysqli_escape_string($conection,$_POST['fecha_a']);
					$f_de = $fecha_de.' 00:00:00';
					$f_a = $fecha_a.' 23:59:59';
					//$where = "fecha BETWEEN '$f_de' AND '$f_a'";

					$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM factura 
																WHERE fecha BETWEEN '{$f_de} ' AND '{$f_a}'
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

				$query = mysqli_query($conection,"SELECT f.nofactura,f.fecha,f.totalfactura,f.codcliente,f.status,
															u.nombre as vendedor,
															cl.nombre as cliente
														FROM factura f
														INNER JOIN usuario u 
														ON f.usuario = u.idusuario
														INNER JOIN cliente cl 
														ON f.codcliente = cl.idcliente
														 WHERE f.fecha BETWEEN '{$f_de} ' AND '{$f_a}'
														 AND f.status != 10
														 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
				}*/

				//Fin de la busqueda por rango de fecha
				
				$result = mysqli_num_rows($query);
				$lista = '';
				$detalleTabla = '';
				$arrayData    = array();

				$detalleTabla.='
								<table>
										<tr>
											<th>Fecha / Hora</th>
										    <th>N°.Cedula</th>
											<th>Cliente</th>
											<th>Vendedor</th>
											<th class="">Total Factura</th>
											<th>Estado</th>
											<th class="textcenter">Acciones</th>
										</tr>';

				if ($result > 0) {

					while ($data = mysqli_fetch_array($query)) {
						date_default_timezone_set("America/Managua");
						$fecha0 =  $data['fecha'];
						$fecha1 = date('d-m-Y',strtotime($fecha0.'+ 30 days'));
						$fecha_actual = date('d-m-Y');
						/*$dateDifference = abs(strtotime($fecha1) - strtotime($fecha2));

						$years  = floor($dateDifference / (365 * 60 * 60 * 24));
						$months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
						$days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));*/
						if (strtotime($fecha_actual) > strtotime($fecha1)) {
							$conteo = '<span class="anulada">Vencido</span>';
						}else{
							$conteo = $fecha1;
						}
						//$conteo = $days.' d - '.$months.' m - '.$years.' a';
						
						
						if ($data['status'] == 1) {
							$estatus = '<span class="pagada">Pagada</span>';
						}else if ($data['status'] == 2){
							$estatus = '<span class="anulada">Anulada</span>';
						}else{
							$estatus = '<span class="credito">Crédito</span>';
						}
						$totalfactura= number_format($data["saldo"],2);
						$detalleTabla.='<tr id="row_'.$data['nofactura'].'">
							<td>'.$data["fecha"].'</td>
							<td></td>
							<td>'.$data["cliente"].'</td>
							<td>'.$data["vendedor"].'</td>
							<td class="totalfactura">'.$moned.' '.$totalfactura - $data['abono'].'</td>
							<td>'.$conteo.'</td>
							<td>
								<div class="div_acciones">
									<div>
										<a href class="btn_view view_ticket" title="Pagar" onclick="event.preventDefault(); verTicket('.$data['codcliente'].','.$data['nofactura'].');"><i class="far fa-money-bill-alt"></i></a>
									</div>
									<div>
										<a href class="btn_view view_factura" title="Ver movimientos" onclick="event.preventDefault(); verFactura('.$data['codcliente'].','.$data['nofactura'].');"><i class="fas fa-eye"></i></a>
									</div>';

				}
				$detalleTabla.='</table>';

				$lista.='<ul>';

				if ($pagina > 1) {
					$lista.= '<li><a href="javascript:listaVentas();"><i class="fas fa-step-backward"></i></a></li>
				<li><a href="javascript:listaVentas('.($pagina-1).');"><i class="fas fa-caret-left"></i></a></li>';
				}
				for($i=max(1, min($pagina-3,$total_pagina-7)); $i < max(8, min($pagina+4,$total_pagina+1)); $i++)
				//for ($i=1; $i <= $total_pagina; $i++) 
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