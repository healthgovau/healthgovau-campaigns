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
CONST ABOUT_CURRENT_CAMPAIGN = '[about-this-campaign]';
CONST ABOUT_CURRENT_CAMPAIGN_HTML = '<li class="first leaf"><a href="[about-this-campaign]" title="About this campaign">About this campaign</a></li>';
CONST FEEDBACK_LINK = '[feedback-link]';
CONST CAMPAIGNS = [
  'girlsmove' => 156,
  'breastscreen' => 166,
  'immunisationfacts' => 931,
  'smokes' => 6,
  'drughelp' => 1376,
  'longliveyou' => 2331,
];

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
    $vid = $variables['element']['#object']->vid;
    $video_node = node_load($nid, $vid);
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
    $variables['location_map'] = '';
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

  // Add class and logic to hide field to paragraph views listing field.
  if ($variables['element']['#field_name'] == 'field_views_listing') {
    foreach ($variables['items'] as $key => $item) {
      $para = array_values($item['entity']['paragraphs_item'])[0]['#entity'];
      if ($para->bundle == 'campaign_listing_view') {
        // Get the paragraph values.
        $view_name = $para->field_para_view_name[LANGUAGE_NONE][0]['value'];
        $campaign_id = $para->field_campaign[LANGUAGE_NONE][0]['target_id'];
        $view_mode = $para->field_para_view_mode[LANGUAGE_NONE][0]['value'];

        // Load the view.
        $view = views_get_view($view_name);
        $view->get_total_rows = TRUE;
        $view->set_display($view_mode);
        $view->preview = TRUE;
        $view->pre_execute(array($campaign_id));
        $view->execute();

        // Remove field if views has no results.
        if ($view->total_rows == 0) {
          unset($variables['items'][$key]);
        }
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
    $variables['immunisation_survey'] = '<div id=\'SI_9mpXcehBBbiUe6V\'><!--DO NOT REMOVE-CONTENTS PLACED HERE--></div>';
  }
  elseif (isset($node->field_campaign[LANGUAGE_NONE])) {
    if ($node->field_campaign[LANGUAGE_NONE][0]['target_id'] == '931') {
      drupal_add_js(drupal_get_path('theme', 'healthgovau') . '/js/immunisation-survey.js');
      $variables['immunisation_survey'] = '<div id=\'SI_9mpXcehBBbiUe6V\'><!--DO NOT REMOVE-CONTENTS PLACED HERE--></div>';
    }
  }

  // Publications.
  if ($variables['type'] == 'publication') {
    // If publication date is the same as last modified, hide the last modified.
    if (isset($variables['content']['changed_date'])) {
      $changed = strtotime($variables['content']['changed_date']['#items'][0]['value']);
      $published = strtotime($variables['field_date_published'][0]['value']);
      if ($changed == $published) {
        $variables['content']['changed_date']['#access'] = FALSE;
      }
    }
  }

  // Check to see if this role has access to view this content type.
  global $user;
  if (key_exists('node', $variables)) {
    // Deny access to anonymous users for Help pages.
    if ($variables['node']->type == 'help') {
      if (key_exists(1, $user->roles)) {
        drupal_access_denied();
      }
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
  if ($variables['theme_hook_original'] == 'menu_tree__menu_long_live_you_menu') {
    $variables['tree'] = str_replace('/longliveyou', '', $variables['tree']);
  }
}

/**
 * Implements THEME_preprocess_entity().
 */
function healthgovau_preprocess_entity(&$variables) {
  if ($variables['entity_type'] == 'bean') {

    $bean = $variables['bean'];

    // Custom token replacement.
    if (isset($variables['content']['field_bean_body'])) {
      $variables['content']['field_bean_body'][0]['#markup'] = str_replace(THEME_PATH_TOKEN, '/' . path_to_theme(), $variables['content']['field_bean_body'][0]['#markup']);
      $variables['content']['field_bean_body'][0]['#markup'] = str_replace(THEME_PATH_TOKEN_GENERIC, '/' . path_to_theme(), $variables['content']['field_bean_body'][0]['#markup']);

      // Replace token in footer about this campaign link with current campaign about page.
      if ($campaign_id = _healthgovau_find_current_campaign()) {
        $campaign_alias = drupal_get_path_alias('node/' . $campaign_id);
        if ($campaign_alias !== 'node/' . $campaign_id && drupal_lookup_path('source', $campaign_alias . '/about-this-campaign')) {
          $replaced_output = str_replace(ABOUT_CURRENT_CAMPAIGN, '/' . $campaign_alias . '/about-this-campaign', ABOUT_CURRENT_CAMPAIGN_HTML);
          $variables['content']['field_bean_body'][0]['#markup'] = str_replace(ABOUT_CURRENT_CAMPAIGN, $replaced_output, $variables['content']['field_bean_body'][0]['#markup']);
        }
        else {
          $variables['content']['field_bean_body'][0]['#markup'] = str_replace(ABOUT_CURRENT_CAMPAIGN, '', $variables['content']['field_bean_body'][0]['#markup']);
        }
      }
      else {
        $variables['content']['field_bean_body'][0]['#markup'] = str_replace(ABOUT_CURRENT_CAMPAIGN, '', $variables['content']['field_bean_body'][0]['#markup']);
      }

      // Feedback link token.
      $path = '/feedback';
      $options['query'] = array(
        'referrer' => drupal_get_path_alias(),
        'campaign' => _healthgovau_get_campaign_name(),
      );
      $options['attributes']['class'] = array('button--feedback');
      $options['attributes']['role'] = array('button');
      $link = l('Provide feedback', $path, $options);
      $variables['content']['field_bean_body'][0]['#markup'] = str_replace(FEEDBACK_LINK, $link, $variables['content']['field_bean_body'][0]['#markup']);
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
        else {
          // This page is not following the pattern, try to find the campaign ID.
          if (in_array(arg(0), array_keys(CAMPAIGNS))) {
            $campaign_id = CAMPAIGNS[arg(0)];
            $campaign_node = node_load($campaign_id);
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

    // Add class to longliveyou global logo block.
    if ($bean->delta == 'longliveyou---global-logo') {
      $variables['classes_array'][] = 'page-header__logo';
    }
  }
  
  // Paragraphs.
  if ($variables['entity_type'] == 'paragraphs_item') {
    $paragraph = $variables['elements']['#entity'];

    // Render views in paragraph for listing paragraph bundle.
    if ($paragraph->bundle == 'campaign_listing_view') {

      // Get the paragraph values.
      $view_name = $paragraph->field_para_view_name[LANGUAGE_NONE][0]['value'];
      $campaign_id = $paragraph->field_campaign[LANGUAGE_NONE][0]['target_id'];
      $view_mode = $paragraph->field_para_view_mode[LANGUAGE_NONE][0]['value'];

      // Load the view.
      $view = views_get_view($view_name);
      $view->get_total_rows = TRUE;
      $view->set_display($view_mode);
      $view->preview = TRUE;
      $view->pre_execute(array($campaign_id));
      $view->execute();

      if ($view->total_rows > 0) {
        // Render the view.
        $variables['para_listing_view'] = $view->preview();
        // If there are no more records to show, hide the more link.
        if ($view->total_rows <= count($view->result)) {
          $variables['content']['field_para_more_link']['#access'] = FALSE;
        }
        // Add some classes to help identify it.
        $variables['classes_array'][] = 'paragraphs-view-' . $view->name;
        $variables['classes_array'][] = 'paragraphs-view-display-' . $view->current_display;
      }
    }

    // Render bean blocks in paragraph.
    if ($paragraph->bundle == 'para_block') {
      $output = '';
      if (isset($paragraph->field_para_block_id[LANGUAGE_NONE])) {
        foreach ($paragraph->field_para_block_id[LANGUAGE_NONE] as $block_delta) {
          $block = block_load('bean', $block_delta['value']);
          $block_render = _block_render_blocks(array($block));
          $block_renderable_array = _block_get_renderable_array($block_render);
          $output .= drupal_render($block_renderable_array);
        }
      }
      $variables['rendered_blocks'] = $output;
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

  // For longliveyou or any anchor links.
  // @todo Refactor this part to find a pattern for anchor links.
  if ($breadcrumb[0] == '<a href="/longliveyou">Healthy and active</a>') {
    $breadcrumb[0] = '<a href="/longliveyou">Home</a>';
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

    // Add a unique id so it can be referenced in an email.
    $date = new DateTime();
    $form['submitted']['reference']['#default_value'] = 'REF-' . $date->format('yj') . '-' . _health_gen_uid(4);

    // Make sure this page isn't cache by Akamai or Drupal.
    drupal_add_http_header('Cache-Control', 'no-cache, no-store');
    drupal_page_is_cacheable(FALSE);
  }
}

/**
 * Generate a random letter based ID of varing lengths.
 *
 * @param int $length
 *
 * @return string
 */
function _health_gen_uid($length = 10) {
  $str = "";
  for ($x = 0; $x < $length; $x++) {
    $str .= substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1);
  }
  return $str;
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

/**
 * Implements hook_file_entity_download_link().
 *
 * Output differently for Image content type.
 *
 * @param $variables
 *
 * @return string
 */
function healthgovau_file_entity_download_link($variables) {

  // If this is not an image content type, do the normal formatting.
  $nid = arg(1);
  if (is_numeric($nid)) {
    $node = node_load($nid);
    if ($node->type != 'image' && $node->type != 'publication' && $node->type != 'audio') {
      return theme_file_entity_download_link($variables);
    }
  }

  // Grab the file.
  $file = $variables['file'];

  // Get file title to use in analytics and accessibility.
  // Default is the resource title.
  $title = $node->title;

  // Publications.
  if ($node->type == 'publication' || $node->type == 'audio') {
    $docs = $node->field_resource_documents[$node->language];
    foreach ($docs as $doc) {
      $entities = entity_load('paragraphs_item', [$doc['value']]);
      if (!empty($entities)) {
        $para_documents = array_pop($entities);
        foreach ($para_documents->field_resource_document[LANGUAGE_NONE] as $resource_document) {
          $entities = entity_load('paragraphs_item', [$resource_document['value']]);
          if (!empty($entities)) {
            $para_document = array_pop($entities);
            if ($para_document->field_file[LANGUAGE_NONE][0]['fid'] == $file->fid) {
              if (count($docs) > 1) { // Multiple document parts.
                $title .= ': ' . $para_documents->field_resource_file_title[LANGUAGE_NONE][0]['value'];
              }
              // Get page count.
              if (isset($para_document->field_resource_file_pages)) {
                $no_of_pages = $para_document->field_resource_file_pages[LANGUAGE_NONE][0]['value'];
              }
            }
          }
        }
      }
    }
  }

  // Images.
  if ($node->type == 'image') {
    $docs = $node->field_para_images[$node->language];
    foreach ($docs as $doc) {
      $entities = entity_load('paragraphs_item', [$doc['value']]);
      if (!empty($entities)) {
        $para_documents = array_pop($entities);
        if ($para_documents->field_file[LANGUAGE_NONE][0]['fid'] == $file->fid) {
          // Get sizing.
          if (isset($para_documents->field_paragraph_title)) {
            $size = $para_documents->field_paragraph_title[LANGUAGE_NONE][0]['value'];
          }
        }
      }
    }
  }

  // Construct the link.
  $variables['text'] = '<div class="file__link">Download <span>' . $title . ' as</span> ' . healthgovau_get_friendly_mime($file->filemime) . '</div>';

  // Add metatdata (file size, image size, no of pages)
  $variables['text'].= '<span class="file__meta"> - ' . format_size($file->filesize);
  if (isset($no_of_pages)) {
    $variables['text'].= ', ' . $no_of_pages . ' pages';
  }
  if (isset($size)) {
    $variables['text'].= ', ' . $size;
  }
  $variables['text'].= '</span>';

  // Get the icon.
  $icon_directory = $variables['icon_directory'];
  $icon = theme('file_icon', array('file' => $file, 'icon_directory' => $icon_directory));

  // Get the path to the file.
  $uri = file_entity_download_uri($file);

  // Set options as per anchor format described at
  // http://microformats.org/wiki/file-format-examples
  $uri['options']['attributes']['type'] = $file->filemime . '; length=' . $file->filesize;
  $uri['options']['html'] = TRUE;

  // Add filename attribute for analytics.
  $uri['options']['attributes']['data-filename'] = $title;
  $uri['options']['attributes']['data-filetype'] = $file->filemime;

  // Output the link.
  $output = '<span class="file"> ' . $icon . ' ' . l($variables['text'], $uri['path'], $uri['options']) . '</span>';

  return $output;
}


/**
 * Convert a mimetype into a human readable format.
 *
 * @param string $mimetype
 *
 * @return string $human
 */
function healthgovau_get_friendly_mime($mimetype) {
  $descriptions = [
    'application/pdf' => '<abbr title="Portable Document Format">PDF</abbr>',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '<abbr title="Microsoft Word document">Word</abbr>',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '<abbr title="Microsoft Excel document">Excel</abbr>',
    'text/plain' => 'plain text',
    'image/jpeg' => '<abbr title="Joint Photographic Experts Group">JPEG</abbr>',
    'image/png' => '<abbr title="Portable Network Graphics">PNG</abbr>',
    'image/gif' => '<abbr title="Graphics Interchange Format">GIF</abbr>',
    'video/mp4' => '<abbr title="MPEG-4 Part 14">MP4</abbr>',
    'audio/mpeg' => '<abbr title="MPEG-1 Audio Layer III">MP3</abbr>',
  ];
  if (array_key_exists($mimetype, $descriptions)) {
    return $descriptions[$mimetype];
  }
  return $mimetype;
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

  // Add hero logo to current metatag OG:image.
  if (isset($node->field_social_image[LANGUAGE_NONE])) {
    $social_image_url = file_create_url($node->field_social_image[LANGUAGE_NONE][0]['uri']);
    $meta_og_image = [
      '#type' => 'html_tag',
      '#tag' => 'meta',
      '#attributes' => [
        'property' => 'og:image',
        'content' => $social_image_url,
      ]
    ];
    drupal_add_html_head($meta_og_image, 'meta_og_image');
  }
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
 * Helper function to find the campaign the current page belongs to.
 * 
 * @return mixed Node ID or FALSE
 */
function _healthgovau_find_current_campaign() {
  if (arg(0) == 'node' && is_numeric(arg(1))) {
    // It is a node page.
    $node = node_load(arg(1));
    if ($node->type == 'campaign') {
      // This is a campaign node.
      return $node->nid;
    }
    else if (isset($node->field_campaign[LANGUAGE_NONE])) {
      // This is a campaign related node.
      return $node->field_campaign[LANGUAGE_NONE][0]['target_id'];
    }
    
    return FALSE;
  }
  else {
    // This is not a node page.
    if (arg(0) == 'campaign' && is_numeric(arg(1))) {
      // This is a campaign related view page.
      return arg(1);
    }
    return FALSE;
  }
}

/**
 * Helper function to get the name of the current campaign.
 * @return string The campaign title or an empty string.
 */
function _healthgovau_get_campaign_name() {
  if ($nid = _healthgovau_find_current_campaign()) {
    $node = node_load($nid);
    if ($node) {
      return $node->title;
    }
  }
  return '';
}

/**
 * Implement THEME_toc_filter().
 */
function healthgovau_toc_filter($variables) {
  $output = '';
  $output .= '<nav class="index-links">';
  $output .= '<h2 id="index-links">' . t('In this section') . '</h2>';
  $output .= $variables['content'];
  $output .= '</nav>';
  return $output;
}

/**
 * Implements THEME_form_element_label().
 *
 * Alters the checkbox and radio buttons so the markup is usable for the uikit.
 */
function healthgovau_form_element_label($variables) {
  $element = $variables['element'];
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // If title and required marker are both empty, output no label.
  if ((!isset($element['#title']) || $element['#title'] === '') && empty($element['#required'])) {
    return '';
  }

  // If the element is required, a required marker is appended to the label.
  $required = !empty($element['#required']) ? theme('form_required_marker', array('element' => $element)) : '';
  $title = key_exists('#title', $element) ? filter_xss_admin($element['#title']) : '';

  $attributes = array();
  // Show label only to screen readers to avoid disruption in visual flows.
  if ($element['#title_display'] == 'invisible') {
    $attributes['class'] = 'element-invisible';
  }

  if (!empty($element['#id'])) {
    $attributes['for'] = $element['#id'];
  }

  $output = '';

  // Find out what type of form element this is.
  $type = !empty($element['#type']) ? $element['#type'] : FALSE;
  $checkbox = $type && $type === 'checkbox';
  $radio = $type && $type === 'radio';

  // Construct the title.
  $title = $t('!title !required', array('!title' => $title, '!required' => $required));

  if ($checkbox || $radio) {
    // Checkboxes and radios need a span around them to support UI kit styling.
    $output .= $element['#children'];
    $output .= '<span class="input__text">';
    $output .= $title;
    $output .= '</span>';
  } else {
    // The leading whitespace helps visually separate fields from inline labels.
    $output = $title;
  }
  return ' <label' . drupal_attributes($attributes) . '>' . $output . "</label>\n";
}
