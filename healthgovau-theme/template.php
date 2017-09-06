<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

CONST SOCIAL_MEDIA = '//assets.juicer.io';
CONST GOOGLE_MAP_API = '//maps.googleapis.com/maps/api/staticmap';
CONST THEME_PATH_TOKEN = '/sites/all/themes/healthgovau-theme';
CONST THEME_PATH_TOKEN_GENERIC = '[theme-path]';

/**
 * Implements THEME_preprocess_html().
 */
function healthgovau_preprocess_html(&$variables) {

  // Attributes for html element.
  $variables['html_attributes_array'] = array(
    'lang' => $variables['language']->language,
    'dir' => $variables['language']->dir,
  );

  // Serialize RDF Namespaces into an RDFa 1.1 prefix attribute.
  if ($variables['rdf_namespaces']) {
    $prefixes = array();
    foreach (explode("\n  ", ltrim($variables['rdf_namespaces'])) as $namespace) {
      // Remove xlmns: and ending quote and fix prefix formatting.
      $prefixes[] = str_replace('="', ': ', substr($namespace, 6, -1));
    }
    $variables['rdf_namespaces'] = ' prefix="' . implode(' ', $prefixes) . '"';
  }

  // Add current path, split up, to the body.
  $path = drupal_get_path_alias();
  $aliases = explode('/', $path);
  foreach($aliases as $alias) {
    $variables['classes_array'][] = drupal_clean_css_identifier($alias);
  }

  // Add google analytics JS.
  $env = theme_get_setting('env');
  $auth = theme_get_setting('ga_auth');
  $id = theme_get_setting('ga_id');

  switch ($env) {
    case 'prod': {
      $js = '(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'' . $id . '\');';
      drupal_add_js($js, 'inline');
      $variables['tag_manager'] = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $id . '"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
      break;
    }
    default:
      $js = '(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl+ \'&gtm_auth=' . $auth .'&gtm_preview=env-7&gtm_cookies_win=x\';f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'' . $id .'\');';
      drupal_add_js($js, 'inline');
      $variables['tag_manager'] = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $id . '&gtm_auth=' . $auth . '&gtm_preview=env-7&gtm_cookies_win=x"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
  }

  // Add font scripts.
  drupal_add_js('//ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js', 'external');
  drupal_add_js('WebFont.load({
	  google: {
	    families: [\'Open+Sans:400italic,700,400:latin,latin-ext\']
	  }
	});', 'inline');
}

/**
 * Override or insert variables into the html templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("html" in this case.)
 */
function healthgovau_process_html(&$variables, $hook) {
  // Flatten out html_attributes.
  $variables['html_attributes'] = drupal_attributes($variables['html_attributes_array']);
}


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

  // Add modal js to transcript field.
  if ($variables['element']['#field_name'] == 'field_transcript') {
    $object = $variables['element']['#object'];
    $variables['nid'] = $object->nid;

    // Add modal JS.
    drupal_add_js(drupal_get_path('theme', 'healthgovau') . '/js/healthgovau.modal.js');
  }
  
  // Create variable for address field.
  if ($variables['element']['#field_name'] == 'field_address') {
    // Only show the map in event node view. 
    if (arg(0) == 'node' && is_numeric(arg(1))) {
      $node = node_load(arg(1));
      if ($node->type == 'event') {
        $node = $variables['element']['#object'];
        $google_api = theme_get_setting('ga_api');
        $lat = isset($node->field_location_lat[LANGUAGE_NONE]) ? $node->field_location_lat[LANGUAGE_NONE][0]['value'] : '0';
        $long = isset($node->field_location_long[LANGUAGE_NONE]) ? $node->field_location_long[LANGUAGE_NONE][0]['value'] : 0;
        $src = GOOGLE_MAP_API . '?center=' . $lat . ',' . $long . '&zoom=13&size=300x300&maptype=roadmap&key=' . $google_api;
        $src .= '&markers=color:blue%7Clabel:S%7C' . $lat . ',' . $long;
        $address = $variables['items'][0]['#address'];
        $variables['location_map'] ='<img src="' . $src . '" alt="' . $address['thoroughfare'] . ' ' . $address['locality'] .'" />';
      }
    }
  }
}

/**
 * Implements THEME_preprocess_page().
 */
