<?php /* Template Name: pdf-address */ ?>
<?php
global $wpdb, $current_user;
$pid = $_GET['pid'];
$order_id = $_GET['oid'];
$is_current_user_owner = false;
if (isset($pid) && $pid != '' && isset($order_id) && $order_id != '') {
  $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
}
if (!is_user_logged_in() || !$is_current_user_owner)
  wp_redirect(home_url());

$post_id = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT post_id FROM orders where id = %d ", array($order_id)
    )
);

if($pid != $post_id){
    wp_redirect(home_url());
}

$shop = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT post_title,post_address,post_city,post_region,post_zip FROM wp_geodir_gd_place_detail where post_id = %d ", array($pid)
    )
);

$customer = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM shipping_address where order_id = %d ", array($order_id)
    )
);



require('pdf/fpdf.php');
define('FPDF_FONTPATH','font/');
class PDF extends FPDF{

    function reciever_address($customer){

        $w = strlen($customer->address)+20;

        $this->Cell($w,5,' ',0, 0,'L',0);   // empty cell with left,top, and right borders
        $this->Ln();

        $this->Cell($w,10,iconv( 'UTF-8','TIS-620','ชื่อที่อยู่ผู้รับ'), 0,0,'L',0);  // cell with left and right borders
        $this->Ln();
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$customer->name),0,0,'L',0);
        $this->Ln();
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$customer->address),0,0,'L',0);
        $this->Ln();
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$customer->district),0,0,'L',0);
        $this->Ln();
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$customer->province),0,0,'L',0);
        $this->Ln();
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$customer->postcode),0,0,'L',0);
        $this->Ln();

        $this->Cell($w,5,'',0,0,'L',0);   // empty cell with left,bottom, and right borders
        $this->Ln();

    }

    function sender_address($shop){
        
        $w = strlen($shop->post_address)+20;

        $this->SetXY(150, 110);
        $this->Cell($w,5,' ',0,0,'L',0);   // empty cell with left,top, and right borders
        $this->Ln();

        $this->SetXY(150, 115);
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620','ชื่อที่อยู่ผู้ส่ง'),0,0,'L',0);  // cell with left and right borders
        $this->Ln();

        $this->SetXY(150, 125);
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$shop->post_title),0,0,'L',0);
        $this->Ln();

        $this->SetXY(150, 135);
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$shop->post_address),0,0,'L',0);
        $this->Ln();

        $this->SetXY(150, 145);
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$shop->post_city),0,0,'L',0);
        $this->Ln();

        $this->SetXY(150, 155);
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$shop->post_region),0,0,'L',0);
        $this->Ln();

        $this->SetXY(150, 165);
        $this->Cell($w,10,iconv( 'UTF-8','TIS-620',$shop->post_zip),0,0,'L',0);
        $this->Ln();

        $this->SetXY(150, 175);
        $this->Cell($w,5,'',0,0,'L',0);   // empty cell with left,bottom, and right borders
        $this->Ln();

    }

    function print_address($shop, $customer){
        $this->AddPage();
        $this->AddFont('angsa','','angsa.php');
        $this->SetFont('angsa','',22);
        $this->reciever_address($customer);
        $this->sender_address($shop);
    }
}

$pdf = new PDF('L','mm','A4');
$pdf->print_address($shop, $customer);
$pdf->Output();

ob_end_flush(); 

?>