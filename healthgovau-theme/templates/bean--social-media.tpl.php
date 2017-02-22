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
<section class="<?php print $classes; ?> band--primary clearfix"<?php print $attributes; ?>>

  <div class="wrapper">

    <h2>Social Media</h2>

    <div class="content"<?php print $content_attributes; ?>>
      <div class="social__feed">
        <ul class="juicer-feed" data-feed-id="<?php print $sm_id; ?>" data-per="<?php print $sm_col; ?>"></ul>
        <p><a href="<?php print $sm_page; ?>" class="see-more">More social media</a></p>
      </div>
      <div class="social__feature">
        <h3 class="light">Follow us</h3>
        <ul class="social__links--stacked">
          <? if ($instagram_link != ''): ?>
            <?php print $instagram_link; ?>
          <? endif; ?>
          <? if ($facebook_link != ''): ?>
            <?php print $facebook_link; ?>
          <? endif; ?>
          <? if ($youtube_link != ''): ?>
            <?php print $youtube_link; ?>
          <? endif; ?>
          <? if ($twitter_link != ''): ?>
            <?php print $twitter_link; ?>
          <? endif; ?>
        </ul>
        <? if ($sm_tag != ''): ?>
          <?php print $sm_tag; ?>
        <? endif; ?>
      </div>
    </div>

  </div>
</section>
