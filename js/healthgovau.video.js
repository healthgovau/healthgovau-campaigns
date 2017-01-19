(function ($, Drupal, window, document, undefined) {

// To understand behaviors, see https://drupal.org/node/756722#behaviors
  Drupal.behaviors.videoPlay = {
    attach: function(context, settings) {

      //YouTube embed on click
      $(".field-name-field-video-promoted-thumbnail", context).on("click", ".js-video-play", function(e) {
        e.preventDefault();
        var videoid = $(this).attr("data-youtubeid");
        // Create an iFrame with autoplay set to true
        var iframe_url = "https://www.youtube.com/embed/" + videoid + "?autoplay=1&autohide=2&border=0&wmode=opaque&rel=0&html5=1&fs=1";
        if ($(this).data('params')) iframe_url += '&' + $(this).data('params');
        // The height and width of the iFrame should be the same as parent
        var iframe = $('<iframe/>', { 'title': 'YouTube video player', 'id': 'youtube-iframe', 'allowfullscreen': 'allowfullscreen', 'frameborder': '0', 'aria-live': 'assertive', 'src': iframe_url });
        // Replace the YouTube thumbnail with YouTube HTML5 Player
        $('.field-name-field-video-length', context).hide();
        console.log(iframe);
        $(this).replaceWith(iframe);
        $("iframe", context).wrap( '<div class="video"><div class="video-wrapper"></div></div>' );
      });
    }
  };

})(jQuery, Drupal, this, this.document);
