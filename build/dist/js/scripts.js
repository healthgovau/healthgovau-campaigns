// Health local scripts
$(function() {

    console.log("Loaded scripts.js");

    //YouTube embed on click
      $(document).on("click", ".js-video-play", function(e) {
        e.preventDefault();
        var videoid = $(this).attr("data-youtubeid");
        console.log(videoid + ' clicked');
        // Create an iFrame with autoplay set to true
        var iframe_url = "https://www.youtube.com/embed/" + videoid + "?autoplay=1&autohide=2&border=0&wmode=opaque&rel=0&html5=1&fs=1";
        if ($(this).data('params')) iframe_url += '&' + $(this).data('params');
        // The height and width of the iFrame should be the same as parent
        var iframe = $('<iframe/>', { 'title': 'YouTube video player', 'id': 'youtube-iframe', 'allowfullscreen': 'allowfullscreen', 'frameborder': '0', 'aria-live': 'assertive', 'src': iframe_url });
        // Replace the YouTube thumbnail with YouTube HTML5 Player
        $(this).replaceWith(iframe);
      });

});
