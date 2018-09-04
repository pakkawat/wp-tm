<?php /* Template Name: test add address */ ?>
<?php get_header();
wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );
?>

<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">

      <article role="article">
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline">
            <?php //the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">
          <!-- <select id="dd_province" ></select> -->
          <div id="address-content" class="wrapper-loading">
            <?php get_template_part( 'address/myaddress', 'list' ); ?>
          </div>
        </section>
        <?php // end article section ?>
        <footer class="article-footer cf"> </footer>
    </article>

  </div>

  </div>
</div>
<?php get_footer(); ?>
