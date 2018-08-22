(function($, Drupal) {

  // Store the collapsed state of the fieldsets, so they can be restored later.
  var collapsed = [];

  Drupal.behaviors.health_adminimal = {
    attach: function(context) {

      // Do a fake resize to get the toolbar top padding to fix itself.
      $(window).trigger('resize');

      /**
       * Prevent users from choosing a text format.
       *
       * @param format
       * @param fields
       */
      function lockTextFormat(format, fields) {
        for(var i=0;i<fields.length;i++) {
          $(fields[i]).val(format);
          $(fields[i]).parent().hide();
        }
      }

      // Apply chosen using the new version of chosen.
      $('.chosen-enable').chosen({width: 400});

      // Remove mandatory from empty labels.
      // We would do this in the template override, but forms_accessible is such a terrible module that the override doesn't work
      // https://www.drupal.org/project/accessible_forms/issues/2971863
      $('label').each(function() {
        if ($(this).text().trim() === '(mandatory)') {
          $(this).hide();
        }
      });
      $('span.form-required').text('*');

      // Lock down the text format authors can use.

      // Simple rich text
      lockTextFormat('simple_rich_text', [
        '.node-video-form .field-name-body .filter-list',
        '.node-publication-form .field-name-field-description .filter-list',
        '.node-image-form .field-name-body .filter-list',
        '.node-video-form .field-name-field-transcript .filter-list'
      ]);
    }
  };

})(jQuery, Drupal);
