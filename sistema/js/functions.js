$(document).ready(function(){

    $('#txt_cod_producto').focus();

    $('.btnMenu').click(function(e){
        e.preventDefault();
        if ($('nav').hasClass('viewMenu')) 
        {
            $('nav').removeClass('viewMenu');
        }else{
            $('nav').addClass('viewMenu');
        }
    });

    $('nav ul li').click(function(){
        $('nav ul li ul').slideUp();
        $(this).children('ul').slideToggle();
    });

    //--------------------- SELECCIONAR FOTO PRODUCTO ---------------------
    $("#foto").on("change",function(){
    	var uploadFoto = document.getElementById("foto").value;
        var foto       = document.getElementById("foto").files;
        var nav = window.URL || window.webkitURL;
        var contactAlert = document.getElementById('form_alert');
        
            if(uploadFoto !='')
            {
                var type = foto[0].type;
                var name = foto[0].name;
                if(type != 'image/jpeg' && type != 'image/jpg' && type != 'image/png')
                {
                    contactAlert.innerHTML = '<p class="errorArchivo">El archivo no es válido.</p>';                        
                    $("#img").remove();
                    $(".delPhoto").addClass('notBlock');
                    $('#foto').val('');
                    return false;
                }else{  
                        contactAlert.innerHTML='';
                        $("#img").remove();
                        $(".delPhoto").removeClass('notBlock');
                        var objeto_url = nav.createObjectURL(this.files[0]);
                        $(".prevPhoto").append("<img id='img' src="+objeto_url+">");
                        $(".upimg label").remove();
                        
                    }
              }else{
              	alert("No selecciono foto");
                $("#img").remove();
              }              
    });

    $('.delPhoto').click(function(){
    	$('#foto').val('');
    	$(".delPhoto").addClass('notBlock');
    	$("#img").remove();

        if ($("#foto_actual") && $("#foto_remove")){
            $("#foto_remove").val('img_producto.png');
        }

    });

    //Modal for add product//
    $('.add_product').click(function(e) {
        /*Act on the event*/
        e.preventDefault();
        var producto = $(this).attr('product');
        var action = 'infoProducto';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,producto:producto},

            success: function(response){            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    //$('#producto_id').val(info.codproducto);
                    //$('.nameProducto').html(info.descripcion);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); sendDataProduct();">'+
                                            '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i> <br> Agregar Producto</h1>'+
                                            '<h2 class="nameProducto">'+info.descripcion+'</h2> <br>'+
                                            '<input type="number" name="cantidad" id="txtCantidad" placeholder="Cantidad del producto" required><br>'+
                                            '<input type="text" name="precio" id="txtPrecio" placeholder="Precio del producto" required>'+
                                            '<input type="hidden" name="producto_id" id="producto_id" value="'+info.codproducto+'" required>'+
                                            '<input type="hidden" name="action" value="addProduct" required>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Agregar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    });

    //Modal for Delete product//
    $('.del_product').click(function(e) {
        /*Act on the event*/
        e.preventDefault();
        var producto = $(this).attr('product');
        var action = 'infoProducto';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,producto:producto},

            success: function(response){            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    //$('#producto_id').val(info.codproducto);
                    //$('.nameProducto').html(info.descripcion);

                    $('.bodyModal').html('<form action="" method="post" name="form_del_product" id="form_del_product" onsubmit="event.preventDefault(); delProduct();">'+
                                            '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i> <br> Eliminar Producto</h1>'+
                                            '<p>¿Está seguro de eliminar el siguiente registro?</p>'+
                                            '<h2 class="nameProducto">'+info.descripcion+'</h2> <br>'+
                                            '<input type="hidden" name="producto_id" id="producto_id" value="'+info.codproducto+'" required>'+
                                            '<input type="hidden" name="action" value="delProduct" required>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_cancel" onclick="coloseModal();"><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_ok"><i class="far fa-trash-alt"></i> Eliminar</button>'+             
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    });

    $('#search_proveedor').change(function(e){
        e.preventDefault();
        var sistema = getUrl();
        location.href = sistema+'buscar_productos.php?proveedor='+$(this).val();
    });

    //Activa compo par registrar cliente
    $('.btn_new_cliente').click(function(e){
        e.preventDefault();
        $('#nom_cliente').removeAttr('disabled');
        $('#tel_cliente').removeAttr('disabled');
        $('#dir_cliente').removeAttr('disabled');

        $('#div_registro_cliente').slideDown();
    });

    //Buscar Cliente
    $('#nit_cliente').keyup(function(e){
        e.preventDefault();

        var cl = $(this).val();
        var action = 'searchCliente';

        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async : true,
            data: {action:action,cliente:cl},

            success: function(response)
            {
                if (response == 0) {
                    $('#idcliente').val('');
                    $('#nom_cliente').val('');
                    $('#tel_cliente').val('');
                    $('#dir_cliente').val('');
                    //Mostrar boton agregar
                    $('.btn_new_cliente').slideDown();
                }else{
                    var data = $.parseJSON(response);
                    $('#idcliente').val(data.idcliente);
                    $('#nom_cliente').val(data.nombre);
                    $('#tel_cliente').val(data.telefono);
                    $('#dir_cliente').val(data.direccion);
                    //Quitar boton agregar
                    $('.btn_new_cliente').slideUp();

                    //Bloque campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');

                    //Ocultar boton guardar
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function(error){
            }
        });
    });

    //Crear cliente - Ventas
    $('#form_new_cliente_venta').submit(function(e){
        e.preventDefault();

        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async : true,
            data: $('#form_new_cliente_venta').serialize(),

            success: function(response)
            {
                if (response != 'error') {
                    //Agregar id al input hidden
                    $('#idcliente').val(response);
                    //Bloque campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');

                    //Ocultar boton gagregar
                    $('.btn_new_cliente').slideUp();
                    //Ocultar boton guardar
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function(error){
            }
        });
    });

    //Buscar Producto
    $('#txt_cod_producto').keyup(function(e){
        e.preventDefault();

        var producto = $(this).val();
        var action = 'infoProducto';
    if (producto != '') 
    {   
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async : true,
            data: {action:action,producto:producto},

            success: function(response)
            {
                
                if (response != 'error')
                 {
                    var info = JSON.parse(response);
                    $('#txt_id_producto').val(info.codproducto);
                    $('#txt_descripcion').html(info.descripcion);
                    $('#txt_existencia').html(info.existencia);
                    $('#txt_cant_producto').val('1');
                    $('#txt_precio').html(info.precio);
                    $('#txt_precio_total').html(info.precio);

                    //Activar cantidad
                    $('#txt_cant_producto').removeAttr('disabled');
                    $('#txt_id_producto').removeAttr('disabled');

                    //Mostrar boton agregar
                    $('#add_product_venta').slideDown();
                 }else{
                    $('#txt_descripcion').html('-');
                    $('#txt_existencia').html('-');
                    $('#txt_cant_producto').val('0');
                    $('#txt_precio').html('0.00');
                    $('#txt_precio_total').html('0.00');

                    //Bloquear cantidad
                    $('#txt_cant_producto').attr('disabled','disabled');

                    //Ocultar boton agregar
                    $('#add_product_venta').slideUp();

                 }
            },
            error: function(error){
            }
        });
     }   
    });

    //Validar cantidad del producto antes de agregar
    $('#txt_cant_producto').keyup(function(e){
        e.preventDefault();
        var precio_total = $(this).val() * $('#txt_precio').html();
        var existencia = parseInt($('#txt_existencia').html());
        $('#txt_precio_total').html(precio_total);

        //Ocultar el boton agregar si la cantidad es menor a 1
        if (($(this).val() < 1 || isNaN($(this).val())) || ($(this).val() > existencia)){
            $('#add_product_venta').slideUp();
        }else{
            $('#add_product_venta').slideDown();
        }
    });

    //Cambiar Password
    $('.newPass').keyup(function(){
        validPass();
    });

    //Form cambiar contraseña
    $('#frmChangePass').submit(function(e){
        e.preventDefault();

        var passActual = $('#txtPassUser').val();
        var passNuevo = $('#txtNewPassUser').val();
        var confirmPassNuevo = $('#txtPassConfirm').val();
        var action = "changePassword";

        if (passNuevo != confirmPassNuevo) {
        $('.alertChangePass').html('<p style="color:red;">Las contraseñas no son iguales.</p>');
        $('.alertChangePass').slideDown();
        return false;
    }

    if (passNuevo.length < 4) {
        $('.alertChangePass').html('<p style="color:red;">La contraseña debe ser de 4 caracteres como mínimo.</p>');
        $('.alertChangePass').slideDown();
        return false;
    }
    $.ajax({
                url : 'ajax.php',
                type: "POST",
                async : true,
                data: {action:action,passActual:passActual,passNuevo:passNuevo},

                success: function(response)
                {

                    if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        if (info.cod == '00') {
                            $('.alertChangePass').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#frmChangePass')[0].reset();
                        }else{
                             $('.alertChangePass').html('<p style="color:red;">'+info.msg+'</p>');
                        }
                          $('.alertChangePass').slideDown();
                    }
                },
                error: function(error){
                }
            });

    });

    //Actualizar datos de la empresa
    $('#frmEmpresa').submit(function(e){
        e.preventDefault();

        var intNit          = $('#txtNit').val();
        var strNombreEmp    = $('#txtNombre').val();
        var strRsocialEmp   = $('#txtRSocial').val();
        var intTelEmp       = $('#txtTelEmpresa').val();
        var strEmailEmp     = $('#txtEmailEmpresa').val();
        var strDirEmp       = $('#txtDirEmpresa').val();
        var intMoneda       = $('#txtMoneda').val();
        var intIva          = $('#txtIva').val();
        var parametros = new FormData($('#frmEmpresa')[0]);

        if (intNit == '' || strNombreEmp == '' || intTelEmp == '' || strEmailEmp == '' || strDirEmp == '') {
            $('.alertFormEmpresa').html('<p style="color:red;">Todos los campos son obligatorio.</p>');
            $('.alertFormEmpresa').slideDown();
            return false;
        }

        $.ajax({
                url : 'ajax.php',
                type: "POST",
                async : true,
                data: parametros,
                contentType: false,
                processData: false,


                beforeSend: function(){
                    $('.alertFormEmpresa').slideUp();
                    $('.alertFormEmpresa').html('');
                    $('#frmEmpresa input').attr('disabled', 'disabled');

                },

                success: function(response)
                {

                        console.log(response);
                        var info = JSON.parse(response);
                        if (info.cod == '00') {
                            $('.alertFormEmpresa').html('<p style="color: #23922d;">'+info.msg+'</p>');                          
                            $('.alertFormEmpresa').slideDown();
                        }else{
                            $('.alertFormEmpresa').html('<p style="color:red;">'+info.msg+'</p>');
                        }
                        $('.alertFormEmpresa').slideDown();
                        $('#frmEmpresa input').removeAttr('disabled');
                   
                },
                error: function(error){
                }
            });
    });

    //Agregar cliente desde ventas
    $('.buscarCliente').click(function(e){
        e.preventDefault();
        serchForDetalleCli(); 
        $('.modalBuscarCl').fadeIn();


    });
   
    $('#busquedaCli').focus();
    $('#busquedaCli').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            serchForDetalleCli(1,valorBusqueda);
        }else{
            serchForDetalleCli(1,'');
        }
        
        
    });

    //Agregar producto desde ventas
        $('.buscarProd').click(function(e){
        e.preventDefault();
        serchForDetalleProd(); 
        $('.modalBuscarPr').fadeIn();


    });
   
    $('#busquedaProd').focus();
    $('#busquedaProd').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            serchForDetalleProd(1,valorBusqueda);
        }else{
            serchForDetalleProd(1,'');
        }
                
    });

    //Lista cliente
    listaCliente();

    $('#busquedaCliente').focus();
    $('#busquedaCliente').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            listaCliente(1,valorBusqueda);
        }else{
            listaCliente(1,'');
        }
        
        
    });

    $('#nuevoCliente').click(function(e){
        e.preventDefault();

        $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); nuevoCliente();">'+                                           
                                            '<h1><i class="fas fa-user-plus" style="font-size: 45pt;"></i> <br> Resgistrar cliente</h1>'+
                                            '<input type="hidden" name="action" value="nuevoCliente" required><br>'+
                                            '<input type="text" name="nitCliente" id="nitCliente" value="" placeholder="Nit" required><br>'+
                                            '<input type="text" name="nombreCliente" id="nombreCliente" value="" placeholder="Nombre y apellidos" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="number" name="telefonoCliente" id="telefonoCliente" value="" placeholder="Teléfono" required><br>'+
                                            '<input type="text" name="direccionCliente" id="direccionCliente" value="" placeholder="Dirección" required><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
        $('.modal').fadeIn();

    })

    //Lista Usuarios
    listaUsuario();

    $('#busquedaUsuario').focus();
    $('#busquedaUsuario').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            listaUsuario(1,valorBusqueda);
        }else{
            listaUsuario(1,'');
        }
        
        
    });

    $('#nuevoUsuario').click(function(e){
        e.preventDefault();
        var action= 'selecionarRol';
       $.ajax({
                url : 'ajax.php',
                type : "POST",
                data : {action:action},

                success: function(response)
                {
                        //console.log(response);
                        var info = JSON.parse(response);

        $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); nuevoUsuario();">'+                                           
                                            '<h1><i class="fas fa-user-plus" style="font-size: 45pt;"></i> <br> Resgistrar usuario</h1>'+
                                            '<input type="hidden" name="action" value="nuevoUsuario" required><br>'+
                                            '<input type="text" name="nombreUsuario" id="nombreUsuario" value="" placeholder="Nombre completo" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="email" name="correoUsuario" id="correoUsuario" value="" placeholder="Correo" required><br>'+
                                            '<input type="text" name="usuario" id="usuario" value="" placeholder="Usuarios" required><br>'+
                                            '<input type="password" name="claveUsuario" id="claveUsuario" value="" placeholder="Clave" required><br>'+
                                            '<select name="rolUsuario" id="rolUsuario" required>'+info.rol+'</select><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
                },
                error: function(error){
                console.log(error);
                }
            });
        $('.modal').fadeIn();

    });

    //Lista proveedor
    listaProveedor();

     $('#busquedaProveedor').focus();
    $('#busquedaProveedor').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            listaProveedor(1,valorBusqueda);
        }else{
            listaProveedor(1,'');
        }
        
        
    });

    $('#nuevoProveedor').click(function(e){
        e.preventDefault();

        $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); nuevoProveedor();">'+                                           
                                            '<h1><i class="far fa-building " style="font-size: 45pt;"></i> <br> Resgistrar proveedor</h1>'+
                                            '<input type="hidden" name="action" value="nuevoProveedor" required><br>'+
                                            '<input type="text" name="nombreProveedor" id="nombreProveedor" value="" placeholder="Nombre del proveedor" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="text" name="nombreContacto" id="nombreContacto" value="" placeholder="Nombre del contacto" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="number" name="telefonoProveedor" id="telefonoProveedor" value="" placeholder="Teléfono" required><br>'+
                                            '<input type="text" name="direccionProveedor" id="direccionProveedor" value="" placeholder="Dirección" required><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
        $('.modal').fadeIn();

    })

    //Lista productos
    listaProductos();

    $('#busquedaProducto').focus();
    $('#busquedaProducto').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            listaProductos(1,valorBusqueda);
        }else{
            listaProductos(1,'');
        }
        
        
    });

    $('#nuevoProducto').click(function(e){
        e.preventDefault();
        var action= 'selecionarProveedor';
       $.ajax({
                url : 'ajax.php',
                type : "POST",
                data : {action:action},

                success: function(response)
                {
                        //console.log(response);
                        var info = JSON.parse(response);

                        $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); nuevoProducto();">'+                                           
                                            '<h1><i class="fa fa-cube " style="font-size: 45pt;"></i> <br> Resgistrar producto</h1>'+
                                            '<input type="hidden" name="action" value="nuevoProducto" required><br>'+
                                            '<select name="nombreProv" id="nombreProv" class="notItemOne">'+info.proveedor+'</select><br>'+
                                            '<input type="text" name="codigoProd" id="codigoProd" value="" placeholder="Código del producto" required><br>'+
                                            '<input type="text" name="nombreProd" id="nombreProd" value="" placeholder="Nombre del producto" required><br>'+
                                            '<input type="number" name="precioProd" id="precioProd" value="" placeholder="Precio del producto" step="any" required><br>'+
                                            '<input type="number" name="cantidadProd" id="cantidadProd" value="" placeholder="Cantidad de producto" required><br>'+
                                            '<input type="file" name="fotoProd" id="fotoProd" value="" placeholder="Foto del producto" ><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
                        
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();

    });

    //Lista ventas
    listaVentas();

    $('#busquedaVentas').focus();
    $('#busquedaVentas').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            listaVentas(1,valorBusqueda);
        }else{
            listaVentas(1,'');
        }       
    });

     //Lista ventas de credito
    /*listaCreditos();

    $('#busquedaCredito').focus();
    $('#busquedaCredito').keyup(function(e){
        e.preventDefault();

        var valorBusqueda = $(this).val();
        //console.log(valorBusqueda);

        if (valorBusqueda != "") 
        {
            listaCreditos(1,valorBusqueda);
        }else{
            listaCreditos(1,'');
        }       
    });*/

