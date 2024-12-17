<?php

	$host = "localhost";
	$user = "admin";
	$pass = "admin";
	$bd = "apartamentos";

	$pdoAdmin = new PDO("mysql:host=$host", $user, $pass);
	$pdoAdmin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdoAdmin->exec('use Apartamentos');
	
?>
