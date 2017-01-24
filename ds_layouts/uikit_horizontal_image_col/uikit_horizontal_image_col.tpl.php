<?php
/**
 * @file
 * Display Suite 2 column template.
 */
?>

<?php if (isset($title_suffix['contextual_links'])): ?>
  <?php print render($title_suffix['contextual_links']); ?>
<?php endif; ?>

<figure>
  <div class="video__thumb">
    <?php print $left; ?>
  </div>
</figure>

<article>
  <?php print $right; ?>
</article>