//Busqueda por rango de fecha
    $('.btn_rango_fecha').click(function(e){
        e.preventDefault();
        var desde = $('#fecha_de').val();
        var hasta = $('#fecha_a').val();
        var busqueda = $('#busquedaVentas').val();
        if (desde == '' || hasta == '') {
            return false;
        }

        $.ajax({
            url: 'action/data_ventas.php',
            type: "POST",
            async: true,
            data: {fecha_de:desde,fecha_a:hasta,busqueda:busqueda},

            success: function(response){
                    //console.log(response);
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#listaVentas').html(info.detalle);
                        $('#paginadorVentas').html(info.totales);

                     }else{
                        $('#listaVentas').html('<table>'+
                                                    '<tr>'+
                                                        '<th>No.</th>'+
                                                        '<th>Fecha</th>'+
                                                        '<th>Cliente</th>'+
                                                        '<th>Vendedor</th>'+
                                                        '<th>Estado</th>'+
                                                        '<th class="textright">Total Factura</th>'+
                                                        '<th class="textright">Acciones</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorVentas').html('');
                        //console.log('no data');

                     }
            },
            error: function(error){
                console.log(error);
            }
        });
    });
    $('#reporte_pdf').click(function(e){
        e.preventDefault();
        var rows = $('#listaVentas tr').length;
        if (rows > 0) 
        {
            var pagina = '';
            var busqueda = $('#busquedaVentas').val();
            var fecha_de = $('#fecha_de').val();
            var fecha_a = $('#fecha_a').val();

            if (fecha_de != '' || fecha_a != '') {
                generarReportePDF_rango(fecha_de,fecha_a,busqueda);
            }else{
                generarReportePDF(pagina,busqueda);
            }
 
            location.reload();
        }
    });

    /*$('#reporte_pdf_rango').click(function(e){
        e.preventDefault();
        var rows = $('#listaVentas tr').length;
        if (rows > 0) 
        {

            var fecha_de = $('#fecha_de').val();
            var fecha_a = $('#fecha_a').val();

 
            generarReportePDF_rango(fecha_de,fecha_a,busqueda);
            
            location.reload();
        }
    });*/


}); //End ready

