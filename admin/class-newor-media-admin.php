<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link https://newormedia.com
 * @since 1.0.0
 *
 * @package Newor_Media
 * @subpackage Newor_Media/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Newor_Media
 * @subpackage Newor_Media/admin
 * @author Newor Media <kawal@newormedia.com>
 */
class Newor_Media_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

  /**
	 * Remote site settings.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $remoteSettings.
	 */
	private $remoteSettings = [];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
    add_action('admin_menu', array($this, 'addPluginAdminMenu'), 9);   
    add_action('admin_init', array($this, 'registerAndBuildFields'));
    add_action('wp_ajax_delete_ads_txt', array($this, 'deleteAdsTxt'));
    
    global $pagenow;
    if ($pagenow == 'admin.php') {
      if (isset($_GET['page']) && $_GET['page'] == 'newor-media') {
        $url = get_site_url();
        $parts = parse_url($url);
        $site_host = $parts['host'] ?? '';
        $request = wp_remote_get(NEWOR_MEDIA_BASE_URL . '/site-settings?site=' . $site_host);
        if (!is_wp_error($request)) {
          $body = wp_remote_retrieve_body($request);
          $this->remoteSettings = json_decode($body, TRUE);
          $options = get_option('newor_media_settings', []);
          if (!$options) {
            $options = [];
          }
          // If options not set then set the site id as a start.
          // This is needed for ads.txt file to work.
          if (isset($this->remoteSettings['site_id'])) {
            $options['site_id'] = $this->remoteSettings['site_id'];
          }
          else {
            $options['ads_enabled'] = 0;
          }
          $options['site_deactivated'] = $this->remoteSettings['site_deactivated'] ?? 0;
          update_option('newor_media_settings', $options);
        }
      }
    }
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/newor-media-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/newor-media-admin.js', array('jquery'), $this->version, FALSE);
	}

  /**
   * Add admin menu.
   * 
   * @since 1.0.0
   */
  public function addPluginAdminMenu() {
    $icon_url = plugins_url() . '/' . $this->plugin_name . '/images/nm-icon.png';
    add_menu_page( 'Newor Media Ad Management', 'Newor Media', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'), $icon_url, 26);
  }

  /**
   * Display plugin Admin Dashboard.
   * 
   * @since 1.0.0
   */
  public function displayPluginAdminDashboard() {
		require_once 'partials/'.$this->plugin_name.'-admin-display.php';
  }

  /**
   * Register and build settings field.
   * 
   * @since 1.0.0
   */
  public function registerAndBuildFields() {
    add_settings_section(
      'newor-media-ads-txt', 
      '',  
      [$this, 'adsTxtStatus'],
      'newor-media-ad-settings'                   
    );
    
    $options = get_option('newor_media_settings');
    
    if (!empty($options['site_deactivated'])) {
      add_settings_section(
        'newor-media-site-deactivated', 
        '',  
        array($this, 'siteDeactivated'),    
        'newor-media-site-deactivated'                   
      );
    }
    else {
      add_settings_section(
        'newor-media-ad-settings', 
        '',  
        array($this, 'settings'),    
        'newor-media-ad-settings'                   
      );
      register_setting('newor-media-ad-settings', 'newor_media_settings');
    }
  }

  /**
   * Include settings form.
   * 
   * @since 1.0.0
   */
  public function settings() {
    include __DIR__ . '/templates/ad-unit-settings.php';
  }

  /**
   * Display site deactivated section.
   * 
   * @since 1.0.0
   */
  public function siteDeactivated() {
    ?>
    <div class="notice notice-warning newor-media-new-publisher-message"><p>Site is deactivated</p><div class="nm-contact-button"><a class="button button-primary" target="_blank" href="<?php esc_url(NEWOR_MEDIA_BASE_URL . '/contact-wp?site=' . get_site_url()); ?>">Contact Us</a></div></div>
    <?php
  }

  /**
   * Placement options.
   * 
   * @since 1.0.0
   */
  public function placementOptions() {
    return [
      'before_content' => 'Before Content',
      'after_content' => 'After Content',
      'after_paragraph' => 'After Paragraph',
      'after_image' => 'After Image',
      'after_featured_image' => 'After Featured Image',
    ];
  }

  /**
   * Placement options that are compatible with paragraph fields.
   * 
   * @since 1.0.0
   */
  public function paragraphCompatibleOptions() {
    return [
      'after_paragraph' => 'After Paragraph',
      'after_image' => 'After Image',
    ];
  }
  
  /**
   * Check for ads.txt status.
   * 
   * @since 1.0.0
   */
  public function adsTxtStatus() {
    $error = '';
    $is_physical = false;
    $options = get_option('newor_media_settings');
    // Check for static ads.txt file.
    $ads_txt_static_path = ABSPATH . 'ads.txt';
    if (file_exists($ads_txt_static_path)) {
      $error = 'Physical ads.txt file found at ' . ABSPATH . 'ads.txt. Please delete that file in order to run Newor Media ads.';
      $is_physical = true;
      $options = get_option('newor_media_settings');
      $options['ads_enabled'] = 0;
      update_option('newor_media_settings', $options);
    }
    else {
      $ads_txt_url = get_site_url() . '/ads.txt';
      $request = wp_remote_get($ads_txt_url);
      if (!is_wp_error($request) && isset($request['response']['code']) && $request['response']['code'] == 200) {
        $contents = wp_remote_retrieve_body($request);
        if (stripos($contents, 'newormedia.com') === FALSE) {
          $error = 'Newor Media ads.txt lines are missing';
        }
      }
    }

    if (!$error) {
      $message = '<span class="message success">Valid</span>';
    }
    else {
      $message = '<span class="message error">' . $error . '</span>';
    }
    return [
      'message' => $message,
      'error' => $error ? 1 : 0,
      'is_physical' => $is_physical,
    ];
    return $message;
  }

  /**
   * Check for conflicting plugins.
   * 
   * @since 1.0.0
   */
  public function adminNotices() {
    global $pagenow;
    $conflicts = [];
    if ($pagenow == 'admin.php') {
      if (isset($_GET['page']) && $_GET['page'] == 'newor-media') {
        $conflicts = [];
        $plugins_list = [
          'ad-inserter',
          'quick-adsense',
          'quick-adsense-reloaded',
          'advanced-ads',
        ];
        $active_plugins = get_option('active_plugins');
        foreach ($active_plugins as $active_plugin) {
          $plugin_name = explode('/', $active_plugin);
          if (isset($plugin_name[0])) {
            if (in_array($plugin_name[0], $plugins_list)) {
              $conflicts[] = '<strong>' . $plugin_name[0] . '</strong>';
            }
          }
        }
      }
    }
    ?>
    
    <?php if ($conflicts): ?>
      <div class="notice notice-warning is-dismissible">
        <p>The following plugin(s) may cause conflict with the Newor Media Ads: <?php print wp_kses_post(implode(', ', $conflicts)); ?></p>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['settings-updated'])): ?>
      <div class="notice notice-success is-dismissible">
        <p><strong>Please clear site cache for the changes to take effect.</strong></p>
      </div>
    <?php endif; ?>
     <?php if (isset($_GET['ads-txt-deleted'])): ?>
      <?php if ($_GET['ads-txt-deleted'] == 0): ?>
         <div class="notice notice-warning is-dismissible">
          <p><strong>Could not delete the ads.txt file as the file is not writable. Please delete the file manually.</strong></p>
        </div>
      <?php else: ?>
        <div class="notice notice-success is-dismissible">
          <p><strong>Physical ads.txt file has been deleted. Please enable ads below to start running ads!</strong></p>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    <?php
  }

  /**
   * Delete ads.txt file ajax action callback.
   * 
   * @since 1.0.1
   */
  public function deleteAdsTxt() {
    $ads_txt_static_path = ABSPATH . 'ads.txt';
    if (is_writable(dirname($ads_txt_static_path))) {
      unlink($ads_txt_static_path);
      echo 1;
    }
    else {
      echo 0;
    }
    wp_die();
  }

}
