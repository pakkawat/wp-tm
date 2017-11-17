<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-single whoop-bbpress">
    <?php //geodir_breadcrumb();?>
    <div class="clearfix geodir-common">
        <div class="whoop-forum-cats">
            <div class="whoop-forum-cats-inner">
                <div class="whoop-forum-cats-l-wrap">
                    <div class="whoop-forum-cats-search-and-new">
                        <div class="whoop-forum-cats-search">
                            <form role="search" method="get" id="bbp-search-form" action="<?php bbp_search_url(); ?>">
                                <div class="whoop-forum-search-wrap">
                                    <input type="hidden" name="action" value="bbp-search-request" />
                                    <div class="whoop-forum-search-input">
                                        <input type="text" placeholder="Search Talk" value="<?php echo esc_attr( bbp_get_search_terms() ); ?>" name="bbp_search" id="bbp_search" />
                                    </div>
                                    <button type="submit" class="whoop-forum-search-btn">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php if (is_user_logged_in() && is_single()) {
                            global $post;
                            $topic_id    = bbp_get_topic_id( $post->ID );
                            $forum_id = bbp_get_topic_forum_id( $topic_id );
                            $new_link = bbp_get_forum_permalink($forum_id);
                            ?>
                        <div class="hr-line"></div>
                        <div class="whoop-forum-cats-new">
                            <a href="<?php echo $new_link; ?>#new-post" class="whoop-btn whoop-btn-primary whoop-btn-small whoop-btn-full">
                                <?php _e( 'New Conversation', GEODIRECTORY_FRAMEWORK ); ?>
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="whoop-forum-cats-list">
                        <ul class="whoop-forum-cats-list-inner">
                            <?php
                            $forum_id = bbp_get_forum_id();
                            $forum_args = array(
                                'posts_per_page' => 100,
                                'post_type' => bbp_get_forum_post_type(),
                                'order' => 'ASC'
                            );
                            $forums = query_posts( $forum_args );
                            foreach ($forums as $forum) {
                                $link = get_permalink($forum->ID);
                                if ($forum->ID == $forum_id) {
                                    $class = 'active';
                                } else {
                                    $class = '';
                                }
                            ?>
                            <li>
                                <a class="<?php echo $class; ?>" href="<?php echo $link; ?>">
                                    <div class="forum-cat-text-wrap">
                                        <?php echo $forum->post_title; ?>
                                        <div class="forum-cat-last-updated">
                                        <?php echo bbp_get_forum_last_active_time( $forum->ID ); ?>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <?php
                            }
                            wp_reset_query();
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="geodir_content" class="" role="main">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
                    <header class="article-header">
                        <h1 class="page-title" itemprop="headline">
                            <?php the_title(); ?>
                        </h1>
                        <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
                    </header>
                    <?php // end article header ?>
                    <section class="entry-content cf bb-forum" itemprop="articleBody">
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
                            <?php _e( 'This is the error message in the page.php template.', GEODIRECTORY_FRAMEWORK ); ?>
                        </p>
                    </footer>
                </article>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
