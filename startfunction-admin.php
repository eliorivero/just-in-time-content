<?php
defined( 'ABSPATH' ) || exit;

/**
 * Creates a settings page for the plugin
 * @author StartFunction
 * @since	1.0.0
 */
class StartFunction_JITC_Admin {

	var $settings;
	var $settings_page_id;
	var $basefile;
	var $prefix;
	var $base_uri;
	var $plugin_data = array();
	
	function __construct ( $args = array() ) {
		$this->prefix = $args['prefix'] . '_';
		$this->base_uri = $args['base_uri'];
		$this->settings = get_option( $this->prefix . 'settings' );

		if ( is_admin() ) {
			$this->settings_page_id = $this->prefix . 'options';
			$this->basefile = $args['basefile'];

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script_and_style' ) );

			register_activation_hook( $this->basefile, array( $this, 'activate' ) );
			register_uninstall_hook( $this->basefile, $this->prefix . 'uninstall_hook' );
		}
	}

	/**
	 * Creates the contextual help for this plugin
	 * @param string
	 * @return string
	 * @since 1.0.0
	 */
	function help() {
		$html = '<h5>' . esc_html__( 'Welcome!', 'startfunction' ) . '</h5>';
		$html .= '<p>' . sprintf( esc_html__( 'This plugin displays a different home page based on the times of the day selected. If there is a time period where no page has been assigned, it will use the page you select in Settings > Reading in the Your homepage displays option.', 'startfunction' ), $this->plugin_data['Name'] ) . '</p>';
		
		$html .= '<p><em>' . sprintf( esc_html__( '%s created by Elio Rivero. Follow %s on Twitter for the latest updates.', 'startfunction' ),
			'<a href="https://startfunction.com/time-based-content-for-wordpress">' . $this->plugin_data['Name'] . '</a>',
			'<a href="https://twitter.com/eliorivero">@eliorivero</a>'
		) . '</em></p>';

		get_current_screen()->add_help_tab( array(
			'id'      => $this->prefix . 'help-main',
			'title'   => esc_html__( 'Introduction', 'startfunction' ),
			'content' => $html,
		));

	}
	
