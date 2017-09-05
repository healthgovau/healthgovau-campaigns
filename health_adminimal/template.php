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
  $output .= '<h2 id="index-links">' . t('Contents') . '</h2>';
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

  $media_forms = array('file-entity-add-upload', 'media-internet-add-upload');

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