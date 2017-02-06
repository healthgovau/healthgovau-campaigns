// Health local scripts

function parallax(){
  if( $(".js-parallax-window").length > 0 ) {
    var plxBackground = $(".js-parallax-background");
    var plxWindow = $(".js-parallax-window");

    var plxWindowTopToPageTop = $(plxWindow).offset().top;
    var windowTopToPageTop = $(window).scrollTop();
    var plxWindowTopToWindowTop = plxWindowTopToPageTop - windowTopToPageTop;

    var plxBackgroundTopToPageTop = $(plxBackground).offset().top;
    var windowInnerHeight = window.innerHeight;
    var plxBackgroundTopToWindowTop = plxBackgroundTopToPageTop - windowTopToPageTop;
    var plxBackgroundTopToWindowBottom = windowInnerHeight - plxBackgroundTopToWindowTop;
    var plxSpeed = 0.32;

    plxBackground.css('top', - (plxWindowTopToWindowTop * plxSpeed) + 'px');
  }
}

$(function() {

    //YouTube embed on click
      $(document).on("click", ".js-video-play", function(e) {
        e.preventDefault();
        var videoid = $(this).attr("data-youtubeid");
        // Create an iFrame with autoplay set to true
        var iframe_url = "https://www.youtube.com/embed/" + videoid + "?autoplay=1&autohide=2&border=0&wmode=opaque&rel=0&html5=1&fs=1";
        if ($(this).data('params')) iframe_url += '&' + $(this).data('params');
          // The height and width of the iFrame should be the same as parent
        var iframe = $('<iframe/>', { 'title': 'YouTube video player', 'id': 'youtube-iframe', 'allowfullscreen': 'allowfullscreen', 'frameborder': '0', 'aria-live': 'assertive', 'src': iframe_url });
          // Replace the YouTube thumbnail with YouTube HTML5 Player
          $(this).replaceWith(iframe);
      });

    // Sticky nav
    var stickyNavTop = $('.sticky-nav').offset().top;
    //var navHeight;
    var stickyNav = function(){
      var scrollTop = $(window).scrollTop();
      if (scrollTop > stickyNavTop) {
        $('.sticky-nav').addClass('on');
        //if($('.sticky-nav').hasClass('on')){
        //  navHeight = $('.sticky-nav').height();
        //  $('html').css('margin-top', navHeight + 'px');
        //}
      } else {
        $('.sticky-nav').removeClass('on');
        //$('html').css('margin-top', '0');
      }
    }

    // Currently this is inefficient as it is called repeatedly
    // Consider debouncing or setting a flag
    $(document).bind('ready scroll', function() {
      if ($(".js-parallax-window").length) {
        parallax();
      }
      stickyNav();
    });

    // Share this
    //var url = encodeURIComponent($(location).attr('href'));
    //var title = $("h1").val();
    //$(".social__links-item .twitter").attr("href","https://twitter.com/share?text="+title+"&url="+url);
    //$(".social__links-item .facebook").attr("href","https://facebook.com/sharer.php?u="+url);
    //$(".social__links-item .linkedin").attr("href","http://www.linkedin.com/shareArticle?mini=true&url="+url);
    //$(".social__links-item .email").attr("href","mailto:?subject=Smokes&body="+url);

});
