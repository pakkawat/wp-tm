<?php if(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))header('X-UA-Compatible: IE=edge,chrome=1');// Google Chrome Frame for IE ?>
<!doctype html>
<!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->
<head>
<meta charset="utf-8" />
<?php // mobile meta (hooray!) ?>
<meta name="HandheldFriendly" content="True" />
<meta name="MobileOptimized" content="320" />
<meta name="viewport" content="width=device-width, initial-scale=1.0 " />
<?php global $gdf;?>
<link rel="apple-touch-icon" href="<?php echo $gdf['site_apple_touch_icon']['url']; ?>">
<link rel="icon" href="<?php echo $gdf['site_favicon']['url']; ?>">
<!--[if IE]>
			<link rel="shortcut icon" href="<?php echo $gdf['site_favicon']['url']; ?>">
<![endif]-->
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
<?php // wordpress head functions ?>
<?php wp_head(); ?>
<?php // end of wordpress head ?>
<?php // drop Google Analytics Here ?>
<?php // end analytics ?>

</head>
<script type="text/javascript" src="https://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/onesignal_iosandandroid.js"></script>
<body <?php body_class(); ?>>
<div id="container">
  <?php if ( is_front_page() ) { ?>
    <header class="tamzang_header" style="background-image: url(<?php echo get_stylesheet_directory_uri().'/images/'.rand(1,93).'.jpg'; ?>)">
      <a class="mobile-left" href="#mobile-navigation-left"><i class="fa fa-bars"></i></a>
      <div class="tamzang_header_content">
        <div class="tamzang_login_wrapper">
          <div class="arrange_unit">
            <?php if ( is_user_logged_in() ) { ?>
              <div class="whoop-account">
                  <a class="whoop-account-dd-link" href="" style="border: 2px solid #fff;">
                      <?php
                      $current_user = wp_get_current_user();
                      echo get_avatar( $current_user->ID, 28, '', '', array('class' => 'no-border-right') );
                      if ( class_exists( 'BuddyPress' ) ) {
                          $user_link = bp_get_loggedin_user_link();
                      } else {
                          $user_link = get_author_posts_url( $current_user->ID );
                      }
                      ?>
                      <i class="fa fa-caret-down"></i>
                  </a>
                  <div class="whoop-account-details whoop-dd-menu whoop-dd-menu-with-arrow">
                      <div class="whoop-dd-menu-arrow">
                      </div>
                      <div class="whoop-dd-menu-group">
                          <div class="whoop-account-info">
                              <div class="whoop-media-avatar">
                                  <div class="whoop-photo-box">
                                      <a href="<?php echo $user_link ?>">
                                          <?php echo get_avatar( $current_user->ID, 60 ); ?>
                                      </a>
                                  </div>
                              </div>
                              <div class="whoop-media-info">
                                  <ul class="whoop-user-info">
                                      <li class="whoop-user-name">
                                          <a href="<?php echo $user_link ?>">
                                              <?php echo whoop_bp_member_name(whoop_get_current_user_name($current_user)); ?>
                                          </a>

                                      </li>
                                      <li class="user-location">
                                          <b><?php echo whoop_get_user_location($current_user->ID); ?></b>
                                      </li>
                                  </ul>
                                  <ul class="user-account-stats">
                                      <?php if ( class_exists( 'BuddyPress' )  && bp_is_active( 'friends' )) { ?>
                                      <li class="whoop-friend-count">
                                          <i class="fa fa-users"></i>
                                          <?php //echo friends_get_friend_count_for_user( $current_user->ID );
                                          echo whoop_get_friend_count_for_user($current_user->ID); ?>
                                      </li>
                                      <?php } ?>
                                      <li class="whoop-review-count">
                                          <i class="fa fa-star"></i>
                                          <?php $count = geodir_get_review_count_by_user_id($current_user->ID );
                                          if($count) {
                                              echo $count;
                                          } else {
                                              echo "0";
                                          }?>
                                      </li>
                                  </ul>
                              </div>
                          </div>
                      </div>
                      <?php if ( class_exists( 'BuddyPress' ) ) { ?>
                      <ul class="whoop-dd-menu-group whoop-bold">
                              <li class="whoop-dd-menu-link">
                                  <a href="<?php echo home_url('/my-order/'); ?>">
                                      <i class="fa fa-user"></i> <?php echo 'รายการสั่งซื้อสินค้าของฉัน'; ?>
                                  </a>
                              </li>
                              <li class="whoop-dd-menu-link">
                                  <a href="<?php echo home_url('/รายชื่อร้านที่รอสั่ง/'); ?>">
                                      <i class="fa fa-user"></i> รายชื่อร้านที่รอสั่ง
                                  </a>
                              </li>
                              <li class="whoop-dd-menu-link">
                                  <a href="<?php echo bp_get_loggedin_user_link().'myshop/'; ?>">
                                      <i class="fa fa-user"></i> <?php echo 'ร้านค้าของฉัน'; ?>
                                  </a>
                              </li>
                              <li class="whoop-dd-menu-link">
                                  <a href="<?php echo $user_link; ?>">
                                      <i class="fa fa-user"></i> <?php echo __('About Me', GEODIRECTORY_FRAMEWORK); ?>
                                  </a>
                              </li>
                              <li class="whoop-dd-menu-link">
                                  <a href="<?php echo $user_link.'settings/'; ?>">
                                      <i class="fa fa-cog"></i> <?php echo __('Account Settings', GEODIRECTORY_FRAMEWORK); ?>
                                  </a>
                              </li>
                      </ul>
                      <?php } ?>
                      <ul class="whoop-dd-menu-group">
                          <li class="whoop-dd-menu-link">
                              <a href="<?php echo wp_logout_url(home_url()); ?>">
                                  <?php echo __('Log Out', GEODIRECTORY_FRAMEWORK); ?>
                              </a>
                          </li>
                      </ul>
                  </div>
              </div>
            <?php } else {
            ?>
                <div class="whoop-account">
                    <ul class="whoop-signup-btns">
                        <li>
                            <a href="<?php echo apply_filters('geodir_signup_reg_form_link', geodir_login_url()); ?>" class="tamzang-signup-button">
                                <?php echo __('Sign Up', GEODIRECTORY_FRAMEWORK); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php
            }?>
          </div>
        </div>
        <div class="tamzang_inner">
          <div class="tamzang_logo_center">
            <div> <a href='<?php echo esc_url( home_url( '/' ) ); ?>' title='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>' rel='home'><img src='<?php echo get_stylesheet_directory_uri(); ?>/images/Tm12.png' alt='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>'></a> </div>
          </div>
          <div class="tamzang_logo_center">
              <?php if ( is_active_sidebar( 'header-right' ) ) {?>
                  <?php dynamic_sidebar('header-right');?>
              <?php }?>
          </div>
          <div class="tamzang_header_wrapper">
            <nav role="navigation" class="tamzang_header_menu" id="mobile-navigation-left">
                <?php
                global $wp_query;
                wp_nav_menu(array(
                    'container' => false,                           // remove nav container
                    'container_class' => 'menu cf',                 // class of container (should you choose to use it)
                    //'menu' => __( 'The Main Menu', GEODIRECTORY_FRAMEWORK ),  // nav name // removed because it was breaking WPML lang switcher
                    'menu_class' => 'nav top-nav cf',               // adding custom nav class
                    'theme_location' => 'main-nav',                 // where it's located in the theme
                    'before' => '',                                 // before the menu
                    'after' => '',                                  // after the menu
                    'link_before' => '',                            // before each link
                    'link_after' => '',                             // after each link
                    'depth' => 0,                                   // limit the depth of the nav
                    'fallback_cb' => ''                             // fallback function (if there is one)
                )); ?>
            </nav>
          </div>
        </div>
      </div>

    </header>


  <?php } else { ?>
    <header class="header" role="banner">
      <?php global $gdf; if(!empty($gdf) && !$gdf['head-gdf-adminbar']){
    	  if(!empty($gdf) && !$gdf['head-gdf-adminbar-fixed']){
    	  ?>
      <style>html {margin-top: 31px !important;}.geodirf-ab{position:fixed;width:100%;top:0;left:0;z-index:1005;}</style>
    <?php }?>
      <div class="geodirf-ab">
        <div class="geodirf-ab-wrap">
          <div class="geodirf-ab-left">
            <?php dynamic_sidebar('admin-bar-left');?>
          </div>
          <div class="geodirf-ab-right">
            <?php dynamic_sidebar('admin-bar-right');?>
          </div>
        </div>
      </div>
      <?php }?>
      <a class="mobile-left" href="#mobile-navigation-left"><i class="fa fa-bars"></i></a>
      <?php if(!empty($gdf) && !$gdf['head-mobile-login']){?>
      <a class="mobile-right" href="#mobile-navigation-right"><i class="fa fa-user"></i></a>
      <div id="mobile-navigation-right">
        <div>
          <?php if(class_exists('geodir_loginwidget')){ the_widget( 'geodir_loginwidget',  'mobile-login-widget', '');}?>
          <?php if(class_exists('geodir_advance_search_widget')){the_widget( 'geodir_advance_search_widget',  'mobile-search-widget', '');}?>
        </div>
      </div>
      <?php }?>
      <div id="inner-header" class="wrap cf">
        <?php if ( isset( $gdf['site_logo']) &&  isset( $gdf['site_logo']['url']) && $gdf['site_logo']['url'] ) : ?>
        <div class='site-logo <?php if (has_nav_menu('main-nav')) { echo 'whoop-menu-active'; } ?>'> <a href='<?php echo esc_url( home_url( '/' ) ); ?>' title='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>' rel='home'><img src='<?php echo $gdf['site_logo']['url']; ?>' alt='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>'></a> </div>
        <?php else : ?>
        <div class='site-logo site-logo-text'>
          <h1 class='site-title'><a href='<?php echo esc_url( home_url( '/' ) ); ?>' title='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>' rel='home'>
            <?php bloginfo( 'name' ); ?>
            </a></h1>
    <!--      <h2 class='site-description'>-->
    <!--        --><?php //bloginfo( 'description' ); ?>
    <!--      </h2>-->
        </div>
        <?php endif; ?>
        <?php if(!is_page_template( 'claimbusiness.php' )) { ?>
          <div class="header-right-area <?php if ( !is_active_sidebar( 'header-right' ) ) { echo 'header-right-no-widget'; }?>">
              <?php if ( is_active_sidebar( 'header-right' ) ) {?>
                  <?php dynamic_sidebar('header-right');?>
              <?php }?>
              <nav role="navigation" id="mobile-navigation-left">
                  <?php
                  global $wp_query;
                  wp_nav_menu(array(
                      'container' => false,                           // remove nav container
                      'container_class' => 'menu cf',                 // class of container (should you choose to use it)
                      //'menu' => __( 'The Main Menu', GEODIRECTORY_FRAMEWORK ),  // nav name // removed because it was breaking WPML lang switcher
                      'menu_class' => 'nav top-nav cf',               // adding custom nav class
                      'theme_location' => 'main-nav',                 // where it's located in the theme
                      'before' => '',                                 // before the menu
                      'after' => '',                                  // after the menu
                      'link_before' => '',                            // before each link
                      'link_after' => '',                             // after each link
                      'depth' => 0,                                   // limit the depth of the nav
                      'fallback_cb' => ''                             // fallback function (if there is one)
                  )); ?>
              </nav>
          </div>
        <?php } ?>
          <?php if ( is_user_logged_in() ) { ?>
            <div class="whoop-account">
                <a class="whoop-account-dd-link" href="">
                    <?php
                    $current_user = wp_get_current_user();
                    echo get_avatar( $current_user->ID, 28 );
                    if ( class_exists( 'BuddyPress' ) ) {
                        $user_link = bp_get_loggedin_user_link();
                    } else {
                        $user_link = get_author_posts_url( $current_user->ID );
                    }
                    ?>
                    <i class="fa fa-caret-down"></i>
                </a>
                <div class="whoop-account-details whoop-dd-menu whoop-dd-menu-with-arrow">
                    <div class="whoop-dd-menu-arrow">
                    </div>
                    <div class="whoop-dd-menu-group">
                        <div class="whoop-account-info">
                            <div class="whoop-media-avatar">
                                <div class="whoop-photo-box">
                                    <a href="<?php echo $user_link ?>">
                                        <?php echo get_avatar( $current_user->ID, 60 ); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="whoop-media-info">
                                <ul class="whoop-user-info">
                                    <li class="whoop-user-name">
                                        <a href="<?php echo $user_link ?>">
                                            <?php echo whoop_bp_member_name(whoop_get_current_user_name($current_user)); ?>
                                        </a>

                                    </li>
                                    <li class="user-location">
                                        <b><?php echo whoop_get_user_location($current_user->ID); ?></b>
                                    </li>
                                </ul>
                                <ul class="user-account-stats">
                                    <?php if ( class_exists( 'BuddyPress' )  && bp_is_active( 'friends' )) { ?>
                                    <li class="whoop-friend-count">
                                        <i class="fa fa-users"></i>
                                        <?php //echo friends_get_friend_count_for_user( $current_user->ID );
                                        echo whoop_get_friend_count_for_user($current_user->ID); ?>
                                    </li>
                                    <?php } ?>
                                    <li class="whoop-review-count">
                                        <i class="fa fa-star"></i>
                                        <?php $count = geodir_get_review_count_by_user_id($current_user->ID );
                                        if($count) {
                                            echo $count;
                                        } else {
                                            echo "0";
                                        }?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php if ( class_exists( 'BuddyPress' ) ) { ?>
                    <ul class="whoop-dd-menu-group whoop-bold">
                            <li class="whoop-dd-menu-link">
                                <a href="<?php echo home_url('/my-order/'); ?>">
                                    <i class="fa fa-user"></i> <?php echo 'รายการสั่งซื้อสินค้าของฉัน'; ?>
                                </a>
                            </li>
                            <li class="whoop-dd-menu-link">
                                  <a href="<?php echo home_url('/รายชื่อร้านที่รอสั่ง/'); ?>">
                                      <i class="fa fa-user"></i> รายชื่อร้านที่รอสั่ง
                                  </a>
                              </li>
                            <li class="whoop-dd-menu-link">
                                <a href="<?php echo bp_get_loggedin_user_link().'myshop/'; ?>">
                                    <i class="fa fa-user"></i> <?php echo 'ร้านค้าของฉัน'; ?>
                                </a>
                            </li>
                            <li class="whoop-dd-menu-link">
                                <a href="<?php echo $user_link; ?>">
                                    <i class="fa fa-user"></i> <?php echo __('About Me', GEODIRECTORY_FRAMEWORK); ?>
                                </a>
                            </li>
                            <li class="whoop-dd-menu-link">
                                <a href="<?php echo $user_link.'settings/'; ?>">
                                    <i class="fa fa-cog"></i> <?php echo __('Account Settings', GEODIRECTORY_FRAMEWORK); ?>
                                </a>
                            </li>
                    </ul>
                    <?php } ?>
                    <ul class="whoop-dd-menu-group">
                        <li class="whoop-dd-menu-link">
                            <a href="<?php echo wp_logout_url(home_url()); ?>">
                                <?php echo __('Log Out', GEODIRECTORY_FRAMEWORK); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
          <?php } else {
          ?>
              <div class="whoop-account">
                  <ul class="whoop-signup-btns">
                      <li>
                          <a href="<?php echo apply_filters('geodir_signup_reg_form_link', geodir_login_url()); ?>" class="whoop-btn whoop-btn-primary whoop-signup-btn">
                              <?php echo __('Sign Up', GEODIRECTORY_FRAMEWORK); ?>
                          </a>
                      </li>
                  </ul>
              </div>
          <?php
          }?>
      </div>
    </header>
	<?php kento_notify(); ?>
<?php } ?>
<script type="text/javascript">
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>

