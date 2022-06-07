<?php 

	include "../conexion.php";
	session_start();

//print_r($_POST);exit;

	if (!empty($_POST)) {

		//extraer datos del producto
		if ($_POST['action'] == 'infoProducto')
		 {
			$producto_id = $_POST['producto'];

			$query_conf = mysqli_query($conection,"SELECT moneda FROM configuracion");
		$result_conf = mysqli_num_rows($query_conf);

				if ($result_conf > 0) {
					$info_conf = mysqli_fetch_assoc($query_conf);
					$moned = $info_conf['moneda'];
				}

			$query = mysqli_query($conection,"SELECT codproducto,codigo,descripcion,existencia,precio FROM producto
												WHERE codigo = $producto_id AND status = 1 ");
			mysqli_close($conection);

			$result = mysqli_num_rows($query);
			if ($result > 0) {
				$data = mysqli_fetch_assoc($query);
				echo json_encode($data,JSON_UNESCAPED_UNICODE);
				exit;
			}
			echo 'error';
			exit;
		}
		//Agregar producto a entrada
		if ($_POST['action'] == 'addProduct')
		 {
		 	if (!empty($_POST['cantidad']) || !empty($_POST['precio']) || !empty($_POST['producto_id']))
		 	{
		 		$cantidad    = $_POST['cantidad'];
		 		$precio      = $_POST['precio'];
		 		$producto_id = $_POST['producto_id'];
		 		$usuario_id  = $_SESSION['idUser'];

		 		$query_insert = mysqli_query($conection,"INSERT INTO entradas(codproducto,
		 																	  cantidad,
		 																	  precio,
		 																	  usuario_id) 
		 														        VALUES($producto_id,
		 														               $cantidad,
		 														               $precio,
		 														               $usuario_id)");
		 		if ($query_insert) {
		 			//Ejecutar procedimiento almacenado
		 			$query_upd = mysqli_query($conection, "CALL actualizar_precio_producto($cantidad,$precio,$producto_id)");
		 			$result_pro = mysqli_num_rows($query_upd);
		 			if ($result_pro > 0) {
		 				$data = mysqli_fetch_assoc($query_upd);
		 				$data['producto_id'] = $producto_id;
		 				echo json_encode($data,JSON_UNESCAPED_UNICODE);
		 				exit;
		 			}
		 		}else{
		 			echo 'error';
		 		}
		 		mysqli_close($conection);

		 	}else{
		 		echo 'error';
		 	}
		 	exit;


		 } 
		
		//Eliminar producto
		if ($_POST['action'] == 'delProduct')
		 {
		 	if (empty($_POST['producto_id']) || !is_numeric($_POST['producto_id'])) {
		 		echo 'error';
		 	}else{
				$idproducto = $_POST['producto_id'];

				//$query_delete = mysqli_query($conection,"DELETE FROM usuario WHERE idusuario = $idusuario ");
				$query_delete = mysqli_query($conection,"UPDATE producto SET status = 0 WHERE codproducto = $idproducto ");
				mysqli_close($conection);
				if($query_delete){
					echo 'ok';
				}else{
					echo 'error';
				}
			}
			echo 'error';
			exit;	
		}
		//Buscar Cliente
		if ($_POST['action'] == 'searchCliente') 
		{
			if (!empty($_POST['cliente'])) {
				$nit = $_POST['cliente'];

				$query = mysqli_query($conection,"SELECT * FROM cliente WHERE nit LIKE '$nit' OR idcliente LIKE '$nit' and status = 1");
				mysqli_close($conection);
				$result = mysqli_num_rows($query);

				$data = '';
				if ($result > 0) {
					$data = mysqli_fetch_assoc($query);
				}else{
					$data = 0;
				}
				echo json_encode($data,JSON_UNESCAPED_UNICODE);
			}
			exit;
		}

		//Registro Cliente - Ventas
		if ($_POST['action'] == 'addCliente')
		 {
			$nit        = $_POST['nit_cliente'];
			$nombre     = $_POST['nom_cliente'];
			$telefono   = $_POST['tel_cliente'];
			$direccion  = $_POST['dir_cliente'];
			$usuario_id = $_SESSION['idUser'];

			$query = mysqli_query($conection,"SELECT * FROM cliente WHERE nit = '$nit' ");
				$result = mysqli_fetch_array($query);
				
				if ($result > 0){
					$msg ='El número de CI ya existe.';
				}else{
					$query_insert = mysqli_query($conection,"INSERT INTO cliente(nit,nombre,telefono,direccion,usuario_id)
																	VALUES('$nit','$nombre','$telefono','$direccion','$usuario_id')");
				
		}
			mysqli_close($conection);
			if ($query_insert) {
				$codCliente = mysqli_insert_id($conection);
				$msg = $codCliente;
			}else{
				$msg='error';
			}
			echo $msg;
			exit;
		}

		//Agregar producto al detalle temporal
		if ($_POST['action'] == 'addProductoDetalle'){
			//print_r($_POST);
			if (empty($_POST['producto']) || empty($_POST['cantidad'])) 
			{
				echo 'error';
			}else{
				$codproducto = $_POST['producto'];
				$cantidad    = $_POST['cantidad'];
				$token       = md5($_SESSION['idUser']);

				$query_iva = mysqli_query($conection,"SELECT iva,moneda FROM configuracion");
				$result_iva = mysqli_num_rows($query_iva);

				$query_detalle_temp = mysqli_query($conection,"CALL add_detalle_temp($codproducto,$cantidad,'$token')");
				$result = mysqli_num_rows($query_detalle_temp);

				$detalleTabla = '';
				$sub_total    = 0;
				$iva          = 0;
				$total        = 0;
				$arrayData    = array();

				if ($result > 0) {
				   if ($result_iva > 0) {
					$info_iva = mysqli_fetch_assoc($query_iva);
					$iva = $info_iva['iva'];
					$moned = $info_iva['moneda'];
				}

				while ($data = mysqli_fetch_assoc($query_detalle_temp)) {
					$precioTotal1 = number_format($data['cantidad'] * $data['precio_venta'], 2);
					$precioTotal  = round($data['cantidad'] * $data['precio_venta'], 2);
					$sub_total    = round($sub_total + $precioTotal,2);
					$total        = round($total + $precioTotal, 2);
					$precio_venta = number_format($data['precio_venta'],2);

					$detalleTabla .= '<tr>
						                <td style="display:none;">'.$data['codproducto'].'</td>
						                 <td class="">'.$data['cantidad'].'</td>
						                <td colspan="3">'.$data['descripcion'].'</td>
						                <td class="">'.$moned.' '.$precio_venta.'</td>
						                <td class="">'.$moned.' '.$precioTotal1.'</td>
						                <td class="">
							                <a class="link_delete" href="#" onclick="event.preventDefault();
							                  del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
						                </td>
						             </tr>';
				}

				$impuesto = round($sub_total * ($iva / 100), 2);
				$tl_sniva = round($sub_total - $impuesto, 2);
				$total    = number_format($tl_sniva + $impuesto, 2);
				$tl_sniva1 = number_format($sub_total - $impuesto, 2);
				$impuesto1 = number_format($sub_total * ($iva / 100), 2);

				$detalleTotales = '<tr>
						               <td colspan="5" class="textright">TOTAL</td>
						               <td class="">'.$moned.' '.$total.'</td>
						               <td></td>
					               </tr>';

				$arrayData['detalle'] = $detalleTabla;
				$arrayData['totales'] = $detalleTotales;

				echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				echo 'error';
			}
			mysqli_close($conection);
		}
		exit;
	   }

	   //Extraer datos del detalle_temp
	   	if ($_POST['action'] == 'serchForDetalle'){
			if (empty($_POST['user'])) 
			{
				echo 'error';
			}else{
				
				$token = md5($_SESSION['idUser']);

				$query = mysqli_query($conection,"SELECT tmp.correlativo,
														tmp.token_user,
														tmp.cantidad,
														tmp.precio_venta,
														p.codproducto,
														p.descripcion
													FROM detalle_temp tmp
													INNER JOIN producto p
													ON tmp.codproducto = p.codproducto
													WHERE token_user = '$token' ");
				$result = mysqli_num_rows($query);

				$query_iva = mysqli_query($conection,"SELECT iva, moneda FROM configuracion");
				$result_iva = mysqli_num_rows($query_iva);

				$detalleTabla = '';
				$sub_total    = 0;
				$iva          = 0;
				$total        = 0;
				$arrayData    = array();

				if ($result > 0) {
				   if ($result_iva > 0) {
					$info_iva = mysqli_fetch_assoc($query_iva);
					$iva = $info_iva['iva'];
					$moned = $info_iva['moneda'];
				}

				while ($data = mysqli_fetch_assoc($query)){
					$precioTotal1 = number_format($data['cantidad'] * $data['precio_venta'], 2);
					$precioTotal  = round($data['cantidad'] * $data['precio_venta'], 2);
					$sub_total    = round($sub_total + $precioTotal,2);
					$total        = round($total + $precioTotal, 2);
					$precio_venta = number_format($data['precio_venta'],2);

					$detalleTabla .= '<tr>
						                <td style="display:none;">'.$data['codproducto'].'</td>
						                <td class="">'.$data['cantidad'].'</td>
						                <td colspan="3">'.$data['descripcion'].'</td>
						                <td class="">'.$moned.' '.$precio_venta.'</td>
						                <td class="">'.$moned.' '.$precioTotal1.'</td>
						                <td class="">
							                <a class="link_delete" href="#" onclick="event.preventDefault();
							                  del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
						                </td>
						             </tr>';
				}

				$impuesto = round($sub_total * ($iva / 100), 2);
				$tl_sniva = round($sub_total - $impuesto, 2);
				$total    = number_format($tl_sniva + $impuesto, 2);
				$tl_sniva1 = number_format($sub_total - $impuesto, 2);
				$impuesto1 = number_format($sub_total * ($iva / 100), 2);

				$detalleTotales = '<tr>
						               <td colspan="5" class="textright">TOTAL</td>
						               <td class="">'.$moned.' '.$total.'</td>
						               <td></td>
					               </tr>';

				$arrayData['detalle'] = $detalleTabla;
				$arrayData['totales'] = $detalleTotales;

				echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				echo 'error';
			}
			mysqli_close($conection);
		}
		exit;
	   }

	   	if ($_POST['action'] == 'delProductoDetalle'){
	   		
	   		if (empty($_POST['id_detalle'])) 
			{
				echo 'error';
			}else{

				$id_detalle = $_POST['id_detalle'];
				$token      = md5($_SESSION['idUser']);

				$query_iva = mysqli_query($conection,"SELECT iva,moneda FROM configuracion");
				$result_iva = mysqli_num_rows($query_iva);

				$query_detalle_temp = mysqli_query($conection,"CALL del_detalle_temp($id_detalle,'$token')");
				$result = mysqli_num_rows($query_detalle_temp);

				$detalleTabla = '';
				$sub_total    = 0;
				$iva          = 0;
				$total        = 0;
				$arrayData    = array();

				if ($result > 0) {
				   if ($result_iva > 0) {
					$info_iva = mysqli_fetch_assoc($query_iva);
					$iva = $info_iva['iva'];
					$moned = $info_iva['moneda'];
				}

				while ($data = mysqli_fetch_assoc($query_detalle_temp)){
					$precioTotal1 = number_format($data['cantidad'] * $data['precio_venta'], 2);
					$precioTotal  = round($data['cantidad'] * $data['precio_venta'], 2);
					$sub_total    = round($sub_total + $precioTotal,2);
					$total        = round($total + $precioTotal, 2);
					$precio_venta = number_format($data['precio_venta'],2);

					$detalleTabla .= '<tr>
						                <td style="display:none;">'.$data['codproducto'].'</td>
						                 <td class="">'.$data['cantidad'].'</td>
						                <td colspan="3">'.$data['descripcion'].'</td>
						                <td class="">'.$moned.' '.$precio_venta.'</td>
						                <td class="">'.$moned.' '.$precioTotal1.'</td>
						                <td class="">
							                <a class="link_delete" href="#" onclick="event.preventDefault();
							                  del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
						                </td>
						             </tr>';
				}

				$impuesto = round($sub_total * ($iva / 100), 2);
				$tl_sniva = round($sub_total - $impuesto, 2);
				$total    = number_format($tl_sniva + $impuesto, 2);
				$tl_sniva1 = number_format($sub_total - $impuesto, 2);
				$impuesto1 = number_format($sub_total * ($iva / 100), 2);

				$detalleTotales = '<tr>
						               <td colspan="5" class="textright">TOTAL</td>
						               <td class="">'.$moned.' '.$total.'</td>
						               <td></td>
					               </tr>';

				$arrayData['detalle'] = $detalleTabla;
				$arrayData['totales'] = $detalleTotales;

				echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				echo 'error';
			}
			mysqli_close($conection);
		}
		exit;
	   	}

	   	//Anular Venta
	   	if ($_POST['action'] == 'anularVenta') {

	   		$token     = md5($_SESSION['idUser']);
	   	
	   		$query_del = mysqli_query($conection,"CALL cancelar_venta('$token')");
	   		mysqli_close($conection);
	   		if ($query_del) {
	   			echo 'ok';
	   		}else{
	   			echo 'error';
	   		}
	   		exit;
	   	}

	   		//Procesar Venta
	   	if ($_POST['action'] == 'procesarVenta'){

	   		if (empty($_POST['codcliente'])) {
	   			$codcliente = 1;
	   		}else{
	   			$codcliente = $_POST['codcliente'];
	   		}

	   		$token   = md5($_SESSION['idUser']);
	   		$usuario = $_SESSION['idUser'];

	   		$query = mysqli_query($conection,"SELECT * FROM detalle_temp WHERE token_user = '$token'");
	   		$result = mysqli_num_rows($query);

	   		if ($result > 0)
	   		 {
			   	$query_procesar = mysqli_query($conection,"CALL procesar_venta($usuario,$codcliente,'$token')");
			   	$result_detalle = mysqli_num_rows($query_procesar);

			   	if ($result_detalle > 0) {
			   		$data = mysqli_fetch_assoc($query_procesar);
			   		echo json_encode($data,JSON_UNESCAPED_UNICODE);
			   	}else{
			   		echo 'error';
			   	}
	   		}else{
	   			echo 'error';
	   		}
	   		mysqli_close($conection);
	   		exit;
	   	}

	   	// Info Factura
	   	if ($_POST['action'] == 'infoFactura') {
	   		if (!empty($_POST['nofactura'])) {

	   			$noventa = $_POST['nofactura'];

	   			$query = mysqli_query($conection,"SELECT * FROM venta WHERE noventa = '$noventa' AND status = 1");
	   			mysqli_close($conection);

	   			$result = mysqli_num_rows($query);
	   			if ($result > 0) {
	   				
	   				$data = mysqli_fetch_assoc($query);
	   				echo json_encode($data,JSON_UNESCAPED_UNICODE);
	   				exit;
	   			}
	   		}
	   		echo "error";
	   		exit;
	   	}

	   	//Anular Factuea
	   	if ($_POST['action'] == 'anularFactura') {

	   		if (!empty($_POST['noFactura'])) 
	   		{
	   			$noFactura = $_POST['noFactura'];

	   			$query_anular = mysqli_query($conection,"CALL anular_venta($noFactura)");
	   			mysqli_close($conection);
	   			$result = mysqli_num_rows($query_anular);
	   			if ($result > 0) {
	   				$data = mysqli_fetch_assoc($query_anular);
	   				echo json_encode($data,JSON_UNESCAPED_UNICODE);
	   				exit;
	   			}
	   		}
	   		echo "error";
	   		exit;
	   	}

	   		//Cambiar contraseña
	   	if ($_POST['action'] == 'changePassword') {


	   		if (!empty($_POST['passActual']) && !empty($_POST['passNuevo'] )) 
	   		{
	   			$password = md5($_POST['passActual']);
	   			$newPass = md5($_POST['passNuevo']);
	   			$idUser = $_SESSION['idUser'];

	   			$code = '';
	   			$msg = '';
	   			$arrData = array();

	   			$query_user = mysqli_query($conection,"SELECT * FROM usuario
	   															WHERE clave = '$password' AND idusuario = $idUser ");
	   			$result = mysqli_num_rows($query_user);
	   			if ($result > 0)
	   			{
	   				$query_update = mysqli_query($conection,"UPDATE usuario SET clave = '$newPass' WHERE idusuario = $idUser");
	   				mysqli_close($conection);

	   				if ($query_update) {
	   					$code = '00';
	   					$msg = "Su contraseña se ha actualizado con éxito.";
	   				}else{
	   					$code = '2';
	   					$msg = "No es posible cambiar su contraseña.";
	   				}
	   			}else{
	   				$code = '1';
	   				$msg = "La contraseña actual es incorrecta.";
	   			}
	   			$arrData = array('cod' => $code, 'msg' => $msg);
	   			echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   			
	   			
	   		}else{
	   			echo "error";
	   		}
	   		
	   		exit;
	   	}

	   	//Actualizar datos empresa
	   	if ($_POST['action'] == 'updateDataEmpresa')  {
	   	if(empty($_POST['txtNit']) || empty($_POST['txtNombre']) || empty($_POST['txtRSocial']) || empty($_POST['txtTelEmpresa']) || empty($_POST['txtEmailEmpresa']) || empty($_POST['txtDirEmpresa'])) 
	   		{
	   			   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
	   		     }else{
	   		     	$intNit 	= $_POST['txtNit'];
	   		     	$strNombre 	= $_POST['txtNombre'];
	   		     	$strRSocial	= $_POST['txtRSocial'];
	   		     	$intTel 	= intval($_POST['txtTelEmpresa']);
	   		     	$strEmail 	= $_POST['txtEmailEmpresa'];
	   		     	$strDir	 	= $_POST['txtDirEmpresa'];
	   		     	$strMoneda 	= $_POST['txtMoneda'];
	   		     	//$strIva 	= $_POST['txtIva'];

	   		     	$foto = $_FILES['foto'];
					$nombre_foto = $foto['name'];
					$type 		 = $foto['type'];
					$url_temp    = $foto['tmp_name'];

					if ($nombre_foto != '') 
					{
						$destino = 'factura/img/';
						$img_nombre = 'logo_'.md5(date('d-m-Y H:m:s'));
						$imgProducto = $img_nombre.'.jpg';
						$src = $destino.$imgProducto;

						$queryUpd = mysqli_query($conection," UPDATE configuracion SET nit = '$intNit',
	   		     																   nombre = '$strNombre',
	   		     																   razon_social = '$strRSocial',
	   		     																   telefono = $intTel,
	   		     																   email = '$strEmail',
	   		     																   direccion = '$strDir',
	   		     																   moneda = '$strMoneda',
	   		     																   foto = '$imgProducto'
	   		     																   WHERE id = 1");
					}else{					

	   		     	$queryUpd = mysqli_query($conection," UPDATE configuracion SET nit = '$intNit',
	   		     																   nombre = '$strNombre',
	   		     																   razon_social = '$strRSocial',
	   		     																   telefono = $intTel,
	   		     																   email = '$strEmail',
	   		     																   direccion = '$strDir',
	   		     																   moneda = '$strMoneda'
	   		     																   WHERE id = 1");
	   		     	}
	   		     	mysqli_close($conection);

	   		     	if ($queryUpd) {
	   		     		if ($nombre_foto != ''){
						move_uploaded_file($url_temp,$src);
						}
	   		     		$code = '00';
	   		     		$msg = "Datos actualizados correctamente";
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al actualizar los datos";
	   		     	}
	   		     }

	   		     $arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//buscar cliente desde ventas
	   		if ($_POST['action'] == 'busquedaCliente'){
			
		

				$query = mysqli_query($conection,"SELECT nit,nombre,telefono,direccion FROM cliente WHERE status = 1");

				if (isset($_POST['consulta'])) {
					$q =mysql_escape_string($conection,$_POST['consulta']);
					$query = mysqli_query($conection, "SELECT nit,nombre,telefono,direccion FROM cliente WHERE nit LIKE '%".$q."%' OR nombre LIKE '%".$q."%' AND status = 1");
				}
				$result = mysqli_num_rows($query);

				$detalleTabla = '';
				$arrayData    = array();

				if ($result > 0) {

				while ($data = mysqli_fetch_assoc($query)){

					$detalleTabla .= '<tr>
						                <td>'.$data['nit'].'</td>
						                <td colspan="">'.$data['nombre'].'</td>
						                <td class="">'.$data['telefono'].'</td>
						                <td class="">'.$data['direccion'].'</td>
						                <td class="textcenter">
							                <a class="link_edit" href="#" onclick="event.preventDefault();
							                  del_product_detalle('.$data['nit'].');"><i class="fas fa-plus"></i></a>
						                </td>
						             </tr>';
				}

				$detalleTabla.='</table>';

				//$arrayData['detalle'] = $detalleTabla;

				//echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				$detalleTabla= "No se encontraron considencias.";
				//echo 'error';
			}
			echo $detalleTabla;
			mysqli_close($conection);
		
		exit;
	   }

	   //Agregar producto al detalle temporal

		if ($_POST['action'] == 'addProductoDetalle2'){
			//print_r($_POST);
			if (empty($_POST['producto']) || empty($_POST['cantidad'])) 
			{
				echo 'error';
			}else{
				$codproducto = $_POST['producto'];
				$cantidad    = $_POST['cantidad'];
				$token       = md5($_SESSION['idUser']);

				$query_iva = mysqli_query($conection,"SELECT iva,moneda FROM configuracion");
				$result_iva = mysqli_num_rows($query_iva);

				$query_detalle_temp = mysqli_query($conection,"CALL add_detalle_temp($codproducto,$cantidad,'$token')");
				$result = mysqli_num_rows($query_detalle_temp);

				$detalleTabla = '';
				$sub_total    = 0;
				$iva          = 0;
				$total        = 0;
				$arrayData    = array();

				if ($result > 0) {
				   if ($result_iva > 0) {
					$info_iva = mysqli_fetch_assoc($query_iva);
					$iva = $info_iva['iva'];
					$moned = $info_iva['moneda'];
				}

				while ($data = mysqli_fetch_assoc($query_detalle_temp)) {
					$precioTotal1 = number_format($data['cantidad'] * $data['precio_venta'], 2);
					$precioTotal  = round($data['cantidad'] * $data['precio_venta'], 2);
					$sub_total    = round($sub_total + $precioTotal,2);
					$total        = round($total + $precioTotal, 2);
					$precio_venta = number_format($data['precio_venta'],2);

					$detalleTabla .= '<tr>
						                <td style="display:none;">'.$data['codproducto'].'</td>
						                 <td class="">'.$data['cantidad'].'</td>
						                <td colspan="3">'.$data['descripcion'].'</td>
						                <td class="">'.$moned.' '.$precio_venta.'</td>
						                <td class="">'.$moned.' '.$precioTotal1.'</td>
						                <td class="">
							                <a class="link_delete" href="#" onclick="event.preventDefault();
							                  del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
						                </td>
						             </tr>';
				}

				$impuesto = round($sub_total * ($iva / 100), 2);
				$tl_sniva = round($sub_total - $impuesto, 2);
				$total    = number_format($tl_sniva + $impuesto, 2);
				$tl_sniva1 = number_format($sub_total - $impuesto, 2);
				$impuesto1 = number_format($sub_total * ($iva / 100), 2);

				$detalleTotales = '<tr>
						               <td colspan="5" class="textright">TOTAL</td>
						               <td class="">'.$moned.' '.$total.'</td>
						               <td></td>
					               </tr>';

				$arrayData['detalle'] = $detalleTabla;
				$arrayData['totales'] = $detalleTotales;

				echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);	               
			}else{
				echo 'error';
			}
			mysqli_close($conection);
		}
		exit;
	   }

	   	// Info Cliente
	   	if ($_POST['action'] == 'editarCliente') {
	   		//print_r($_POST);
	   		if (!empty($_POST['cliente'])) {

	   			$cliente = $_POST['cliente'];

	   			$query = mysqli_query($conection,"SELECT * FROM cliente WHERE idcliente = '$cliente' AND status = 1");
	   			mysqli_close($conection);

	   			$result = mysqli_num_rows($query);
	   			if ($result > 0) {
	   				
	   				$data = mysqli_fetch_assoc($query);
	   				echo json_encode($data,JSON_UNESCAPED_UNICODE);
	   				exit;
	   			}
	   		}
	   		echo "error";
	   		exit;
	   	}

	   	  	//Actualizar datos del cliente
	   	if ($_POST['action'] == 'actualizarCliente')  {
	   		
	   	if(empty($_POST['idCliente']) || empty($_POST['nitCliente']) || empty($_POST['nombreCliente']) || empty($_POST['telefonoCliente']) || empty($_POST['direccionCliente'])) 
	   		{
	   			   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
	   		     }else{
	   		     	$idCliente  = $_POST['idCliente'];
	   		     	$intNit 	= $_POST['nitCliente'];
	   		     	$strNombre 	= $_POST['nombreCliente'];
	   		     	$intTel 	= $_POST['telefonoCliente'];
	   		     	$strDir 	= $_POST['direccionCliente'];

	   		     	$query = mysqli_query($conection,"SELECT * FROM cliente WHERE (nit = '$intNit' AND idcliente != $idCliente) ");
				$result = mysqli_fetch_array($query);
				}
				if ($result > 0){
					$code = '2';
					$msg ='El número de CI ya existe.';
				}else{

	   		     	$queryUpd = mysqli_query($conection," UPDATE cliente SET  nit = '$intNit',
	   		     														nombre = '$strNombre',
	   		     														telefono = $intTel,
	   		     														direccion = '$strDir'
	   		     														WHERE idcliente = $idCliente");
	   		     	mysqli_close($conection);

	   		     	if ($queryUpd) {
	   		     		$code = '00';
	   		     		$msg = "Datos actualizados correctamente";
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al actualizar los datos";
	   		     	}
	   		     }

	   		     $arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//Eliminar Cliete
		 if ($_POST['action'] == 'eliminarCliente')
		 {
		 	if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
		 		echo 'error';
		 	}else{
				$idCliente = $_POST['cliente_id'];

				//$query_delete = mysqli_query($conection,"DELETE FROM cliente WHERE idcliente = $idCliente ");
				$query_delete = mysqli_query($conection,"UPDATE cliente SET status = 0 WHERE idcliente = $idCliente ");
				mysqli_close($conection);
				if($query_delete){
					echo 'ok';
				}else{
					echo 'error';
				}
			}
			echo 'error';
			exit;	
		}

		//Nuevo Cliete
		 if ($_POST['action'] == 'nuevoCliente'){
		 	if (empty($_POST['nitCliente']) || empty($_POST['nombreCliente']) || empty($_POST['telefonoCliente']) || empty($_POST['direccionCliente'])) {
		 		   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
		 	}else{
				$nit 		= $_POST['nitCliente'];
				$nombre 	= $_POST['nombreCliente'];
				$telefono 	= $_POST['telefonoCliente'];
				$direccion 	= $_POST['direccionCliente'];
				$user 		= $_SESSION['idUser'];
				$result 	= 0;

				$query = mysqli_query($conection,"SELECT * FROM cliente WHERE nit = '$nit' ");
				$result = mysqli_fetch_array($query);
				}
				if ($result > 0){
					$code = '2';
					$msg ='El número de CI ya existe.';
				}else{
			
				$query_insert = mysqli_query($conection,"INSERT INTO cliente(nit,nombre,telefono,direccion,usuario_id)
					                                           VALUES('$nit','$nombre',$telefono,'$direccion',$user)");
				
			 	mysqli_close($conection);
				if ($query_insert) {
	   		     		$code = '00';
	   		     		$msg = "Cliente registrado correctamente";

	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al registar el cliente";
	   		   }  	
			}
			$arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;	
		}

		//Info Usuarios
	   	if ($_POST['action'] == 'editarUsuario') {
	   		//print_r($_POST);
	   		if (!empty($_POST['usuario'])) {

	   			$id = $_POST['usuario'];

	   			$query = mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.idrol, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol WHERE u.idusuario = $id AND u.status = 1");

	   			$result = mysqli_num_rows($query);
	   			if ($result > 0) {
	   				$dataUsuario = mysqli_fetch_assoc($query);
	   				}
	   				$query_rol = mysqli_query($conection,"SELECT * FROM rol");
					$result_rol = mysqli_num_rows($query_rol);
					mysqli_close($conection);
					$rol= '';
					while ($data = mysqli_fetch_array($query_rol)){
						$rol.='<option value="'.$data['idrol'].'">'.$data['rol'].'</option>';
					}
	   				$arrData = array('usuario' => $dataUsuario, 'rol' => $rol);
			   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
			   		     //echo json_encode($data,JSON_UNESCAPED_UNICODE);
			   			 exit;
	   			
	   		}
	   		echo "error";
	   		exit;
	   	}

	   	  	//Actualizar datos del Usuario
	   	if ($_POST['action'] == 'actualizarUsuario')  {
	   		
	   	if(empty($_POST['idUsuario']) || empty($_POST['nombreUsuario']) || empty($_POST['correoUsuario']) || empty($_POST['usuario']) || empty($_POST['rolUsuario'])) 
	   		{
	   			   $code 	= '1';
	   			   $msg 	= "Todos los campos son abligatorios.";
	   		     }else{
	   		     	$id     	= $_POST['idUsuario'];
	   		     	$nombre 	= $_POST['nombreUsuario'];
	   		     	$usuario 	= $_POST['usuario'];
	   		     	$email 		= $_POST['correoUsuario'];
	   		     	$clave 		= md5($_POST['claveUsuario']);
	   		     	$rol 		= $_POST['rolUsuario'];

	   		     	$query = mysqli_query($conection,"SELECT * FROM usuario 
	   		     										WHERE (usuario = '$usuario' AND idusuario != $id) OR
	   		     										      (correo = '$email' AND idusuario != $id)");
					$result = mysqli_fetch_array($query);
				}

				if ($result > 0){
						$code = '2';
	   		     		$msg = "El usuario o correo ya existe.";
			}else{

	   		     	$queryUpd = mysqli_query($conection," UPDATE usuario SET nombre  = '$nombre',
	   		     															 correo  = '$email',
	   		     															 usuario = '$usuario',
	   		     															 clave 	 = '$clave',
	   		     															 rol     = '$rol'
	   		     															 WHERE idusuario = $id");
	   		     	mysqli_close($conection);

	   		     	if ($queryUpd) {
	   		     		$code = '00';
	   		     		$msg = "Datos actualizados correctamente";
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al actualizar los datos";
	   		     	}
	   		     }

	   		     $arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//Eliminar Usuario
		 if ($_POST['action'] == 'eliminarUsuario')
		 {
		 	if (empty($_POST['usuario_id']) || !is_numeric($_POST['usuario_id'])) {
		 		echo 'error';
		 	}else{
				$idUsuario = $_POST['usuario_id'];

				//$query_delete = mysqli_query($conection,"DELETE FROM usuario WHERE idusuario = $idUsuario ");
				$query_delete = mysqli_query($conection,"UPDATE usuario SET status = 0 WHERE idusuario = $idUsuario ");
				mysqli_close($conection);
				if($query_delete){
					echo 'ok';
				}else{
					echo 'error';
				}
			}
			echo 'error';
			exit;	
		}

		//Nuevo Usuario
		 if ($_POST['action'] == 'nuevoUsuario'){
		 	if (empty($_POST['nombreUsuario']) || empty($_POST['correoUsuario']) || empty($_POST['usuario']) || empty($_POST['claveUsuario']) || empty($_POST['rolUsuario'])) {
		 		   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
		 	}else{
				$nombre 	= $_POST['nombreUsuario'];
				$email 		= $_POST['correoUsuario'];
				$user 		= $_POST['usuario'];
				$clave 	= md5($_POST['claveUsuario']);
				$rol 	= $_POST['rolUsuario'];
				$result 	= 0;

				$query = mysqli_query($conection,"SELECT * FROM usuario WHERE usuario = '$user' OR correo = '$email' ");
				$result = mysqli_fetch_array($query);
				}

				if ($result > 0){
						$code = '2';
	   		     		$msg = "El usuario o correo ya existe.";
			}else{
				$query_insert = mysqli_query($conection,"INSERT INTO usuario(nombre,correo,usuario,clave,rol)
					                                                  VALUES('$nombre','$email','$user','$clave','$rol')");	
			
				mysqli_close($conection);
				if ($query_insert) {
	   		     		$code = '00';
	   		     		$msg = "Usuario registrado correctamente";
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al registar el usuario";
	   		     	}
			}
			$arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;	
		}

		//Info proveedor
	   	if ($_POST['action'] == 'editarProveedor') {
	   		//print_r($_POST);
	   		if (!empty($_POST['proveedor'])) {

	   			$id = $_POST['proveedor'];

	   			$query = mysqli_query($conection,"SELECT * FROM proveedor WHERE codproveedor = '$id' AND status = 1");
	   			mysqli_close($conection);

	   			$result = mysqli_num_rows($query);
	   			if ($result > 0) {
	   				
	   				$data = mysqli_fetch_assoc($query);
	   				echo json_encode($data,JSON_UNESCAPED_UNICODE);
	   				exit;
	   			}
	   		}
	   		echo "error";
	   		exit;
	   	}

	   	  	//Actualizar datos del proveedor
	   	if ($_POST['action'] == 'actualizarProveedor')  {
	   		
	   	if(empty($_POST['idProveedor']) || empty($_POST['nombreProveedor']) || empty($_POST['nombreContacto']) || empty($_POST['telefonoProveedor']) || empty($_POST['direccionProveedor'])) 
	   		{
	   			   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
	   		     }else{
	   		     	$id     	= $_POST['idProveedor'];
	   		     	$proveedor  = $_POST['nombreProveedor'];
	   		     	$contacto 	= $_POST['nombreContacto'];
	   		     	$telefono 	= $_POST['telefonoProveedor'];
	   		     	$direccion 	= $_POST['direccionProveedor'];

	   		     	$query = mysqli_query($conection,"SELECT * FROM proveedor WHERE (proveedor = '$proveedor' AND codproveedor != $id)");
				$result = mysqli_fetch_array($query);
			}

				if ($result > 0){
						$code = '2';
	   		     		$msg = "El proveedor ya existe";
			}else{

	   		     	$queryUpd = mysqli_query($conection," UPDATE proveedor SET proveedor = '$proveedor',
	   		     																contacto = '$contacto',
	   		     																telefono = $telefono,
	   		     																direccion = '$direccion'
	   		     																WHERE codproveedor = $id");
	   		     	mysqli_close($conection);

	   		     	if ($queryUpd) {
	   		     		$code = '00';
	   		     		$msg = "Datos actualizados correctamente";
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al actualizar los datos";
	   		     	}
	   		     }

	   		     $arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//Eliminar proveedor
		 if ($_POST['action'] == 'eliminarProveedor')
		 {
		 	if (empty($_POST['proveedor_id']) || !is_numeric($_POST['proveedor_id'])) {
		 		echo 'error';
		 	}else{
				$id = $_POST['proveedor_id'];

				//$query_delete = mysqli_query($conection,"DELETE FROM proveedor WHERE codproveedor = $id ");
				$query_delete = mysqli_query($conection,"UPDATE proveedor SET status = 0 WHERE codproveedor = $id ");
				mysqli_close($conection);
				if($query_delete){
					echo 'ok';
				}else{
					echo 'error';
				}
			}
			echo 'error';
			exit;	
		}

		//Nuevo proveedor
		 if ($_POST['action'] == 'nuevoProveedor'){
		 	if (empty($_POST['nombreProveedor']) || empty($_POST['nombreContacto']) || empty($_POST['telefonoProveedor']) || empty($_POST['direccionProveedor'])) {
		 		   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
		 	}else{
				$proveedor 	= $_POST['nombreProveedor'];
				$contacto 		= $_POST['nombreContacto'];
				$telefono 		= $_POST['telefonoProveedor'];
				$direccion 		= $_POST['direccionProveedor'];
				$result 	= 0;

				$query = mysqli_query($conection,"SELECT * FROM proveedor WHERE proveedor = '$proveedor'");
				$result = mysqli_fetch_array($query);
			}

				if ($result > 0){
						$code = '2';
	   		     		$msg = "El proveedor ya existe";
			}else{
				$query_insert = mysqli_query($conection,"INSERT INTO proveedor(proveedor,contacto,telefono,direccion)
					                                                  VALUES('$proveedor','$contacto','$telefono','$direccion')");	
			
				mysqli_close($conection);
				if ($query_insert) {
	   		     		$code = '00';
	   		     		$msg = "Proveedor registrado correctamente";
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al registar el proveedor";
	   		     	}
			}
			$arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;	
		}

		//Info producto
	   	if ($_POST['action'] == 'editarProducto') {
	   		//print_r($_POST);
	   		if (!empty($_POST['producto'])) {

	   			$id = $_POST['producto'];

	   			$query = mysqli_query($conection,"SELECT p.codproducto,p.codigo,p.descripcion,p.precio,p.foto,pr.codproveedor,pr.proveedor 
														FROM producto p
														INNER JOIN proveedor pr 
														ON p.proveedor = pr.codproveedor
														WHERE p.codproducto = $id AND p.status != 10");

	   			$result = mysqli_num_rows($query);
	   			if ($result > 0) {
	   				$data = mysqli_fetch_assoc($query);
					}
					$query_prov= mysqli_query($conection,"SELECT * FROM proveedor");
					$result_prov= mysqli_num_rows($query_prov);
					mysqli_close($conection);
					$proveedor= '';
					while ($data_prov= mysqli_fetch_array($query_prov)) {
						$proveedor.='<option value="'.$data_prov['codproveedor'].'">'.$data_prov['proveedor'].'</option>';
					}
					$arrData = array('proveedor' => $proveedor, 'producto' => $data);
			   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
			   		     //echo json_encode($data,JSON_UNESCAPED_UNICODE);
			   			 exit;
	   		}
	   		echo "error";
	   		exit;
	   	}

	   	  	//Actualizar datos del producto
	   	if ($_POST['action'] == 'actualizarProducto'){
	   		//print_r($_POST);
	   	if(empty($_POST['idProducto']) || empty($_POST['nombreProveedorProd']) || empty($_POST['codigoProducto']) || empty($_POST['nombreProducto']) || empty($_POST['prcioProducto'])) 
	   		{
	   			   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
	   		     }else{
	   		     	$id     	= $_POST['idProducto'];
	   		     	$proveedor  = $_POST['nombreProveedorProd'];
	   		     	$codigo     = $_POST['codigoProducto'];
	   		     	$producto 	= $_POST['nombreProducto'];
	   		     	$precio 	= $_POST['prcioProducto'];
	   		     	
	   		     	$foto = $_FILES['foto'];
					$nombre_foto = $foto['name'];
					$type 		 = $foto['type'];
					$url_temp    = $foto['tmp_name'];

					$imgProducto = 'img_producto.png';

					if ($nombre_foto != '') 
					{
						$destino = 'img/uploads/';
						$img_nombre = 'img_'.md5(date('d-m-Y H:m:s'));
						$imgProducto = $img_nombre.'.jpg';
						$src = $destino.$imgProducto;

						$query_update = mysqli_query($conection," UPDATE producto SET codigo = '$codigo',
	   		     																descripcion = '$producto',
	   		     																proveedor 	= $proveedor,
	   		     																precio 		= $precio,
	   		     																foto 		= '$imgProducto'
	   		     																WHERE codproducto = $id");
					}else{
						$query_update = mysqli_query($conection," UPDATE producto SET codigo = '$codigo',
	   		     																descripcion = '$producto',
	   		     																proveedor 	= $proveedor,
	   		     																precio 		= $precio
	   		     																WHERE codproducto = $id");
					}
					mysqli_close($conection);						

	   		     		if($query_update){
	   		     			if ($nombre_foto != ''){
							move_uploaded_file($url_temp,$src);
						}
	   		     		$code = '00';
	   		     		$msg = "Datos actualizados correctamente";
	   		     		
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al actualizar los datos";
	   		     	}
	   		     }

	   		     $arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//Eliminar producto
		 if ($_POST['action'] == 'eliminarProducto')
		 {
		 	if (empty($_POST['producto_id2']) || !is_numeric($_POST['producto_id2'])) {
		 		echo 'error';
		 	}else{
				$id = $_POST['producto_id2'];

				//$query_delete = mysqli_query($conection,"DELETE FROM producto WHERE codproducto = $id ");
				$query_delete = mysqli_query($conection,"UPDATE producto SET status = 0 WHERE codproducto = $id ");
				mysqli_close($conection);
				if($query_delete){
					echo 'ok';
				}else{
					echo 'error';
				}
			}
			echo 'error';
			exit;	
		}

		//Nuevo producto
		 if ($_POST['action'] == 'nuevoProducto'){
		 	//print_r($_POST);
		 	if (empty($_POST['nombreProv']) || empty($_POST['nombreProd']) || empty($_POST['precioProd']) || $_POST['precioProd'] <= 0 || empty($_POST['cantidadProd'])){
		 		   $code = '1';
	   			   $msg = "Todos los campos son abligatorios.";
		 	}else{
				$proveedor   = $_POST['nombreProv'];
				$codigo 	 = $_POST['codigoProd'];
				$producto 	 = $_POST['nombreProd'];
				$precio  	 = $_POST['precioProd'];
				$cantidad    = $_POST['cantidadProd'];
				$usuario_id  = $_SESSION['idUser'];

				$foto = $_FILES['fotoProd'];
				$nombre_foto = $foto['name'];
				$type 		 = $foto['type'];
				$url_temp    = $foto['tmp_name'];

				$imgProducto = 'img_producto.png';

			if ($nombre_foto != '') 
			{
				$destino = 'img/uploads/';
				$img_nombre = 'img_'.md5(date('d-m-Y H:m:s'));
				$imgProducto = $img_nombre.'.jpg';
				$src = $destino.$imgProducto;
			}
			
			$query = mysqli_query($conection,"SELECT * FROM producto WHERE codigo = '$codigo'");
				$result = mysqli_fetch_array($query);
			}
			if ($result > 0){
					$code = '2';
					$msg='El código del producto ya existe';
			}else{
			
			$query_insert = mysqli_query($conection,"INSERT INTO producto(
				proveedor,codigo,descripcion,precio,existencia,usuario_id,foto)
					                                           VALUES('$proveedor','$codigo','$producto','$precio','$cantidad','$usuario_id','$imgProducto')");
			
			mysqli_close($conection);
			if($query_insert){
				if ($nombre_foto != ''){
					move_uploaded_file($url_temp,$src);	
			}
				
				if ($query_insert) {
	   		     		$code = '00';
	   		     		$msg = "Producto registrado correctamente";
	   		     	}
	   		     	}else{
	   		     		$code = '2';
	   		     		$msg = "Error al registar el producto";
	   		     	}
			}
			$arrData = array('cod' => $code, 'msg' => $msg);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;	
		}

		//Seleccionar proveeedor para registrar producto
		if ($_POST['action'] == 'selecionarProveedor'){
			$query= mysqli_query($conection,"SELECT * FROM proveedor");
			$result= mysqli_num_rows($query);
			$proveedor= '';
			while ($data= mysqli_fetch_array($query)) {
				$proveedor.='<option value="'.$data['codproveedor'].'">'.$data['proveedor'].'</option>';
			}
			$arrData = array('proveedor' => $proveedor);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//Seleccionar rol para registrar usuario
		if ($_POST['action'] == 'selecionarRol'){
			$query= mysqli_query($conection,"SELECT * FROM rol");
			$result= mysqli_num_rows($query);
			$rol= '';
			while ($data= mysqli_fetch_array($query)) {
				$rol.='<option value="'.$data['idrol'].'">'.$data['rol'].'</option>';
			}
			$arrData = array('rol' => $rol);
	   		     echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
	   		     exit;
	   	}

	   	//Activar producto
		 if ($_POST['action'] == 'activarProducto')
		 {
		 	if (empty($_POST['producto_id_2']) || !is_numeric($_POST['producto_id_2'])) {
		 		echo 'error';
		 	}else{
				$id = $_POST['producto_id_2'];

				//$query_delete = mysqli_query($conection,"DELETE FROM producto WHERE codproducto = $id ");
				$query_delete = mysqli_query($conection,"UPDATE producto SET status = 1 WHERE codproducto = $id ");
				mysqli_close($conection);
				if($query_delete){
					echo 'ok';
				}else{
					echo 'error';
				}
			}
			echo 'error';
			exit;	
		}
		   
	  	  
	}
    exit;

?>
