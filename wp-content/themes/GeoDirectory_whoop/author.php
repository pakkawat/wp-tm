<?php get_header(); ?>
<div id="geodir_wrapper" class="geodir-archive">
    <?php //geodir_breadcrumb();?>
    <div class="clearfix geodir-common">
        <div id="geodir_content" class="" role="main">
            <h1 class="entry-title"><?php $author_obj = $wp_query->get_queried_object();
                //echo $author_obj->display_name; ?></h1>
            <?php
            // user profile text
            echo "<h1 class='whoop-author-p-title'>".ucfirst(esc_attr(sprintf( __("%s's Profile", GEODIRECTORY_FRAMEWORK), $author_obj->display_name )))."</h1>";

            // user listings
            echo "<h4>".__("Listings", GEODIRECTORY_FRAMEWORK)."</h4>";
            geodir_user_show_listings($author_obj->ID,'link');

            // user favs
            $fav_count = geodir_user_favourite_listing_count($author_obj->ID);
            if(!empty($fav_count )){
                echo "<h4>".__("Favorites", GEODIRECTORY_FRAMEWORK)."</h4>";
                geodir_user_show_favourites($author_obj->ID,'link');
            }
            ?>
        </div>
        <?php get_sidebar('blog-listing'); ?>
    </div>
</div>
<?php get_footer(); ?>
