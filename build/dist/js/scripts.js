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
    var plxSpeed = 0.35;

    plxBackground.css('top', - (plxWindowTopToWindowTop * plxSpeed) + 'px');
  }
}

function debounce(func, wait, immediate) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
};

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

    // Sticky nav
    var stickyNavTop = $('.sticky-nav').offset().top;
    var stickyNav = function(){
      var scrollTop = $(window).scrollTop();
      if (scrollTop > stickyNavTop) {
        $('.sticky-nav').addClass('on');
      } else {
        $('.sticky-nav').removeClass('on');
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

    // Transcript
    $(".video__transcript-link").click(function(e){
      e.preventDefault();
        $(this).parent().next(".video__transcript-content").slideToggle('slow');
    })

});
