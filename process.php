<?php
session_start();
error_reporting(E_ALL);
ini_set('include_path', ini_get('include_path').';../Classes/');

require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once("include/connection.php");
require_once("include/functions.php");
// Create new PHPExcel object
$objfinal = new PHPExcel();

// Set properties
$objfinal->getProperties()->setCreator("{$_SESSION['username']}");
$objfinal->getProperties()->setLastModifiedBy("{$_SESSION['username']}");
$objfinal->getProperties()->setTitle("Office 2007 XLSX Document");
$objfinal->getProperties()->setSubject("Office 2007 XLSX Document");
$objfinal->getProperties()->setDescription("Document for Office 2007 XLSX, generated using PHP classes.");



 $objinitial = PHPExcel_IOFactory::load("upload.xlsx");

//PART ONE : ADD SALES OF INDIVIDUAL MONTHS AND DELIVER THEM TO THE DATABASE
monthlysales();

//PART TWO : CALCULATE INDEXES AND DEVIATIONS AND FILL THEM IN DATABASE
index_stdev();    

//PART THREE : CALCULATE FORECAST AND OPTIMIZE PARAMETERS AND GET THREE SUBSEQUENT FORECASTS
do{
	$forecast_array = Forecast();
	$count = count($forecast_array);

}while($forecast_array[$count-1] <0 &&  $forecast_array[$count-2] <0 &&  $forecast_array[$count-3] <0 && $forecast_array[$count-1]>(2.5*$forecast_array[$count-37]) && $forecast_array[$count-2]>(2.5*$forecast_array[$count-38]) && $forecast_array[$count-3]>(2.5*$forecast_array[$count-39]));
// echo "The Forecast is " ;
// print_r($forecast_array);
// echo "</br> </br>";

//PART FOUR : GENERATE INVENTORY LEVELS FOR THE NEXT MONTH. APRIL, 2014 HERE.
$no_of_days = 27;
$lead_time = 10;
$ini_inventory = 23746;
$ROQ = 50000;
$inventory = Inventory(15000, $no_of_days, $lead_time, $ini_inventory, $ROQ);
$objfinal->getActiveSheet()->setTitle('Final Analysis');
$objfinal->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "Day");
$objfinal->getActiveSheet()->setCellValueByColumnAndRow(1, 1, "Sale");
$objfinal->getActiveSheet()->setCellValueByColumnAndRow(2, 1, "ESS");
$objfinal->getActiveSheet()->setCellValueByColumnAndRow(3, 1, "Inventory");
$objfinal->getActiveSheet()->setCellValueByColumnAndRow(4, 1, "State");
$inventory_size=count($inventory);
$start =$inventory_size -$no_of_days;
for($i=0;$i<$inventory_size;++$i)
{
    if($i<$start)
    {
        for($j=0;$j<=1;++$j)
        {
            $objfinal->getActiveSheet()->setCellValueByColumnAndRow($j, $i+2, $inventory[$i][$j]);
        }
    }
    else
    {   
        for($j=0;$j<=4;++$j)
        {
            $objfinal->getActiveSheet()->setCellValueByColumnAndRow($j, $i+2, $inventory[$i][$j]);
        }
    }
}
$objWriter = new PHPExcel_Writer_Excel2007($objfinal);
$objWriter->save(str_replace('process.php', 'finalfile.xlsx', __FILE__));


//Inventory($no_of_days, $lead_time, $ini_inventory, $ROQ);
















// Add some random data
// $objfinal->setActiveSheetIndex(0);
// $objfinal->getActiveSheet()->SetCellValue('A1', 'Hello');
// $objfinal->getActiveSheet()->SetCellValue('B2', 'world!');
// $objfinal->getActiveSheet()->SetCellValue('C1', 'Hello');
// $objfinal->getActiveSheet()->SetCellValue('D2', 'world!');

// Rename sheet
// $objfinal->getActiveSheet()->setTitle('Analysis');

		
// Save Excel 2007 file
// $objWriter = new PHPExcel_Writer_Excel2007($objfinal);
// $objWriter->save(str_replace('process.php', 'finalfile.xlsx', __FILE__));



// check if file is running
//echo "ho gaya";


header("Location: index.php?id=3");
mysqli_close($db);
?>