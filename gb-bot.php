<?php
/**
* Plugin Name: GB&bull;BOT
* Plugin URI: https://generationsbeyond.com/gb-bot/
* Description: Make your website do more stuff.
* Version: 0.3.0
* Author: Generations Beyond
* Author URI: https://generationsbeyond.com/
* License: GPLv3
* Text Domain: gb-bot
**/

// Plugin Update Checker Support
require 'plugin-update-checker/plugin-update-checker.php';
$gbBotUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://bitbucket.org/generationsbeyond/gb-bot',
	__FILE__,
	'gb-bot'
);
if (file_exists('../wp-content/plugins/gb-bot/env.php'))
	include 'env.php';
$gbBotUpdateChecker->setBranch(isset($gbbot_env) ? $gbbot_env : 'master'); // Set in env.php

require_once('includes/functions.php' );

class GBBot {
	/**
	* Constructor
	*/
	public function __construct() {
		$this->plugin               = new stdClass;
		$this->plugin->name         = 'gb-bot';
		$this->plugin->displayName  = 'GB&bull;BOT';
		$this->plugin->version      = '0.3.0';
		$this->plugin->folder       = plugin_dir_path( __FILE__ );
		$this->plugin->url          = plugin_dir_url( __FILE__ );

		add_action( 'admin_init', array( &$this, 'registerSettings' ) );
		add_action( 'wp_dashboard_setup', array( &$this, 'registerDashboardWidget' ) );
		add_action( 'admin_menu', array( &$this, 'registerAdminPanel' ) );
		add_action( 'wp_footer', array( &$this, 'outputReBoundTrackingCode' ) );

		// CPT actions
		if(gbbot_cpt_settings('enabled')) {
		    add_action( 'init', 'gbbot_register_cpt' );
			add_action( 'add_meta_boxes', 'gbtc_cpt_register_meta_boxes' );
		}

		// Add branch name after plugin row if not on master
		add_action( 'after_plugin_row_gb-bot/gb-bot.php', function ( $file, $plugin ) {
			if (file_exists('../wp-content/plugins/gb-bot/env.php'))
				include 'env.php';
			if (isset($gbbot_env) && $gbbot_env != 'master') {
				$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
				printf(
					'<tr class="plugin-update-tr"><td colspan="%s" class="plugin-update update-message notice inline notice-warning notice-alt"><div class="update-message"><h4 style="font-weight: normal; margin: 0; font-size: 14px;">%s</h4></div></td></tr>',
					$wp_list_table->get_column_count(),
					'<strong>Note:</strong> You are currently configured to receive updates from the <code>'.$gbbot_env.'</code> branch of '.$plugin['Name'].'.'
				);
			}
		}, 10, 2 );
	}

	/**
	* Register Settings
	*/
	function registerSettings() {
		register_setting( $this->plugin->name, 'gbbot_rebound_id', 'trim' );
	}

