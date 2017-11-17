
<div id="gd-sidebar-wrapper" class="sidebar" role="complementary">
  <?php if ( is_active_sidebar( 'page-details' ) ) : ?>
  <?php dynamic_sidebar( 'page-details' ); ?>
  <?php else : ?>
  <?php
	  /*
	   * This content shows up if there are no widgets defined in the backend.
	  */
  ?>
  <div class="no-widgets">
    <p>
      <?php _e( 'This is a widget ready area. Add some and they will appear here.', GEODIRECTORY_FRAMEWORK );  ?>
    </p>
  </div>
  <?php endif; ?>
</div>
