(function( $ ) {
	'use strict';
  
  jQuery(document).ready(function() {
    jQuery('#newor-media-ad-management-wrapper select, input').change(function () {
      jQuery('.newor-media-unsaved-changes').show();
    });

    jQuery('#newor-media-ad-management-wrapper .ad-placement-select').change(function() {
      var selected = jQuery(this).find(':selected');
      if (selected.hasClass('is-paragraph-compatible')) {
        jQuery(this).parents('td').find('.paragraph-setting').addClass('is-active');
        jQuery(this).parents('td').find('.paragraph-setting').attr('required', 'required');
        jQuery(this).parents('td').find('.paragraph-setting').removeAttr('disabled');
      }
      else {
        jQuery(this).parents('td').find('.paragraph-setting').val('');
        jQuery(this).parents('td').find('.paragraph-setting').removeClass('is-active');
        jQuery(this).parents('td').find('.paragraph-setting').attr('disabled', 'disabled');
        jQuery(this).parents('td').find('.paragraph-setting').removeAttr('required');
      }
    });
    
    toggleAdPlacements();

    function toggleAdPlacements() {
      if (jQuery('#newor-media-ad-management-wrapper #enable-ads-input').is(':checked')) {
        jQuery('#newor-media-ad-unit-settings .form-table').show();
      }
      else {
        jQuery('#newor-media-ad-unit-settings .form-table').hide();
      }
    }

    jQuery('#newor-media-ad-management-wrapper #enable-ads-input').change(function() {
      toggleAdPlacements();
    });

    jQuery('#newor-media-ad-unit-settings .view-sizes').click(function(e) {
      e.stopPropagation();
      var val = jQuery(this).val();
      if (val == 'Show Sizes') {
        jQuery(this).val('Hide Sizes');
      }
      else {
        jQuery(this).val('Show Sizes');
      }
      jQuery(this).parent().find('.size-table').toggle();
    });

    jQuery('#nm-delete-ads-txt').click(function() {
      var data = {
        'action': 'delete_ads_txt',
      };
      // We can also pass the url value separately from ajaxurl for front end AJAX implementations
      jQuery.post(ajaxurl, data, function(response) {
        window.location.href = window.location.href + '&ads-txt-deleted=' + response;
      });
    });
  });

})( jQuery );
