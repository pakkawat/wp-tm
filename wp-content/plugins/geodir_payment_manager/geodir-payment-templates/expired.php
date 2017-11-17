<?php
/**
 * Template to display page content of the expired listing. 
 *
 * @since 2.0.0
 * @package GeoDirectory_Payment_Manager
 */
 
get_header();

###### WRAPPER OPEN ######
/** This action is documented in geodir-payment-templates/cancel.php */
do_action('geodir_wrapper_open', 'expired-page', 'geodir-wrapper', '');

    ###### TOP CONTENT ######
    /**
     * Called before the main content and the page specific content.
     *
     * @since 2.0.0
     * @param string $type Page type.
     */
    do_action('geodir_top_content', 'expired-page');
    
    /**
     * Calls the top section widget area and the breadcrumbs on the details page.
     *
     * @since 2.0.0
     */
    do_action('geodir_expired_before_main_content');
    
    /** This action is documented in geodirectory-templates/add-listing.php */
    do_action('geodir_before_main_content', 'expired-page');

    ###### MAIN CONTENT WRAPPERS OPEN ######
    /** This action is documented in geodir-payment-templates/cancel.php */
    do_action('geodir_wrapper_content_open', 'expired-page', 'geodir-wrapper-content', '');
    
        /** This action is documented in geodir-payment-templates/success.php */
        do_action('geodir_add_page_content', 'before', 'expired-page');
        
        global $post;
        
        $title = get_the_title();
        $content = __( 'This listing is no longer available!', 'geodir_payments' );
        
        /**
         * Filter the expired listing title.
         *
         * @since 2.0.0
         * @param string $title The listing title.
         * @param array $post The WP_Post object.
         */
        $title = apply_filters( 'geodir_payment_expired_listing_title', $title, $post );
        /**
         * Filter the expired listing content.
         *
         * @since 2.0.0
         * @param string $title The listing content.
         * @param array $post The WP_Post object.
         */
        $content = apply_filters( 'geodir_payment_expired_listing_content', $content, $post );
        ?>
        <div class="geodir-expired-page">
            <?php if ( !empty( $title ) ) { ?>
                <h1 class="entry-title"><?php echo $title ;?></h1>
            <?php } ?>
            <div class="entry-content"><?php echo $content ;?></div>
        </div>
        <?php

        /** This action is documented in geodir-payment-templates/success.php */
        do_action('geodir_add_page_content', 'after', 'expired-page');

    ###### MAIN CONTENT WRAPPERS CLOSE ######
    /** This action is documented in geodir-payment-templates/cancel.php */
    do_action('geodir_wrapper_content_close', 'expired-page');
    
    /** This action is documented in geodir-payment-templates/cancel.php */
    do_action('geodir_after_main_content');

###### WRAPPER CLOSE ######	
/** This action is documented in geodir-payment-templates/cancel.php */
do_action('geodir_wrapper_close', 'expired-page');

get_footer();