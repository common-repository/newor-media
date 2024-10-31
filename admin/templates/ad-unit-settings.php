<?php
  $options = get_option('newor_media_settings');
  $ad_units = $this->remoteSettings['ad_units'] ?? [];
  $site_id = $this->remoteSettings['site_id'] ?? '';
  $paragraph_compatible = $this->paragraphCompatibleOptions();
  $ads_txt_status = $this->adsTxtStatus();
?>

<div id="newor-media-ad-unit-settings">
  <?php if (!empty($site_id)): ?>
    <div class="newor-media-ads-txt-status">
      <strong>Ads.txt status:</strong> <?php print wp_kses($ads_txt_status['message'], ['span' => ['class' => []]]); ?>
    </div>
    <?php if (!empty($ads_txt_status['error'])): ?>
      <?php if (!empty($ads_txt_status['is_physical'])): ?>
        <button id="nm-delete-ads-txt" class="button button-primary" type="button">Delete existing ads.txt and sync with Newor Media</button>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($ad_units && empty($ads_txt_status['error'])) :?>
    <?php
      $ads_enabled = $options['ads_enabled'] ?? 0;
    ?>
    <div class="newor-media-enable-ads">
      <label class="toggle" for="enable-ads-input">
        <input <?php print ($ads_txt_status['error'] ? 'disabled' : ''); ?> id="enable-ads-input" class="toggle-checkbox" type="checkbox" name="newor_media_settings[ads_enabled]" value="1" <?php print checked( 1, $ads_enabled, false ); ?> />
        <div class="toggle-switch"></div>
        <span class="toggle-label">Enable Ads</span>
      </label>
    </div>
    <input type="hidden" name="newor_media_settings[site_id]" value="<?php print esc_attr($site_id); ?>">
    <input type="hidden" name="newor_media_settings[delay_header_script]" value="<?php print esc_attr($this->remoteSettings['delay_header_script']); ?>">
    <input type="hidden" name="newor_media_settings[delay_header_script_time]" value="<?php print esc_attr($this->remoteSettings['delay_header_script_time']); ?>">
    <table  <?php print (!$ads_enabled ? 'style="display: none;" ' : ''); ?> class="form-table">
        <caption><h2>Ad placements</h2></caption>
        <thead>
          <tr>
            <th>Ad Unit</th>
            <th>Size</th>
            <th>Type</th>
            <th>Placement</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($ad_units as $ad_unit_id => $ad_unit): ?>
          <?php
            $default_placement = $options['placements'][$ad_unit_id]['placement'] ?? '';
            $default_para = $options['placements'][$ad_unit_id]['para'] ?? '';
          ?>
          <tr>
            <td><?php print esc_html($ad_unit['title']); ?></td>
            <td>
              <div class="newor-media-ad-unit-sizes-wrapper">
                <input class="button view-sizes" type="button" value="Show Sizes">
                <table class="size-table hidden">
                  <thead>
                    <tr>
                      <th>Device</th>
                      <th>Size</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($ad_unit['sizes'] as $device => $size): ?>
                      <tr class="newor-media-ad-unit-sizes">
                        <td class="newor-media-ad-unit-sizes-device">
                          <?php print esc_html(ucfirst($device)); ?>
                        </td>
                        <td class="newor-media-ad-unit-sizes-size">
                          <?php print esc_html($size); ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
              </table>
            <td>
              <?php print esc_html($ad_unit['type']); ?>
            </td>
            <td>
              <div class="newor-media-inline">
                <?php if ($ad_unit['placement_method'] == 'plugin'): ?>
                  <select class="ad-placement-select" name="<?php print esc_attr('newor_media_settings[placements][' . $ad_unit_id . '][placement]'); ?>">
                    <option value="">- Select -</option>
                    <?php foreach ($this->placementOptions() as $p_option => $option_name): ?>
                      <?php
                        $option_class = isset($paragraph_compatible[$p_option]) ? 'is-paragraph-compatible' : '';
                        $selected = $default_placement == $p_option;
                      ?>
                      <option <?php print ($selected ? 'selected' : ''); ?> class="<?php print esc_attr($option_class); ?>" value="<?php print esc_attr($p_option); ?>"><?php print esc_html($option_name); ?></option>
                    <?php endforeach; ?>
                    <?php
                      $input_class = isset($paragraph_compatible[$default_placement]) ? ' is-active' : '';
                    ?>
                    <input <?php print ($input_class ? 'required' : ''); ?> min="1" max="30" <?php print (!$input_class ? 'disabled' : ''); ?> class="<?php print esc_attr('paragraph-setting' . $input_class); ?>" type="number" name="<?php print esc_attr('newor_media_settings[placements][' . $ad_unit_id . '][para]'); ?>" value="<?php print esc_attr($default_para); ?>">
                <?php elseif ($ad_unit['placement_method'] == 'manual'): ?>
                  <p><strong>&lt;div id="waldo-tag-<?php print esc_html($ad_unit_id); ?>"&gt;&lt;/div&gt;</strong><p>
                <?php else: ?>
                  <?php if ($ad_unit['placement_method'] == 'automatic'): ?>
                    <?php if ($ad_unit['type'] == 'sticky_footer'): ?>
                      <?php $enabled = $options['sticky_footer_enabled'] ?? 'enabled'; ?>
                      <select class="ad-placement-select-sticky-footer" name="newor_media_settings[sticky_footer_enabled]">
                        <option <?php print selected('enabled', $enabled, false ); ?> value="enabled">Enabled</option>
                        <option <?php print selected('disabled', $enabled, false ); ?> value="disabled">Disabled</option>
                      </select>
                      <input type="hidden" name="newor_media_settings[sticky_footer]" value="<?php print esc_attr($ad_unit_id); ?>">
                    <?php elseif ($ad_unit['type'] == 'interstitial'): ?>
                      <?php $enabled = $options['interstitial_enabled'] ?? 'enabled'; ?>
                      <select class="ad-placement-select-interstitial" name="newor_media_settings[interstitial_enabled]">
                        <option <?php print selected('enabled', $enabled, false ); ?> value="enabled">Enabled</option>
                        <option <?php print selected('disabled', $enabled, false ); ?> value="disabled">Disabled</option>
                      </select>
                    <?php else: ?>
                      <p><strong>Dynamic placement</strong></p>
                    <?php endif; ?>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>                
      </table>
    <?php endif; ?>
    <?php if (!empty($site_id)) :?>
      <?php if (empty($ads_txt_status['error'])): ?>
        <div class="newor-media-ad-settings-submit">    
          <?php submit_button(); ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="notice notice-warning newor-media-new-publisher-message"><p>We could not find your site in our system</p><div class="nm-contact-button"><a class="button button-primary" target="_blank" href="https://newormedia.com/sign">Apply Now</a></div></div>
    <?php endif; ?>
</div>