function generarReportePDF(pagina,busqueda){
    var ancho = 1000;
    var alto = 800;
    //Calcularposicion x,y para centrar la ventana
    var x = parseInt((window.screen.width/2) - (ancho / 2));
    var y = parseInt((window.screen.height/2) - (alto / 2));

    $url = 'factura/generaReporte.php?pagina='+pagina+'&busqueda='+busqueda;
    window.open($url,"Factura","left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
}

function generarReportePDF_rango(desde,hasta,busqueda){
    var ancho = 1000;
    var alto = 800;
    //Calcularposicion x,y para centrar la ventana
    var x = parseInt((window.screen.width/2) - (ancho / 2));
    var y = parseInt((window.screen.height/2) - (alto / 2));

    $url = 'factura/generaReporteRango.php?fecha_de='+desde+'&fecha_a='+hasta+'&busqueda='+busqueda;
    window.open($url,"Factura","left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
}


function validPass(){
    var passNuevo = $('#txtNewPassUser').val();
    var confirmPassNuevo = $('#txtPassConfirm').val();
    if (passNuevo != confirmPassNuevo) {
        $('.alertChangePass').html('<p style="color:red;">Las contraseñas no son iguales.</p>');
        $('.alertChangePass').slideDown();
        return false;
    }

    if (passNuevo.length < 4) {
        $('.alertChangePass').html('<p style="color:red;">La contraseña debe ser de 4 caracteres como mínimo.</p>');
        $('.alertChangePass').slideDown();
        return false;
    }
     $('.alertChangePass').html('');
     $('.alertChangePass').slideDown();
}

//Anular factura
function anularFactura(){
    var noFactura = $('#no_factura').val();
    var action = 'anularFactura';

    $.ajax({
            url : 'ajax.php',
            type: "POST",
            async : true,
            data: {action:action,noFactura:noFactura},

            success: function(response)
            {
               if (response == 'error') {
                    $('.alertAddProduct').html('<p style="color:red;">Error al anular la venta.</p>');
               }else{
                    $('#row_'+noFactura+' .estado').html('<span class="anulada">Anulada</span>');
                    $('#form_anular_factura .btn_ok').remove();
                    $('#row_'+noFactura+' .div_factura').html('<button type="button" class="btn_anular inactive" ><i class="fas fa-ban"></i></button>');
                    $('.alertAddProduct').html('<p>Venta anulada.</p>');
               }
            },
            error: function(error){

            }
    });
}

function generarPDF(cliente,factura){
    var ancho = 1000;
    var alto = 800;
    //Calcularposicion x,y para centrar la ventana
    var x = parseInt((window.screen.width/2) - (ancho / 2));
    var y = parseInt((window.screen.height/2) - (alto / 2));

    $url = 'factura/generaFactura.php?cl='+cliente+'&f='+factura;
    window.open($url,"Factura","left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
}

function generarPDFTicket(cliente,factura){
    var ancho = 1000;
    var alto = 800;
    //Calcularposicion x,y para centrar la ventana
    var x = parseInt((window.screen.width/2) - (ancho / 2));
    var y = parseInt((window.screen.height/2) - (alto / 2));

    $url = 'factura/generaTicket.php?cl='+cliente+'&f='+factura;
    window.open($url,"Factura","left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
}

function del_product_detalle(correlativo){
     var action = 'delProductoDetalle';
     var id_detalle = correlativo;

       $.ajax({
                url : 'ajax.php',
                type : "POST",
                async : true,
                data : {action:action,id_detalle:id_detalle},

                success: function(response)
                {
                    if (response != 'error') 
                    {
                        var info = JSON.parse(response);

                        $('#detalle_venta').html(info.detalle);
                        $('#detalle_totales').html(info.totales);

                        $('#txt_cod_producto').val('');
                        $('#txt_descripcion').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').html('0.00');
                        $('#txt_precio_total').html('0.00');

                        //Bloquear cantidad
                        $('#txt_cant_producto').attr('disabled','disabled');

                        //Ocultar boton agregar
                        $('#add_product_venta').slideUp();

                    }else{
                        $('#detalle_venta').html('');
                         $('#detalle_totales').html('');
                    }
                    viewProcesar();
                   
                },
                error: function(error){

                }
            });

}

//Mostrar/Ocultar boton procesar
function viewProcesar(){
    if ($('#detalle_venta tr').length > 0) 
    {
        $('#btn_facturar_venta').show();
    }else{
        $('#btn_facturar_venta').hide();
    }
}

function serchForDetalle(id){
    var action = 'serchForDetalle';
    var user = id;

       $.ajax({
                url : 'ajax.php',
                type : "POST",
                async : true,
                data : {action:action,user:user},

                success: function(response)
                {
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#detalle_venta').html(info.detalle);
                        $('#detalle_totales').html(info.totales);

                     }else{
                        console.log('no data');

                     }
                     viewProcesar();
                },
                error: function(error){

                }
            });
}

function getUrl() {
    var loc = window.location;
    var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/') + 1);
    return loc.href.substring(0, loc.href.length - ((loc.pathname + loc.search + loc.hash).length - pathName.length));
}

function sendDataProduct(){

    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al agregar el producto.</p>');
               }else{
                    var info = JSON.parse(response);
                    $('.row'+info.producto_id+' .celPrecio').html(info.nuevo_precio);
                    $('.row'+info.producto_id+' .celExistencia').html(info.nueva_existencia);
                    $('#txtCantidad').val('');
                    $('#txtPrecio').val('');
                    $('.alertAddProduct').html('<p>Producto guardado correctamente.</p>');
               }
               
            },

            error: function(error){
                console.log(error);
            }
     
        });
    
}

