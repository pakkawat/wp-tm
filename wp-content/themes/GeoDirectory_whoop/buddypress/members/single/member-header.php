<?php

/**
 * BuddyPress - Users Header
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php do_action( 'bp_before_member_header' ); ?>

<div id="item-header-avatar">
	<a href="<?php bp_displayed_user_link(); ?>">

		<?php bp_displayed_user_avatar( 'type=full&width=100&height=100' ); ?>

	</a>
</div><!-- #item-header-avatar -->

<div id="item-header-content">

	<?php do_action( 'bp_before_member_header_meta' ); ?>

	<div id="item-meta">

        <div class="whoop-member-profile-info">
            <?php $user_domain = whoop_bp_get_user_domain(); ?>
        <?php if( bp_is_active( 'friends' )) { ?>
            <span class="whoop-friend-count"><i class="fa fa-users"></i>
                <a href="<?php echo $user_domain; ?>friends/">
                    <?php //echo friends_get_friend_count_for_user( bp_displayed_user_id());
                    echo  whoop_get_friend_count_for_user(bp_displayed_user_id());?>
                    <?php echo __('Friends', GEODIRECTORY_FRAMEWORK); ?>
                </a>
            </span>
        <?php } ?>
            <span class="whoop-review-count"><i class="fa fa-star"></i>
                <a href="<?php echo $user_domain; ?>reviews/">
                    <?php $count = geodir_get_review_count_by_user_id(bp_displayed_user_id());
                    if($count) {
                        echo $count;
                    } else {
                        echo "0";
                    }?>
                    <?php echo __('Reviews', GEODIRECTORY_FRAMEWORK); ?>
                </a>
            </span>
        </div>

		<div id="item-buttons">

			<?php do_action( 'bp_member_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php
		/***
		 * If you'd like to show specific profile fields here use:
		 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
		 */
		 do_action( 'bp_profile_header_meta' );

		 ?>

	</div><!-- #item-meta -->

</div><!-- #item-header-content -->

<?php do_action( 'bp_after_member_header' ); ?>