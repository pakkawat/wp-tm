<?php
//Rename Favourite to Bookmark
function whoop_geodir_add_favourite_text($text)
{
    $text = __('Add to Bookmarks', GEODIRECTORY_FRAMEWORK);
    return $text;
}
add_filter('geodir_add_favourite_text', 'whoop_geodir_add_favourite_text');

function whoop_geodir_remove_favourite_text($text)
{
    $text = __('Remove from Bookmarks', GEODIRECTORY_FRAMEWORK);
    return $text;
}
add_filter('geodir_remove_favourite_text', 'whoop_geodir_remove_favourite_text');

function whoop_geodir_favourite_text($text)
{
    $text = __('Bookmark', GEODIRECTORY_FRAMEWORK);
    return $text;
}
add_filter('geodir_favourite_text', 'whoop_geodir_favourite_text');

function whoop_geodir_unfavourite_text($text)
{
    $text = __('Bookmarked', GEODIRECTORY_FRAMEWORK);
    return $text;
}
add_filter('geodir_unfavourite_text', 'whoop_geodir_unfavourite_text');

function whoop_geodir_favourite_icon($icon)
{
    $icon = 'fa fa-bookmark';
    return $icon;
}
add_filter('geodir_favourite_icon', 'whoop_geodir_favourite_icon');

//geodir_buddypress addon
function whoop_geodir_buddy_favourites_text($text)
{
    $text = __('Bookmarks', GEODIRECTORY_FRAMEWORK);
    return $text;
}
add_filter('gdbuddypress_favourites_text', 'whoop_geodir_buddy_favourites_text');

function whoop_geodir_buddy_favourites_slug($slug)
{
    $slug = 'bookmarks';
    return $slug;
}
add_filter('gdbuddypress_favourites_slug', 'whoop_geodir_buddy_favourites_slug');
