<?php

/**
 * @file
 * Display Suite 2 column template.
 */
?>
<div class="bean-list-horizontal--narrow">

  <?php if (isset($title_suffix['contextual_links'])): ?>
  <?php print render($title_suffix['contextual_links']); ?>
  <?php endif; ?>

    <div class="row-wrapper">
        <figure>
          <?php print $left; ?>
        </figure>

        <article>
          <?php print $right; ?>
        </article>
    </div>

</div>
