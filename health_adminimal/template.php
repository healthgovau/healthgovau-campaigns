<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * Implement THEME_toc_filter().
 */
function health_adminimal_toc_filter($variables) {
  $output = '';
  $output .= '<nav class="index-links">';
  $output .= '<h2 id="index-links">' . t('In this section') . '</h2>';
  $output .= $variables['content'];
  $output .= '</nav>';
  return $output;
}

/**
 * Implements THEME_toc_filter_back_to_top().
 */
function health_adminimal_toc_filter_back_to_top($variables) {
  return '<span class="back-to-index-link"><a href="#index-links">' . t('Back to contents ↑') . '</a></span>';
}


/**
 * Implements hook_form_alter().
 * @param $form
 */
function health_adminimal_form_alter(&$form) {

  $media_forms = array('file-entity-add-upload', 'media-internet-add-upload', 'file_entity_edit');

  if (in_array($form['#id'], $media_forms)) {

    // Make alt text mandatory.
    if (key_exists('field_file_image_alt_text', $form)) {
      $form['field_file_image_alt_text'][$form['field_file_image_alt_text']['#language']][0]['value']['#required'] = TRUE;
      $form['field_file_image_alt_text'][$form['field_file_image_alt_text']['#language']][0]['#required'] = TRUE;
      $form['field_file_image_alt_text'][$form['field_file_image_alt_text']['#language']]['#required'] = TRUE;
    }

    // Clear filename from title to force users to enter a sensible title.
    if (key_exists('filename', $form)) {
      $form['filename']['#default_value'] = '';
    }

  }
}


/**
 * Implements theme_fieldset().
 */
function health_adminimal_fieldset($variables) {
  $element = $variables['element'];

  // Make the information below a wysiwyg in a collapsible fieldset.
  if (key_exists('guidelines', $element)) {
    $element['#title'] = 'Formatting information';
    $element['#attributes']['class'][] = 'collapsible';
    $element['#attributes']['class'][] = 'collapsed';
  }

  element_set_attributes($element, array('id'));
  _form_set_class($element, array('form-wrapper'));

  $output = '<fieldset' . drupal_attributes($element['#attributes']) . '>';
  if (!empty($element['#title'])) {
    // Always wrap fieldset legends in a SPAN for CSS positioning.
    $output .= '<legend><span class="fieldset-legend">' . $element['#title'] . '</span></legend>';
  }
  $output .= '<div class="fieldset-wrapper">';
  if (!empty($element['#description'])) {
    $output .= '<div class="fieldset-description">' . $element['#description'] . '</div>';
  }
  $output .= $element['#children'];
  if (isset($element['#value'])) {
    $output .= $element['#value'];
  }
  $output .= '</div>';
  $output .= "</fieldset>\n";
  return $output;
}



/**
 * Implements theme_form_element().
 */
function health_adminimal_form_element($variables) {
  $element = &$variables['element'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'] = array('form-item');
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  $output = '<div' . drupal_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      // Help text.
      if (!empty($element['#description'])) {
        $output .= '<div class="description">' . $element['#description'] . "</div>\n";
      }
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      // Help text.
      if (!empty($element['#description'])) {
        $output .= '<div class="description">' . $element['#description'] . "</div>\n";
      }
      break;

    case 'none':
    case 'attribute':
      // Help text.
      if (!empty($element['#description'])) {
        $output .= '<div class="description">' . $element['#description'] . "</div>\n";
      }
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  $output .= "</div>\n";

  return $output;
}

/**
 * Implements hook_js_alter().
 *
 * Perform necessary alterations to the JavaScript before it is presented on the page.
 *
 * @param array $javascript
 *   An array of all JavaScript being presented on the page.
 */
function health_adminimal_js_alter(&$javascript) {
  // Add/replace chosen js.
  $javascript['profiles/govcms/libraries/chosen/chosen.jquery.min.js'] = [
    'data' => drupal_get_path('theme', 'health_adminimal') . '/js/libraries/chosen/chosen.jquery.min.js',
    'version' => '1',
    'group' => -100,
    'type' => 'file',
    'weight' => 1,
    'every_page' => FALSE,
    'preprocess' => TRUE,
    'requires_jquery' => TRUE,
    'scope' => 'header',
    'cache' => TRUE,
    'defer' => FALSE,
  ];
  // Remove standard chosen config as we will use our own.
  if (key_exists('profiles/govcms/modules/contrib/chosen/chosen.js', $javascript)) {
    unset($javascript['profiles/govcms/modules/contrib/chosen/chosen.js']);
  }
}

/**
 * Implements hook_css_alter().
 */
function health_adminimal_css_alter(&$css) {
  // Add/replace chosen css.
  $css['profiles/govcms/libraries/chosen/chosen.css'] = [
    'data' => drupal_get_path('theme', 'health_adminimal') . '/js/libraries/chosen/chosen.min.css',
    'group' => -100,
    'type' => 'file',
    'weight' => 1,
    'every_page' => FALSE,
    'media' => 'all',
    'preprocess' => TRUE,
    'browsers' => ['IE'=> TRUE, '!IE' => TRUE],
  ];
}
