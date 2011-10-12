<?php
// $Id$

 /* techcommons - theme-settings.php (Drupal 6) - 6/7/11 */

// Include the definition of zen_settings() and zen_theme_get_default_settings().
include_once './' . drupal_get_path('theme', 'zen') . '/theme-settings.php';


/**
 * Implementation of THEMEHOOK_settings() function.
 *
 * @param $saved_settings
 *   An array of saved settings for this theme.
 * @return
 *   A form array.
 */
function techcommons_settings($saved_settings) {

  // Get the default values from the .info file.
  $defaults = zen_theme_get_default_settings('techcommons');

  // Merge the saved variables and their default values.
  $settings = array_merge($defaults, $saved_settings);

  /*
   * Create the form using Forms API: http://api.drupal.org/api/6
   */
  $form = array();
  /* -- Delete this line if you want to use this setting
  $form['techcommons_example'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('Use this sample setting'),
    '#default_value' => $settings['techcommons_example'],
    '#description'   => t("This option doesn't do anything; it's just an example."),
  );
  // */

  // Add the base theme's settings.
  $form += zen_settings($saved_settings, $defaults);

  // Remove some of the base theme's settings.
  unset($form['themedev']['zen_layout']); // We don't need to select the base stylesheet.

  // -- Should the alternating color title effect be applied? - borrowed from Nitobe theme: http://drupal.org/project/nitobe
  $default = $settings['techcommons_title_effect'];
  $default = (!isset($default)) ? FALSE : (boolean)$default;
  $desc    = t('Should the title be adjusted to apply an alternate color to every other word and remove inter-word spacing?');
  $form['techcommons_title_effect'] = array(
    '#type'           => 'checkbox',
    '#title'          => t('Apply title effect'),
    '#default_value'  => $default,
    '#description'    => $desc,
  );

  // Return the form
  return $form;
}
