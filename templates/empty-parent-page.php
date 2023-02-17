<?php
/*
 * Template Name: Empty Parent Page
 * Description: Redirects empty parent page to first child page
 */

# Parent menu goes to first child page
# askwpgirl.com/redirect-parent-page-first-child-page-wordpress
$child_page = get_pages( "child_of=" . $post->ID . "&sort_column=menu_order" );
if ( $child_page ) {
    $parent_page = $child_page[0];
    wp_redirect( get_permalink( $parent_page->ID ) );
}