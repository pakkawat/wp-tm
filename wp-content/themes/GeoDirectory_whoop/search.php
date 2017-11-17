<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-search-page">
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main">
      <h1 class="archive-title"><span>
        <?php _e( 'Search Results for:', GEODIRECTORY_FRAMEWORK ); ?>
        </span> <?php echo esc_attr(get_search_query()); ?></h1>
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article">
        <header class="article-header">
          <h3 class="search-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
            <?php the_title(); ?>
            </a></h3>
            <p class="byline vcard"> <?php printf(__( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author"><a href="%3$s" >%4$s</a></span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), esc_url( get_author_posts_url( get_the_author_meta('ID') ) ), get_the_author_meta('display_name')); ?></p>
        </header>
        <section class="entry-content">
          <?php the_excerpt( '<span class="read-more">' . __( 'Read more &raquo;', GEODIRECTORY_FRAMEWORK ) . '</span>' ); ?>
        </section>
        <footer class="article-footer"> <?php printf( __( 'Filed under: %1$s', GEODIRECTORY_FRAMEWORK ), get_the_category_list(', ') ); ?>
          <?php the_tags( '<p class="tags"><span class="tags-title">' . __( 'Tags:', GEODIRECTORY_FRAMEWORK ) . '</span> ', ', ', '</p>' ); ?>
        </footer>
        <!-- end article footer -->
      </article>
      <?php endwhile; ?>
      <?php geodirf_page_navi(); ?>
      <?php else : ?>
      <article id="post-not-found" class="hentry cf">
        <header class="article-header">
          <h1>
            <?php _e( 'Sorry, No Results.', GEODIRECTORY_FRAMEWORK ); ?>
          </h1>
        </header>
        <section class="entry-content">
          <p>
            <?php _e( 'Try your search again.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </section>
        <footer class="article-footer">
          <p>
            <?php _e( 'This is the error message in the search.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>
    <?php get_sidebar('blog-listing'); ?>
  </div>
</div>
<?php get_footer(); ?>
