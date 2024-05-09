<?php
/**
* Plugin Name: GB&bull;BOT
* Plugin URI: https://generationsbeyond.com/gb-bot/
* Description: A collection of useful functions and features to proactively enhance your website.
* Version: 1.5.0
* Author: Generations Beyond
* Author URI: https://generationsbeyond.com/
* License: GPLv3
* Text Domain: gb-bot
**/
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Check if GB Theme Core is active
global $GBTC_ACTIVE;
$GBTC_ACTIVE = file_exists(get_stylesheet_directory().'/core/init.php');

if ($GBTC_ACTIVE) {

	// Do not allow plugin updates
	add_action( 'after_plugin_row_gb-bot/gb-bot.php', function ( $file, $plugin ) {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		printf(
			'<tr class="plugin-update-tr active"><td colspan="%s" class="plugin-update"><div style="background: #fefefe;" class="notice inline notice-info notice-alt"><p>%s</p></div>%s</td></tr>',
			$wp_list_table->get_column_count(),
			'<strong>Note:</strong> You are currently using <strong>GB Theme Core</strong> as your active theme. Switch to <strong>Proactive by GB</strong> to continue receiving '.$plugin['Name'].' updates.',
			'<script>document.querySelector(\'[data-plugin="gb-bot/gb-bot.php"]\').classList.add(\'update\')</script>'
		);
	}, 10, 2 );

} else {

	// Plugin Update Checker Support
	require 'plugin-update-checker/plugin-update-checker.php';
	$gbBotUpdateChecker = PucFactory::buildUpdateChecker(
		'https://github.com/generations-beyond/gb-bot/',
		__FILE__,
		'gb-bot'
	);
	$gbBotUpdateChecker->setBranch(get_option('gbbot_active_branch', 'master'));
	$gbBotUpdateChecker->getVcsApi()->enableReleaseAssets();

}

if ( ! function_exists( 'is_plugin_active' ) )
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

