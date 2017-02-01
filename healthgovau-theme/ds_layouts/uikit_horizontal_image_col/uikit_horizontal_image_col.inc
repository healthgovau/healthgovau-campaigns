<?php
function ds_uikit_horizontal_image_col() {
  return array(
    'label' => t('UI KIT horizontal list with image'),
    'regions' => array(
      'left' => t('figure'),
      'right' => t('article'),
    ),
    // Add this line if there is a default css file.
    'css' => TRUE,
    // Add this line if you're using DS 2.x for icon preview
    'image' => TRUE,
  );
}
