<?php get_header(); ?>
    <div id="geodir_wrapper" class="geodir-archive">
        <?php //geodir_breadcrumb();?>
        <div class="clearfix geodir-common">
            <div id="geodir_content" class="whoop-full-width" role="main">
                <ul class="gd-list-items-ul">
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <li>
                            <div id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
                                <div class="gd-list-item-author">
                                    <div class="comment-meta comment-author vcard">
                                        <?php
                                        global $post;
                                        $user = get_user_by('id', $post->post_author);
                                        $name = whoop_bp_member_name(whoop_get_current_user_name($user));
                                        if (class_exists('BuddyPress')) {
                                            $user_link = bp_core_get_user_domain($post->post_author);
                                            $permalink = esc_url(add_query_arg(array('list_id' => $post->ID), $user_link.'lists/'));
                                        } else {
                                            $permalink = get_the_permalink();
                                            $user_link = get_author_posts_url( $post->post_author );
                                        }
                                        ?>
                                        <?php echo get_avatar($post->post_author, 60); ?>
                                        <cite><b class="reviewer">
                                                <a href="<?php echo $user_link; ?>" class="url"><?php echo $name; ?></a>
                                            </b>
                                        </cite>
                                        <?php whoop_get_user_stats($post->post_author); ?>
                                    </div>
                                </div>
                                <div class="gd-list-item-title">
                                    <h3>
                                        <a href="<?php echo $permalink; ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>
                                    <p>
                                        <?php echo wp_trim_words(stripcslashes(strip_tags(get_the_content())), 20); ?>
                                    </p>
                                </div>
                                <div class="gd-list-item-updated">
                                    <?php _e( 'Updated', GEODIRECTORY_FRAMEWORK ); ?> <?php the_modified_time( 'm/d/Y' ); ?>
                                </div>
                            </div>
                        </li>
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
                        </article>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
<?php get_footer(); ?>