//Eliminar Producto
function delProduct(){

    var pr = $('#producto_id').val();
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_del_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al eliminar el producto.</p>');
               }else{
                    $('.row'+pr).remove();
                    $('#form_del_product .btn_ok').remove();
                    $('.alertAddProduct').html('<p>Producto eliminado correctamente.</p>');
               }
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}

function coloseModal(){

    $('.alertAddProduct').html('');
    $('#txtCantidad').val('');
    $('#txtPrecio').val('');
    $('.modal').fadeOut();
    $('.modalBuscarCl').fadeOut();
    $('.modalBuscarPr').fadeOut();
}


function agregarCliente(id){
 
        var cl = id;
         var action ='searchCliente';

        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async : true,
            data: {action:action,cliente:cl},

            success: function(response)
            {

                    var data = $.parseJSON(response);
                    $('#nit_cliente').val(data.nit);
                
            },
            error: function(error){
            }
        });

        $('.modalBuscarCl').fadeOut();
        $('#nit_cliente').focus();

           
     }

function serchForDetalleCli(pagina,busqueda){
    
         var pagina= pagina;
       $.ajax({
                url : 'action/data_cliente_2.php',
                type : "POST",
                data : {pagina:pagina,busqueda:busqueda},

                success: function(response)
                {
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#dataCliente').html(info.detalle);
                        $('#paginadorCliente').html(info.totales);

                     }else{
                        $('#dataCliente').html('<table>'+
                                                    '<tr>'+
                                                        '<th>Nit</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Teléfono</th>'+
                                                        '<th>Dirección</th>'+
                                                        '<th>Acción</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorCliente').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }
            });
}     

