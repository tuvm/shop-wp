<?php

/** Set default timezone (will throw a notice otherwise) */
date_default_timezone_set('Asia/Ho_Chi_Minh');

// include PHPExcel
require('Classes/PHPExcel.php');
require ('../../../wp-load.php');

// create new PHPExcel object
$objPHPExcel = new PHPExcel;

// set default font
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');

// set default font size
$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

// create the writer
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");



/**

 * Define currency and number format.

 */

// currency format, € with < 0 being in red color

// number format, with thousands separator and two decimal points.
$numberFormat = '#,#0.##;[Red]-#,#0.##';



// writer already created the first sheet for us, let's get it
$objSheet = $objPHPExcel->getActiveSheet();

// rename the sheet
$objSheet->setTitle('Data export '.date('d - m - Y'));



// let's bold and size the header font and write the header
// as you can see, we can specify a range of cells, like here: cells from A1 to A4
$objSheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(10);



// write header

$objSheet->getCell('A1')->setValue('Mã đơn hàng');
$objSheet->getCell('B1')->setValue('Họ tên');
$objSheet->getCell('C1')->setValue('Số điện thoại');
$objSheet->getCell('D1')->setValue('Email');
$objSheet->getCell('E1')->setValue('Sản phẩm đặt mua');
$objSheet->getCell('F1')->setValue('Địa chỉ nhận');
$objSheet->getCell('G1')->setValue('Số lượng');
$objSheet->getCell('H1')->setValue('Tổng tiền');
// we could get this data from database, but here we are writing for simplicity

global $wpdb;
$objSheet->getCell('A2')->setValue('Motherboard');

$data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'muahangnhanh'." ORDER BY `id` DESC");
$i=2;
foreach($data as $key => $row){
    $objSheet->getCell('A'.$i)->setValue($row->madonhang);
    $objSheet->getCell('B'.$i)->setValue($row->ten);
    $objSheet->getCell('C'.$i)->setValue($row->sdt);
    $objSheet->getCell('D'.$i)->setValue($row->email);
    $objSheet->getCell('E'.$i)->setValue($row->sanpham);
	$objSheet->getCell('F'.$i)->setValue($row->diachi);
    $objSheet->getCell('G'.$i)->setValue($row->soluong);
    $objSheet->getCell('H'.$i)->setValue($row->thanhtien);
    $i +=1;
}

// autosize the columns
$objSheet->getColumnDimension('A')->setAutoSize(true);
$objSheet->getColumnDimension('B')->setAutoSize(true);
$objSheet->getColumnDimension('C')->setAutoSize(true);
$objSheet->getColumnDimension('D')->setAutoSize(true);
$objSheet->getColumnDimension('E')->setAutoSize(true);
$objSheet->getColumnDimension('F')->setAutoSize(true);
$objSheet->getColumnDimension('G')->setAutoSize(true);
$objSheet->getColumnDimension('G')->setAutoSize(true);

//Setting the header type
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="export '. date('d - m - Y').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

/* If you want to save the file on the server instead of downloading, replace the last 4 lines by 
    $objWriter->save('file.xlsx');
*/

?>