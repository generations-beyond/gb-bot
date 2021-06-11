<?php 
/*
*Team Post type options
*/
function gbbot_cpt_settings($option) {
	$gbbot_team_cpt_enable = get_option('gbbot_team_cpt_enable');
	$gbbot_team_post_label = get_option('gbbot_team_post_label');
	$gbbot_team_post_type = get_option('gbbot_team_post_type');

    switch ($option) {
        case 'enabled':
            $value = $gbbot_team_cpt_enable;
            break;
        case 'label':
            $value = $gbbot_team_post_label;
            break;
        case 'type':
            $value = $gbbot_team_post_type;
            break;
        default:
            $value = false;
            break;
    }
    return $value;
}

/** 
*  CREATE CUSTOM POST TYPE
*/
function gbbot_register_cpt() {

    /**
     * Post Type: $post_label
     */
    $post_label = gbbot_cpt_settings("label");
    $post_type = gbbot_cpt_settings("type");

    $labels = [
        "name" => __( "$post_label", "custom-post-type-ui" ),
        "singular_name" => __( "$post_label", "custom-post-type-ui" ),
    ];

    $args = [
        "label" => __( "$post_label", "custom-post-type-ui" ),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => [ "slug" => "$post_type", "with_front" => true ],
        "query_var" => true,
        "menu_icon" => "dashicons-admin-users",
        "supports" => [ "title", "thumbnail" ],
        "show_in_graphql" => false,
    ];

    register_post_type( "$post_type", $args );

}

/**
 * Register meta box(es).
 */
function gbtc_cpt_register_meta_boxes() {
    $post_label = gbbot_cpt_settings("label");
    $post_type = gbbot_cpt_settings("type");
    
    add_meta_box( $post_type.'-details-group', $post_label." Details", 'gbtc_team_details_display_callback', $post_type );
}
add_action( 'add_meta_boxes', 'gbtc_cpt_register_meta_boxes' );



/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function gbtc_team_details_display_callback( $post ) {
    // # Display code/markup goes here. Don't forget to include nonces!

    // Get settings
    $post_label = gbbot_cpt_settings("label");
    $post_type = gbbot_cpt_settings("type");

    // https://developer.wordpress.org/reference/functions/wp_nonce_field/
    // We will check the nonce when we are saving the field value.
    $nonce_file = $post_type.'_details_nonce';
    wp_nonce_field( basename( __FILE__ ), "$nonce_file" );


    // Get or create field variables
    $team_name_first = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-name-first', true );
    $team_name_middle = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-name-middle', true );
    $team_name_last = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-name-last', true );
    $team_title = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-title', true );
    $team_description = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-description', true );
    $team_contact_phone = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-contact-phone', true );
    $team_contact_extension = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-contact-extension', true );
    $team_contact_email = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-contact-email', true );
    $team_social_linkedin = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-social-linkedin', true );
    $team_social_instagram = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-social-instagram', true );
    $team_social_twitter = get_post_meta( $post->ID, '_gbcpt_'.$post_type.'-social-twitter', true );

    // html and style for metabox
    include 'partials/team-metabox.php';
}

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function gbtc_save_meta_box( $post_id ) {
    /** 
     * # security check
     * 
     * We need to verify this came from the our screen and with proper authorization, 
     * because save_post can be triggered at other times.  
     * 
     * Don't forget to include nonce checks!
    */

    // Get settings
    $post_label = gbbot_cpt_settings("label");
    $post_type = gbbot_cpt_settings("type");

    // Check if our nonce is set.
    $nonce_file = $post_type.'_details_nonce';
    if ( ! isset( $_POST["$nonce_file"] ) ) {
        return $post_id;
    }

    $nonce = $_POST["$nonce_file"];

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce, basename( __FILE__ ) ) ) {
        return $post_id;
    }


    // If this is an autosave, our form has not been submitted,
    // so we don't want to do anything.    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Check the user's permissions.
    if ( $post_type == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    // # check data validation
    // https://developer.wordpress.org/themes/theme-security/data-validation
    // I use the HTML attributes for the data validation which works for the modern browsers. 
    // For the old browser, the data validation may not work.
    // So you can integrate the jquery validation plugin(https://jqueryvalidation.org) or ajax validation to do the data validation instead.


    // # Now, our data is safe for saving to the database
    // ## Sanitize the user input.
    // https://developer.wordpress.org/reference/functions/sanitize_text_field/
    $team_name_first = sanitize_text_field($_POST['_'.$post_type.'-name-first']);
    $team_name_middle = sanitize_text_field($_POST['_'.$post_type.'-name-middle']);
    $team_name_last = sanitize_text_field($_POST['_'.$post_type.'-name-last']);
    $team_title = sanitize_text_field($_POST['_'.$post_type.'-title']);
    $team_description = $_POST['_'.$post_type.'-description'];

    $team_contact_phone = sanitize_text_field($_POST['_'.$post_type.'-contact-phone']);
    $team_contact_extension = sanitize_text_field($_POST['_'.$post_type.'-contact-extension']);
    $team_contact_email = sanitize_text_field($_POST['_'.$post_type.'-contact-email']);
    $team_social_linkedin = esc_url_raw($_POST['_'.$post_type.'-social-linkedin']);
    $team_social_instagram = esc_url_raw($_POST['_'.$post_type.'-social-instagram']);
    $team_social_twitter = esc_url_raw($_POST['_'.$post_type.'-social-twitter']);

    // ## Update the meta field(custom field).
    // https://developer.wordpress.org/reference/functions/update_post_meta/
    // If the meta field never been saved to the wp_postmeta table before,
    // update_post_meta function will save the meta field for you.
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-name-first', $team_name_first );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-name-middle', $team_name_middle );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-name-last', $team_name_last );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-title', $team_title );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-description', $team_description );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-contact-phone', $team_contact_phone );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-contact-extension', $team_contact_extension );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-contact-email', $team_contact_email );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-social-linkedin', $team_social_linkedin );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-social-instagram', $team_social_instagram );
    update_post_meta( $post_id, '_gbcpt_'.$post_type.'-social-twitter', $team_social_twitter );


    // ## Delete the meta field(custom field)
    // The meta field value will stay with the post ID until the post is deleted permanently.
    // You don't need to delete the meta field manually.
}
// https://developer.wordpress.org/reference/hooks/save_post/
add_action( 'save_post', 'gbtc_save_meta_box' );

?>