function serchForDetalleProd(pagina,busquedaProd){
        var pagina= pagina;
       $.ajax({
                url : 'action/data_producto.php',
                type : "POST",
                data : {pagina:pagina,busquedaProd:busquedaProd},

                success: function(response)
                {
                    //console.log(response);
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#dataProd').html(info.detalle);
                        $('#paginadorProd').html(info.totales);

                     }else{
                        $('#dataProd').html('<table>'+
                                                    '<tr>'+
                                                        '<th>Código</th>'+
                                                        '<th>Descripción</th>'+
                                                        '<th>Existencia</th>'+
                                                        '<th>Precio</th>'+
                                                        '<th>Foto</th>'+
                                                        '<th>Cantidad</th>'+
                                                        '<th>Acción</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                        $('#paginadorProd').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }                  
                
            });
}

function agregarProducto(codigo){
            //alert(codigo);
            var codproducto = codigo;
            var cantidad = $('#txt_cant_producto_2'+ codproducto).val();
            var existencia = parseInt($('#existencia_2'+ codproducto).val());
            //alert(existencia);
            //Ocultar el boton agregar si la cantidad es menor a 1

            var action = 'addProductoDetalle2';
            if (cantidad > existencia){
                alert('No hay inventarios suficiente.');
                return false;
            }

            $.ajax({
                url : 'ajax.php',
                type : "POST",
                async : true,
                data : {action:action,producto:codproducto,cantidad:cantidad},
                success: function(response)
                {
                    if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#detalle_venta').html(info.detalle);
                        $('#detalle_totales').html(info.totales);

                        $('#txt_cod_producto').val('');
                        $('#txt_descripcion').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').html('0.00');
                        $('#txt_precio_total').html('0.00');

                        //Bloquear cantidad
                        $('#txt_cant_producto').attr('disabled','disabled');

                        //Ocultar boton agregar
                        $('#add_product_venta').slideUp();

                     }else{
                        console.log('no data');

                     }
                     viewProcesar();
                },
                error: function(error){
                }
            });

    $('.modalBuscarPr').fadeOut();
    //location.reload();
    //$('.modalBuscarPr').fadeIn();
}
//Lista de clientes
function listaCliente(pagina,busqueda){
    
         var pagina= pagina;
       $.ajax({
                url : 'action/data_cliente.php',
                type : "POST",
                data : {pagina:pagina,busqueda:busqueda},

                success: function(response)
                {
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#listaCliente').html(info.detalle);
                        $('#paginadorClient').html(info.totales);

                     }else{
                        $('#listaCliente').html('<table>'+
                                                    '<tr>'+
                                                        '<th>Nit</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Teléfono</th>'+
                                                        '<th>Dirección</th>'+
                                                        '<th>Acción</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorClient').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }
            });
}

   //Modal editar cliente//
   function editarCliente(id)
     {
        var cliente = id;
        var action = 'editarCliente';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,cliente:cliente},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); actualizarCliente();">'+
                                            '<input type="hidden" name="action" value="actualizarCliente">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Actualizar cliente</h1>'+
                                            '<input type="hidden" name="idCliente" value="'+info.idcliente+'">'+
                                            '<input type="text" name="nitCliente" id="nitCliente" value="'+info.nit+'" placeholder="Nit" required><br>'+
                                            '<input type="text" name="nombreCliente" id="nombreCliente" value="'+info.nombre+'" placeholder="Nombre y apellidos" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="number" name="telefonoCliente" id="telefonoCliente" value="'+info.telefono+'" placeholder="Teléfono" required><br>'+
                                            '<input type="text" name="direccionCliente" id="direccionCliente" value="'+info.direccion+'" placeholder="Dirección" required><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

   function actualizarCliente(){

    $('.alertAddProduct').html('');
    var nit = $('#nitCliente').val();
    var nombre = $('#nombreCliente').val();
    var telefono = $('#telefonoCliente').val();
    var direccion = $('#direccionCliente').val();
    if (nit.length < 5){
        $('.alertAddProduct').html('<p style="color:red;">El NIT debe ser de 5 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (nombre.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Nombre debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (telefono.length < 8){
        $('.alertAddProduct').html('<p style="color:red;">El telefono debe ser de 8 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (direccion.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">La direccion debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){          

                    var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
            },

            error: function(error){
                console.log(error);
            }
     
        });    
}

//Modal eliminar cliente//
   function infoEliminarCliente(id)
     {
        var cliente = id;
        var action = 'editarCliente';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,cliente:cliente},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); eliminarCliente();">'+
                                            '<input type="hidden" name="action" value="eliminarCliente">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Eliminar cliente</h1>'+
                                            '<input type="hidden" name="cliente_id" id="cliente_id" value="'+info.idcliente+'">'+
                                            '<p>¿Está seguro de eliminar el siguiente registro?</p>'+
                                            '<h2 class="nameProducto">'+info.nombre+'</h2> <br>'+
                                            '<h2 class="nameProducto">'+info.nit+'</h2> <br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Eliminar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Eliminar cliente
function eliminarCliente(){

    var cliente = $('#cliente_id').val();
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al eliminar el cliente.</p>');
               }else{
                    $('.row'+cliente).remove();
                    $('#form_add_product .btn_new').remove();
                    $('.alertAddProduct').html('<p>Cliente eliminado correctamente.</p>');
               }
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}


    //Registrar cliente
