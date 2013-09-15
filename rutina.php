<?php
function retornarConexion()
{
$conexion=mysql_connect("localhost","root","colldao") or
	die("Problemas en la conexión.");
mysql_select_db("INTEGRITY",$conexion) or
	die("Problemas en la selección de la base de datos");
return $conexion;
}
?>

