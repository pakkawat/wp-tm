<?php get_header(); ?>
<?php
$sidebar = false;
$full_width = false;
if (bp_is_members_directory()) {
  $sidebar = apply_filters('whoop_members_page_sidebar', true);
  if (!$sidebar) {
    $full_width = true;
  }
}
if (bp_is_activity_directory()) {
  $sidebar = apply_filters('whoop_activity_page_sidebar', true);
  if (!$sidebar) {
    $full_width = true;
  }
}
?>
<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="<?php if ($full_width) { echo "whoop-full-width-content"; } ?>" role="main">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
	      <?php do_action('whoop_bp_page_header'); ?>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">
          <?php
			// the content (pretty self explanatory huh)
			the_content();

			/*
			 * Link Pages is used in case you have posts that are set to break into
			 * multiple pages. You can remove this if you don't plan on doing that.
			 *
			 * Also, breaking content up into multiple pages is a horrible experience,
			 * so don't do it. While there are SOME edge cases where this is useful, it's
			 * mostly used for people to get more ad views. It's up to you but if you want
			 * to do it, you're wrong and I hate you. (Ok, I still love you but just not as much)
			 *
			 * http://gizmodo.com/5841121/google-wants-to-help-you-avoid-stupid-annoying-multiple-page-articles
			 *
			*/
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', GEODIRECTORY_FRAMEWORK ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );
		?>
        </section>
        <?php // end article section ?>
	      <?php do_action('whoop_bp_page_footer'); ?>
        <?php comments_template(); ?>
      </article>
      <?php endwhile; else : ?>
      <article id="post-not-found" class="hentry cf">
        <header class="article-header">
          <h1>
            <?php _e( 'Oops, Post Not Found!', GEODIRECTORY_FRAMEWORK ); ?>
          </h1>
        </header>
        <section class="entry-content">
          <p>
            <?php _e( 'Uh Oh. Something is missing. Try double checking things.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </section>
        <footer class="article-footer">
          <p>
            <?php _e( 'This is the error message in the buddypress.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>
    <?php
    if ($sidebar) {
      get_sidebar('bp-details');
    }
    ?>
  </div>
</div>
<?php get_footer(); ?>