	/**
	 * Creates the options page for this plugin
	 * @since 1.0.0
	 */
	function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__('You do not have sufficient permissions to access this page.', 'startfunction') );
		}
		?>
		<div class="wrap">
			<h2><?php echo $this->plugin_data['Name']; ?></h2>
			
			<form action="options.php" method="post">
				<?php settings_fields( $this->prefix . 'settings' ); ?>
				<?php do_settings_sections( $this->prefix . 'options' ); ?>
				<input class="button-primary" name="<?php _e( 'Submit','startfunction' ); ?>" type="submit" value="<?php esc_attr_e( 'Save Changes', 'startfunction' ); ?>" />
			</form>
		</div>
		<?php
	}
	
	/**
	 * Defines the settings field for the plugin options page.
	 * @since 1.0.0
	 */
	function admin_init() {
		$page = $this->prefix . 'options';

		register_setting( $this->prefix . 'settings', $this->prefix . 'settings', [ $this, 'validate_options' ] );

		add_settings_section( $this->prefix . 'morning', esc_html__( 'Morning Settings', 'startfunction' ), [ $this, 'morning' ], $page );
		add_settings_section( $this->prefix . 'noon', esc_html__( 'Noon Settings', 'startfunction' ), [ $this, 'noon' ], $page );
		add_settings_section( $this->prefix . 'afternoon', esc_html__( 'Afternoon Settings', 'startfunction' ), [ $this, 'afternoon' ], $page );
		add_settings_section( $this->prefix . 'night', esc_html__( 'Night Settings', 'startfunction' ), [ $this, 'night' ], $page );
		add_settings_section( $this->prefix . 'general', esc_html__( 'General Settings', 'startfunction' ), [ $this, 'general' ], $page );
		add_settings_section( $this->prefix . 'donate', '', [ $this, 'donate' ], $page );

		$hours = $this->get_hours();

		$pages_raw = get_pages( [
			'numberposts' => -1,
			'orderby' => 'title',
		] );

		$pages = ['' => ''];
		
		foreach ( $pages_raw as $page ) {
			$pages[$page->ID] = $page->post_title;
		}

		$sections['morning'] = [
			$this->get_hours_select( true, '07:00', 'morning', $hours ),
			$this->get_hours_select( false, '11:00', 'morning', $hours ),
			$this->get_page_for_time( 'morning', $pages ),
		];
		
		$sections['noon'] = [
			$this->get_hours_select( true, '11:00', 'noon', $hours ),
			$this->get_hours_select( false, '13:00', 'noon', $hours ),
			$this->get_page_for_time( 'noon', $pages ),
		];
		
		$sections['afternoon'] = [
			$this->get_hours_select( true, '13:00', 'afternoon', $hours ),
			$this->get_hours_select( false, '18:00', 'afternoon', $hours ),
			$this->get_page_for_time( 'afternoon', $pages ),
		];
		
		$sections['night'] = [
			$this->get_hours_select( true, '18:00', 'night', $hours ),
			$this->get_hours_select( false, '00:00', 'night', $hours ),
			$this->get_page_for_time( 'night', $pages ),
		];

		$sections['general'] = [
			[
				'id' => 'delete_on_uninstall_chk',
				'label' => esc_html__( 'Remove Options on Uninstall', 'startfunction' ),
				'type' => 'checkbox',
				'default' => 1,
				'help' => esc_html__( 'Check this to remove options on uninstall.', 'startfunction' ),
			],
		];
		
		foreach ($sections as $key => $fields) {
			foreach($fields as $field){
				add_settings_field(	$this->prefix . $field['id'], $field['label'], array(&$this, $field['type']),
					$this->prefix . 'options', $this->prefix . $key,
					array( 'field_id' => $field['id'],	'field_default' => $field['default'],
						'field_class' => isset($field['class'])? $field['class'] : null,
						'field_help' => isset($field['help'])? $field['help'] : null,
						'field_ops' => isset($field['options'])? $field['options'] : null )
				);
			}
		}
	}

	/**
	 * When the plugin is activated, setup some options on the database
	 * 
	 * @since 1.0.0
	 */
	function activate() {
		$defaults = array(
			'morning_start_time_sel' => '09:00',
			'morning_end_time_sel' => '11:00',
			'morning_page_sel' => '',
			
			'noon_start_time_sel' => '11:00',
			'noon_end_time_sel' => '13:00',
			'noon_page_sel' => '',
			
			'afternoon_start_time_sel' => '13:00',
			'afternoon_end_time_sel' => '18:00',
			'afternoon_page_sel' => '',
			
			'night_start_time_sel' => '18:00',
			'night_end_time_sel' => '00:00',
			'night_page_sel' => '',
			
			'delete_on_uninstall_chk' => null,
		);
		add_option( $this->prefix . 'settings', $defaults );
	}

	/**
	 * Return a list of the 24 hours as an associative [value => label] array like:
	 * 21:00 => 09:00 pm
	 * The label is rendered using the time format selected by user in Settings > General
	 * 
	 * @return array
	 * 
	 * @since 1.0.0
	 */
	function get_hours() {
		$times = [];
		foreach ( range( 0, 86400, 3600 ) as $timestamp ) {
			$hour_mins = wp_date( 'H:i', $timestamp, new DateTimeZone('UTC') );
			$times[$hour_mins] = wp_date( get_option('time_format'), $timestamp, new DateTimeZone('UTC') );
		}
		return $times;
	}

	/**
	 * Get the options to render a select control that includes the 24 hs of the day
	 * 
	 * @param bool $is_start
	 * @param string $default
	 * @param string $field_id
	 * @param array $hours
	 * 
	 * @return array
	 * 
	 * @since 1.0.0
	 */
	function get_hours_select($is_start, $default, $field_id, $hours) {
		return [
			'id' => $field_id . ( $is_start ? '_start_time_sel' : '_end_time_sel' ),
			'label' => $is_start ? esc_html__( 'Start time', 'startfunction' ) : esc_html__( 'End time', 'startfunction' ),
			'type' => 'select',
			'default' => $default,
			'options' => $hours,
			'help' => $is_start ? esc_html__( 'Select the start time for this page.', 'startfunction' ) : esc_html__( 'Select the end time for this page.', 'startfunction' )
		];
	}

	/**
	 * Get options to render a control to select a page
	 * 
	 * @param string $field_id
	 * @param array $pages
	 * 
	 * @return array
	 * 
	 * @since 1.0.0
	 */
	function get_page_for_time($field_id, $pages) {
		return [
			'id' => $field_id . '_page_sel',
			'label' => esc_html__( 'Page to show', 'startfunction' ),
			'type' => 'select',
			'default' => '',
			'options' => $pages,
			'help' => esc_html__( 'Select the page to show during this time range.', 'startfunction' )
		];
	}
	
	/**
	 * Validates options trying to be saved. Specific sentences are required for each value.
	 * 
	 * @param array
	 * @return array
	 * 
	 * @since 1.0.0
	 */
	function validate_options($input){
		$options = $this->settings;
		
		//Validate select
		foreach ( $input as $key => $value ) {
			if ( strpos( $key,'_sel' ) ) {
				$options[$key] = $input[$key];
			}
		}
		
		// Transfer checkboxes
		foreach ( $options as $key => $value ) {
			if( strpos($key, '_chk') ) {
				$options[$key] = $input[$key];
			}
		}

		$uninstallable_plugins = (array) get_option('uninstall_plugins');
		
		if ( isset( $input['delete_on_uninstall_chk'] ) ) {
			$options['delete_on_uninstall_chk'] = 'on';
			$uninstallable_plugins[plugin_basename($this->basefile)] = true;
		} else {
			unset( $uninstallable_plugins[plugin_basename($this->basefile)] );
		}
		update_option('uninstall_plugins', $uninstallable_plugins);

		return $options;
	}
	
	/**
	 * Callback for morning settings section
	 * 
	 * @since 1.0.0
	 */
	function morning() {
		echo '<p>' . esc_html__( 'Settings for the page to show in the morning.', 'startfunction' ) . '</p>';
	}

	/**
	 * Callback for noon settings section
	 * 
	 * @since 1.0.0
	 */
	function noon() {
		echo '<p>' . esc_html__( 'Settings for the page to show at noon.', 'startfunction' ) . '</p>';
	}
	
	/**
	 * Callback for afternoon settings section
	 * 
	 * @since 1.0.0
	 */
	function afternoon() {
		echo '<p>' . esc_html__( 'Settings for the page to show after noon.', 'startfunction' ) . '</p>';
	}
	
	/**
	 * Callback for night settings section
	 * 
	 * @since 1.0.0
	 */
	function night() {
		echo '<p>' . esc_html__( 'Settings for the page to show at night.', 'startfunction' ) . '</p>';
	}

	/**
	 * Callback for general settings section
	 * @since 1.0.0
	 */
	function general() {
		echo '<p>' . esc_html__( 'General plugin settings.', 'startfunction' ) . '</p>';
	}
	
	/**
	 * Callback for donate section
	 * @since 1.0.0
	 */
	function donate() {
		echo '<div class="startfunction-donate-wrap"><p>If you found this useful, feel free to buy me a coffee ☕️ so I can stay awake and create more useful plugins!</p><p><a class="startfunction-donate-button button-secondary" href="https://www.paypal.com/donate?business=NT84KUJBWKF9G&item_name=Donate%21&currency_code=USD">Please donate!</a></p></div>';
	}

	/**
	 * Creates a checkbox control
	 * 
	 * @param array
	 * 
	 * @since 1.0.0
	 */
	function checkbox($args) {
		extract($args);
		$options = $this->settings;

		if ( isset ( $options[$field_id] ) ) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}
		echo "<label for='".$this->prefix."$field_id'><input $checked id='".$this->prefix."$field_id' name='".$this->prefix."settings[$field_id]' type='checkbox' />";
		if( isset($field_help) ) echo " $field_help";
		echo '</label>';
	}
	
	
	/**
	 * Creates a select element
	 * @param array
	 * @since 1.0.0
	 */
	function select($args) {
		extract($args);
		$options = $this->settings;
		$options[$field_id] = isset($options[$field_id])? $options[$field_id] : $field_default;
		$class = ( isset($field_class) )? "class='$field_class'" : "";
		echo "<select id='".$this->prefix."$field_id' $class name='".$this->prefix."settings[$field_id]'>";
		foreach($field_ops as $key => $value){
			if( isset($options[$field_id]) ){
				if( $key == $options[$field_id]) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
			} else {
				$selected = '';
			}
			echo "<option value='$key' $selected>" . $value . '</option>';
		}
		echo '</select>';
		if( isset($field_help) ){
			echo "<br/><span class='description'>$field_help</span>";
		}
	}
	
	/**
	 * Creates Settings link on plugins list page.
	 * @param array
	 * @param string
	 * @return array
	 * @since 1.0.0
	 */
	function settings_link( $links, $file ) {
		if ( $file == plugin_basename( $this->basefile ) ) {
			$links[] = "<a href='options-general.php?page=".$this->settings_page_id."'><b>" . esc_html__( 'Settings', 'startfunction' ) . "</b></a>";
		}
		return $links;
	}
	
	/**
	 * Adds Settings link on plugins page. Create options page on wp-admin.
	 * @since 1.0.0
	 */
	function plugin_menu() {
		$this->plugin_data = get_plugin_data( $this->basefile );
		$page_title = $this->plugin_data['Name'];
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), -10, 2 );
		$op = add_options_page( $page_title, $page_title, 'manage_options', $this->settings_page_id, array( $this, 'options_page' ) );
		add_action( 'load-' . $op, array( $this, 'help' ) );
	}

	/**
	 * Get plugin setting
	 * @param string $key Settings key.
	 * @param mixed $default Default value to be used if there's not a setting set.
	 * @return mixed If setting exists, returns it, otherwise the default value if it was passed, otherwise, false.
	 * @since 1.0.0
	 */
	function get( $key, $default = null ) {
		if ( isset( $this->settings[$key] ) ) {
			return $this->settings[$key];
		} elseif ( ! is_null( $default ) ) {
			return $default;
		} else {
			return false;
		}
	}

	/**
	 * Enqueue scripts and styles needed
	 * @since 1.0.0
	 */
	function admin_enqueue_script_and_style( $hook ) {
		if ( 'settings_page_'.$this->prefix.'options' == $hook ) {
			wp_enqueue_style( $this->prefix.'settings', $this->base_uri . '/css/sf-admin-style.css' );
		}
	}

} // class end

/**
 * When the plugin is deactivated, run this function.
 * @since 1.0.0
 */
function startfunction_jitc_uninstall_hook() {
	// if uninstall not called from WordPress exit
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit ();
	}

	$startfunction_jitc_settings = get_option( 'startfunction_jitc_settings' );

	if ( isset( $startfunction_jitc_settings['delete_on_uninstall_chk'] ) ) {
		delete_option( 'startfunction_jitc_settings' );
	}
}
