<?php
$allowedExts = array("xlsx");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if (in_array($extension, $allowedExts)) {
  if ($_FILES["file"]["error"] > 0) {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
  } else {
    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    echo "Type: " . $_FILES["file"]["type"] . "<br>";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";
    if (file_exists("../EPC/" . $_FILES["file"]["name"])) {
      echo $_FILES["file"]["name"] . " already exists. ";
    } else {
      move_uploaded_file($_FILES["file"]["tmp_name"],
      "../EPC/" . $_FILES["file"]["name"]);
      echo "Stored in: " . "../EPC/" . $_FILES["file"]["name"];
    }
  }
  header("Location: index.php?id=1");
die();
} else {
  echo "Invalid file";
}

?>