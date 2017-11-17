<?php

/**
 * BuddyPress - Users Profile
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php if( bp_current_action() != "public")
{ ?>
<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php
            add_filter('bp_is_single_item', '__return_true');
            bp_get_options_nav('settings');
            add_filter('bp_is_single_item', '__return_false');
        ?>
	</ul>
</div><!-- .item-list-tabs -->
<?php } ?>
<?php do_action( 'bp_before_profile_content' ); ?>

<div class="profile" role="main">

<?php switch ( bp_current_action() ) :

	// Edit
	case 'edit'   :
		bp_get_template_part( 'members/single/profile/edit' );
		break;

	// Change Avatar
	case 'change-avatar' :
		bp_get_template_part( 'members/single/profile/change-avatar' );
		break;

	// Compose
	case 'public' :
		bp_get_template_part( 'members/single/profile/view' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch; ?>
</div><!-- .profile -->

<?php do_action( 'bp_after_profile_content' ); ?>