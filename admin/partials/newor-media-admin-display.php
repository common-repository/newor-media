<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link https://newormedia.com
 * @since 1.0.0
 *
 * @package Newor_Media
 * @subpackage Newor_Media/admin/partials
 */
?>

<div class="wrap" id="newor-media-ad-management-wrapper">
	<img src="<?php print plugins_url(); ?>/<?php print $this->plugin_name; ?>/images/nm-logo.png" />
	<h2 id="newormedia-ad-management-title">Newor Media Ad Management</h2>  
	<?php settings_errors(); ?>  
	<form method="POST" action="options.php"> 
		<?php
      settings_fields('newor-media-ad-settings');
		  do_settings_sections( 'newor-media-ad-settings' );
      do_settings_sections( 'newor-media-site-deactivated' );
		?>
	</form>
  <div class="notice notice-warning newor-media-unsaved-changes">
    <p>You have unsaved changes</p>
  </div>
</div>