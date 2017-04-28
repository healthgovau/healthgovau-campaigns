<?php
function ds_uikit_list_vertical_reverse() {
  return array(
    'label' => t('UI KIT: List - Vertical reverse'),
    'regions' => array(
      'title' => t('Title'),
      'meta' => t('Meta'),
      'main' => t('Content'),
      'footer' => t('Footer'),
      'figure'  => t('Figure'),
    ),
    'image' => TRUE,
  );
}
