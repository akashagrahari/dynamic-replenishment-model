<?php
    session_start();
?>
<?php
    if(!isset($_SESSION['username'])){
      header("Location: signin.php?id=2");
        die();
    }
?>
<?php include("head.php"); ?>

    <div class="container theme-showcase" role="main">

      
      <div class="jumbotron">
         <h1>Welcome to the application</h1>
        <p>Please upload the file you wish to process.</p>
        <p>Upload the xlsx file here</p>
    <form role="form" action="upload.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="exampleInputFile">File input</label>
          <input type="file" name="file" id="file">
          
        </div>
        
        <button type="submit" name="submit" class="btn btn-success">Submit</button>
    </form>
  </br>
    <?php
      if(isset($_GET['id']))
      {
      $id=$_GET['id'];
      if(isset($id))
      {

      
        
      if($id == 1)
        {echo "<p class=\"alert alert-info\">The file was successfully uploaded</p>";
      echo "<a href = \"process.php\"><button type=\"button\" class=\"btn btn-default btn-lg\">
  <span class=\"glyphicon glyphicon-expand\"></span> Process
</button></a>";}
elseif($id == 3)
{
  echo "<p class=\"alert alert-info\">All Done! Download the file below.</p>";
}
    }}
    ?>




    <h3>Download the final file here</h3>
    <form action="download.php" method="post">
           <button type="submit" name="submit" value="Download File" class="btn btn-success"><span class="glyphicon glyphicon-download-alt"></span> Download</button>
       </form>
     </br>
       <?php
      if(isset($_GET['id']))
      {
      $id=$_GET['id'];
      if(isset($id))
      {
      if($id == 2)
        echo "<p class=\"alert alert-info\">Upload a File First</p>";
    }}
    ?>
      </div>
      <?php require("foot.php"); ?>
 