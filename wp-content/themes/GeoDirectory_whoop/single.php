<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-single geodir-single-post">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <?php
		  /*
		   * Ah, post formats. Nature's greatest mystery (aside from the sloth).
		   *
		   * So this function will bting in the needed template file depending on what the post
		   * format is. The different post formats are located in the post-formats folder.
		   *
		   *
		   * REMEMBER TO ALWAYS HAVE A DEFAULT ONE NAMED "format.php" FOR POSTS THAT AREN'T
		   * A SPECIFIC POST FORMAT.
		   *
		   * If you want to remove post formats, just delete the post-formats folder and
		   * replace the function below with the contents of the "format.php" file.
		  */
          get_template_part( 'post-formats/format', get_post_format() );
	  ?>
      <?php endwhile; ?>
      <?php else : ?>
      <article id="post-not-found" class=">
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
            <?php _e( 'This is the error message in the single.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>
    <?php get_sidebar('blog-details'); ?>
  </div>
</div>
<?php get_footer(); ?>
