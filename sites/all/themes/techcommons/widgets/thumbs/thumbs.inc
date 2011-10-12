<?php
// $Id: upanddown.inc,v 1.1.2.1 2010/07/24 23:23:54 marvil07 Exp $

/**
 * @file
 * Up (Down) Widget.
 * From http://drupal.org/node/1016804 - Edited 6/30/11 - Vote Up only
 */

/**
 * Implementation of hook_vud_widgets(). (Specialized)
 */
function techcommons_thumbs_vud_widgets() {
  return array(
    'title' => t('Thumbs Up Only'),
    'widget template' => 'widget',
  );
}