function healthgovau_preprocess_page(&$variables) {
  // Add images path to variable.
  $variables['images'] = '/' . drupal_get_path('theme', 'healthgovau') . '/images';

  // Add hero indicator.
  if (arg(0) == 'node' && is_numeric(arg(1))) {
    // This is a node page.
    $node = node_load(arg(1));
    if ($node->type == 'campaign') {
      $variables['full_hero'] = 'js-parallax-window hero';
      $variables['hero_bg'] = 'js-parallax-background hero-bg';
    }
    else {
      $variables['full_hero'] = 'hero--content';
      $variables['hero_bg'] = 'hero-bg';
    }
  }
  else {
    $variables['full_hero'] = 'hero--content';
    $variables['hero_bg'] = 'hero-bg';
  }
}

/**
 * Implements THEME_preprocess_node().
 */
function healthgovau_preprocess_node(&$variables) {

  $node = $variables['node'];

  // Replace absolute path with dynamic path to theme.
  if (isset($variables['content']['body'])) {
    $variables['content']['body'][0]['#markup'] = str_replace(THEME_PATH_TOKEN, '/' . path_to_theme(), $variables['content']['body'][0]['#markup']);
    $variables['content']['body'][0]['#markup'] = str_replace(THEME_PATH_TOKEN_GENERIC, '/' . path_to_theme(), $variables['content']['body'][0]['#markup']);
  }

  // Change background color and image for campaign and related content type.
  if ($variables['type'] == 'campaign') {
    $campaign_nid = $variables['nid'];
    _healthgovau_set_hero_bg($campaign_nid, FALSE);
  }
  else {
    if (isset($variables['field_campaign'][LANGUAGE_NONE])) {
      $campaign_nid = $variables['field_campaign'][LANGUAGE_NONE][0]['target_id'];
      _healthgovau_set_hero_bg($campaign_nid, TRUE);
    } 
    if (isset($variables['field_campaign'][0])) {
      $campaign_nid = $variables['field_campaign'][0]['target_id'];
      _healthgovau_set_hero_bg($campaign_nid, TRUE);
    }
  }

  // Create variables for social media page.
  if ($variables['type'] == 'social_media') {
    // Add juicer js and css.
    drupal_add_js(SOCIAL_MEDIA . '/embed.js', 'external');
    drupal_add_css(SOCIAL_MEDIA . '/embed.css', 'external');

    // Find the field values.
    $sm_id = $variables['field_social_media_id'][0]['value'];
    $sm_col = $variables['field_social_media_column'][0]['value'];
    $sm_perpage = $variables['field_social_media_per_page'][0]['value'];
    $sm_type = isset($variables['field_social_media_type'][0]) ? $variables['field_social_media_type'][0]['value'] : '';
    $variables['sm_id'] = $sm_id;
    $variables['sm_col'] = $sm_col;
    $variables['sm_perpage'] = $sm_perpage;
    $variables['sm_type'] = $sm_type;
  }

  // Add status indicator to event page using preprocess field.
  if ($variables['type'] == 'event') {
    $start_date = strtotime($node->field_event_date[LANGUAGE_NONE][0]['value']);
    $end_date = strtotime($node->field_event_date[LANGUAGE_NONE][0]['value2']);

    $indicator = 'Open';
    if (time() < $start_date) {
      $indicator = 'Upcoming';
    }
    else if (time() > $end_date) {
      $indicator = 'Completed';
    }
    $variables['event_status'] ='<div class="field fa-info-circle"><div class="field-label">Event status: <span class="event_status">' . $indicator . '</span></div></div>';
  }

  // Add immunisation JS to all immunisation node pages.
  $variables['immunisation_survey'] = '';
  if ($node->nid == '931') {
    drupal_add_js(drupal_get_path('theme', 'healthgovau') . '/js/immunisation-survey.js');
    $variables['immunisation_survey'] = '<div id=\'SI_e9tKdVZvk8qGE0B\'><!--DO NOT REMOVE-CONTENTS PLACED HERE--></div>';
  }
  elseif (isset($node->field_campaign[LANGUAGE_NONE])) {
    if ($node->field_campaign[LANGUAGE_NONE][0]['target_id'] == '931') {
      drupal_add_js(drupal_get_path('theme', 'healthgovau') . '/js/immunisation-survey.js');
      $variables['immunisation_survey'] = '<div id=\'SI_e9tKdVZvk8qGE0B\'><!--DO NOT REMOVE-CONTENTS PLACED HERE--></div>';
    }
  }
}

/**
 * Implements THEME_preprocess_views_views().
 */
function healthgovau_preprocess_views_view(&$vars) {
  // Add hero background image to campaign videos, activity, event view.
  if ($vars['view']->name == 'campaign_videos' || $vars['view']->name == 'activities' || $vars['view']->name == 'event') {
    $campaign_nid = $vars['view']->args[0];
    _healthgovau_set_hero_bg($campaign_nid, FALSE);
  }
}

