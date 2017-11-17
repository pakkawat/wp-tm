<?php
/*
 * This is the default post format.
 *
 * So basically this is a regular post. if you don't want to use post formats,
 * you can just copy ths stuff in here and replace the post format thing in
 * single.php.
 *
 * The other formats are SUPER basic so you can style them as you like.
 *
 * Again, If you want to remove post formats, just delete the post-formats
 * folder and replace the function below with the contents of the "format.php" file.
*/
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article" itemscope
         itemtype="http://schema.org/BlogPosting">
    <header class="article-header">
        <h1 class="entry-title single-title" itemprop="headline">
            <?php the_title(); ?>
        </h1>

        <p class="byline vcard"> <?php printf(__( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author"><a href="%3$s" >%4$s</a></span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), esc_url( get_author_posts_url( get_the_author_meta('ID') ) ), get_the_author_meta('display_name')); ?></p>
        <meta itemprop="datePublished" content="<?php the_time( 'Y-m-d') ?>">
        <meta itemprop="dateModified" content="<?php the_modified_time( 'Y-m-d' ) ?>">
	<span itemprop="author" itemscope itemtype="http://schema.org/Person">
		<meta itemprop="name" content="<?php the_author() ?>">
	</span>
      <span itemprop="author">
          <meta itemprop="mainEntityOfPage" content="<?php the_author_link() ?>">
      </span>
        <?php
        if ( has_post_thumbnail() ) {
            $tn_id = get_post_thumbnail_id( $post->ID );
            $img = wp_get_attachment_image_src( $tn_id, 'full' );
            $width = $img[1];
            $height = $img[2];
            ?>
            <span itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
            <img src="<?php the_post_thumbnail_url(); ?>" alt="" class="sr-only">
            <meta itemprop="url" content="<?php the_post_thumbnail_url(); ?>">
            <meta itemprop="width" content="<?php echo $width; ?>">
            <meta itemprop="height" content="<?php echo $height; ?>">
        </span>
            <?php
        }
        ?>
        <?php
        global $gdf;
        if ( isset( $gdf['site_logo']) &&  isset( $gdf['site_logo']['url']) && $gdf['site_logo']['url'] ) {
            ?>
            <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                    <meta itemprop="url" content="<?php echo $gdf['site_logo']['url']; ?>">
                    <meta itemprop="width" content="<?php echo $gdf['site_logo']['width']; ?>">
                    <meta itemprop="height" content="<?php echo $gdf['site_logo']['height']; ?>">
                </div>
                <meta itemprop="name" content="<?php bloginfo( 'name' ); ?>">
            </div>
            <?php
        }
        ?>
    </header>
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
        wp_link_pages(array(
            'before' => '<div class="page-links"><span class="page-links-title">' . __('Pages:', GEODIRECTORY_FRAMEWORK) . '</span>',
            'after' => '</div>',
            'link_before' => '<span>',
            'link_after' => '</span>',
        ));
        ?>
    </section>
    <?php // end article section ?>
    <footer
        class="article-footer"> <?php printf(__('Filed under: %1$s', GEODIRECTORY_FRAMEWORK), get_the_category_list(', ')); ?>
        <?php the_tags('<p class="tags"><span class="tags-title">' . __('Tags:', GEODIRECTORY_FRAMEWORK) . '</span> ', ', ', '</p>'); ?>
    </footer>
    <?php // end article footer ?>
    <?php
    if (get_post_type() != 'gd_list') {
        comments_template();
    }
    ?>
</article>
<?php // end article ?>
