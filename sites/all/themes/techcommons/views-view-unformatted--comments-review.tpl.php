<?php
// $Id: views-view-unformatted.tpl.php,v 1.6 2008/10/01 20:52:11 merlinofchaos Exp $
/**
 * @file views-view-unformatted.tpl.php
 * Default simple view template to display a list of rows.
 *
 * @ingroup views_templates
 * 
 * https://beartracks.berkeley.edu/browse/UCTSR-69
 * Collapsible comments under a review
 */
?>
<?php 
drupal_add_js('misc/collapse.js');
drupal_add_js(drupal_get_path('module', 'tsr') . '/js/tsr_comments.js');
?>
<?php if (!empty($title)): ?>
  <h3><?php print $title; ?></h3>
<?php endif; ?>
<fieldset class="collapsible">
  <legend id="tc_comments_legend">Comments (<?php echo count($rows);?>)</legend>
  <div>
  <?php foreach ($rows as $id => $row): ?>
    <div class="<?php print $classes[$id]; ?>">
      <?php print $row; ?>
    </div>
  <?php endforeach; ?>
  </div>
</fieldset>
