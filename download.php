<?php
	$file = 'finalfile.xlsx';

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}
else
{
	header("Location: index.php?id=2");
die();
}

	// header('Content-Type: application/download');
 //  	header('Content-Disposition: attachment; filename="finalfile.csv"');
 //  	header("Content-Length: " . filesize("finalfile.csv"));
 //  	$fp = fopen("finalfile.csv", "r");
 //  	fpassthru($fp);
 //  	fclose($fp);
?>
