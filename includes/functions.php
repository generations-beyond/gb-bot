<?php 

if ( ! function_exists( 'is_plugin_active' ) )
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

/*
*Team Post type options
*/
function gbbot_cpt_settings($option) {
    $gbbot_team_cpt_enable = get_option('gbbot_team_cpt_enable', false);
    $gbbot_team_post_label = !empty(get_option('gbbot_team_post_label')) ? get_option('gbbot_team_post_label') : 'Team';
    $gbbot_team_post_type = !empty(get_option('gbbot_team_post_type')) ? get_option('gbbot_team_post_type') : 'team_member';

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

/**
* A function to be used by the Elements Usage Calculator for Elementor
* @return Multi-dimensional array of post_types and posts
*/
function gb_calculate_elements_usage() {
    // Initialize $allowed_post_types to empty
    $allowed_post_types = [];

    // Grab elementor_cpt_support option (Elementor -> Settings -> Post Types)
    $elementor_cpt_support = get_option('elementor_cpt_support', []);

    // Loop through all available post types and add them to $allowed_post_types if they use the Elementor builder
    foreach(get_post_types() as $post_type) {
        if (in_array($post_type, $elementor_cpt_support) ||
            strpos($post_type, 'elementor_') !== false || // Default elementor CPTs (they are not included in the wp_option)
            in_array($post_type, ['e-landing-page']) // For outliers
        ) {
            $allowed_post_types[] = $post_type;
        }
    }

    // Create a WP_Query object that returns all posts that support the Elementor builder
    $post_args = array(
        'post_type' => $allowed_post_types,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    $the_query = new WP_Query( $post_args ); 

    // Loop through all posts and sort them into categories based on post_type
    if ( $the_query->have_posts() ) {
        $output = [];
        foreach ($the_query->posts as $p) {
            // Ensure a category for the current post's post_type exists
            if (!isset($output[$p->post_type]))
                $output[$p->post_type] = [];

            // Get metadata for the post so that we can detect the elements usage
            $meta = get_post_meta($p->ID);

            // Add a new entry to the post_type category for this post
            $output[$p->post_type][] = [
                'ID' => $p->ID,
                'post_title' => $p->post_title,
                'post_status' => $p->post_status,
                'permalink' => get_the_permalink($p->ID),
                '_elementor_controls_usage' => isset($meta['_elementor_controls_usage']) ? $meta['_elementor_controls_usage'] : false,
            ];
        }

        // Sort output by post_type alphabetically and return
        ksort($output);
        return $output;
    } else {
        return [];
    }
}

// Taxonomy: Page Categories
add_action( 'init', function () {
    $labels = array(
		"name" => __( "Page Categories", "gb-ocean-child" ),
		"singular_name" => __( "Page Category", "gb-ocean-child" ),
		"menu_name" => __( "Categories", "gb-ocean-child" ),
	);
	$args = array(
		"label" => __( "Page Categories", "gb-ocean-child" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'page_categories', 'with_front' => true, ),
		"show_admin_column" => true,
		"show_in_rest" => true,
		"rest_base" => "page_categories",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => true,
		);
	register_taxonomy( "page_categories", array( "page" ), $args );
});

// Remove links in nav with href="#"
add_filter( 'wp_nav_menu_items', function ( $menu ) {
    return str_replace( '<a href="#"', '<a style="cursor:pointer"', $menu );
} );

// Prevent certain email addresses from being able to submit forms
add_action( 'elementor_pro/forms/validation/email', function( $field, $record, $ajax_handler ) {
	$spamemails = array(
		"ericjonesonline@outlook.com",
		"eric@talkwithwebvisitor.com",
		"eric.jones.z.mail@gmail.com",
		"eric@talkwithcustomer.com",
	);
	if ( in_array( $field['value'] , $spamemails) ) {
		$ajax_handler->add_error( $field['id'], 'We do not like spam, try another email address.' );
	}
}, 10, 3 );

// Prevent lesser users from creating higher users
add_filter('editable_roles', 'remove_higher_levels');
function remove_higher_levels($all_roles) {
    $user = wp_get_current_user();
    $next_level = 'level_' . ($user->user_level + 1);
	if( !in_array('gb_admin',$user->roles ) ) {
		foreach ( $all_roles as $name => $role ) {
			if (isset($role['capabilities'][$next_level]) || $name == 'gb_admin') {
				unset($all_roles[$name]);
			}
		}
	}
    return $all_roles;
}

// Rank Math check
if( is_plugin_active('seo-by-rank-math/rank-math.php') ) {
    /**
     * Fix Rank Math issue where it doesn't use the default 
     * OpenGraph image if another image exists on the page
     */
    add_filter('rank_math/opengraph/pre_set_content_image', function() {
        return true;
    });
    
    /**
     * Change the Rank Math Metabox Priority
     * @param array $priority Metabox Priority.
     */
    add_filter( 'rank_math/metabox/priority', function( $priority ) {
        return 'low';
    });
}

// Add following functions only if GBTC is inactive
if( !$GBTC_ACTIVE ) {

    /**
     * Add a post display state for special pages in the page list table.
     *
     * @param array   $post_states An array of post display states.
     * @param WP_Post $post        The current post object.
     */
    add_filter( 'display_post_states', 'gb_add_display_post_states', 10, 2 );
    function gb_add_display_post_states( $post_states, $post ) {
        if ( get_post_meta($post->ID,'_wp_page_template',true) === 'page-parent.php' ) {
            $post_states['gb_empty_parent'] = __( 'Empty Parent Page' );
        }
        return $post_states;
    }

    /**
     * Display Featured Image in lists for certain Post Types
     */
    add_filter('manage_posts_columns', 'gb_add_image_column_to_post_type', 10, 2);
    function gb_add_image_column_to_post_type($post_columns, $post_type) {
        $enabled_post_types = get_option('gbbot_featured_image_post_types', []);
        if (in_array($post_type, $enabled_post_types)) {
            $post_columns = array_slice($post_columns, 0, 1, true) +
                            array('image' => __( 'Image', 'Image of the post' )) +
                            array_slice($post_columns, 1, NULL, true);
        }
        return $post_columns;
    }

    add_action('manage_posts_custom_column', 'gb_display_posts_featured_image', 10, 2);
    function gb_display_posts_featured_image($column, $post_id) {
        if ($column == 'image') {
            $image = get_the_post_thumbnail_url($post_id, "thumbnail");
            if ($image == "")
                $image = plugin_dir_url( __FILE__ ) .'../assets/no-image.png';
            echo '<img src=' . $image . ' style="max-height:100px;max-width:100px;height:auto;width:auto;">';
        }
    }

    /**
    * Get categories from a specific taxonomy
    * @param $cat_taxonomy - (required) slug of the category taxonomy
    * @param @args - (optional) arguments for category query
    * @return WP_Term Object - from get_categories() https://developer.wordpress.org/reference/functions/get_category/
    * 	or False - if no $cat_taxonomy provided or no categories found.
    */
    function gb_get_taxonomy( $cat_taxonomy = false, $args = [] ) {
        // Exit if no taxonomy is given
        if ( !$cat_taxonomy )
            return false;
        $cat_args = array(
        'taxonomy' => $cat_taxonomy,
        'orderby' => 'name',
        'order'   => 'ASC',
        'hide_empty' => false,
        'number' => 0
        );
        $cat_args = array_merge($cat_args, $args);
        $cats = get_categories($cat_args);
        if ( $cats )
            return $cats;
        else
            return false;
    }

    /**
    * Gets either the WP_Query Object or WP_Post Object
    * @param $post_type - (required) slug of the post type
    * @param $args - (optional) array of query arguments
    * @param $rt_query - (optional) false returns the WP_Post Object, true returns the WP_Query Object 
    * @return WP_Post Object - if $rt_query is false https://developer.wordpress.org/reference/classes/wp_post/
    * 	or WP_Query - if no $rt_query is true https://developer.wordpress.org/reference/classes/wp_query/
    * 	or False - if no $post_type provided or no posts found.
    */
    function gb_get_posts( $post_type = false, $args = [], $rt_query = false ) {
        // Exit if no $post_type given
        if ( !$post_type )
            return false;
        $post_args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 0,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        $post_args = array_merge( $post_args, $args );
        $the_query = new WP_Query( $post_args ); 
        if ( $the_query->have_posts() ) : 
            if ( $rt_query )
                return $the_query;
            else
                return $the_query->posts;
        else :
            return false;
        endif;
    }

    /**
    * Get a limited part of the content - sans html tags and shortcodes - 
    * according to the amount written in $limit. Make sure words aren't cut in the middle
    * @param $the_content - (required) post content or string to be shortened
    * @param $limit - (optional) number of characters
    * @param $ending - (optional) character at the end of the shortened character, defaults to ellipse
    * @return string - the shortened content
    */
    function gb_the_short_content($the_content, $limit = 150, $ending = '&#8230') {
        $content = $the_content;
        // sometimes there are <p> tags that separate the words, and when the tags are removed, 
        // words from adjoining paragraphs stick together.
        // so replace the end <p> tags with space, to ensure unstickiness of words
        $content = strip_tags($content);
        $content = strip_shortcodes($content);
        $content = trim(preg_replace('/\s+/', ' ', $content));
        $ret = $content;
        // if the limit is more than the length, this will be returned
        if (mb_strlen($content) >= $limit) {
            $ret = mb_substr($content, 0, $limit);
            // make sure not to cut the words in the middle:
            // 1. first check if the substring already ends with a space
            if (mb_substr($ret, -1) !== ' ') {
                // 2. If it doesn't, find the last space before the end of the string
                $space_pos_in_substr = mb_strrpos($ret, ' ');
                // 3. then find the next space after the end of the string (using the original string)
                $space_pos_in_content = mb_strpos($content, ' ', $limit);
                // 4. now compare the distance of each space position from the limit
                if ($space_pos_in_content != false && $space_pos_in_content - $limit <= $limit - $space_pos_in_substr) {
                    // if the closest space is in the original string, take the substring from there
                    $ret = mb_substr($content, 0, $space_pos_in_content);
                } else {
                    // else take the substring from the original string, but with the earlier (space) position
                    $ret = mb_substr($content, 0, $space_pos_in_substr);
                }
            }
        }
        return $ret . $ending;
    }

    // Add Alpine.js attribute options to every Elementor Pro widget
    if (is_plugin_active( 'elementor-pro/elementor-pro.php' )) {
        add_action('elementor/element/before_section_end', function( $section, $section_id, $args ) {
            if( $section_id == '_section_attributes' ){
                $repeater = new \Elementor\Repeater();
                $repeater->add_control(
                    'alpine_attribute_name', [
                        'label' => __( 'Name', 'gb-alpine' ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => __( 'Attribute name', 'gb-alpine' ),
                        'label_block' => true,
                    ]
                );
                $repeater->add_control(
                    'alpine_attribute_value',
                    [
                        'label' => __( 'Value', 'gb-alpine' ),
                        'type' => \Elementor\Controls_Manager::TEXTAREA,
                        'dynamic' => [
                            'active' => true,
                        ],
                        'placeholder' => __( '', 'gb-alpine' ),
                        'rows' => 10,
                    ]
                );
                $section->add_control(
                    'list_alpine_attributes',
                    [
                        'label' => __( 'Alpine.js Attributes', 'gb-alpine' ),
                        'type' => \Elementor\Controls_Manager::REPEATER,
                        'fields' => $repeater->get_controls(),
                        'default' => [],
                        'prevent_empty' => false,
                        'title_field' => '{{{ alpine_attribute_name }}}',
                    ]
                );
            }
        }, 10, 3 );

        function gb_render_alpine_atts($section, $widget = false) {
            $element = $widget ?: $section;
            $settings = $element->get_settings();
            if(!empty($settings['list_alpine_attributes']) && $settings['list_alpine_attributes'][0]['alpine_attribute_name'] !== '' ) {
                foreach ($settings['list_alpine_attributes'] as $attItem) {
                    $element->add_render_attribute( '_wrapper', [
                        $attItem['alpine_attribute_name'] => do_shortcode($attItem['alpine_attribute_value']),
                    ] );
                }
            }
            // return content for widgets
            if($widget) {
                return $section;
            }
        }

        add_action( 'elementor/frontend/section/before_render', 'gb_render_alpine_atts', 10, 1 ); //params: section
        add_action( 'elementor/frontend/column/before_render', 'gb_render_alpine_atts', 10, 1 ); //params: column
        add_action( 'elementor/frontend/container/before_render', 'gb_render_alpine_atts', 10, 1 ); //params: container
        add_action( 'elementor/widget/render_content', 'gb_render_alpine_atts', 10, 2 ); //params: content, widget
    }

} else {
    // Deregister OceanWP's image lightbox scripts so we don't get double lightbox issues
    add_action( 'wp_enqueue_scripts', function() {
        wp_deregister_script('magnific-popup');
        wp_deregister_script('oceanwp-lightbox');
        wp_deregister_style('magnific-popup');
    }, 99);
}
?>
