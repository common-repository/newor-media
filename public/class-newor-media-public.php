<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link https://newormedia.com
 * @since 1.0.0
 *
 * @package Newor_Media
 * @subpackage Newor_Media/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package Newor_Media
 * @subpackage Newor_Media/public
 * @author Newor Media <kawal@newormedia.com>
 */
class Newor_Media_Public {

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
   * HTML dom parser.
   * 
   * @since 1.0.0
	 * @access private
   */
  private $htmlDomParser = NULL;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

    $file_path = WP_PLUGIN_DIR . '/' . $plugin_name . '/vendor/autoload.php';
    if (file_exists($file_path)) {
      include_once $file_path;
      if (class_exists('\voku\helper\HtmlDomParser')) {
        $this->htmlDomParser = new \voku\helper\HtmlDomParser();
      }
    }
	}

  /**
	 * Ads.txt lines.
	 *
	 * @since 1.0.0
	 */
	public function ads_txt() {
    $request = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : false;
	  if ( '/ads.txt' === $request || '/ads.txt?' === substr( $request, 0, 9)) {
      header( 'Content-Type: text/plain' );

      $options = get_option('newor_media_settings');
      $ads_txt_url = NEWOR_MEDIA_BASE_URL . '/site/' . $options['site_id'] . '/ads.txt';
      wp_redirect($ads_txt_url);
      die();
    }
	}

  /**
	 * Add Newor Media header script.
	 *
	 * @since 1.0.0
	 */
	public function header_script() {
    $options = get_option('newor_media_settings');
    if (!empty($options['ads_enabled'])) {
      $nm_script_url = '//cdn.thisiswaldo.com/static/js/' . (int) $options['site_id'] . '.js';
      if (!empty($options['delay_header_script'])) {
        $time = $options['delay_header_script_time'] ?? 2000;
        $nm_inline = 'setTimeout(function() {';
        $nm_inline .= 'var e = document.createElement("script");';
        $nm_inline .= 'e.type="text/javascript",';
        $nm_inline .= 'e.src="' . $nm_script_url . '",';
        $nm_inline .= 'document.getElementsByTagName("head")[0].appendChild(e);';
        $nm_inline .= '}, ' . $time . ');';
        wp_add_inline_script('nm_delayed_script', $nm_inline);
      }
      else {
        wp_enqueue_script('nm_script', $nm_script_url, [], $this->version);
      }
    }
	}

  /**
	 * Add Newor Media sticky footer ad.
	 *
	 * @since  1.0.0
	 */
	public function footer_ads() {
    $options = get_option('newor_media_settings');
    if (!empty($options['sticky_footer'])) {
      if (!isset($options['sticky_footer_enabled']) || (isset($options['sticky_footer_enabled']) && $options['sticky_footer_enabled'] == 'enabled')) {
        print '<div id="' . esc_attr('waldo-tag-' . $options['sticky_footer']) . '"></div>';
      }
    }
    if (!isset($options['interstitial_enabled']) || (isset($options['interstitial_enabled']) && $options['interstitial_enabled'] == 'disabled')) {
      print '<div id="waldo-tag-disable-interstitial"></div>';
    }
	}

  /**
	 * Add Newor Media ad after featured image.
	 *
	 * @since 1.0.0
	 */
	public function after_featured_image($html) {
    if (is_singular() && !is_front_page()) {
      $options = get_option('newor_media_settings');
      if (isset($options['placements'])) {
        foreach ($options['placements'] as $ad_unit_id => $placement) {
          if (!empty($placement['placement']) && $placement['placement'] == 'after_featured_image') {
            $html .= '<div id="waldo-tag-' . $ad_unit_id . '"></div>';
          }
        }
      }
    }
    return $html;
	}

  /**
	 * Add Newor Media ads in-content.
	 *
	 * @since 1.0.0
	 */
	public function in_content_ads($html) {
    if (is_singular() && !is_front_page()) {
      $options = get_option('newor_media_settings');

      // First add before and after content ads that do not requrie the html dom parser.
      if (isset($options['placements'])) {
        foreach ($options['placements'] as $ad_unit_id => $placement) {
          if ($placement['placement'] == 'before_content') {
            $html = '<div id="waldo-tag-' . $ad_unit_id . '"></div>' . $html;
          }

          if ($placement['placement'] == 'after_content') {
            $html = $html . '<div id="waldo-tag-' . $ad_unit_id . '"></div>';
          }
        }
      }

      // Add other in-content ad units.
      try {
        if ($html && $this->htmlDomParser) {
          $dom = $this->htmlDomParser::str_get_html($html);
          if ($dom) {
            if (isset($options['placements'])) {
              foreach ($options['placements'] as $ad_unit_id => $placement) {
                if (!empty($placement['placement'])) {
                  if ($placement['placement'] == 'after_paragraph' && !empty($placement['para'])) {
                    $html_para_no = $placement['para'] - 1;
                    $ps = $dom->find('p');
                    if ($p = $dom->find('p')[$html_para_no] ?? NULL) {
                      $p->outerhtml = $p->outerhtml . '<div id="waldo-tag-' . $ad_unit_id . '"></div>';
                      $html = $dom->save();
                    }
                  }

                  if ($placement['placement'] == 'after_image' && !empty($placement['para'])) {
                    $html_para_no = $placement['para'] - 1;
                    if ($img = $dom->find('img')[$html_para_no] ?? NULL) {
                      $img->outerhtml = $img->outerhtml . '<div id="waldo-tag-' . $ad_unit_id . '"></div>';
                      $html = $dom->save();
                    }
                  }
                }
              } 
            }
          }
        }
      }
      catch (\Exception $th) {
    
      }
    }
    return $html;
	}

}
