<?php
/**
 * @file
 * Display Suite 2 column template.
 */
?>

<?php if (isset($title_suffix['contextual_links'])): ?>
  <?php print render($title_suffix['contextual_links']); ?>
<?php endif; ?>

<div class="ds-4col group-top">
  <div class="ds-wrapper video">
    <div class="group-top-left">
      <div class="video__thumb video-wrapper">
        <?php print $top_left; ?>
      </div>
      <div class="group-bottom-left">
        <?php print $bottom_left; ?>
      </div>
    </div>

    <div class="group-top-right">
      <div class="video__extrainfo meta">
        <?php print $top_right; ?>
      </div>
      <div class="group-bottom-right">
        <?php print $bottom_right; ?>
      </div>
    </div>
  </div>
</div>
