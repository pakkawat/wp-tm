<div id="buddypress">

	<div id="item-nav">
		<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
			<ul>

				<?php bp_get_displayed_user_nav(); ?>

				<?php do_action( 'bp_member_options_nav' ); ?>

			</ul>
			<div class="whoop-member-title">
				<h1>
					<?php echo whoop_bp_member_name(bp_get_displayed_user_displayname()).__('\'s Profile', GEODIRECTORY_FRAMEWORK); ?>
				</h1>
                <?php if( bp_is_active( 'friends' ) ) {
                    whoop_check_friendship();
                } ?>
			</div>
		</div>
	</div><!-- #item-nav -->

	<?php do_action( 'bp_before_member_home_content' ); ?>
	<div class="whoop-member-item-wrap">
        <?php do_action( 'template_notices' ); ?>
	<div id="item-header" role="complementary">

        <?php bp_get_template_part( 'members/single/member-header' ) ?>

        <div class="whoop-profile-fields">
            <span><?php echo __('Member Since', GEODIRECTORY_FRAMEWORK) ?></span>
            <p><?php echo date_i18n("F Y", strtotime(get_userdata(bp_displayed_user_id())->user_registered));  ?></p>

            <?php if ( bp_has_profile() ) : ?>

                <?php while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

                    <?php if ( bp_profile_group_has_fields() ) : ?>

                                <?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

                                    <?php if ( bp_field_has_data() && @bp_get_the_profile_field_value()) : ?>

                                            <span><?php bp_the_profile_field_name(); ?></span>

                                            <?php bp_the_profile_field_value(); ?>

                                    <?php endif; ?>

                                <?php endwhile; ?>

                    <?php endif; ?>

                <?php endwhile; ?>

            <?php endif; ?>
        </div>

	</div><!-- #item-header -->

	<div id="item-body" role="main">

		<?php do_action( 'bp_before_member_body' );

		if ( bp_is_user_activity() || !bp_current_component() ) :
			bp_get_template_part( 'members/single/activity' );

		elseif ( bp_is_user_blogs() ) :
			bp_get_template_part( 'members/single/blogs'    );

		elseif ( bp_is_user_friends() ) :
			bp_get_template_part( 'members/single/friends'  );

		elseif ( bp_is_user_groups() ) :
			bp_get_template_part( 'members/single/groups'   );

		elseif ( bp_is_user_messages() ) :
			bp_get_template_part( 'members/single/messages' );

		elseif ( bp_is_user_profile() ) :
			bp_get_template_part( 'members/single/profile'  );

		elseif ( bp_is_user_forums() ) :
			bp_get_template_part( 'members/single/forums'   );

		elseif ( bp_is_user_notifications() ) :
			bp_get_template_part( 'members/single/notifications' );

		elseif ( bp_is_user_settings() ) :
			bp_get_template_part( 'members/single/settings' );

		// If nothing sticks, load a generic template
		else :
			bp_get_template_part( 'members/single/plugins'  );

		endif;

		do_action( 'bp_after_member_body' ); ?>

	</div><!-- #item-body -->
        <?php do_action( 'bp_whoop_sidebar' ); ?>
	</div>
	<?php do_action( 'bp_after_member_home_content' ); ?>

</div><!-- #buddypress -->
