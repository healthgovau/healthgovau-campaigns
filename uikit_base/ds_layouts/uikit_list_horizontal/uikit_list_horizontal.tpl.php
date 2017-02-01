<?php
/**
 * @file
 * Display Suite layout for the UI Kit horizontal list style.
 *
 * @see http://guides.service.gov.au/design-guide/components/list-styles/#horizontal-style
 */
?>

<?php if (!empty($title_suffix['contextual_links'])): ?>
  <?php print render($title_suffix['contextual_links']); ?>
<?php endif; ?>

<?php if (!empty($figure)): ?>
<figure>
  <?php print $figure; ?>
</figure>
<?php endif; ?>

<article>
  <h3><?php print $title; ?></h3>
  <?php if (!empty($meta)): ?>
  <div class="meta">
    <?php print $meta; ?>
  </div>
  <?php endif; ?>
  <?php print $main; ?>
  <?php if (!empty($footer)): ?>
  <footer>
    <?php print $footer; ?>
  </footer>
  <?php endif; ?>
</article>
