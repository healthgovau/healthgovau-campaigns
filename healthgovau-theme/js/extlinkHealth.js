(function ($) {

  // Customised version of the jQuery used by extlinks.
  // https://www.drupal.org/project/extlink
  // v7.x-1.18

  // Default settings.
  Drupal.settings.extlinkHealth = {
    extRel: 'external' // Name to apply to the relative attribute of external links.
  };

  Drupal.extlinkHealth = Drupal.extlinkHealth || {};

  Drupal.extlinkHealth.attach = function (context, settings) {
    if (!settings.hasOwnProperty('extlinkHealth')) {
      return;
    }

    // Strip the host name down, removing ports, subdomains, or www.
    var pattern = /^(([^\/:]+?\.)*)([^\.:]{4,})((\.[a-z]{1,4})*)(:[0-9]{1,5})?$/;
    var host = window.location.host.replace(pattern, '$3$4');
    var subdomain = window.location.host.replace(pattern, '$1');

    // Determine what subdomains are considered internal.
    var subdomains;
    if (settings.extlinkHealth.extSubdomains) {
      subdomains = "([^/]*\\.)?";
    }
    else if (subdomain == 'www.' || subdomain == '') {
      subdomains = "(www\\.)?";
    }
    else {
      subdomains = subdomain.replace(".", "\\.");
    }

    // Build regular expressions that define an internal link.
    var internal_link = new RegExp("^https?://" + subdomains + host, "i");

    // Extra internal link matching.
    var extInclude = false;
    if (settings.extlinkHealth.extInclude) {
      extInclude = new RegExp(settings.extlinkHealth.extInclude.replace(/\\/, '\\'), "i");
    }

    // Extra external link matching.
    var extExclude = false;
    if (settings.extlinkHealth.extExclude) {
      extExclude = new RegExp(settings.extlinkHealth.extExclude.replace(/\\/, '\\'), "i");
    }

    // Extra external link CSS selector exclusion.
    var extCssExclude = false;
    if (settings.extlinkHealth.extCssExclude) {
      extCssExclude = settings.extlinkHealth.extCssExclude;
    }

    // Extra external link CSS selector explicit.
    var extCssExplicit = false;
    if (settings.extlinkHealth.extCssExplicit) {
      extCssExplicit = settings.extlinkHealth.extCssExplicit;
    }

    // Find all links which are NOT internal and begin with http as opposed
    // to ftp://, javascript:, etc. other kinds of links.
    // When operating on the 'this' variable, the host has been appended to
    // all links by the browser, even local ones.
    // In jQuery 1.1 and higher, we'd use a filter method here, but it is not
    // available in jQuery 1.0 (Drupal 5 default).
    var external_links = new Array();
    var mailto_links = new Array();
    $("a:not(." + settings.extlinkHealth.extClass + ", ." + settings.extlinkHealth.mailtoClass + "), area:not(." + settings.extlinkHealth.extClass + ", ." + settings.extlinkHealth.mailtoClass + ")", context).each(function (el) {
      try {
        var url = this.href.toLowerCase();
        if (url.indexOf('http') == 0
          && ((!url.match(internal_link) && !(extExclude && url.match(extExclude))) || (extInclude && url.match(extInclude)))
          && !(extCssExclude && $(this).parents(extCssExclude).length > 0)
          && !(extCssExplicit && $(this).parents(extCssExplicit).length < 1)) {
          external_links.push(this);
        }
        // Do not include area tags with begin with mailto: (this prohibits
        // icons from being added to image-maps).
        else if (this.tagName != 'AREA'
          && url.indexOf('mailto:') == 0
          && !(extCssExclude && $(this).parents(extCssExclude).length > 0)
          && !(extCssExplicit && $(this).parents(extCssExplicit).length < 1)) {
          mailto_links.push(this);
        }
      }
        // IE7 throws errors often when dealing with irregular links, such as:
        // <a href="node/10"></a> Empty tags.
        // <a href="http://user:pass@example.com">example</a> User:pass syntax.
      catch (error) {
        return false;
      }
    });

    if (settings.extlinkHealth.extClass) {
      Drupal.extlinkHealth.applyClassAndSpan(external_links, settings.extlinkHealth.extClass);
    }

    if (settings.extlinkHealth.extRel) {
      Drupal.extlinkHealth.applyExternalRel(external_links, settings.extlinkHealth.extRel);
    }

    if (settings.extlinkHealth.mailtoClass) {
      Drupal.extlinkHealth.applyClassAndSpan(mailto_links, settings.extlinkHealth.mailtoClass);
    }

    if (settings.extlinkHealth.extTarget) {
      // Apply the target attribute to all links.
      $(external_links).attr('target', settings.extlinkHealth.extTarget);
    }

    Drupal.extlinkHealth = Drupal.extlinkHealth || {};

    // Set up default click function for the external links popup. This should be
    // overridden by modules wanting to alter the popup.
    Drupal.extlinkHealth.popupClickHandler = Drupal.extlinkHealth.popupClickHandler || function () {
        if (settings.extlinkHealth.extAlert) {
          return confirm(settings.extlinkHealth.extAlertText);
        }
      }

    $(external_links).click(function (e) {
      return Drupal.extlinkHealth.popupClickHandler(e);
    });
  };

  /**
   * Apply a class and a trailing <span> to all links not containing images.
   *
   * @param links
   *   An array of DOM elements representing the links.
   * @param class_name
   *   The class to apply to the links.
   */
  Drupal.extlinkHealth.applyClassAndSpan = function (links, class_name) {
    var $links_to_process;
    if (Drupal.settings.extlinkHealth.extImgClass) {
      $links_to_process = $(links);
    }
    else {
      var links_with_images = $(links).find('img').parents('a');
      $links_to_process = $(links).not(links_with_images);
    }
    $links_to_process.addClass(class_name);
    var i;
    var length = $links_to_process.length;
    for (i = 0; i < length; i++) {
      var $link = $($links_to_process[i]);
      if ($link.css('display') == 'inline' || $link.css('display') == 'inline-block') {
        if (class_name == Drupal.settings.extlinkHealth.mailtoClass) {
          $link.append('<span class="' + class_name + '"><span class="element-invisible"> ' + Drupal.settings.extlinkHealth.mailtoLabel + '</span></span>');
        }
        else {
          $link.append('<span class="' + class_name + '"><span class="element-invisible"> ' + Drupal.settings.extlinkHealth.extLabel + '</span></span>');
        }
      }
    }
  };

  /**
   * Apply a name to the relative attribute of a link.
   * Excludes links around images.
   *
   * @param links
   *   An array of DOM elements representing the links.
   * @param rel_name
   *   The value to place in the rel attribute.
   */
  Drupal.extlinkHealth.applyExternalRel = function (links, rel_name) {
    var links_with_images = $(links).find('img').parents('a');
    var $links_to_process = $(links).not(links_with_images);
    $links_to_process.each(function() {
      $(this).attr('rel', rel_name);
    });
  };

  Drupal.behaviors.extlinkHealth = Drupal.behaviors.extlinkHealth || {};
  Drupal.behaviors.extlinkHealth.attach = function (context, settings) {
    // Backwards compatibility, for the benefit of modules overriding extlink
    // functionality by defining an "extlinkAttach" global function.
    if (typeof extlinkAttach === 'function') {
      extlinkAttach(context);
    }
    else {
      Drupal.extlinkHealth.attach(context, settings);
    }
  };

})(jQuery);
