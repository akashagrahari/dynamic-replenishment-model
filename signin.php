<?php
    session_start();
    if(isset($_SESSION['username']))
    {
      header("Location: index.php");
  die();
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>EPC Application</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
     <link href="css/footer.css" rel="stylesheet">
    
  </head>

  <body role="document">

 <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          
          <a class="navbar-brand" href="index.php">EPC Application</a>

          </div>
        </br>
        
      </div>

    </div>
</br>

    <div class="container theme-showcase" role="main">


      <div class="jumbotron">

    <div class="container">


        <form class="form-signin" role="form" action="check.php" method = "post">
        <h2 class="form-signin-heading">Please sign in</h2>
        <input type="username" class="form-control" placeholder="Username" name="username" required autofocus>
        <input type="password" class="form-control" placeholder="Password" name="password" required>
        <label class="checkbox">
          <input type="checkbox" value="remember-me" name="check"> Remember me
        </label>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
    </br>
      <?php
          if(isset($_GET['id'])) {
            if($_GET['id'] == 1) {
              echo "<div class=\"alert alert-danger\"><strong>Wrong Login Credentials!</strong> Please Try Again.</div>";
            }
            elseif($_GET['id'] == 2){
              echo "<div class=\"alert alert-danger\"><strong>Access Denied!</strong> Please Login First.</div>";
            }

          }
      ?>
    
    

    </div> 
  </div>
</div>
<?php require("foot.php"); ?>
 