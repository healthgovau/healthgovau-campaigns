<?php

/**
 * @file
 * Theme settings.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function healthgovau_form_system_theme_settings_alter(&$form, &$form_state, $form_id = NULL) {

  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  $form['ga'] = array(
    '#type' => 'fieldset',
    '#title' => t('Google analytics settings'),
  );
  $form['ga']['ga_api'] = array(
    '#type' => 'textfield',
    '#title' => t('Google api KEY'),
    '#default_value' => theme_get_setting('ga_api'),
    '#size' => 255,
  );
  $form['ga']['env'] = array(
    '#type' => 'select',
    '#title' => 'Environment',
    '#options' => array(
      'dev' => 'Development',
      'stage' => 'Staging',
      'prod' => 'Production',
    ),
    '#default_value' => theme_get_setting('env'),
  );
  $form['ga']['ga_auth'] = array(
    '#type' => 'textfield',
    '#title' => t('Google analytics token'),
    '#default_value' => theme_get_setting('ga_auth'),
    '#size' => 255,
  );
  $form['ga']['ga_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Google analytics ID'),
    '#default_value' => theme_get_setting('ga_id'),
    '#size' => 255,
  );
}
