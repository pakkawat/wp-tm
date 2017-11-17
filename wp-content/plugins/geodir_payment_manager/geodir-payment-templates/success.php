<?php
/**
 * Template to display page content when payment status is success. 
 *
 * @since 1.0.0
 * @package GeoDirectory_Payment_Manager
 */
 
get_header();
###### WRAPPER OPEN ######
/** This action is documented in geodir-payment-templates/cancel.php */
do_action('geodir_wrapper_open', 'success-page', 'geodir-wrapper', '');

	###### TOP CONTENT ######
	/** This action is documented in geodir-payment-templates/cancel.php */
	do_action('geodir_before_main_content');
	/** This action is documented in geodir-payment-templates/cancel.php */
	do_action('geodir_success_before_main_content');

	###### MAIN CONTENT WRAPPERS OPEN ######
	/** This action is documented in geodir-payment-templates/cancel.php */
	do_action('geodir_wrapper_content_open', 'success-page', 'geodir-wrapper-content', '');
    
    /**
     * Adds page content to the page.
     *
     * @since 1.4.1
     *
     * @param string 'before' Position to add the post content. 'before' or 'after'.
     * @param string 'home-page' Current page name.
     */
    do_action('geodir_add_page_content', 'before', 'info-page');

$title = isset($_REQUEST['renew']) ? RENEW_SUCCESS_TITLE : POSTED_SUCCESS_TITLE;

if (isset($postid) && $postid != '') {
    $_REQUEST['pid'] = $postid;
}
$paymentmethod = geodir_get_post_meta((int)$_REQUEST['pid'], 'paymentmethod', true);
$paid_amount = geodir_get_post_meta((int)$_REQUEST['pid'], 'paid_amount', true);

$invoice_info = array();
if (!empty($_REQUEST['inv']) && ($invoice_info = geodir_get_invoice((int)$_REQUEST['inv']))) {
    $paymentmethod = $invoice_info->paymentmethod != '' ? $invoice_info->paymentmethod : $paymentmethod;
    $paid_amount = $invoice_info->paied_amount != '' ? $invoice_info->paied_amount : $paid_amount;
}

$payment_options = $paymentmethod != '' ? get_payment_options($paymentmethod) : NULL;
$paid_amount = geodir_payment_price($paid_amount);

if ($paymentmethod == 'prebanktransfer') {
    $filecontent = stripslashes(get_option('post_pre_bank_trasfer_msg_content'));
    if (!$filecontent) {
        $filecontent = POSTED_SUCCESS_PREBANK_MSG;
    }
} else {
    $filecontent = stripslashes(get_option('post_added_success_msg_content'));
    if (!$filecontent) {
        $filecontent = POSTED_SUCCESS_MSG;
    }
}
if (!$_REQUEST['pid']) {
    $title = PAYMENT_FAIL_TITLE;
    $filecontent = PAYMENT_FAIL_MSG;
}

/**
 * Filter the page title for payment success page.
 *
 * @since 1.3.8
 *
 * @param string $title Page title.
 * @param object $invoice_info The invoice data.
 */
$title = apply_filters('geodir_payment_template_success_title', $title, $invoice_info);

/**
 * Filter the page success message for payment success page.
 *
 * @since 1.3.8
 *
 * @param string $filecontent The message content.
 * @param object $invoice_info The invoice data.
 */
$filecontent = apply_filters('geodir_payment_template_success_msg', $filecontent, $invoice_info);

$order_id = $_REQUEST['pid'];
$bank_name = '';
$account_sortcode = '';
$account_number = '';
$bank_reference_number = '';

if ($paymentmethod == 'prebanktransfer' && !empty($payment_options)) {
    $bank_name = $payment_options['bankinfo'];
    $account_sortcode = $payment_options['bank_accountsc'];
    $account_number = $payment_options['bank_accountid'];
    $bank_reference_number = isset($payment_options['bank_reference_number']) ? $payment_options['bank_reference_number'] : '';
}

$post_link = get_permalink($_REQUEST['pid']);
$store_name = get_option('blogname');
$store_name_url = '<a href="' . home_url() . '">' . $store_name . '</a>';

$search_array = array('[#order_amt#]', '[#bank_name#]', '[#account_sortcode#]', '[#account_number#]', '[#bank_reference_number#]', '[#orderId#]', '[#site_name#]', '[#submitted_information_link#]', '[#submited_information_link#]', '[#site_name_url#]');
/**
 * Filter the array of success message search vars.
 *
 * @since 1.3.8
 *
 * @param array $search_array Array of searched vars.
 * @param object $invoice_info The invoice data.
 */
$search_array = apply_filters('geodir_payment_template_success_search_vars', $search_array, $invoice_info);

$replace_array = array($paid_amount, $bank_name, $account_sortcode, $account_number, $bank_reference_number, $order_id, $store_name, $post_link, $post_link, $store_name_url);
/**
 * Filter the array of success message replace vars.
 *
 * @since 1.3.8
 *
 * @param array $replace_array Array of replaced vars.
 * @param object $invoice_info The invoice data.
 */
$replace_array = apply_filters('geodir_payment_template_success_replace_vars', $replace_array, $invoice_info);

$filecontent = str_replace($search_array, $replace_array, $filecontent);
?>
<div class="geodir_preview_section">
    <h1><?php echo $title; ?></h1>
    <?php echo $filecontent; ?>
</div> <!-- geodir_preview_section #end -->
<?php
    /** This action is documented in geodir-payment-templates/success.php */
    do_action('geodir_add_page_content', 'after', 'info-page');

	###### MAIN CONTENT WRAPPERS CLOSE ######
	/** This action is documented in geodir-payment-templates/cancel.php */
	do_action('geodir_after_main_content');
	/** This action is documented in geodir-payment-templates/cancel.php */
	do_action('geodir_wrapper_content_close', 'details-page');

	###### SIDEBAR ######
	/** This action is documented in geodir-payment-templates/cancel.php */
	do_action('geodir_author_sidebar_right');

###### WRAPPER CLOSE ######	
/** This action is documented in geodir-payment-templates/cancel.php */
do_action('geodir_wrapper_close', 'success-page');
get_footer();