<?php
/** Error reporting */
error_reporting(E_ALL);

/** Include path **/
ini_set('include_path', ini_get('include_path').';../Classes/');

// /** PHPExcel */
// require_once 'PHPExcel/Classes/PHPExcel.php';

// /** PHPExcel_Writer_Excel2007 */
// require_once 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';

// // Create new PHPExcel object
// //echo date('H:i:s') . " Create new PHPExcel object\n";
// $objPHPExcel = new PHPExcel();

// // Set properties
// //echo date('H:i:s') . " Set properties\n";
// $objPHPExcel->getProperties()->setCreator("Akash Agrahari");
// $objPHPExcel->getProperties()->setLastModifiedBy("Akash Agrahari");
// $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Document");
// $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Document");
// $objPHPExcel->getProperties()->setDescription("Document for Office 2007 XLSX, generated using PHP classes.");


// // Add some data
// //echo date('H:i:s') . " Add some data\n";
// $objPHPExcel->setActiveSheetIndex(0);
// $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Hello');
// $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
// $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
// $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');

// // Rename sheet
// //echo date('H:i:s') . " Rename sheet\n";
// $objPHPExcel->getActiveSheet()->setTitle('Analysis');

		
// // Save Excel 2007 file
// //echo date('H:i:s') . " Write to Excel2007 format\n";
// $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
// $objWriter->save(str_replace('process.php', 'final.xlsx', __FILE__));

// require_once 'PHPExcel/IOFactory.php';
// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// // If you want to output e.g. a PDF file, simply do:
// //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
// $objWriter->save('MyExcel.xslx);


// Echo done
//echo date('H:i:s') . " Done writing file.\r\n";



//reading Files!!!!!! :D

require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
$objPHPExcel = PHPExcel_IOFactory::load("final.xlsx");
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    $worksheetTitle     = $worksheet->getTitle();
    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $nrColumns = ord($highestColumn) - 64;
    echo "<br>The worksheet ".$worksheetTitle." has ";
    echo $nrColumns . ' columns (A-' . $highestColumn . ') ';
    echo ' and ' . $highestRow . ' row.';
    echo '<br>Data: <table border="1"><tr>';
    for ($row = 1; $row <= $highestRow; ++ $row) {
        echo '<tr>';
        for ($col = 0; $col < $highestColumnIndex; ++ $col) {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $val = $cell->getValue();
            $dataType = PHPExcel_Cell_DataType::dataTypeForValue($val);
            echo '<td>' . $val . '<br>(Typ ' . $dataType . ')</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

// converting file types

// $objPHPExcel = PHPExcel_IOFactory::load("XMLTest.xml");
// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// $objWriter->save('covertedXml2Xlsx.xlsx');

?>