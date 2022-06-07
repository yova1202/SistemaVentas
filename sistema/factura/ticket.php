<?php
	$subtotal 	= 0;
	$iva 	 	= 0;
	$impuesto 	= 0;
	$tl_sniva   = 0;
	$total 		= 0;
 //print_r($configuracion); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Venta</title>
    <link rel="stylesheet" href="styleticket.css">
</head>
<body>
<?php echo $anulada; ?>
<div id="page_pdf">
	<br>
	<br>
	<table id="factura_head">
		<tr>
			<td class="logo_factura">
				<div>
					<img src="img/<?php echo $configuracion['foto']; ?>">
				</div>
			</td>
		</tr>
		<tr>
			<td class="info_empresa">
				<?php
					if($result_config > 0){
						$iva = $configuracion['iva'];
						$moned = $configuracion['moneda'];
				 ?>
				<div>
					<span class="h2"><?php echo strtoupper($configuracion['nombre']); ?></span>
					<p><?php echo $configuracion['razon_social']; ?></p>
					<p><?php echo $configuracion['direccion']; ?></p>
					<p>RUC: <?php echo $configuracion['nit']; ?></p>
					<p>Teléfono: <?php echo $configuracion['telefono']; ?></p>
					<p>Email: <?php echo $configuracion['email']; ?></p>
					<p>-------------------------------------------------------------------------</p>
				</div>
				<?php
					}
				 ?>
			</td>
			</tr>
			<tr>
			<td class="info_factura">
				<div class="round">
					<span class="h3">No. Venta: <strong><?php echo $venta['noventa']; ?></strong></span>
					<p>Fecha: <?php echo $venta['fecha']; ?>             Hora: <?php echo $venta['hora']; ?></p>
					<p>Vendedor: <?php echo $venta['vendedor']; ?></p>
					<p>-------------------------------------------------------------------------</p>
				</div>
			</td>
		</tr>
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Cliente: <?php echo $venta['nombre']; ?></span>
					<p>Cod cliente: <?php echo $venta['nit']; ?> Teléfono: <?php echo $venta['telefono']; ?></p>
					<p>Dirección: <?php echo $venta['direccion']; ?></p>
					<p>-------------------------------------------------------------------------</p>
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_detalle">
			<thead>
				<tr>
					<th width="50px">Cant.</th>
					<th class="textleft">Descripción</th>
					<th class="" width="150px">Precio Unitario.</th>
					<th class="" width="150px"> Precio Total</th>
				</tr>
			</thead>
			<tbody id="detalle_productos">

			<?php

				if($result_detalle > 0){

					while ($row = mysqli_fetch_assoc($query_productos)){
						$precio_venta = number_format($row['precio_venta'],2);
						$precio_total = number_format($row['precio_total'],2);
			 ?>
				<tr>
					<td width=""><?php echo $row['cantidad']; ?></td>
					<td><?php echo $row['descripcion']; ?></td>
					<td width=""><?php echo $moned.' '.$precio_venta; ?></td>
					<td class=""><?php echo $moned.' '.$precio_total; ?></td>
				</tr>
			<?php
						$precio_total = $row['precio_total'];
						$subtotal = round($subtotal + $precio_total, 2);
					}
				}

				$impuesto 	= round($subtotal * ($iva / 100), 2);
				$tl_sniva 	= round($subtotal - $impuesto,2 );
				$total 		= number_format($tl_sniva + $impuesto,2);
				$tl_sniva1 = number_format($subtotal - $impuesto,2);
				$impuesto1 = number_format($subtotal * ($iva / 100), 2);
			?>
			</tbody>
			<tfoot id="detalle_totales">
				<tr>
					<td colspan="2" class="textright"><span>TOTAL </span></td>
					<td></td>
					<td class=""><span> <?php echo $moned.' '.$total; ?></span></td>
				</tr>
		</tfoot>
	</table>
	<p>-------------------------------------------------------------------------</p>
	<div>
		<!--<p class="nota">Si usted tiene preguntas sobre esta factura, <br>pongase en contacto con nombre, teléfono y Email</p>-->
		<h4 class="label_gracias">¡Gracias por su compra!</h4>
	</div>

</div>

</body>
</html>