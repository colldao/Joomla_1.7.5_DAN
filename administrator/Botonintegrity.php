<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Documento sin t&iacute;tulo</title>
<script src="Scripts/AC_RunActiveContent.js" type="text/javascript"></script>
</head>

<body>






<script type="text/javascript">
AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0','width','100','height','22','title','Albarán','src','button1','quality','high','pluginspage','http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash','movie','button1' ); //end AC code
</script><noscript><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="100" height="22" title="Albarán">
         <param name="movie" value="button1.swf" />
         <param name="quality" value="high" />
         <embed src="button1.swf" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="100" height="22" ></embed>
       </object></noscript>

<?php
$conexion=mysql_connect("localhost","root","colldao") or
  die("Problemas en la conexion");
mysql_select_db("INTEGRITY",$conexion) or
  die("Problemas en la selección de la base de datos");
$registros=mysql_query("select compania,fecha_entrega,fecha_devolucion,dni_agente,num_serie from albaran_AGENTE",$conexion) or
  die("Problemas en el select:".mysql_error());

echo "<table align=center bgcolor=#66CCFF   border=1 cellspacing=0 cellpadding=0>"; 
 echo '<tr>
		<td><a href="#?ordenado=compania">COMPAÑÍA</a></td>
	        <td><a href="#?ordenado=fecha_entrega">FECHA ENTREGA</a></td>
		<td><a href="#?ordenado=fecha_devolucion">FECHA DEVOLUCIÓN</a></td>
		<td><a href="#?ordenado=dni_agente">DNI AGENTE</a></td>
		<td><a href="#?ordenado=num_serie">NÚM. SERIE</a></td>
		</tr>';		
while ($reg=mysql_fetch_array($registros))
{
  echo '<tr>';
  echo '<td>'.$reg['compania'].'</td>';
  echo '<td>'.$reg['fecha_entrega'].'</td>';
  echo '<td>'.$reg['fecha_devolucion'].'</td>';
  echo '<td>'.$reg['dni_agente'].'</td>';
  echo '<td>'.$reg['num_serie'].'</td>';
  echo '</tr>';
}
echo '</table>';

mysql_close($conexion);
?>

<?php
$conexion=mysql_connect("localhost","root","colldao") or
  die("Problemas en la conexion");
mysql_select_db("INTEGRITY",$conexion) or
  die("Problemas en la selección de la base de datos");
$registros=mysql_query("SELECT albaran_agente.Id_albaran_agente, albaran_agente.compania,albaran_agente.fecha_entrega, albaran_agente.fecha_devolucion, albaran_agente.dni_agente, albaran_agente.num_serie,compras.producto,compras.cant_salida,compras.precio_venta
FROM compras INNER JOIN albaran_agente ON compras.num_de_serie = albaran_agente.num_serie",$conexion) or
  die("Problemas en el select:".mysql_error());

echo "<table align=center bgcolor=#66CCFF   border=1 cellspacing=0 cellpadding=0>"; 
 echo '<tr>
		<td><a href="#?ordenado=Id_albaran_agente">ID</a></td>
                <td><a href="#?ordenado=compania">COMPAÑÍA</a></td>
	        <td><a href="#?ordenado=fecha_entrega">FECHA ENTREGA</a></td>
		<td><a href="#?ordenado=fecha_devolucion">FECHA DEVOLUCIÓN</a></td>
		<td><a href="#?ordenado=dni_agente">DNI AGENTE</a></td>
		<td><a href="#?ordenado=num_serie">NÚM. SERIE</a></td>
                <td><a href="#?ordenado=producto">PRODUCTO</a></td>
	        <td><a href="#?ordenado=cant_salida">CANT. SALIDA</a></td>
		<td><a href="#?ordenado=precio_venta">PRECIO VENTA</a></td>
		

		</tr>';		
while ($reg=mysql_fetch_array($registros))
{
  echo '<tr>';
  echo '<td>'.$reg['Id_albaran_agente'].'</td>';
  echo '<td>'.$reg['compania'].'</td>';
  echo '<td>'.$reg['fecha_entrega'].'</td>';
  echo '<td>'.$reg['fecha_devolucion'].'</td>';
  echo '<td>'.$reg['dni_agente'].'</td>';
  echo '<td>'.$reg['num_serie'].'</td>';
  echo '<td>'.$reg['producto'].'</td>';
  echo '<td>'.$reg['cant_salida'].'</td>';
  echo '<td>'.$reg['precio_venta'].'</td>';
  echo '</tr>';
}
echo '</table>';

mysql_close($conexion);
?>







</body>
</html>