function nuevoCliente(){

    $('.alertAddProduct').html('');
    var nit = $('#nitCliente').val();
    var nombre = $('#nombreCliente').val();
    var telefono = $('#telefonoCliente').val();
    var direccion = $('#direccionCliente').val();
    if (nit.length < 5){
        $('.alertAddProduct').html('<p style="color:red;">El NIT debe ser de 5 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (nombre.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Nombre debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (telefono.length < 8){
        $('.alertAddProduct').html('<p style="color:red;">El telefono debe ser de 8 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (direccion.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">La direccion debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){ 
            console.log(response);           
              var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
                        listaCliente();
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}

//Lista de usuarios
function listaUsuario(pagina,busqueda){
    
         var pagina= pagina;
       $.ajax({
                url : 'action/data_usuario.php',
                type : "POST",
                data : {pagina:pagina,busqueda:busqueda},

                success: function(response)
                {
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#listaUsuario').html(info.detalle);
                        $('#paginadorUsuario').html(info.totales);

                     }else{
                        $('#listaUsuario').html('<table>'+
                                                    '<tr>'+
                                                        '<th>ID</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Correo</th>'+
                                                        '<th>Usuario</th>'+
                                                        '<th>Rol</th>'+
                                                        '<th>Acciones</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorUsuario').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }
            });
}

   //Modal editar Usuario
   function editarUsuario(id)
     {
        var usuario = id;
        var action = 'editarUsuario';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,usuario:usuario},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); actualizarUsuario();">'+
                                            '<input type="hidden" name="action" value="actualizarUsuario">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Actualizar usuario</h1>'+
                                            '<input type="hidden" name="idUsuario" value="'+info.usuario.idusuario+'">'+
                                            '<input type="text" name="nombreUsuario" id="nombreUsuario" value="'+info.usuario.nombre+'" placeholder="Nombre y apellidos" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="email" name="correoUsuario" id="correoUsuario" value="'+info.usuario.correo+'" placeholder="Correo electrónico" required><br>'+
                                            '<input type="text" name="usuario" id="usuario" value="'+info.usuario.usuario+'" placeholder="Usuario" required><br>'+
                                            '<input type="password" name="claveUsuario" id="claveUsuario" value="" placeholder="Clave"><br>'+
                                            '<select name="rolUsuario" id="rolUsuario" required><option value="'+info.usuario.idrol+'">'+info.usuario.rol+'</option>'+info.rol+'</select><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Actualizar usuarios
   function actualizarUsuario(){
    $('.alertAddProduct').html('');
    var nombre = $('#nombreUsuario').val();
    var usuario = $('#usuario').val();
    var clave = $('#claveUsuario').val();
    if (nombre.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Nombre debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (usuario.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Usuario debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    
    if($("#correoUsuario").val().indexOf('@', 0) == -1 || $("#correoUsuario").val().indexOf('.', 0) == -1) {
        $('.alertAddProduct').html('<p style="color:red;">El correo electrónico no es correcto.</p>');
            return false;
        }

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){          
//console.log(response);
                    var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
            },

            error: function(error){
                console.log(error);
            }
     
        });    
}

//Modal eliminar usuario//
   function infoEliminarUsuario(id)
     {
        var usuario = id;
        var action = 'editarUsuario';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,usuario:usuario},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); eliminarUsuario();">'+
                                            '<input type="hidden" name="action" value="eliminarUsuario">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Eliminar usuario</h1>'+
                                            '<input type="hidden" name="usuario_id" id="usuario_id" value="'+info.usuario.idusuario+'">'+
                                            '<p>¿Está seguro de eliminar el siguiente registro?</p>'+
                                            '<h2 class="nameProducto">'+info.usuario.nombre+'</h2> <br>'+
                                            '<h2 class="nameProducto">'+info.usuario.usuario+'</h2> <br>'+
                                            '<h2 class="nameProducto">'+info.usuario.rol+'</h2> <br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Eliminar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Eliminar Usuario
function eliminarUsuario(){

    var usuario = $('#usuario_id').val();
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al eliminar el usuario.</p>');
               }else{
                    $('.row'+usuario).remove();
                    $('#form_add_product .btn_new').remove();
                    $('.alertAddProduct').html('<p>Usuario eliminado correctamente.</p>');
               }
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}


    //Registrar usuario
