<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * Implements THEME_preprocess_node().
 */
function healthgovau_preprocess_node(&$variables) {
  // Change background color and image for campaign content type.
  if ($variables['type'] == 'campaign') {
    // Get the background color for hero.
    $color = $variables['field_campaign_hero_bg_color'][LANGUAGE_NONE][0]['value'];
    drupal_add_css('section.hero {background-color:' . $color . ';}', 'inline');

    // Get the background image for hero.
    if (isset($variables['field_campaign_hero_bg_image'][LANGUAGE_NONE]) && !empty($variables['field_campaign_hero_bg_image'][LANGUAGE_NONE][0])) {
      $image_url = file_create_url($variables['field_campaign_hero_bg_image'][LANGUAGE_NONE][0]['uri']);
      drupal_add_css('section.hero {background-image: url(' . $image_url . '); background-size: cover;}', 'inline');
    }
  }
}
