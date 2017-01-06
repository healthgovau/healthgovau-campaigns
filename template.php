<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * Implements THEME_preprocess_field().
 */
function healthgovau_preprocess_field(&$variables) {
  if ($variables['element']['#field_name'] == 'field_video_thumbnail') {
    // Add link to content variable for video thumbnail field.
    $nid = $variables['element']['#object']->nid;
    $variables['link'] = '/' . drupal_get_path_alias('node/' . $nid);
  }
  else if ($variables['element']['#field_name'] == 'field_video_promoted_thumbnail') {
    $nid = $variables['element']['#object']->nid;
    $video_node = node_load($nid);
    $youtube_code = $video_node->field_youtube_video_id[LANGUAGE_NONE][0]['value'];
    $variables['youtube_code'] = $youtube_code;
    // Add JS to embed video.
    drupal_add_js(drupal_get_path('theme', 'healthgovau') . '/js/healthgovau.video.js');
  }
}

/**
 * Implements THEME_preprocess_page().
 */
function healthgovau_preprocess_page(&$variables) {
  // Add hero indicator.
  if (arg(0) == 'campaign') {
    // This is a view or panel page.
    $variables['hero'] = TRUE;
  }
  else if (arg(0) == 'node' && is_numeric(arg(1))) {
    // This is a node page.
    $node = node_load(arg(1));
    if ($node->type == 'campaign' || isset($node->field_campaign[LANGUAGE_NONE])) {
      $variables['hero'] = TRUE;
    }
    else {
      $variables['hero'] = FALSE;
    }
  }
  else {
    $variables['hero'] = FALSE;
  }
}

/**
 * Implements THEME_preprocess_node().
 */
function healthgovau_preprocess_node(&$variables) {
  // Change background color and image for campaign and related content type.
  if ($variables['type'] == 'campaign') {
    $campaign_nid = $variables['nid'];
    _healthgovau_set_hero_bg($campaign_nid, FALSE);
  }
  else {
    if (isset($variables['field_campaign'][0])) {
      $campaign_nid = $variables['field_campaign'][0]['target_id'];
      _healthgovau_set_hero_bg($campaign_nid, TRUE);
    }
  }
}

/**
 * Implements THEME_preprocess_views_views().
 */
function healthgovau_preprocess_views_view(&$vars) {
  // Add hero background image to campaign videos view.
  if ($vars['view']->name == 'campaign_videos') {
    $campaign_nid = $vars['view']->args[0];
    _healthgovau_set_hero_bg($campaign_nid, FALSE);
  }
}

/**
 * Implements THEME_breadcrumb().
 */
function healthgovau_breadcrumb($variables) {
  // Hide breadcrumb for campaign content type.
  if (arg(0) == 'node' && is_numeric(arg(1))) {
    // This is a node page.
    $keys = array_keys($variables['crumbs_trail']);
    if ($keys[0] == 'node' && substr($keys[1], 0, 5) == 'node/') {
      $page_argument = $variables['crumbs_trail'][$keys[1]]['page_arguments'][0];
      $type = $page_argument->type;
      switch ($type) {
        case 'campaign':
          return '';
        // @todo: add other related content types in.
        case 'video':
          return _healthgovau_campaign_breadcrumb($page_argument);
        case 'campaign_standard_page':
          return _healthgovau_campaign_breadcrumb($page_argument);
      }
    }
  }

  // Default breadcrumb from uikit theme.
  $breadcrumb = $variables['breadcrumb'];

  if (!empty($breadcrumb)) {
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    // Process breadcrumb for UI KIT format.
    $breadcrumb_list = '<ul>';
    foreach($breadcrumb as $link) {
      $breadcrumb_list .= '<li>' . $link . '</li>';
    }
    $breadcrumb_list .= '</ul>';

    // Add UI KIT tag and style to breadcrumb.
    $output .= '<nav class="breadcrumbs" aria-label="breadcrumb"><div class="wrapper">' . $breadcrumb_list . '</div></nav>';
    return $output;
  }
}

/**
 * Helper function to set background image and color for campaign and campaign related content type.
 *
 * @param $campaign_nid
 */
function _healthgovau_set_hero_bg($campaign_nid, $random) {
  $campaign = node_load($campaign_nid);
  if (isset($campaign->field_campaign_hero_bg_color[LANGUAGE_NONE]) && isset($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE])) {
    // Get the background color for hero.
    $color = $campaign->field_campaign_hero_bg_color[LANGUAGE_NONE][0]['value'];
    drupal_add_css('section.hero {background-color:' . $color . ';}', 'inline');

    // Get the background image for hero.
    if (isset($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE]) && !empty($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE][0])) {
      // Use random background image.
      $image_num = 0;
      if ($random == TRUE) {
        $image_num = array_rand($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE]);
      }
      $image_url = file_create_url($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE][$image_num]['uri']);
      drupal_add_css('section.hero {background-image: url(' . $image_url . '); background-size: cover;}', 'inline');
    }
  }
}

/**
 * Helper function to generate breadcrumb for campaign related content type.
 *
 * @param $node
 *   The current node.
 *
 * @return string
 *   The breadcrumb HTML.
 */
function _healthgovau_campaign_breadcrumb($node) {
  // Compose breadcrumb.
  $campaign = $node->field_campaign[LANGUAGE_NONE][0]['entity'];
  $campaign_url = $campaign->path['alias'];
  $breadcrumb = array(
    '<a href="/' . $campaign_url . '">' . $campaign->title . '</a>',
    $node->title,
  );
  $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';
  // Process breadcrumb for UI KIT format.
  $breadcrumb_list = '<ul>';
  foreach($breadcrumb as $link) {
    $breadcrumb_list .= '<li>' . $link . '</li>';
  }
  $breadcrumb_list .= '</ul>';
  $output .= '<nav class="breadcrumbs" aria-label="breadcrumb"><div class="wrapper">' . $breadcrumb_list . '</div></nav>';
  return $output;
}
