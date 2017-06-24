<?php
    session_start();
?> 
<?php 
	include("include/connection.php");

	if(isset($_POST['username']) && isset($_POST['password'])){
		$username = $_POST['username'];
		$password = $_POST['password'];
		$enc_pwd=sha1($password);
		//adding a user
		// mysqli_query($db,"INSERT INTO 
		// 	users (username, password)
		// 	VALUES ('$username', '$enc_pwd')");
		$result = mysqli_query($db,"SELECT * FROM users");
		while($row = mysqli_fetch_array($result)) {
  			if($row['username'] == $username && $row['password'] == $enc_pwd ) {
  				$_SESSION['username']=$row['username'];
  				header("Location: index.php");
				die();
  			}
  			else {
  				header("Location: signin.php?id=1");
				die();
  			}

		}
		
	}
	mysqli_close($db);
	
?>
