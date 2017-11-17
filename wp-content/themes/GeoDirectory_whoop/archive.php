<?php get_header(); ?>
<div id="geodir_wrapper" class="geodir-archive">
    <?php //geodir_breadcrumb();?>
    <div class="clearfix geodir-common">
        <div id="geodir_content" class="" role="main">
            <?php if (is_category()) { ?>
                <h1 class="archive-title h2"> <span>
        <?php _e('Posts Categorized:', GEODIRECTORY_FRAMEWORK); ?>
        </span>
                    <?php single_cat_title(); ?>
                </h1>
            <?php } elseif (is_tag()) { ?>
                <h1 class="archive-title h2"> <span>
        <?php _e('Posts Tagged:', GEODIRECTORY_FRAMEWORK); ?>
        </span>
                    <?php single_tag_title(); ?>
                </h1>
            <?php } elseif (is_author()) {
                $author_id = get_query_var('author');
                ?>
                <h1 class="archive-title h2"> <span>
        <?php _e('Posts By:', GEODIRECTORY_FRAMEWORK); ?>
        </span>
                    <?php the_author_meta('display_name', $author_id); ?>
                </h1>
            <?php } elseif (is_day()) { ?>
                <h1 class="archive-title h2"> <span>
        <?php _e('Daily Archives:', GEODIRECTORY_FRAMEWORK); ?>
        </span>
                    <?php the_time('l, F j, Y'); ?>
                </h1>
            <?php } elseif (is_month()) { ?>
                <h1 class="archive-title h2"> <span>
        <?php _e('Monthly Archives:', GEODIRECTORY_FRAMEWORK); ?>
        </span>
                    <?php the_time('F Y'); ?>
                </h1>
            <?php } elseif (is_year()) { ?>
                <h1 class="archive-title h2"> <span>
        <?php _e('Yearly Archives:', GEODIRECTORY_FRAMEWORK); ?>
        </span>
                    <?php the_time('Y'); ?>
                </h1>
            <?php } elseif (is_post_type_archive()) { ?>
                <h1 class="archive-title h2">
          <span>
            <?php post_type_archive_title(); ?>
          </span>
                </h1>
            <?php } ?>
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article">
                    <header class="article-header">
                        <h3 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark"
                                                      title="<?php the_title_attribute(); ?>">
                                <?php the_title(); ?>
                            </a></h3>

                        <p class="byline vcard">
                            <?php
                            printf(__('Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span> <span class="amp">&</span> filed under %4$s.', GEODIRECTORY_FRAMEWORK), get_the_time('c'), get_the_time(__('F jS, Y', GEODIRECTORY_FRAMEWORK)), get_the_author_meta('display_name'), get_the_category_list(', '));
                            ?>
                        </p>
                    </header>
                    <section class="entry-content cf">
                        <?php the_post_thumbnail('geodirf-thumb-300'); ?>
                        <?php the_excerpt(); ?>
                    </section>
                    <footer class="article-footer"></footer>
                </article>
            <?php endwhile; ?>
                <?php geodirf_page_navi(); ?>
            <?php else : ?>
                <article id="post-not-found" class="hentry cf">
                    <header class="article-header">
                        <h1>
                            <?php _e('Oops, Post Not Found!', GEODIRECTORY_FRAMEWORK); ?>
                        </h1>
                    </header>
                    <section class="entry-content">
                        <p>
                            <?php _e('Uh Oh. Something is missing. Try double checking things.', GEODIRECTORY_FRAMEWORK); ?>
                        </p>
                    </section>
                    <footer class="article-footer">
                        <p>
                            <?php _e('This is the error message in the archive.php template.', GEODIRECTORY_FRAMEWORK); ?>
                        </p>
                    </footer>
                </article>
            <?php endif; ?>
        </div>
        <?php get_sidebar('blog-listing'); ?>
    </div>
</div>
<?php get_footer(); ?>