/**
 * Implements THEME_preprocess_block().
 */
function healthgovau_preprocess_block(&$vars) {
  $block = $vars['block'];
  // Add title variable if the current block is video card block.
  if ($block->bid == 'views-campaign_videos-block_1' || $block->bid == 'views-campaign_videos-block_4') {
    if (arg(0) == 'node' && is_numeric(arg(1))) {
      // This is a node page.
      $node = node_load(arg(1));
      if ($node->type == 'campaign') {
        // This is a campaign landing page.
        if (isset($node->field_campaign_vblock_3_title[LANGUAGE_NONE])) {
          $vars['vblock_3_title'] = $node->field_campaign_vblock_3_title[LANGUAGE_NONE][0]['value'];
        } 
      }
    }
  }
  
  // Add links to activity random block.
  if ($block->bid == 'views-activities-block') {
    $vars['content'] = _healthgovau_campaign_activity_filter(). $vars['content'];
  }
  if ($block->delta == '-exp-activities-page') {
    $vars['content'] = _healthgovau_campaign_activity_filter();
  }
}

/**
 * Implements THEME_preprocess_menu_tree().
 */
function healthgovau_preprocess_menu_tree(&$variables) {
  // Filter the breastscreen menu link for anchors.
  if ($variables['theme_hook_original'] == 'menu_tree__menu_breastscreen_menu') {
    $variables['tree'] = str_replace('/breastscreen', '', $variables['tree']);
  }
}

/**
 * Implements THEME_preprocess_entity().
 */
function healthgovau_preprocess_entity(&$variables) {
  if ($variables['entity_type'] == 'bean') {

    $bean = $variables['bean'];

    // Replace absolute path with dynamic path to theme.
    if (isset($variables['content']['field_bean_body'])) {
      $token = '/sites/all/themes/healthgovau-theme';
      $variables['content']['field_bean_body'][0]['#markup'] = str_replace($token, '/' . path_to_theme(), $variables['content']['field_bean_body'][0]['#markup']);
    }

    // For social media bean blocks.
    if ($bean->type == 'social_media') {
      // Add juicer js and css.
      drupal_add_js(SOCIAL_MEDIA . '/embed.js', 'external');
      drupal_add_css(SOCIAL_MEDIA . '/embed.css', 'external');

      // Find the field values.
      $sm_id = $bean->field_social_media_id[LANGUAGE_NONE][0]['value'];
      $sm_col = $bean->field_social_media_column[LANGUAGE_NONE][0]['value'];
      $instagram = !isset($bean->field_instagram_id[LANGUAGE_NONE]) ? '' : '<li class="social__links-item"><a href="' . $bean->field_instagram_id[LANGUAGE_NONE][0]['value'] . '" class="instagram">Instagram</a></li>';
      $facebook = !isset($bean->field_facebook_id[LANGUAGE_NONE]) ? '' : '<li class="social__links-item"><a href="' . $bean->field_facebook_id[LANGUAGE_NONE][0]['value'] . '" class="facebook">Facebook</a></li>';
      $youtube = !isset($bean->field_youtube_channel_id[LANGUAGE_NONE]) ? '' : '<li class="social__links-item"><a href="' . $bean->field_youtube_channel_id[LANGUAGE_NONE][0]['value'] . '" class="youtube">YouTube</a></li>';
      $twitter = !isset($bean->field_twitter_id[LANGUAGE_NONE]) ? '' : '<li class="social__links-item"><a href="' . $bean->field_twitter_id[LANGUAGE_NONE][0]['value'] . '" class="twitter">Twitter</a></li>';
      $sm_page = !isset($bean->field_social_media_page_link[LANGUAGE_NONE]) ? '#' : $bean->field_social_media_page_link[LANGUAGE_NONE][0]['value'];
      $sm_tag = !isset($bean->field_social_media_tag[LANGUAGE_NONE]) ? '' : '<div class="social-media-tag">' . $bean->field_social_media_tag[LANGUAGE_NONE][0]['value'] . '</div>';
      $sm_desc = !isset($bean->field_social_media_description[LANGUAGE_NONE]) ? '' : $bean->field_social_media_description[LANGUAGE_NONE][0]['value'];
      $variables['sm_desc'] = $sm_desc;
      $variables['sm_id'] = $sm_id;
      $variables['sm_col'] = $sm_col;
      $variables['instagram_link'] = $instagram;
      $variables['facebook_link'] = $facebook;
      $variables['youtube_link'] = $youtube;
      $variables['twitter_link'] = $twitter;
      $variables['sm_page'] = $sm_page;
      $variables['sm_tag'] = $sm_tag;
    }

    // For share this block.
    if ($bean->delta == 'share-this') {
      Global $base_url;
      $current_url = drupal_encode_path($base_url . '/' . drupal_get_path_alias(current_path()));
      $current_title = drupal_get_title();
      $variables['field_bean_body'][0]['value'] = str_replace('[current-page:title]', $current_title, $variables['field_bean_body'][0]['value']);
      $variables['field_bean_body'][0]['value'] = str_replace('[current-page:url]', $current_url, $variables['field_bean_body'][0]['value']);
      $variables['content']['field_bean_body'][0]['#markup'] = $variables['field_bean_body'][0]['value'];
    }

    // For hero logo block.
    if ($bean->delta == 'campaign-hero-logo') {
      // Get the logo image for hero.
      if (arg(0) == 'node' && is_numeric(arg(1))) {
        // It is a node page.
        $node = node_load(arg(1));
        if (isset($node->field_campaign_hero_logo[LANGUAGE_NONE])) {
          // This is a campaign node.
          _healthgovau_campaign_hero_logo($node, $variables);
        }
        else if (isset($node->field_campaign[LANGUAGE_NONE])) {
          // This is a campaign related node.
          $campaign_node = node_load($node->field_campaign[LANGUAGE_NONE][0]['target_id']);
          if (isset($campaign_node->field_campaign_hero_logo[LANGUAGE_NONE])) {
            // Find out the logo.
            _healthgovau_campaign_hero_logo($campaign_node, $variables);
          }
          else {
            $variables['logo_img'] = '';
            $variables['logo_url'] = '';
          }
        }
        else {
          $variables['logo_img'] = '';
          $variables['logo_url'] = '';
        }
      }
      else {
        // This is not a node page.
        if (arg(0) == 'campaign' && is_numeric(arg(1))) {
          // This is a campaign related view page.
          $campaign_node = node_load(arg(1));
          if (isset($campaign_node->field_campaign_hero_logo[LANGUAGE_NONE])) {
            // Find out the logo.
            _healthgovau_campaign_hero_logo($campaign_node, $variables);
          }
          else {
            $variables['logo_img'] = '';
            $variables['logo_url'] = '';
          }
        }
      }
    }
  }
}

