<?php
/**
 * @file
 * Default theme implementation for beans.
 *
 * Available variables:
 * - $content: An array of comment items. Use render($content) to print them all, or
 *   print a subset such as render($content['field_example']). Use
 *   hide($content['field_example']) to temporarily suppress the printing of a
 *   given element.
 * - $title: The (sanitized) entity label.
 * - $url: Direct url of the current entity if specified.
 * - $page: Flag for the full page state.
 * - $classes: String of classes that can be used to style contextually through
 *   CSS. It can be manipulated through the variable $classes_array from
 *   preprocess functions. By default the following classes are available, where
 *   the parts enclosed by {} are replaced by the appropriate values:
 *   - entity-{ENTITY_TYPE}
 *   - {ENTITY_TYPE}-{BUNDLE}
 *
 * Other variables:
 * - $classes_array: Array of html class attribute values. It is flattened
 *   into a string within the variable $classes.
 *
 * @see template_preprocess()
 * @see template_preprocess_entity()
 * @see template_process()
 */
?>
<section class="<?php print $classes; ?> band--gradient clearfix"<?php print $attributes; ?>>

  <div class="wrapper">

    <h2>Social Media</h2>

    <div class="content"<?php print $content_attributes; ?>>
      <div class="social__feed">
        <ul class="juicer-feed" data-feed-id="<?php print $sm_id; ?>" data-per="<?php print $sm_col; ?>"></ul>
        <p><a href="<?php print $sm_page; ?>" class="see-more">More social media</a></p>
      </div>
      <div class="social__feature">
        <h3 class="light">Follow us</h3>
        <ul>
          <!--<li class="social__links-item"><a href="#">Instagram</a></li>-->
          <li class="social__links-item"><a href="<?php print $facebook_link; ?>" class="facebook">Facebook</a></li>
          <li class="social__links-item"><a href="<?php print $youtube_link; ?>" class="youtube">YouTube</a></li>
          <li class="social__links-item"><a href="<?php print $twitter_link; ?>" class="twitter">Twitter</a></li>
        </ul>
      </div>
    </div>

  </div>
</section>