	/**
	* Register the dashboard widget
	*/
	function registerDashboardWidget() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget('gbbot_widget', 'GB&bull;BOT', array( &$this, 'renderDashboardWidget' ));
	}

	/**
	* Output the Dashboard Widget
	*/
	function renderDashboardWidget() {
		include_once( $this->plugin->folder . 'views/dashboard-widget.php' );
	}

	/**
	* Register the plugin settings panel
	*/
	function registerAdminPanel() {
		add_menu_page($this->plugin->name, $this->plugin->displayName, 'manage_options', $this->plugin->name, array( &$this, 'renderAdminPanel' ), 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNTExLjc1IiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDUxMS43NSA1MTIiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNhMGE1YWE7CiAgICAgICAgZmlsbC1ydWxlOiBldmVub2RkOwogICAgICB9CiAgICA8L3N0eWxlPgogICAgPGNsaXBQYXRoIGlkPSJjbGlwLXBhdGgiPgogICAgICA8cmVjdCB4PSItMC4yODEiIHk9Ii0wLjMyOCIgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgogIDxnIGNsaXAtcGF0aD0idXJsKCNjbGlwLXBhdGgpIj4KICAgIDxwYXRoIGlkPSJnYi1ib3QtcGx1Z2luLWludmVydC1ub21vdXRoIiBjbGFzcz0iY2xzLTEiIGQ9Ik0yNS44ODQsNDM4LjU1NWE1LjY0OSw1LjY0OSwwLDAsMCw1LjQzNiw1LjE3NWwzMzUuODM3LDEyLjQxMWE0Ny45MzksNDcuOTM5LDAsMCwwLDYuODExLS4wNzhjMi45MjMtLjMyMiw0OC40MTMuNjExLDQ4LjQxMywwLjYxMSwyLjA2NCwwLDQuMTQ0LTEuNjU0LDUuMTQtMy40NDlsODMuODg4LTE4NS4yNDFjMS4yMzktMi4yMy42LTQuMjU5LTEuMDM3LTYuNzc5TDQ0MC44MjcsMTI3LjMzM2MtMC4wMi0uMDE5LTAuMDQ1LTAuMDMxLTAuMDY0LTAuMDUxYTUuNjM5LDUuNjM5LDAsMCwwLS41MjItMC40MDZjLTAuMTI2LS4wOTItMC4yNDYtMC4yLTAuMzc3LTAuMjc2cy0wLjI5Mi0uMTQ2LTAuNDM3LTAuMjE5Yy0wLjE5My0uMDk0LTAuMzgxLTAuMi0wLjU3Ny0wLjI2OS0wLjAyMy0uMDA2LTAuMDQtMC4wMTktMC4wNi0wLjAyNi0wLjg4OC0uMzEyLTc0LjQ4Ni0yNS43Ny0xNzEuNDMtMzQuNzYxbDEuMDUyLTE0LjUzN2E1LjYzNCw1LjYzNCwwLDAsMC00LjMzNC01Ljg3NkMyMTMuMDI2LDU4LjgsMTQ4LjEsNjguNDE1LDE0NS4zNjQsNjguODI5YTUuNjQsNS42NCwwLDAsMC00LjgxLDUuNTY0VjkxLjMyN2MtNTAuMyw1LjE3NS05Ni4xODEsMTYuODM3LTEzNi45LDM0LjkzYTUuNjE3LDUuNjE3LDAsMCwwLTMuMzMxLDUuNTg2Wm0xMjYtMzU5LjIzOWMxMS4wMzItMS4zNjEsMzkuNTI4LTQuMyw2OS42NzYtMi43NDUsMTEuOCwwLjYwNywxMS4zODgsMTIuMjU4LS41MzIsMTEuOTE3cS05LjMzNi0uMjY0LTE4LjgzNC0wLjI2OS0yNS44MjMsMC01MC4zMSwyLjA1NFY3OS4zMTZaTTQ3LjUsODguNTg3YTguNTI3LDguNTI3LDAsMCwwLDExLjktMS42NDljNi42MTItOC43LDEzLjMyMS0xMy40MTUsMTkuOTM5LTE0LjAyMiw3LjMyNS0uNjg2LDEyLjgsNC4wOCwxMy4wMzcsNC4yODdhOC41MzYsOC41MzYsMCwwLDAsMTEuOTQ5LS4zMjEsOC40LDguNCwwLDAsMC0uMjY0LTExLjkzMywzNS4yNDQsMzUuMjQ0LDAsMCwwLTI1LjktOC44NzhjLTExLjcyMi45MzYtMjIuNiw3LjktMzIuMzIxLDIwLjY5NEE4LjQsOC40LDAsMCwwLDQ3LjUsODguNTg3Wk0zMzUuNzcsNzkuOGMwLjEtLjA3OSwxMC4zNi03Ljc1NiwyMS42NTgtNi4wNDUsNi44LDEuMDI4LDEyLjk4NCw1LjM5MSwxOC4zODYsMTIuOTY5YTguNTI4LDguNTI4LDAsMCwwLDExLjg0MiwyLjAxMyw4LjQwNiw4LjQwNiwwLDAsMCwyLjAyNS0xMS43NjZjLTguMjIxLTExLjU0LTE4LjI4NC0xOC4yNDktMjkuOTEzLTE5LjkzOC0xOC41NTctMi43LTMzLjgsOC45NDktMzQuNDM3LDkuNDQ1YTguMzg0LDguMzg0LDAsMCwwLTEuNDYxLDExLjhBOC41NTUsOC41NTUsMCwwLDAsMzM1Ljc3LDc5LjhaTTI5MS4yLDE2NS42MzNjMzUsMCw2My4zNzcsMjguMTc1LDYzLjM3Nyw2Mi45MzFTMzI2LjIsMjkxLjUsMjkxLjIsMjkxLjVzLTYzLjM3Ny0yOC4xNzUtNjMuMzc3LTYyLjkzMVMyNTYuMiwxNjUuNjMzLDI5MS4yLDE2NS42MzNabTAuMjgxLDEyLjIwOWE1MS4wMjEsNTEuMDIxLDAsMSwxLTUxLjM1LDUxLjAyQTUxLjE4Niw1MS4xODYsMCwwLDEsMjkxLjQ4MSwxNzcuODQyWm02LjMsODcuNjEzYTM0LjE1NCwzNC4xNTQsMCwwLDEtMzQuMjYxLTM0LjA0NiwzMy43MTgsMzMuNzE4LDAsMCwxLDMuMjA2LTE0LjM4LDE0LjE2MywxNC4xNjMsMCwxLDAsMTYuNTU3LTE2LjQ2OEEzNC4xLDM0LjEsMCwxLDEsMjk3Ljc4NCwyNjUuNDU1Wk0xNjEuNywzMDEuMTM0bDI2LjQ4MS0xNi4zNDEtMi43MzksMjkuOTU5Wk0xMTcuMiwxNjIuMDZjMzUsMCw2My4zNzcsMjguMTgyLDYzLjM3Nyw2Mi45NDdTMTUyLjIsMjg3Ljk1NCwxMTcuMiwyODcuOTU0cy02My4zNzctMjguMTgyLTYzLjM3Ny02Mi45NDdTODIuMiwxNjIuMDYsMTE3LjIsMTYyLjA2Wm0wLjMsMTIuMjQyYTUxLDUxLDAsMSwxLTUxLjM2Nyw1MUE1MS4xODUsNTEuMTg1LDAsMCwxLDExNy41LDE3NC4zWm04LjIsODcuMzQ4QTM0LjE1NCwzNC4xNTQsMCwwLDEsOTEuNDQsMjI3LjZhMzMuNzI2LDMzLjcyNiwwLDAsMSwzLjIyOC0xNC40MjksMTQuMTQ5LDE0LjE0OSwwLDEsMCwxNi41MzgtMTYuNDJBMzQuMSwzNC4xLDAsMSwxLDEyNS43LDI2MS42NVpNMzEyLjcwOCwzNzUuMDYzYTUuOSw1LjksMCwwLDAtMS42NDItMy4wODJsLTMyLjgzMS0zMi42MThjLTAuMDgzLS4wODUtMC4xODMtMC4xNDItMC4yNzItMC4yMmE1LjU2Niw1LjU2NiwwLDAsMC0uNS0wLjRjLTAuMTUyLS4xMDYtMC4zLTAuMi0wLjQ2Mi0wLjI5YTUuNzkxLDUuNzkxLDAsMCwwLS41NDEtMC4yODNjLTAuMTY2LS4wNzQtMC4zMzMtMC4xMzUtMC41MDctMC4xOTUtMC4xOTItLjA2Ni0wLjM4Ny0wLjEyNi0wLjU4OC0wLjE3NC0wLjE2OC0uMDM3LTAuMzMyLTAuMDY0LTAuNS0wLjA4OGE2LjIsNi4yLDAsMCwwLS42NTItMC4wNjNjLTAuMTU1LDAtLjMwOCwwLTAuNDYzLjAwN2E1Ljc2Myw1Ljc2MywwLDAsMC0uNjkxLjA2NGMtMC4xNDkuMDI0LS4zLDAuMDU4LTAuNDQ3LDAuMWE1LjcxNCw1LjcxNCwwLDAsMC0uNjY0LjIsNC41ODEsNC41ODEsMCwwLDAtLjQ1NC4yYy0wLjEzNS4wNi0uMjczLDAuMS0wLjQsMC4xNzJhMTQ3LjQyMiwxNDcuNDIyLDAsMCwxLTE0MS4zNzIsMGMtMC4xMTMtLjA2NC0wLjIzLTAuMS0wLjM0NS0wLjE0OWE1LjczNSw1LjczNSwwLDAsMC0uNTQtMC4yMzIsNS40MDYsNS40MDYsMCwwLDAtLjU5NS0wLjE3NmMtMC4xNjgtLjA0Mi0wLjMzNi0wLjA4LTAuNTA2LTAuMTA1YTUuOTE2LDUuOTE2LDAsMCwwLS42NDktMC4wNjRjLTAuMTY0LS4wMDgtMC4zMjgtMC4wMTMtMC40OS0wLjAwNmE1Ljc1Myw1Ljc1MywwLDAsMC0uNjQ0LjA2Miw0LjgsNC44LDAsMCwwLS41LjA4OSw2LjAzOCw2LjAzOCwwLDAsMC0uNjA5LjE3OGMtMC4xNi4wNTctLjMxOSwwLjExNC0wLjQ3NiwwLjE4M2E1Ljc1OCw1Ljc1OCwwLDAsMC0uNTg2LjMwOSw0Ljc0OCw0Ljc0OCwwLDAsMC0uNDA2LjI1NCw1Ljg4OSw1Ljg4OSwwLDAsMC0uNTY2LjQ1N2MtMC4wNzMuMDY3LS4xNTYsMC4xMTQtMC4yMjQsMC4xODNMOTQuMjM1LDM2Ny41MjZjLTAuMDk0LjA5NC0uMTYsMC4yLTAuMjQ3LDAuMy0wLjEwOS4xMjQtLjIzMiwwLjIzLTAuMzMyLDAuMzY0LTAuMDM1LjA0Ny0uMDU3LDAuMS0wLjA5MSwwLjE1MS0wLjEwOC4xNTQtLjE5NSwwLjMxNC0wLjI4NSwwLjQ3NGE2LjExOCw2LjExOCwwLDAsMC0uMy41NjVjLTAuMDY1LjE1LS4xMDksMC4zLTAuMTYxLDAuNDU1YTUuOSw1LjksMCwwLDAtLjIuNjU0Yy0wLjAyOS4xNC0uMDQxLDAuMjgyLTAuMDYxLDAuNDI0YTUuOTIsNS45MiwwLDAsMC0uMDY4LjcxYzAsMC4xNDEuMDE1LDAuMjgsMC4wMjEsMC40MjNhNi4yLDYuMiwwLDAsMCwuMDcuN2MwLjAyNiwwLjE0NS4wNzEsMC4yODUsMC4xMDYsMC40MjhhNS42LDUuNiwwLDAsMCwuMi42NjdjMC4wNTIsMC4xMzkuMTI0LDAuMjY4LDAuMTg4LDAuNGE1LjgwNyw1LjgwNywwLDAsMCwuMzM1LjYzMmMwLjA3OCwwLjEyMy4xNzIsMC4yMzYsMC4yNTgsMC4zNTNhNS44NjIsNS44NjIsMCwwLDAsLjQ3Ni41NzdjMC4wMzEsMC4wMzMuMDUzLDAuMDcyLDAuMDg1LDAuMSwwLjA4OSwwLjA5LjE5MiwwLjE1MSwwLjI4NiwwLjIzMywwLjEyOCwwLjExMy4yMzgsMC4yMzgsMC4zNzUsMC4zNCwzMC40MjUsMjIuNCw2OS44MjQsMzQuNzM3LDExMC45NDMsMzQuNzM3LDM3LjkxNCwwLDc0Ljk0Ny0xMC42ODYsMTA0LjI3OS0zMC4wODhsMC4wMTgtLjAxMnMwLjAxLS4wMDcuMDE3LTAuMDFhNS42NjgsNS42NjgsMCwwLDAsLjU3MS0wLjQ2OGMwLjExMi0uMS4yMzYtMC4xNzEsMC4zNDItMC4yNzUsMCwwLDAsMCwwLDBhNS45MzksNS45MzksMCwwLDAsLjc1Ny0wLjkyMSw2LjM2OCw2LjM2OCwwLDAsMCwuMzI3LTAuNjE1YzAuMDY3LS4xMzUuMTU1LTAuMjYxLDAuMjExLTAuNGE1LjgsNS44LDAsMCwwLC4yNzItMC44OTFjMC4wMTYtLjA3LjA0NS0wLjEzMiwwLjA2LTAuMmE2LjExNyw2LjExNywwLDAsMCwuMDg3LTAuODU1YzAuMDA2LS4wOTQuMDI3LTAuMTg2LDAuMDI4LTAuMjc5YTUuOTQ4LDUuOTQ4LDAsMCwwLS4wODQtMC45MTJDMzEyLjcxNywzNzUuMjE0LDMxMi43MiwzNzUuMTM4LDMxMi43MDgsMzc1LjA2M1pNNDQwLjUsMTU2bDU5LDExMS41LTgwLDE3NC0zNy45MjMtMS41MjRaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMC4yODEgLTAuMzI4KSIvPgogIDwvZz4KPC9zdmc+Cg==');
		add_submenu_page($this->plugin->name, $this->plugin->displayName, 'Settings', 'manage_options', $this->plugin->name, array( &$this, 'renderAdminPanel' ));
		add_submenu_page($this->plugin->name, $this->plugin->displayName, 'Integrations','manage_options', $this->plugin->name.'-integrations', array( &$this, 'renderAdminPanel' ));
		add_submenu_page($this->plugin->name, $this->plugin->displayName, 'Tools','manage_options', $this->plugin->name.'-tools', array( &$this, 'renderAdminPanel' ));
	}

	/**
	* Output the Administration Panel
	* Save POSTed data from the Administration Panel into a WordPress option
	*/
	function renderAdminPanel() {
		// only admin user can access this page
		if ( !current_user_can( 'administrator' ) ) {
			echo '<p>' . __( 'Sorry, you are not allowed to access this page.', 'gb-bot' ) . '</p>';
			return;
		}

		// Save Settings
		if ( isset( $_REQUEST['submit'] ) ) {
			// Check nonce
			if ( !isset( $_REQUEST[$this->plugin->name.'_nonce'] ) ) {
				// Missing nonce
				$this->errorMessage = __( 'nonce field is missing. Settings NOT saved.', 'gb-bot' );
			} elseif ( !wp_verify_nonce( $_REQUEST[$this->plugin->name.'_nonce'], $this->plugin->name ) ) {
				// Invalid nonce
				$this->errorMessage = __( 'Invalid nonce specified. Settings NOT saved.', 'gb-bot' );
			} else {
				// Save
				// $_REQUEST has already been slashed by wp_magic_quotes in wp-settings
				// so do nothing before saving
				$page = $_GET['page'];
				switch ($page) {
					case $this->plugin->name:
						update_option( 'gbbot_team_cpt_enable', $_REQUEST['gbbot_team_cpt_enable'] );
						update_option( 'gbbot_team_post_label', $_REQUEST['gbbot_team_post_label'] );
						update_option( 'gbbot_team_post_type', $_REQUEST['gbbot_team_post_type'] );
						$this->message = __( 'Settings Saved. Refresh the page to see the changes.', 'gb-bot' );
						break;

					case $this->plugin->name.'-integrations':
						update_option( 'gbbot_rebound_id', $_REQUEST['gbbot_rebound_id'] );
						$this->message = __( 'Settings Saved.', 'gb-bot' );
						break;
					
					default:
						# code...
						break;
				}
			}
		}

		// Get latest settings
		$this->settings = array(
			'gbbot_rebound_id' => esc_html( wp_unslash( get_option( 'gbbot_rebound_id' ) ) ),
		);

		// Load Settings Form
		include_once( $this->plugin->folder . '/views/main.php' );
	}

	/**
	* Outputs ReBound Tracking Code to the frontend footer
	*/
	function outputReBoundTrackingCode() {
		// Ignore admin, feed, robots or trackbacks
		if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
			return;
		}

		// Get meta
		$meta = get_option( 'gbbot_rebound_id' );
		if ( empty( $meta ) ) {
			return;
		}
		if ( trim( $meta ) == '' ) {
			return;
		}

		// Output
		echo '<!-- GB ReBound Pixel -->';
		echo '<img src="https://trkn.us/pixel/conv/ppt=9999;g=homepage;gid=99999;ord=' . wp_unslash( $meta ) . '" height="0" width="0" border="0"  />';
	}
}

$gbbot = new GBBot();