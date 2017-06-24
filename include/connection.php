<?php
//create connection
$db = mysqli_connect("localhost","root","akash","epc");
	if(mysqli_connect_errno()){
		echo "Failed to connect to MySQL: ". mysqli_connect_error();
	}
?>