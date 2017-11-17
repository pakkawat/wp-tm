<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-home">
    <div class="clearfix geodir-common">
        <div id="geodir_content" class="" role="main">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
                    <header class="article-header">
                        <h1 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
                                <?php the_title(); ?>
                            </a></h1>
                        <p class="byline vcard"> <?php printf(__( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author"><a href="%3$s" >%4$s</a></span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), esc_url( get_author_posts_url( get_the_author_meta('ID') ) ), get_the_author_meta('display_name')); ?></p>
                    </header>
                    <section class="entry-content cf">
                        <?php the_content(); ?>
                    </section>
                    <footer class="article-footer cf">
                        <p class="footer-comment-count">
                            <?php comments_number( __( '<span>No</span> Comments', GEODIRECTORY_FRAMEWORK ), __( '<span>One</span> Comment', GEODIRECTORY_FRAMEWORK ), _n( '<span>%</span> Comments', '<span>%</span> Comments', get_comments_number(), GEODIRECTORY_FRAMEWORK ) );?>
                        </p>
                        <?php printf( __( '<p class="footer-category">Filed under: %1$s</p>', GEODIRECTORY_FRAMEWORK ), get_the_category_list(', ') ); ?>
                        <?php the_tags( '<p class="footer-tags tags"><span class="tags-title">' . __( 'Tags:', GEODIRECTORY_FRAMEWORK ) . '</span> ', ', ', '</p>' ); ?>
                    </footer>
                </article>
            <?php endwhile; ?>
                <?php geodirf_page_navi(); ?>
            <?php else : ?>
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
                            <?php _e( 'This is the error message in the index.php template.', GEODIRECTORY_FRAMEWORK ); ?>
                        </p>
                    </footer>
                </article>
            <?php endif; ?>
        </div>
        <?php get_sidebar('page-details'); ?>
    </div>
</div>
<?php get_footer(); ?>
