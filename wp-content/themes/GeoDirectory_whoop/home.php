<?php
get_header(); ?>

<div id="geodir_wrapper" class="geodir-single geodir-home-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main">

      <?php
      $default_posts_per_page = get_option( 'posts_per_page' );
      $temp = $wp_query; $wp_query= null;
        $wp_query = new WP_Query(); $wp_query->query('showposts='.$default_posts_per_page . '&paged='.$paged);
      if ($wp_query->have_posts()) : while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
     <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
        <header class="article-header">
          <h3 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
            <?php the_title(); ?>
            </a></h3>
          <p class="byline vcard">
            <?php
                                        printf(__( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author"><a href="%3$s" >%4$s</a></span> <span class="amp">&</span> filed under %5$s.', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(__( 'F jS, Y', GEODIRECTORY_FRAMEWORK )), get_author_posts_url( get_the_author_meta( 'ID' ) ), get_the_author_meta( 'display_name' ), get_the_category_list(', '));
                                    ?>
          </p>
        </header>
        <section class="entry-content cf">
          <?php the_post_thumbnail( 'geodirf-thumb-300' ); ?>
          <?php the_excerpt(); ?>
        </section>
        <footer class="article-footer"> </footer>
      </article>
      <?php endwhile; geodirf_page_navi(); else : ?>
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
            <?php _e( 'This is the error message in the page.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>
    <?php get_sidebar('page-details'); ?>
  </div>
</div>
<?php get_footer(); ?>
