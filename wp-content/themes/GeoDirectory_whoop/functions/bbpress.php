<?php
//bbpress
add_filter('bbp_no_breadcrumb', '__return_true' );
add_filter('bbp_get_user_subscribe_link', '__return_false');

add_filter( 'bbp_get_single_forum_description', 'whoop_return_blank' );
add_filter( 'bbp_get_single_topic_description', 'whoop_return_blank' );

function whoop_bbp_get_reply_author_link() {
    $reply_id = bbp_get_reply_id( 0 );
    $author_url = bbp_get_reply_author_url( $reply_id );
    $author_id = bbp_get_reply_author_id( $reply_id );
    $avatar = bbp_get_reply_author_avatar( $reply_id, 60 );
    $name = bbp_get_reply_author_display_name( $reply_id );
    ?>
    <div class="comment-meta comment-author vcard">
        <?php echo $avatar; ?>
        <cite><b class="reviewer">
                <a href="<?php echo $author_url; ?>" class="url"><?php echo whoop_bp_member_name($name); ?></a>
            </b>
        </cite>
        <?php whoop_get_user_stats($author_id); ?>
    </div>
<?php
}

include_once(get_template_directory() .'/whoop-widgets/bbpress/today-in-talk.php');