class GBBot {
	/**
	* Constructor
	*/
	public function __construct() {
		global $GBTC_ACTIVE;
		
		$plugin_data = get_plugin_data( __FILE__, false );
		
		$this->plugin               = new stdClass;
		$this->plugin->name         = 'gb-bot';
		$this->plugin->displayName  = $plugin_data['Name'];
		$this->plugin->version      = $plugin_data['Version'];
		$this->plugin->folder       = plugin_dir_path( __FILE__ );
		$this->plugin->url          = plugin_dir_url( __FILE__ );

		$this->GBTC_ACTIVE = $GBTC_ACTIVE;
		$this->WARNING_CLASS = 'notice-warning notice-alt';

		// Check availability of other plugins
		$this->checkActivePlugins();

		// Register frontend notices
		$this->registerNotices();

		// Get latest settings
		$this->refreshSettings();

		// Set up permissions for advanced features
		$this->setUpPermissions();

		// Initialize GB-BOT Core
		$this->initGBBOTCore();

		// Initialize GBTC Overlap functionality if GBTC is not active
		if ( !$this->GBTC_ACTIVE ) {
			$this->initGBTCOverlap();
		}

		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'registerDashboardWidget' ) );
		add_action( 'admin_menu', array( $this, 'registerAdminPanel' ) );
		add_action( 'wp_footer', array( $this, 'outputReBoundTrackingCode' ) );

		// CPT actions
		if ($this->settings['gbbot_team_cpt_enable']) {
			add_action( 'init', 'gbbot_register_cpt' );
			add_action( 'add_meta_boxes', 'gbbot_cpt_register_meta_boxes' );
		}

		// Add branch name notice after plugin row if not on master
		$gbbot_active_branch = get_option('gbbot_active_branch', 'master');
		if ($gbbot_active_branch != 'master') {
			add_action( 'admin_init', function () use ($gbbot_active_branch) {
				// Only show if current user is a super user
				if ($this->is_super_user) {
					add_action( 'after_plugin_row_gb-bot/gb-bot.php', function ( $file, $plugin ) use ($gbbot_active_branch) {
						$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
						printf(
							'<tr class="plugin-update-tr active"><td colspan="%s" class="plugin-update"><div class="notice inline notice-warning notice-alt"><p>%s</p></div>%s</td></tr>',
							$wp_list_table->get_column_count(),
							'<strong>Note:</strong> You are currently configured to receive updates from the <code>'.$gbbot_active_branch.'</code> branch of '.$plugin['Name'].'.',
							'<script>document.querySelector(\'[data-plugin="gb-bot/gb-bot.php"]\').classList.add(\'update\')</script>'
						);
					}, 10, 2 );
				}
			});
		}

		// Add admin notices
		add_action('admin_notices', function() {
			// Show if the current environment is not production
			if ($this->is_staging) {
				echo '<div class="notice notice-warning">
					<p><b>Notice:</b> Search engine indexing has been discouraged because GB&bull;BOT has identified this server as a <b>staging/development</b> server.</p>
				</div>';
			}

			// Only show if current user is a super user
			if ($this->is_super_user) {
				global $pagenow;
				if ( $pagenow == 'plugins.php' ) {
					// Detect a change in WP_PLUGIN_DIR
					if (WP_PLUGIN_DIR !== substr_replace(get_theme_root(), "", -6)."plugins") {
						echo '<div class="notice notice-warning">
							<p><b>Warning:</b> <code>WP_PLUGIN_DIR</code> has been modified in <code>wp-config.php</code>.</p>
						</div>';
					}
					// Detect a change in WPMU_PLUGIN_DIR
					if (WPMU_PLUGIN_DIR !== substr_replace(get_theme_root(), "", -6)."mu-plugins") {
						echo '<div class="notice notice-warning">
							<p><b>Warning:</b> <code>WPMU_PLUGIN_DIR</code> has been modified in <code>wp-config.php</code>.</p>
						</div>';
					}
				}
			}
		});
	}
	
	/**
	 * Check whether related plugins are activated
	 */
	function checkActivePlugins() {
		$plugins_to_check = [
			'elementor-pro' => 'elementor-pro/elementor-pro.php',
			'rank-math' => 'seo-by-rank-math/rank-math.php',
		];
		$this->active_plugins = [];
		foreach ($plugins_to_check as $key=>$plugin) {
			$this->active_plugins[$key] = is_plugin_active($plugin);
		}
	}
	
	/**
	 * Set up permission check
	 */
	function setUpPermissions() {
		add_action( 'init', array( $this, 'checkPermissions' ) );
		if ( !$this->GBTC_ACTIVE ) {
			add_action( 'init', array( $this, 'protection' ) );
		}
	}

	/**
	 * Check if a user is allowed to use certain features of the plugin
	 */
	function checkPermissions() {
		global $current_user;
		$this->super_users = ['GenBeyond','genbeyond'];
		if ($this->settings['gbbot_super_users'] && is_array($this->settings['gbbot_super_users']))
			$this->super_users = array_merge($this->super_users, $this->settings['gbbot_super_users']);
		if (is_null($current_user) && function_exists('wp_get_current_user'))
			wp_get_current_user();
		$this->is_super_user = in_array($current_user->user_login, $this->super_users);
	}

	/**
	 * Protection functionality
	 */
	function protection() {
		if (!$this->is_super_user) {
			$super_users = $this->super_users;
			$gbbot_theme_dir = $this->plugin->url;
			$plugin_name = $this->plugin->name;
			$version = $this->plugin->version;
			
			add_action('pre_user_query', function($user_search) use ($super_users) {
				global $wpdb;
				$user_search->query_where = str_replace('WHERE 1=1',
						"WHERE 1=1 AND {$wpdb->users}.user_login NOT IN ('".implode("','",$super_users)."')",$user_search->query_where);
			});
			add_action('admin_enqueue_scripts', function() use ($plugin_name, $gbbot_theme_dir, $version) {
				wp_enqueue_style($plugin_name . '-protection', $gbbot_theme_dir . 'assets/styles/protection.css', array(), $version);
			});
		}
	}

	/**
	 * Init GB BOT Core
	 */
	function initGBBOTCore() {
		$gbbot_theme_dir = $this->plugin->url;
		$plugin_name = $this->plugin->name;
		$version = $this->plugin->version;

		// Enqueue backend JS/CSS
		add_action('admin_head', function() use ($plugin_name, $gbbot_theme_dir, $version)  {	
			$page = $_GET['page'] ?? '';
			wp_enqueue_style($plugin_name . '-admin', $gbbot_theme_dir . 'assets/styles/admin.css', array(), $version);
			wp_enqueue_script($plugin_name . '-admin', $gbbot_theme_dir.'assets/scripts/general.js', array('jquery'), $version);
			if (strpos($page, 'gb-bot') !== false) {
				wp_enqueue_script($plugin_name . '-settings', $gbbot_theme_dir.'assets/scripts/settings.js', array('jquery'), $version);
			}
		});

		// Determine if we're in a staging/development environment
		if (wp_get_environment_type() !== 'production'
			|| substr($_SERVER['HTTP_HOST'], 0, 4) === "dev."
			|| substr($_SERVER['HTTP_HOST'], 0, 12) === "development."
		) {
			$this->is_staging = true;
			// Discourage the site from being indexed by search engines
			add_filter('robots_txt', function($output, $public) {
					$robots_txt = "User-agent: *\n";
					$robots_txt .= "Disallow: /";
					return $robots_txt;
			}, 10,  2);
		} else {
			$this->is_staging = false;
		}
	}

	/**
	 * Init GBTC-Overlap Functionality
	 */
	function initGBTCOverlap() {
		$gbbot_theme_dir = $this->plugin->url;
		$plugin_name = $this->plugin->name;
		$version = $this->plugin->version;

		// Enqueue frontend JS/CSS
		add_action('wp_enqueue_scripts', function() use ($plugin_name, $gbbot_theme_dir, $version) {
			wp_enqueue_style($plugin_name . '-styles', $gbbot_theme_dir.'assets/styles/main.css', array(), $version);
			
			wp_enqueue_script($plugin_name . '-frontend', $gbbot_theme_dir.'assets/scripts/frontend.js', array('jquery'), $version);
			
			// AlpineJS
			if ($this->active_plugins['elementor-pro']) {
				if (! (null !== (\Elementor\Plugin::$instance->preview->is_preview_mode()) ? \Elementor\Plugin::$instance->preview->is_preview_mode() : false) ) {
					wp_enqueue_script( 'gb-alpinejs', '//unpkg.com/alpinejs@3.5.0', array(), null);
				}
			}
		});

		// AlpineJS script defer
		add_filter('script_loader_tag', function ($tag, $handle) {
			if ($handle === 'gb-alpinejs') {
				$tag = str_replace("src=", "defer src=", $tag);
			}
			return $tag;
		}, 10, 2);

		// Add empty page parent template
		add_filter( 'theme_page_templates', function($templates) {
			$templates[ str_replace( '\\', '/', plugin_dir_path( __FILE__ ) ) . 'templates/empty-parent-page.php'] = __( 'Empty Parent Page', 'gb-bot' );
			return $templates;
		} );
		// Change the page template to the selected template on the dropdown
		add_filter( 'template_include', function($template) {
			if (is_page()) {
				$meta = get_post_meta(get_the_ID());
				if (!empty($meta['_wp_page_template'][0]) && strpos( $meta['_wp_page_template'][0], $this->plugin->name) && $meta['_wp_page_template'][0] != $template) {
					$template = $meta['_wp_page_template'][0];
				}
			}
			return $template;
		}, 99 );

		// Admin custom styles
		$gbbot_admin_css = $this->settings['gbbot_admin_css'];
		if (!empty($gbbot_admin_css)) {
			add_action('admin_head', function() use ($gbbot_admin_css) {
				echo '<style>' . preg_replace('%\\\\"%', '"', $gbbot_admin_css) . "</style>";
			});
		}
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
		wp_add_dashboard_widget('gbbot_widget', 'GB&bull;BOT', array( $this, 'renderDashboardWidget' ));
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
		add_menu_page($this->plugin->name, $this->plugin->displayName, 'manage_options', $this->plugin->name, array( $this, 'renderAdminPanel' ), 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNTExLjc1IiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDUxMS43NSA1MTIiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNhMGE1YWE7CiAgICAgICAgZmlsbC1ydWxlOiBldmVub2RkOwogICAgICB9CiAgICA8L3N0eWxlPgogICAgPGNsaXBQYXRoIGlkPSJjbGlwLXBhdGgiPgogICAgICA8cmVjdCB4PSItMC4yODEiIHk9Ii0wLjMyOCIgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgogIDxnIGNsaXAtcGF0aD0idXJsKCNjbGlwLXBhdGgpIj4KICAgIDxwYXRoIGlkPSJnYi1ib3QtcGx1Z2luLWludmVydC1ub21vdXRoIiBjbGFzcz0iY2xzLTEiIGQ9Ik0yNS44ODQsNDM4LjU1NWE1LjY0OSw1LjY0OSwwLDAsMCw1LjQzNiw1LjE3NWwzMzUuODM3LDEyLjQxMWE0Ny45MzksNDcuOTM5LDAsMCwwLDYuODExLS4wNzhjMi45MjMtLjMyMiw0OC40MTMuNjExLDQ4LjQxMywwLjYxMSwyLjA2NCwwLDQuMTQ0LTEuNjU0LDUuMTQtMy40NDlsODMuODg4LTE4NS4yNDFjMS4yMzktMi4yMy42LTQuMjU5LTEuMDM3LTYuNzc5TDQ0MC44MjcsMTI3LjMzM2MtMC4wMi0uMDE5LTAuMDQ1LTAuMDMxLTAuMDY0LTAuMDUxYTUuNjM5LDUuNjM5LDAsMCwwLS41MjItMC40MDZjLTAuMTI2LS4wOTItMC4yNDYtMC4yLTAuMzc3LTAuMjc2cy0wLjI5Mi0uMTQ2LTAuNDM3LTAuMjE5Yy0wLjE5My0uMDk0LTAuMzgxLTAuMi0wLjU3Ny0wLjI2OS0wLjAyMy0uMDA2LTAuMDQtMC4wMTktMC4wNi0wLjAyNi0wLjg4OC0uMzEyLTc0LjQ4Ni0yNS43Ny0xNzEuNDMtMzQuNzYxbDEuMDUyLTE0LjUzN2E1LjYzNCw1LjYzNCwwLDAsMC00LjMzNC01Ljg3NkMyMTMuMDI2LDU4LjgsMTQ4LjEsNjguNDE1LDE0NS4zNjQsNjguODI5YTUuNjQsNS42NCwwLDAsMC00LjgxLDUuNTY0VjkxLjMyN2MtNTAuMyw1LjE3NS05Ni4xODEsMTYuODM3LTEzNi45LDM0LjkzYTUuNjE3LDUuNjE3LDAsMCwwLTMuMzMxLDUuNTg2Wm0xMjYtMzU5LjIzOWMxMS4wMzItMS4zNjEsMzkuNTI4LTQuMyw2OS42NzYtMi43NDUsMTEuOCwwLjYwNywxMS4zODgsMTIuMjU4LS41MzIsMTEuOTE3cS05LjMzNi0uMjY0LTE4LjgzNC0wLjI2OS0yNS44MjMsMC01MC4zMSwyLjA1NFY3OS4zMTZaTTQ3LjUsODguNTg3YTguNTI3LDguNTI3LDAsMCwwLDExLjktMS42NDljNi42MTItOC43LDEzLjMyMS0xMy40MTUsMTkuOTM5LTE0LjAyMiw3LjMyNS0uNjg2LDEyLjgsNC4wOCwxMy4wMzcsNC4yODdhOC41MzYsOC41MzYsMCwwLDAsMTEuOTQ5LS4zMjEsOC40LDguNCwwLDAsMC0uMjY0LTExLjkzMywzNS4yNDQsMzUuMjQ0LDAsMCwwLTI1LjktOC44NzhjLTExLjcyMi45MzYtMjIuNiw3LjktMzIuMzIxLDIwLjY5NEE4LjQsOC40LDAsMCwwLDQ3LjUsODguNTg3Wk0zMzUuNzcsNzkuOGMwLjEtLjA3OSwxMC4zNi03Ljc1NiwyMS42NTgtNi4wNDUsNi44LDEuMDI4LDEyLjk4NCw1LjM5MSwxOC4zODYsMTIuOTY5YTguNTI4LDguNTI4LDAsMCwwLDExLjg0MiwyLjAxMyw4LjQwNiw4LjQwNiwwLDAsMCwyLjAyNS0xMS43NjZjLTguMjIxLTExLjU0LTE4LjI4NC0xOC4yNDktMjkuOTEzLTE5LjkzOC0xOC41NTctMi43LTMzLjgsOC45NDktMzQuNDM3LDkuNDQ1YTguMzg0LDguMzg0LDAsMCwwLTEuNDYxLDExLjhBOC41NTUsOC41NTUsMCwwLDAsMzM1Ljc3LDc5LjhaTTI5MS4yLDE2NS42MzNjMzUsMCw2My4zNzcsMjguMTc1LDYzLjM3Nyw2Mi45MzFTMzI2LjIsMjkxLjUsMjkxLjIsMjkxLjVzLTYzLjM3Ny0yOC4xNzUtNjMuMzc3LTYyLjkzMVMyNTYuMiwxNjUuNjMzLDI5MS4yLDE2NS42MzNabTAuMjgxLDEyLjIwOWE1MS4wMjEsNTEuMDIxLDAsMSwxLTUxLjM1LDUxLjAyQTUxLjE4Niw1MS4xODYsMCwwLDEsMjkxLjQ4MSwxNzcuODQyWm02LjMsODcuNjEzYTM0LjE1NCwzNC4xNTQsMCwwLDEtMzQuMjYxLTM0LjA0NiwzMy43MTgsMzMuNzE4LDAsMCwxLDMuMjA2LTE0LjM4LDE0LjE2MywxNC4xNjMsMCwxLDAsMTYuNTU3LTE2LjQ2OEEzNC4xLDM0LjEsMCwxLDEsMjk3Ljc4NCwyNjUuNDU1Wk0xNjEuNywzMDEuMTM0bDI2LjQ4MS0xNi4zNDEtMi43MzksMjkuOTU5Wk0xMTcuMiwxNjIuMDZjMzUsMCw2My4zNzcsMjguMTgyLDYzLjM3Nyw2Mi45NDdTMTUyLjIsMjg3Ljk1NCwxMTcuMiwyODcuOTU0cy02My4zNzctMjguMTgyLTYzLjM3Ny02Mi45NDdTODIuMiwxNjIuMDYsMTE3LjIsMTYyLjA2Wm0wLjMsMTIuMjQyYTUxLDUxLDAsMSwxLTUxLjM2Nyw1MUE1MS4xODUsNTEuMTg1LDAsMCwxLDExNy41LDE3NC4zWm04LjIsODcuMzQ4QTM0LjE1NCwzNC4xNTQsMCwwLDEsOTEuNDQsMjI3LjZhMzMuNzI2LDMzLjcyNiwwLDAsMSwzLjIyOC0xNC40MjksMTQuMTQ5LDE0LjE0OSwwLDEsMCwxNi41MzgtMTYuNDJBMzQuMSwzNC4xLDAsMSwxLDEyNS43LDI2MS42NVpNMzEyLjcwOCwzNzUuMDYzYTUuOSw1LjksMCwwLDAtMS42NDItMy4wODJsLTMyLjgzMS0zMi42MThjLTAuMDgzLS4wODUtMC4xODMtMC4xNDItMC4yNzItMC4yMmE1LjU2Niw1LjU2NiwwLDAsMC0uNS0wLjRjLTAuMTUyLS4xMDYtMC4zLTAuMi0wLjQ2Mi0wLjI5YTUuNzkxLDUuNzkxLDAsMCwwLS41NDEtMC4yODNjLTAuMTY2LS4wNzQtMC4zMzMtMC4xMzUtMC41MDctMC4xOTUtMC4xOTItLjA2Ni0wLjM4Ny0wLjEyNi0wLjU4OC0wLjE3NC0wLjE2OC0uMDM3LTAuMzMyLTAuMDY0LTAuNS0wLjA4OGE2LjIsNi4yLDAsMCwwLS42NTItMC4wNjNjLTAuMTU1LDAtLjMwOCwwLTAuNDYzLjAwN2E1Ljc2Myw1Ljc2MywwLDAsMC0uNjkxLjA2NGMtMC4xNDkuMDI0LS4zLDAuMDU4LTAuNDQ3LDAuMWE1LjcxNCw1LjcxNCwwLDAsMC0uNjY0LjIsNC41ODEsNC41ODEsMCwwLDAtLjQ1NC4yYy0wLjEzNS4wNi0uMjczLDAuMS0wLjQsMC4xNzJhMTQ3LjQyMiwxNDcuNDIyLDAsMCwxLTE0MS4zNzIsMGMtMC4xMTMtLjA2NC0wLjIzLTAuMS0wLjM0NS0wLjE0OWE1LjczNSw1LjczNSwwLDAsMC0uNTQtMC4yMzIsNS40MDYsNS40MDYsMCwwLDAtLjU5NS0wLjE3NmMtMC4xNjgtLjA0Mi0wLjMzNi0wLjA4LTAuNTA2LTAuMTA1YTUuOTE2LDUuOTE2LDAsMCwwLS42NDktMC4wNjRjLTAuMTY0LS4wMDgtMC4zMjgtMC4wMTMtMC40OS0wLjAwNmE1Ljc1Myw1Ljc1MywwLDAsMC0uNjQ0LjA2Miw0LjgsNC44LDAsMCwwLS41LjA4OSw2LjAzOCw2LjAzOCwwLDAsMC0uNjA5LjE3OGMtMC4xNi4wNTctLjMxOSwwLjExNC0wLjQ3NiwwLjE4M2E1Ljc1OCw1Ljc1OCwwLDAsMC0uNTg2LjMwOSw0Ljc0OCw0Ljc0OCwwLDAsMC0uNDA2LjI1NCw1Ljg4OSw1Ljg4OSwwLDAsMC0uNTY2LjQ1N2MtMC4wNzMuMDY3LS4xNTYsMC4xMTQtMC4yMjQsMC4xODNMOTQuMjM1LDM2Ny41MjZjLTAuMDk0LjA5NC0uMTYsMC4yLTAuMjQ3LDAuMy0wLjEwOS4xMjQtLjIzMiwwLjIzLTAuMzMyLDAuMzY0LTAuMDM1LjA0Ny0uMDU3LDAuMS0wLjA5MSwwLjE1MS0wLjEwOC4xNTQtLjE5NSwwLjMxNC0wLjI4NSwwLjQ3NGE2LjExOCw2LjExOCwwLDAsMC0uMy41NjVjLTAuMDY1LjE1LS4xMDksMC4zLTAuMTYxLDAuNDU1YTUuOSw1LjksMCwwLDAtLjIuNjU0Yy0wLjAyOS4xNC0uMDQxLDAuMjgyLTAuMDYxLDAuNDI0YTUuOTIsNS45MiwwLDAsMC0uMDY4LjcxYzAsMC4xNDEuMDE1LDAuMjgsMC4wMjEsMC40MjNhNi4yLDYuMiwwLDAsMCwuMDcuN2MwLjAyNiwwLjE0NS4wNzEsMC4yODUsMC4xMDYsMC40MjhhNS42LDUuNiwwLDAsMCwuMi42NjdjMC4wNTIsMC4xMzkuMTI0LDAuMjY4LDAuMTg4LDAuNGE1LjgwNyw1LjgwNywwLDAsMCwuMzM1LjYzMmMwLjA3OCwwLjEyMy4xNzIsMC4yMzYsMC4yNTgsMC4zNTNhNS44NjIsNS44NjIsMCwwLDAsLjQ3Ni41NzdjMC4wMzEsMC4wMzMuMDUzLDAuMDcyLDAuMDg1LDAuMSwwLjA4OSwwLjA5LjE5MiwwLjE1MSwwLjI4NiwwLjIzMywwLjEyOCwwLjExMy4yMzgsMC4yMzgsMC4zNzUsMC4zNCwzMC40MjUsMjIuNCw2OS44MjQsMzQuNzM3LDExMC45NDMsMzQuNzM3LDM3LjkxNCwwLDc0Ljk0Ny0xMC42ODYsMTA0LjI3OS0zMC4wODhsMC4wMTgtLjAxMnMwLjAxLS4wMDcuMDE3LTAuMDFhNS42NjgsNS42NjgsMCwwLDAsLjU3MS0wLjQ2OGMwLjExMi0uMS4yMzYtMC4xNzEsMC4zNDItMC4yNzUsMCwwLDAsMCwwLDBhNS45MzksNS45MzksMCwwLDAsLjc1Ny0wLjkyMSw2LjM2OCw2LjM2OCwwLDAsMCwuMzI3LTAuNjE1YzAuMDY3LS4xMzUuMTU1LTAuMjYxLDAuMjExLTAuNGE1LjgsNS44LDAsMCwwLC4yNzItMC44OTFjMC4wMTYtLjA3LjA0NS0wLjEzMiwwLjA2LTAuMmE2LjExNyw2LjExNywwLDAsMCwuMDg3LTAuODU1YzAuMDA2LS4wOTQuMDI3LTAuMTg2LDAuMDI4LTAuMjc5YTUuOTQ4LDUuOTQ4LDAsMCwwLS4wODQtMC45MTJDMzEyLjcxNywzNzUuMjE0LDMxMi43MiwzNzUuMTM4LDMxMi43MDgsMzc1LjA2M1pNNDQwLjUsMTU2bDU5LDExMS41LTgwLDE3NC0zNy45MjMtMS41MjRaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMC4yODEgLTAuMzI4KSIvPgogIDwvZz4KPC9zdmc+Cg==');
		add_submenu_page($this->plugin->name, $this->plugin->displayName, 'Settings', 'manage_options', $this->plugin->name, array( $this, 'renderAdminPanel' ));
		// add_submenu_page($this->plugin->name, $this->plugin->displayName, 'Integrations','manage_options', $this->plugin->name.'-integrations', array( $this, 'renderAdminPanel' ));
		add_submenu_page($this->plugin->name, $this->plugin->displayName, 'Tools','manage_options', $this->plugin->name.'-tools', array( $this, 'renderAdminPanel' ));
	}

	/**
	* Refresh settings property with latest values from DB (or the defaults)
	*/
	function refreshSettings() {
		$this->settings = array(
			// General
			'gbbot_team_cpt_enable' => get_option('gbbot_team_cpt_enable', false),
			'gbbot_team_post_label' => get_option('gbbot_team_post_label', ''),
			'gbbot_team_post_type' => get_option('gbbot_team_post_type', ''),

			'gbbot_featured_image_post_types' => get_option('gbbot_featured_image_post_types', []),

			'gbbot_enable_return_to_top' => get_option('gbbot_enable_return_to_top', 'bottom_right'),

			// Integrations
			'gbbot_rebound_id' => esc_html(wp_unslash(get_option('gbbot_rebound_id', ''))),

			// Advanced
			'gbbot_active_branch' => get_option('gbbot_active_branch', ''),
			'gbbot_super_users' => get_option('gbbot_super_users', []),
			'gbbot_admin_css' => get_option('gbbot_admin_css', ''),
			'gbbot_rank_math_author_blacklist' => get_option('gbbot_rank_math_author_blacklist', []),
			'gbbot_rank_math_author_replacement' => get_option('gbbot_rank_math_author_replacement', 0),
		);
	}

	/**
	* Preset notices to be used on the frontend
	*/
	function registerNotices() {
		$this->notices = array(
			'gbtc_warning_label' => '<span style="color:red;font-weight:700;">*</span>',
			'gbtc_warning' => <<<EOD
				<div class="postbox {$this->WARNING_CLASS}">
					<h3><span style="color:red;font-weight:700">*</span> = GB Theme Core Detected</h3>
					<div class="inside">
						<p style="color:red">
							GB Theme Core is currently activated. This means any options in these boxes will have no affect. However, they can be pre-configured before switching themes. 
						</p>
						<p>
							To get the most features out of {$this->plugin->displayName}, install and enable the <strong>Proactive by GB</strong> theme.
						</p>
					</div>
				</div>
			EOD,
			'super_user_only' => '<h3 style="position: absolute;background: #2271b1;color: white;top: 0;right: 0;">Super User Only</h3>',
			'inactive_plugins_warning_label' => '<span style="color:red;font-weight:700;">**</span>',
			'inactive_plugins_warning' => <<<EOD
				<div class="postbox {$this->WARNING_CLASS}">
					<h3><span style="color:red;font-weight:700">**</span> = Related Plugin is Deactivated</h3>
					<div class="inside">
						<p style="color:red">
							A plugin related to this setting is currently deactivated or not installed.
						</p>
						<p>
							Options in these boxes will have no affect. However, they can be pre-configured before activating the related plugin.
						</p>
					</div>
				</div>
			EOD,
		);
	}

	/**
	* Utility function for updating settings
	*/
	function updateOrDeleteOption($option, $value) {
		if (is_null($value) || empty($value)) {
			delete_option($option);
		} else {
			update_option($option, $value);
		}
	}

	/**
	* Output the Administration Panel
	* Save POSTed data from the Administration Panel into a WordPress option
	*/
	function renderAdminPanel() {
		global $GBTC_ACTIVE;

		// only admin user can access this page
		if ( !current_user_can( 'level_10' ) ) {
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
						/**
						* GENERAL TAB
						*/

						// Team CPT
						$this->updateOrDeleteOption('gbbot_team_cpt_enable', ($_REQUEST['gbbot_team_cpt_enable'] ?? null));
						$this->updateOrDeleteOption('gbbot_team_post_label', ($_REQUEST['gbbot_team_post_label'] ?? null));
						$this->updateOrDeleteOption('gbbot_team_post_type', ($_REQUEST['gbbot_team_post_type'] ?? null));

						// Featured Image Admin Thumbnail
						$this->updateOrDeleteOption('gbbot_featured_image_post_types', ($_REQUEST['gbbot_featured_image_post_types'] ?? null));

						// Back to Top arrow
						$this->updateOrDeleteOption('gbbot_enable_return_to_top', ($_REQUEST['gbbot_enable_return_to_top'] ?? null));
						
						/**
						* INTEGRATIONS TAB
						*/
						$this->updateOrDeleteOption('gbbot_rebound_id', ($_REQUEST['gbbot_rebound_id'] ?? null));

						/**
						* ADVANCED TAB
						*/
						// Git branch selection
						$this->updateOrDeleteOption('gbbot_active_branch', ($_REQUEST['gbbot_active_branch'] ?? null));

						// Super-user-only Settings
						if ($this->is_super_user) {
							// Super Users
							$input_super_users = ($_REQUEST['gbbot_super_users'] ?? null);
							$input_super_users = !empty($input_super_users) ? array_filter(array_map(function($input) {
								return trim($input);
							}, explode(',',$input_super_users)), function($input) {
								return !empty($input);
							}) : null;
							$this->updateOrDeleteOption('gbbot_super_users', $input_super_users);

							// Admin CSS
							$this->updateOrDeleteOption('gbbot_admin_css', ($_REQUEST['gbbot_admin_css'] ?? null));

							// Rank Math SEO - "Written By" Override
							if ($this->active_plugins['rank-math']) {
								$this->updateOrDeleteOption('gbbot_rank_math_author_blacklist', ($_REQUEST['gbbot_rank_math_author_blacklist'] ?? null));
								$this->updateOrDeleteOption('gbbot_rank_math_author_replacement', ($_REQUEST['gbbot_rank_math_author_replacement'] ?? null));
							}
						}

						$this->message = __( 'Settings Saved. Refresh the page to see the changes.', 'gb-bot' );
						break;
					
					default:
						# code...
						break;
				}
			}
		}

		// Get latest settings
		$this->refreshSettings();

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
		$meta = get_option( 'gbbot_rebound_id', '' );
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
require_once("includes/functions.php");
require_once("includes/shortcodes.php");