/**
 * Implements THEME_breadcrumb().
 */
function healthgovau_breadcrumb($variables) {
  
  $breadcrumb = $variables['breadcrumb'];

  if (empty($breadcrumb)) {
    return NULL;
  }
  
  // Process breadcrumb for UI KIT format.
  $breadcrumb_list = '<ul>';
  foreach($breadcrumb as $link) {
    $breadcrumb_list .= '<li>' . $link . '</li>';
  }
  $breadcrumb_list .= '</ul>';

  $output = '<nav class="breadcrumbs" aria-label="breadcrumb"><div class="wrapper">' . $breadcrumb_list . '</div></nav>';

  // Provide a navigational heading to give context for breadcrumb links to
  // screen-reader users. Make the heading invisible with .element-invisible.
  return '<h2 class="element-invisible">' . t('You are here') . '</h2>' . $output;
}

/**
 * Implements hook_form_alter().
 */
function healthgovau_form_alter(&$form, &$form_state, $form_id) {
  // Get the referrer page for feedback webform.
  if ($form_id == 'webform_client_form_1') {
    $referrer_url = $_SERVER['HTTP_REFERER'];
    $form['submitted']['is_this_a_feedback_for']['#type'] = 'select';
    $form['submitted']['is_this_a_feedback_for']['#options'] = array(
      'The whole website' => t('The whole website'),
      $referrer_url => t('The page you were just on'),
    );
    // Remove the default validate for the new option in the select list.
    // @todo Use another approach to add the option dynamically.
    $form['#validate'] = array();
    // Attach character countdown JS.
    $form['#attached']['js'][] = drupal_get_path('theme', 'healthgovau') . '/js/healthgovau.feedback.js';
  }
}

/**
 * Implements hook_js_alter().
 */
