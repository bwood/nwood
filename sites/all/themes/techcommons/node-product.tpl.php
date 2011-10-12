<?php
// $Id$

 /* techcommons - node-product.tpl.php (Drupal 6) - 6/16/11 */

?>
<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>"><div class="node-inner">

  <?php if (!$page): ?>
    <h2 class="title">
      <a href="<?php print $node_url; ?>" title="<?php print $title ?>"><?php print $title; ?></a>
    </h2>
  <?php endif; ?>

  <?php if ($unpublished): ?>
    <div class="unpublished"><?php print t('Unpublished'); ?></div>
  <?php endif; ?>

  <?php if ($submitted): ?>
    <div class="meta">
      <?php if ($submitted): ?>
        <div class="submitted">
          <?php print $submitted; ?>
        </div>
        <div class="revised">
          <?php print $revision; ?>
        </div>
      <?php endif; ?>

    </div>
  <?php endif; ?>

  <div class="revisions-link">
   <?php print $revisions_link; ?>
  </div>
  <div class="add-review-link">
   
   <?php 
    /* BW: Improve?  Need to make sure that '[' ']' are not urlencoded when output to browser.
     * (No love from http://api.drupal.org/api/drupal/includes--unicode.inc/function/decode_entities/6)
     */
   print urldecode($add_review_link); ?>
  </div>

  <?php /*?>
  <div class="content"> 
    <?php print $content; ?> 
  </div> 
  <?php */?>

  <?php print $links; ?>

</div></div> <!-- /node-inner, /node -->
