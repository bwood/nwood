<?php
// $Id: widget.tpl.php,v 1.1.2.4 2010/12/05 07:50:06 marvil07 Exp $

/**
 * @file
 * widget.tpl.php
 *
 * UpAndDown widget theme for Vote Up/Down
 * TODO use $show_up_as_link and $show_down_as_link
 *
 * From http://drupal.org/node/1016804 - Edited 7/1/11 - Vote Up only
 */
?>
<div class="vud-widget vud-widget-thumbs" id="<?php print $id; ?>">
  <div class="thumb-text"> Was this review helpful? </div>
  <div class="up-score clear-block">
    <?php if ($show_links): ?>
      <?php if ($show_up_as_link): ?>
        <a href="<?php print $link_up; ?>" rel="nofollow" class="<?php print "$link_class_up"; ?>" title="<?php print t('Vote up!'); ?>">
      <?php endif; ?>
          <div class="<?php print $class_up; ?>" title="<?php print t('Vote up!'); ?>"></div>
          <div class="element-invisible"><?php print t('Vote up!'); ?></div>
      <?php if ($show_up_as_link): ?>
        </a>
      <?php endif; ?>
    <?php endif; ?>
    <span class="up-current-score">(<?php print $up_points; ?>)</span>
  </div>

</div>
