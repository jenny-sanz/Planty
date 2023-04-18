<?php 

//only run this on admin screens to prevent calling home on every pageload
function easy_testimonials_init_automatic_updater()
{
	if( is_admin() ) {
		$consent_given = get_option('_easy_testimonials_upgrade_consented', '');
		if ( !empty ($consent_given) ) {
			$package_url = easy_testimonials_get_upgrade_package_url();
			$is_plugin_install_page = !empty( $_GET['page'] ) && ($_GET['page'] == 'easy-testimonials-install-plugins');

			if ( !empty( $package_url ) ) {
				require_once( "class-tgm-plugin-activation.php" );
				add_action( 'tgmpa_register', 'easy_testimonials_register_required_plugins' );
			} else if ( $consent_given && $is_plugin_install_page ) {
				// oh no, we have consent but no package. that means we couldn't reach the server,
				// but we're trying to go to the install page. so redirect to the install error page instead
				wp_redirect( admin_url('admin.php?page=easy_testimonials_pro_error_page') );					
				exit();
			}
		}
	}
}

// only relevant to pro users who need to upgrade
$etp_active = ( function_exists('is_plugin_active') && is_plugin_active('easy-testimonials-pro/easy-testimonials-pro.php') );
$registered_name = get_option('_easy_t_registered_name', false);
if ( isValidKey() && !$etp_active && !empty($registered_name) ) {
	add_action('admin_init', 'easy_testimonials_init_automatic_updater');
}

function easy_testimonials_register_interstitial_page() 
{
	add_submenu_page( 
		'plugins',
		__('Privacy Notice'),
		__('Privacy Notice'),
		'manage_options',
		'easy_testimonials_pro_privacy_notice',
		'easy_testimonials_render_privacy_notice_page'
	);	
	
	add_submenu_page( 
		'plugins',
		__('Error'),
		__('Error'),
		'manage_options',
		'easy_testimonials_pro_error_page',
		'easy_testimonials_render_error_page'
	);
}
add_action( 'admin_menu', 'easy_testimonials_register_interstitial_page' );

function easy_testimonials_render_error_page()
{
	$members_url = 'https://goldplugins.com/members/?utm_source=easy_testimonials_free_plugin&utm_campaign=pro_install_error&utm_banner=download_via_members_portal';
	$error_msg = '<p>' . __('We will not be able to automatically install Easy Testimonials Pro. Please visit the')
				 . sprintf( ' <a href="%s">%s</a> ', esc_url($members_url), __('Members Portal') )
				 .  __('to download the plugin or contact support.')
				 . '</p>';
?>
	<h1><?php esc_html_e('Error'); ?></h1>
	<?php echo esc_html($error_msg); ?>
<?php
}

function easy_testimonials_render_privacy_notice_page()
{
	$package_url = easy_testimonials_get_upgrade_package_url();
	if ( !empty($_GET['consent']) ) {
		update_option( '_easy_testimonials_upgrade_consented', current_time('timestamp') );
	}
	
	$consent_given = get_option('_easy_testimonials_upgrade_consented', '');
	if ( !empty($consent_given) ) {
		printf('<script type="text/javascript">window.location = "%s";</script>', esc_url(admin_url('admin.php?page=easy-testimonials-install-plugins')));
		die();
	}
	
	$privacy_notice = '<p>In order to install Easy Testimonials Pro, we must contact the Gold Plugins server. We will send only your API key and the URL of this website, in order to verify your license.</p>';
	$privacy_notice .= '<p>We respect your privacy and handle your data carefully. You can view our full <a href="https://goldplugins.com/privacy-policy/?utm_source=easy_testimonials_free_plugin&utm_campaign=view_privacy_policy">Privacy Policy on our website</a>.</p>';	
	$privacy_notice .= sprintf( '<p><button class="button button-primary">%s</button></p>',
							    __('Verify License &amp; Continue') . ' &raquo' );
?>
	<h1><?php esc_html_e('Privacy Notice'); ?></h1>
	<form method="post" action="<?php echo esc_url( add_query_arg('consent', '1') ); ?>">
	<?php
		echo esc_html($privacy_notice);
	?>
	</form>
<?php
}

