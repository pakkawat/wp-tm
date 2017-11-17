<footer class="footer" role="contentinfo">
  <?php global $gdf; if(!empty($gdf) && $gdf['footer-widgets']){//print_r($gdf);
			$x = $gdf['footer-widgets'];
			?>
  
  <div id="widget-footer" class="wrap cf">
    <?php if($gdf['footer-widgets']>0){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-1');?>
    </div>
    <?php }?>
    <?php if($gdf['footer-widgets']>1){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-2');?>
    </div>
    <?php }?>
    <?php if($gdf['footer-widgets']>2){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-3');?>
    </div>
    <?php }?>
    <?php if($gdf['footer-widgets']>3){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-4');?>
    </div>
    <?php }?>
  </div>
  <hr />
  <?php }?>
  <div id="inner-footer" class="wrap cf <?php echo (has_nav_menu( 'footer-links' )) ? 'footer-links-active' : ''; ?>">
    <nav role="navigation">
      <?php wp_nav_menu(array(
    					'container' => '',                              // remove nav container
    					'container_class' => 'footer-links cf',         // class of container (should you choose to use it)
    					'menu' => __( 'Footer Links', GEODIRECTORY_FRAMEWORK ),   // nav name
    					'menu_class' => 'nav footer-nav cf',            // adding custom nav class
    					'theme_location' => 'footer-links',             // where it's located in the theme
    					'before' => '',                                 // before the menu
        			'after' => '',                                  // after the menu
        			'link_before' => '',                            // before each link
        			'link_after' => '',                             // after each link
        			'depth' => 0,                                   // limit the depth of the nav
    					'fallback_cb' => 'geodirf_footer_links_fallback'  // fallback function
						)); ?>
    </nav>
    <p class="source-org copyright">
        <?php global $gdf;
        if(!empty($gdf) && $gdf['footer-copyright-text']) {
            if (strstr($gdf['footer-copyright-text'], 'wpgeodirectory.com') && !is_front_page()) {
                echo wp_strip_all_tags($gdf['footer-copyright-text']);
            } else {
                echo $gdf['footer-copyright-text'];
            }
        }
        ?>
    </p>
  </div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
<!-- end of site. what a ride! -->