function nuevoUsuario(){

    $('.alertAddProduct').html('');
    var nombre = $('#nombreUsuario').val();
    var usuario = $('#usuario').val();
    var clave = $('#claveUsuario').val();
    if (nombre.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Nombre debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (usuario.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Usuario debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    if (clave.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">La contraseña debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    
    if($("#correoUsuario").val().indexOf('@', 0) == -1 || $("#correoUsuario").val().indexOf('.', 0) == -1) {
        $('.alertAddProduct').html('<p style="color:red;">El correo electrónico no es correcto.</p>');
            return false;
        }


    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
                        listaUsuario();
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}

//Lista de proveedor
function listaProveedor(pagina,busqueda){
    
         var pagina= pagina;
       $.ajax({
                url : 'action/data_proveedor.php',
                type : "POST",
                data : {pagina:pagina,busqueda:busqueda},

                success: function(response)
                {
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        //console.log(response);
                        $('#listaProveedor').html(info.detalle);
                        $('#paginadorProveedor').html(info.totales);

                     }else{
                        $('#listaProveedor').html('<table>'+
                                                    '<tr>'+
                                                        '<th>ID</th>'+
                                                        '<th>Proveedor</th>'+
                                                        '<th>Contacto</th>'+
                                                        '<th>Teléfono</th>'+
                                                        '<th>Dirección</th>'+
                                                        '<th>Fecha</th>'+
                                                        '<th>Acciones</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorProveedor').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }
            });
}

//Modal editar proveedor//
   function editarProveedor(id)
     {
        var proveedor = id;
        var action = 'editarProveedor';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,proveedor:proveedor},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); actualizarProveedor();">'+
                                            '<input type="hidden" name="action" value="actualizarProveedor">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Actualizar proveedor</h1>'+
                                            '<input type="hidden" name="idProveedor" value="'+info.codproveedor+'">'+
                                            '<input type="text" name="nombreProveedor" id="nombreProveedor" value="'+info.proveedor+'" placeholder="Nombre de proveedor" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="text" name="nombreContacto" id="nombreContacto" value="'+info.contacto+'" placeholder="Nombre del contacto" onkeypress="return soloLetras(event)" onpaste="return false" required><br>'+
                                            '<input type="number" name="telefonoProveedor" id="telefonoProveedor" value="'+info.telefono+'" placeholder="Teléfono" required><br>'+
                                            '<input type="text" name="direccionProveedor" id="direccionProveedor" value="'+info.direccion+'" placeholder="Dirección" required><br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Actualizar proveedor
   function actualizarProveedor(){

    $('.alertAddProduct').html('');
    var nombre = $('#nombreProveedor').val();
    var contacto = $('#nombreContacto').val();
    var telefono = $('#telefonoProveedor').val();
    var direccion = $('#direccionProveedor').val();
    if (nombre.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Nombre debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
     if (contacto.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El contacto debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
     if (telefono.length < 8){
        $('.alertAddProduct').html('<p style="color:red;">El Teléfono debe ser de 8 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
     if (direccion.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">La Dirección debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){          

                    var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
            },

            error: function(error){
                console.log(error);
            }
     
        });    
}

//Modal eliminar proveedor//
   function infoEliminarProveedor(id)
     {
        var proveedor = id;
        var action = 'editarProveedor';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,proveedor:proveedor},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); eliminarProveedor();">'+
                                            '<input type="hidden" name="action" value="eliminarProveedor">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Eliminar proveedor</h1>'+
                                            '<input type="hidden" name="proveedor_id" id="proveedor_id" value="'+info.codproveedor+'">'+
                                            '<p>¿Está seguro de eliminar el siguiente registro?</p>'+
                                            '<h2 class="nameProducto">'+info.proveedor+'</h2> <br>'+
                                            '<h2 class="nameProducto">'+info.contacto+'</h2> <br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Eliminar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Eliminar proveedor
function eliminarProveedor(){

    var proveedor = $('#proveedor_id').val();
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al eliminar el proveedor.</p>');
               }else{
                    $('.row'+proveedor).remove();
                    $('#form_add_product .btn_new').remove();
                    $('.alertAddProduct').html('<p>Proveedor eliminado correctamente.</p>');
               }
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}


    //Registrar proveedor
function nuevoProveedor(){

    $('.alertAddProduct').html('');
    var nombre = $('#nombreProveedor').val();
    var contacto = $('#nombreContacto').val();
    var telefono = $('#telefonoProveedor').val();
    var direccion = $('#direccionProveedor').val();
    if (nombre.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El Nombre debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
     if (contacto.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">El contacto debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
     if (telefono.length < 8){
        $('.alertAddProduct').html('<p style="color:red;">El Teléfono debe ser de 8 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
     if (direccion.length < 4){
        $('.alertAddProduct').html('<p style="color:red;">La Dirección debe ser de 4 caracteres como mínimo.</p>');
        $('.alertAddProduct').slideDown();
        return false;
    }
    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
                        listaProveedor();
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}

//Lista de productos
function listaProductos(pagina,busqueda){
    
         var pagina= pagina;
       $.ajax({
                url : 'action/data_producto1.php',
                type : "POST",
                data : {pagina:pagina,busqueda:busqueda},

                success: function(response)
                {
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#listaProducto').html(info.detalle);
                        $('#paginadorProducto').html(info.totales);

                     }else{
                        $('#listaProducto').html('<table>'+
                                                    '<tr>'+
                                                        '<th>Código</th>'+
                                                        '<th>Descripción</th>'+
                                                        '<th>Precio</th>'+
                                                        '<th>Existencia</th>'+
                                                        '<th>Proveedor</th>'+
                                                        '<th>Foto</th>'+
                                                        '<th>Acciones</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorProducto').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }
            });
}

//Agregar producto
 function agregarProd(id){
        var producto = id;
        var action = 'infoProducto';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,producto:producto},

            success: function(response){            
                if (response != 'error') {
                   //console.log(response);
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); sendDataProduct();">'+
                                            '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i> <br> Agregar Producto</h1>'+
                                            '<h2 class="nameProducto">'+info.descripcion+'</h2> <br>'+
                                            '<input type="number" name="cantidad" id="txtCantidad" placeholder="Cantidad del producto" required><br>'+
                                            '<input type="text" name="precio" id="txtPrecio" placeholder="Precio del producto" required>'+
                                            '<input type="hidden" name="producto_id" id="producto_id" value="'+info.codproducto+'" required>'+
                                            '<input type="hidden" name="action" value="addProduct" required>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Agregar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Modal editar producto
   function editarProducto(id)
     {
        var producto = id;
        var action = 'editarProducto';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,producto:producto},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    foto = '';
                    classRemove = '';
                    if (info.producto.foto != 'img_producto.png'){
                        classRemove = '';
                        foto = '<img id="img" src="img/uploads/'+info.producto.foto+'" alt="Producto">';
                    }else{
                        classRemove = 'notBlock';
                        foto = '<img id="img" src="img/'+info.producto.foto+'" alt="Producto">';
                    }
                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); actualizarProducto();">'+
                                            '<input type="hidden" name="action" value="actualizarProducto">'+
                                            '<h1><i class="fa fa-cube" style="font-size: 45pt;"></i> <br> Actualizar producto</h1>'+
                                            '<input type="hidden" name="idProducto" value="'+info.producto.codproducto+'">'+
                                            '<select name="nombreProveedorProd" id="nombreProveedorProd" required><option value="'+info.producto.codproveedor+'">'+info.producto.proveedor+'</option>'+info.proveedor+'</select><br>'+
                                            '<input type="text" name="codigoProducto" id="codigoProducto" value="'+info.producto.codigo+'" placeholder="Código del producto" required><br>'+
                                            '<input type="text" name="nombreProducto" id="nombreProducto" value="'+info.producto.descripcion+'" placeholder="Nombre del producto" required><br>'+
                                            '<input step="any" type="number" name="prcioProducto" id="prcioProducto" value="'+info.producto.precio+'" placeholder="Prcio del producto" required>'+
                                            '<div class="photo"><label for="foto"></label><div class="prevPhoto">'+
                                            '<span class="'+classRemove+'"></span>'+
                                            '<label for="foto"></label>'+foto+'</div><div class="upimg"></div><br>'+
                                            '<input type="file" name="foto" id="foto">'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Guardar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

 //Actualizar producto
   function actualizarProducto(){

    $('.alertAddProduct').html('');
    var parametros = new FormData($('#form_add_product')[0]);

    $.ajax({
            url:'ajax.php',
            type:'POST',
            data: parametros,
            contentType: false,
            processData: false,

            success: function(response){          
                    //console.log(response);
                    var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
            },

            error: function(error){
                console.log(error);
            }
     
        });    
}

//Modal eliminar producto//
   function infoEliminarProducto(id)
     {
        var producto = id;
        var action = 'editarProducto';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,producto:producto},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); eliminarProducto();">'+
                                            '<input type="hidden" name="action" value="eliminarProducto">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Desactivar producto</h1>'+
                                            '<input type="hidden" name="producto_id2" id="producto_id2" value="'+info.producto.codproducto+'">'+
                                            '<p>¿Está seguro de eliminar el siguiente registro?</p>'+
                                            '<h2 class="nameProducto">'+info.producto.proveedor+'</h2> <br>'+
                                            '<h2 class="nameProducto">'+info.producto.descripcion+'</h2> <br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Desactivar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Eliminar producto
function eliminarProducto(){

    var producto = $('#producto_id2').val();
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al eliminar el producto.</p>');
               }else{
                    $('.row'+producto).remove();
                    $('#form_add_product .btn_new').remove();
                    $('.alertAddProduct').html('<p>Producto eliminado correctamente.</p>');
               }
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}


    //Registrar producto
function nuevoProducto(){

    var parametros = new FormData($('#form_add_product')[0]);
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:"POST",
            data: parametros,
            contentType: false,
            processData:false,

            success: function(response){  
            console.log(response);          
              var info = JSON.parse(response);
                    if (info.cod == '00') {
                            $('.alertAddProduct').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#form_add_product')[0].reset();
                        }else{
                             $('.alertAddProduct').html('<p style="color:red;">'+info.msg+'</p>');
                        }
                        listaProductos();
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}

//Lista ventas
function listaVentas(pagina,busqueda){
         var pagina= pagina;
       $.ajax({
                url : 'action/data_ventas.php',
                type : "POST",
                data : {pagina:pagina,busqueda:busqueda},

                success: function(response)
                {
                    //console.log(response);
                  if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#listaVentas').html(info.detalle);
                        $('#paginadorVentas').html(info.totales);

                     }else{
                        $('#listaVentas').html('<table>'+
                                                    '<tr>'+
                                                        '<th>No.</th>'+
                                                        '<th>Fecha</th>'+
                                                        '<th>Cliente</th>'+
                                                        '<th>Vendedor</th>'+
                                                        '<th>Estado</th>'+
                                                        '<th class="textright">Total Factura</th>'+
                                                        '<th class="textright">Acciones</th>'+
                                                    '</tr>'+
                                                    '<tbody>'+
                                                    '<tr><td colspan="7">No se encontraron concidencias :(</td></tr>'+
                                                    '</tbody>');
                         $('#paginadorVentas').html('');
                        //console.log('no data');

                     }
                    
                },
                error: function(error){

                }
            });
}

//Modal Form Anular Factura
    function infoAnularFactura(nofactura){
        /*Act on the event*/
        //e.preventDefault();
        var nofactura = nofactura;
        var action = 'infoFactura';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,nofactura:nofactura},

            success: function(response){            
                if (response != 'error') {
                    var info = JSON.parse(response);


                   $('.bodyModal').html('<form action="" method="post" name="form_anular_factura" id="form_anular_factura" onsubmit="event.preventDefault(); anularFactura();">'+
                                            '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i> <br> Anular Venta</h1><br>'+
                                            '<p>¿Realmente desea anular la venta?</p>'+

                                            '<p><strong>No. '+info.noventa+'</strong></p>'+
                                            '<p><strong>Monto. C$ '+info.totalventa+'</strong></p>'+
                                            '<p><strong>Fecha. '+info.fecha+'</strong></p>'+
                                            '<input type="hidden" name="action" value="anularFactura">'+
                                            '<input type="hidden" name="no_factura" id="no_factura" value="'+info.noventa+'" required>'+
                                           
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_cancel" onclick="coloseModal();"><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_ok"><i class="far fa-trash-alt"></i> Anular</button>'+             
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

    //Ver Factura
    function verFactura(codcliente,nofactura){
        var codCliente = codcliente;
        var noFactura = nofactura;
        generarPDF(codCliente,noFactura);
    }

    //Ver Factura
    function verTicket(codcliente,nofactura){
        var codCliente = codcliente;
        var noFactura = nofactura;
        generarPDFTicket(codCliente,noFactura);
    }

    //Agregar producto al detalle con enter
    function agregarProductoAlDetalle(){
        if ($('#txt_cant_producto').val() > 0)
        { 
            var codproducto = $('#txt_id_producto').val();
            var cantidad = $('#txt_cant_producto').val();
            var existencia = parseInt($('#txt_existencia').html());
            var action = 'addProductoDetalle';
           if (cantidad > existencia){
                alert('No hay inventario suficiente.');
                return false;
            }
            

            $.ajax({
                url : 'ajax.php',
                type : "POST",
                async : true,
                data : {action:action,producto:codproducto,cantidad:cantidad},
                success: function(response)
                {
                    //console.log(response);
                    if (response != 'error')
                     {
                        var info = JSON.parse(response);
                        $('#detalle_venta').html(info.detalle);
                        $('#detalle_totales').html(info.totales);

                        $('#txt_id_producto').val('');
                        $('#txt_cod_producto').val('');
                        $('#txt_descripcion').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').html('0.00');
                        $('#txt_precio_total').html('0.00');

                        //Bloquear cantidad
                        $('#txt_cant_producto').attr('disabled','disabled');
                        $('#txt_id_producto').attr('disabled','disabled');

                        //Ocultar boton agregar
                        $('#add_product_venta').slideUp();
                        $('#txt_cod_producto').focus();

                     }else{
                        console.log('no data');

                     }
                     viewProcesar();

                },
                error: function(error){

                }
            });
        }
    }

    function facturar(){
        var rows = $('#detalle_venta tr').length;
        if (rows > 0) 
        {
            var action = 'procesarVenta';
            var codcliente = $('#idcliente').val();
            var tipoPago = $('#tipo_pago').val();

            $.ajax({
                url : 'ajax.php',
                type: "POST",
                async : true,
                data: {action:action,codcliente:codcliente,tipoPago:tipoPago},

                success: function(response)
                {
                    console.log(response);
                    if (response != 'error')
                    {
                        var info = JSON.parse(response);
                        //generarPDF(info.codcliente,info.nofactura)
                        generarPDFTicket(info.codcliente,info.noventa)
                        location.reload();
                    }else{
                        console.log('no data');
                    }
                },
                error: function(error){
                }
            });
        }
    }

    function anularVent(){
        var rows = $('#detalle_venta tr').length;
        if (rows > 0) 
        {
            var action = 'anularVenta';

            $.ajax({
                url : 'ajax.php',
                type: "POST",
                async : true,
                data: {action:action},

                success: function(response)
                {
                    if (response != 'error')
                    {
                        location.reload();
                    }
                },
                error: function(error){
                }
            });
        }
    }

 function soloLetras(e) {
    var key = e.keyCode || e.which,
      tecla = String.fromCharCode(key).toLowerCase(),
      letras = " áéíóúabcdefghijklmnñopqrstuvwxyz",
      especiales = [8, 37, 39, 46],
      tecla_especial = false;

    for (var i in especiales) {
      if (key == especiales[i]) {
        tecla_especial = true;
        break;
      }
    }

    if (letras.indexOf(tecla) == -1 && !tecla_especial) {
      return false;
    }
  }

  //Modal activar producto//
   function infoActivarProducto(id)
     {
        var producto = id;
        var action = 'editarProducto';

       $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: {action:action,producto:producto},

            success: function(response){
            //console.log(response);            
                if (response != 'error') {
                    var info = JSON.parse(response);

                    $('.bodyModal').html('<form action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); activarProducto();">'+
                                            '<input type="hidden" name="action" value="activarProducto">'+
                                            '<h1><i class="fas fa-user" style="font-size: 45pt;"></i> <br> Activar este producto?</h1>'+
                                            '<input type="hidden" name="producto_id_2" id="producto_id_2" value="'+info.producto.codproducto+'">'+
                                            '<p>¿Está seguro de activar el siguiente producto?</p>'+
                                            '<h2 class="nameProducto">'+info.producto.proveedor+'</h2> <br>'+
                                            '<h2 class="nameProducto">'+info.producto.descripcion+'</h2> <br>'+
                                            '<div class="alert alertAddProduct"></div>'+
                                            '<a href="#" class="btn_ok closeModal" onclick="coloseModal(); "><i class="fas fa-ban"></i> Cerrar</a>'+
                                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Activar</button>'+              
                                        '</form>');
                }
            },

            error: function(error){
                console.log(error);
            }
     
        });

        $('.modal').fadeIn();
        
    }

        //Activar producto
function activarProducto(){

    var producto = $('#producto_id_2').val();
    $('.alertAddProduct').html('');

    $.ajax({
            url:'ajax.php',
            type:'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){            
              if (response == 'error')
               {
                 $('alertAddProduct').html('<p style="color: red;">Error al activar el producto.</p>');
               }else{
                    $('.row'+producto).remove();
                    $('#form_add_product .btn_new').remove();
                    $('.alertAddProduct').html('<p>Producto activado correctamente.</p>');
               }
               
            },

            error: function(error)
            {
                console.log(error);
            }
     
        });
    
}