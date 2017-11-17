<?php get_header(); ?>
<div id="geodir_wrapper" class="geodir-404">
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main">
      <article id="post-not-found" class="hentry cf">
        <header class="article-header">
          <h1>
            <?php _e( 'Epic 404 - Article Not Found', GEODIRECTORY_FRAMEWORK ); ?>
          </h1>
        </header>
        <section class="entry-content">
          <p>
            <?php _e( 'The article you were looking for was not found, but maybe try looking again!', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </section>
        <section class="search">
          <p>
            <?php get_search_form(); ?>
          </p>
        </section>
        <footer class="article-footer">
        </footer>
      </article>
    </div>
  </div>
</div>
<?php get_footer(); ?>
