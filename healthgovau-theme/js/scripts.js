/**
 * @file
 * A JavaScript file for the theme.
 *
 * In order for this JavaScript to be loaded on pages, see the instructions in
 * the README.txt next to this file.
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth
(function ($, Drupal, window, document, undefined) {


// To understand behaviors, see https://drupal.org/node/756722#behaviors
Drupal.behaviors.healthgovauCampaign = {
  attach: function(context, settings) {
          
    function parallax(){
      if( $(".js-parallax-window").length) {
        var plxBackground = $(".js-parallax-background");
        var plxWindow = $(".js-parallax-window");

        var plxWindowTopToPageTop = $(plxWindow).offset().top;
        var windowTopToPageTop = $(window).scrollTop();
        var plxWindowTopToWindowTop = plxWindowTopToPageTop - windowTopToPageTop;

        var plxBackgroundTopToPageTop = $(plxBackground).offset().top;
        var windowInnerHeight = window.innerHeight;
        var plxBackgroundTopToWindowTop = plxBackgroundTopToPageTop - windowTopToPageTop;
        var plxBackgroundTopToWindowBottom = windowInnerHeight - plxBackgroundTopToWindowTop;
        var plxSpeed = 0.2;

        plxBackground.css('top', - (plxWindowTopToWindowTop * plxSpeed) + 'px');
      }
    }
 
    // Hero parallax
    if ($(".js-parallax-window").length) {
      parallax();
    }

    $(window).scroll(function(e) {
      if ($(".js-parallax-window").length) {
        parallax();
      }
      if( $(".sticky-nav").length) {
          stickyNav();
      }
    });    
      
    var stickyNav = function(){
     var scrollTop = $(window).scrollTop();
      if (scrollTop > stickyNavTop) {
        $('.sticky-nav').addClass('on');
      } else {
        $('.sticky-nav').removeClass('on');
      }
    }
    // event gallery
    function eventgallery(){
      $('.image-gallery').lightGallery({
        selector: '.img-gallery > a',
        nextHtml: '<span class="sr-only">Next slide</span>',
        prevHtml: '<span class="sr-only">Previous slide</span>'
      });
     
      var $lg = $('.image-gallery');
      
      $lg.lightGallery();

      $lg.on('onAfterOpen.lg',function(event){
          $( ".lg-outer" ).attr( "role", "dialog" );
          $( ".lg-outer" ).attr( "aria-live", "polite" );
      });
      // force focus to light gallery window 
      $( ".image-gallery a" ).click(function() {
        $( ".lg-outer a.lg-close" ).focus();
      });    
    }
    if ($(".image-gallery").length) {
         eventgallery();
    }
      // Active links
	var path = window.location.pathname + window.location.search;
    $('.activity__selector .tags a[href="' + path + '"]').addClass('active');
  }
};


    
})(jQuery, Drupal, this, this.document);