function healthgovau_js_alter(&$variables) {
  // Swap out jQuery to use an updated version of the library for node page.
  if (arg(0) == 'node' && is_numeric(arg(1))) {
    $variables['misc/jquery.js']['data'] = drupal_get_path('theme', 'healthgovau') . '/js/jquery.js';
    $variables['misc/jquery.js']['version'] = '3.1.1';
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
    drupal_add_css('.hero-bg {background-color:' . $color . ';}', 'inline');

    // Get the background image for hero.
    if (isset($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE]) && !empty($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE][0])) {
      // Use random background image.
      $image_num = 0;
      if ($random == TRUE) {
        $image_num = array_rand($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE]);
      }
      $image_url = file_create_url($campaign->field_campaign_hero_bg_image[LANGUAGE_NONE][$image_num]['uri']);
      drupal_add_css('.hero-bg {background-image: url(' . $image_url . '); background-size: cover;}', 'inline');
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
  $campaign_nid = $node->field_campaign[LANGUAGE_NONE][0]['target_id'];
  $campaign = node_load($campaign_nid);
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

/**
 * Helper function to get hero logo link and url.
 *
 * @param $node
 *   The campaign node.
 * @param $variables
 *   The bean variables.
 */
function _healthgovau_campaign_hero_logo($node, &$variables) {
  $image_url = file_create_url($node->field_campaign_hero_logo[LANGUAGE_NONE][0]['uri']);
  $variables['logo_img'] = $image_url;
  $variables['logo_url'] = '/' . drupal_get_path_alias('node/' . $node->nid);
  $variables['logo_alt'] = $node->field_campaign_hero_logo[LANGUAGE_NONE][0]['alt'];

}

/**
 * Helper function to generate filter links to activity view.
 * 
 * @return string
 */
function _healthgovau_campaign_activity_filter() {
  $vocab = taxonomy_vocabulary_machine_name_load('activity_type');
  $terms = taxonomy_get_tree($vocab->vid);

  $markup = '<div class="activity__selector"><h3>Find your activity</h3><p>Do you prefer?</p>';
  $markup .= '<div class="tags"><dl><dt class="visuallyhidden">Type</dt>';
  foreach($terms as $term) {
    $markup .= '<dd><a href="/campaign/' . arg(1) . '/activities?field_activity_type_tid%5B%5D=' . $term->tid . '">' . $term->name . '</a></dd>';
  }
  $markup .= '</dl></div></div>';
  
  return $markup;
}

/**
 * Implements theme_webform_element().
 *
 * @param $variables
 *
 * @return string
 */
function healthgovau_webform_element($variables) {
  // Ensure defaults.
  $variables['element'] += array(
    '#title_display' => 'before',
  );

  $element = $variables['element'];

  // All elements using this for display only are given the "display" type.
  if (isset($element['#format']) && $element['#format'] == 'html') {
    $type = 'display';
  }
  else {
    $type = (isset($element['#type']) && !in_array($element['#type'], array('markup', 'textfield', 'webform_email', 'webform_number'))) ? $element['#type'] : $element['#webform_component']['type'];
  }

  // Convert the parents array into a string, excluding the "submitted" wrapper.
  $nested_level = $element['#parents'][0] == 'submitted' ? 1 : 0;
  $parents = str_replace('_', '-', implode('--', array_slice($element['#parents'], $nested_level)));

  $wrapper_attributes = isset($element['#wrapper_attributes']) ? $element['#wrapper_attributes'] : array('class' => array());
  $wrapper_classes = array(
    'form-item',
    'webform-component',
    'webform-component-' . $type,
  );
  if (isset($element['#title_display']) && strcmp($element['#title_display'], 'inline') === 0) {
    $wrapper_classes[] = 'webform-container-inline';
  }
  $wrapper_attributes['class'] = array_merge($wrapper_classes, $wrapper_attributes['class']);
  $wrapper_attributes['id'] = 'webform-component-' . $parents;
  $output = '<div ' . drupal_attributes($wrapper_attributes) . '>' . "\n";

  // If #title_display is none, set it to invisible instead - none only used if
  // we have no title at all to use.
  if ($element['#title_display'] == 'none') {
    $variables['element']['#title_display'] = 'invisible';
    $element['#title_display'] = 'invisible';
    if (empty($element['#attributes']['title']) && !empty($element['#title'])) {
      $element['#attributes']['title'] = $element['#title'];
    }
  }
  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . _webform_filter_xss($element['#field_prefix']) . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . _webform_filter_xss($element['#field_suffix']) . '</span>' : '';

  // Description text.
  // Always output description text between the label and field.
  $description = '';
  if (!empty($element['#description'])) {
    $description = ' <div class="description">' . $element['#description'] . "</div>\n";
  }

  switch ($element['#title_display']) {
    case 'inline':
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= $description;
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= $description;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= $description;
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  $output .= "</div>\n";

  return $output;
}