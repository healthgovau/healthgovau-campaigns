(function ($) {
  Drupal.behaviors.feedbackCountdown = {
    attach: function (context, settings) {
      $('.form-textarea-wrapper', context).append('<p><span id="chars">1200</span> characters remaining</p>');

      var maxLength = 1200;
      $('textarea').keyup(function() {
        var length = $(this).val().length;
        var length = maxLength-length;
        $(this).parent().find('p #chars').text(length);

        if (length < 0) {
          $(this).css('outline', '2px solid #c00');
          $('input', context).attr('disabled', 'disabled');
        }
        else {
          $(this).css('outline', 'inherit');
          $('input', context).removeAttr('disabled');
        }
      });
    }
  };
}(jQuery));