function easy_testimonials_get_upgrade_package_url()
{
	$package_url = get_transient('_easy_testimonials_upgrade_package_url');
	if ( empty($package_url) ) {
		$package_url = easy_testimonials_get_upgrade_package_url_from_server();
		set_transient('_easy_testimonials_upgrade_package_url', $package_url, 3600); // 1 hr
	}
	return !empty($package_url)
		   ? $package_url
		   : '';
}
function easy_testimonials_get_upgrade_package_url_from_server()
{
	$api_url = 'https://goldplugins.com/';
	$response = wp_remote_post( $api_url, array(
		'method'      => 'POST',
		'timeout'     => 5,//turned down from 20 due to repeated reports of live site slowdowns
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'body'        => array(
			'gp_edd_action' => 'get_upgrade_package',
			'gp_edd_site_url' => home_url(),
			'gp_edd_license' => get_option('easy_t_registered_key'),
			'gp_edd_product_id' => 6993,
			'gp_edd_email' => get_option('easy_t_registered_name'),
		),
		'verify_ssl' => false,
		'cookies'     => array()
		)
	);
	
	if ( !is_wp_error( $response ) ) {
		$response = !empty($response['body'])
					? json_decode($response['body'])
					: array();
		if ( !empty($response) && !empty($response->package_url) ) {
			return $response->package_url;
		}
	}
	
	// unknown error
	return '';
}
function easy_testimonials_register_required_plugins()
{
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$package_url = easy_testimonials_get_upgrade_package_url();
	if ( empty($package_url) ) {
		return;
	}
	
	$plugins = array(
		// This is an example of how to include a plugin from an arbitrary external source in your theme.
		array(
			'name'         => 'Easy Testimonials Pro', // The plugin name.
			'slug'         => 'easy-testimonials-pro', // The plugin slug (typically the folder name).
			'source'       => $package_url,
			'required'     => true, // If false, the plugin is only 'recommended' instead of required.
			'external_url' => 'https://goldplugins.com/downloads/easy-testimonials-pro/?utm_source=easy_testimonials_free_plugin&utm_campaign=install_pro&utm_banner=plugin_info_link', // If set, overrides default API URL and points to an external URL.
		)
/*
		// This is an example of the use of 'is_callable' functionality. A user could - for instance -
		// have WPSEO installed *or* WPSEO Premium. The slug would in that last case be different, i.e.
		// 'wordpress-seo-premium'.
		// By setting 'is_callable' to either a function from that plugin or a class method
		// `array( 'class', 'method' )` similar to how you hook in to actions and filters, TGMPA can still
		// recognize the plugin as being installed.
		array(
			'name'        => 'WordPress SEO by Yoast',
			'slug'        => 'wordpress-seo',
			'is_callable' => 'wpseo_init',
		),
*/
	);
	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'easy-testimonials',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'easy-testimonials-install-plugins', // Menu slug.
		'parent_slug'  => 'easy-testimonials-settings',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => false,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		'strings'      => array(
			'page_title' => __('Install') . ' Easy Testmonials Pro',
			'menu_title' => __('Install') . ' Pro Plugin',
		)
		/*
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'easy-testimonials' ),
			'menu_title'                      => __( 'Install Plugins', 'easy-testimonials' ),
			/* translators: %s: plugin name. * /
			'installing'                      => __( 'Installing Plugin: %s', 'easy-testimonials' ),
			/* translators: %s: plugin name. * /
			'updating'                        => __( 'Updating Plugin: %s', 'easy-testimonials' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'easy-testimonials' ),
			'notice_can_install_required'     => _n_noop(
				/* translators: 1: plugin name(s). * /
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'easy-testimonials'
			),
			'notice_can_install_recommended'  => _n_noop(
				/* translators: 1: plugin name(s). * /
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'easy-testimonials'
			),
			'notice_ask_to_update'            => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'easy-testimonials'
			),
			'notice_ask_to_update_maybe'      => _n_noop(
				/* translators: 1: plugin name(s). * /
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'easy-testimonials'
			),
			'notice_can_activate_required'    => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'easy-testimonials'
			),
			'notice_can_activate_recommended' => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'easy-testimonials'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'easy-testimonials'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'easy-testimonials'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'easy-testimonials'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'easy-testimonials' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'easy-testimonials' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'easy-testimonials' ),
			/* translators: 1: plugin name. * /
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'easy-testimonials' ),
			/* translators: 1: plugin name. * /
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'easy-testimonials' ),
			/* translators: 1: dashboard link. * /
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'easy-testimonials' ),
			'dismiss'                         => __( 'Dismiss this notice', 'easy-testimonials' ),
			'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'easy-testimonials' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'easy-testimonials' ),
			'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
		),
		*/
	);
	tgmpa( $plugins, $config );
}
function easy_testimonials_tgmpa_change_source_name($table_data)
{
	foreach($table_data as $index => $plugin)
	{
		if ($plugin['slug'] == 'easy-testimonials-pro') {
			$table_data[$index]['source'] = '<a href="https://goldplugins.com/?utm_source=easy_testimonials_free&utm_campaign=upgrade_to_pro&utm_banner=plugin_source_link" target="_blank">Gold Plugins</a>';
		}
	}
	return $table_data;	
}
add_filter('tgmpa_table_data_items', 'easy_testimonials_tgmpa_change_source_name');