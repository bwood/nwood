<?php
// $Id$

/* techcommons - template-forms.php (Drupal 6) - 7/12/11 */
     
function techcommons_product_node_form($form) {  
  //Get form id
  $form_id = $form['form_id']['#value'];
      
  //For desired elements, add flag to switch help text positioning for this form only when calling techcommons_form_element()
  $form['field_product_single_sentance'][0]['value']['#techcommons_form_id'] = $form_id; //type: textfield
  $form['group_product_fs_keywords']['field_product_category']['value']['#techcommons_form_id'] = $form_id; //type: checkboxes
  $form['group_product_fs_keywords']['taxonomy']['tags']['3']['#techcommons_form_id'] = $form_id; //type: textfield
  $form['group_product_fs_overview']['field_product_acquire_url'][0]['url']['#techcommons_form_id'] = $form_id; //type: textfield  
  $form['group_product_fs_overview']['field_product_extended_desc'][0]['value']['#techcommons_form_id'] = $form_id; //type: textarea
  $form['group_product_fs_details']['field_product_details'][0]['value']['#techcommons_form_id'] = $form_id; //type: textarea
  //$form['group_product_fs_support']['field_product_ref_provider']['nid']['nid']['#techcommons_form_id'] = $form_id; //type: select - use with Popups
  $form['group_product_fs_support']['field_product_ref_provider'][0]['nid']['nid']['#techcommons_form_id'] = $form_id; //type: textfield - use with Node Relationships
  $form['revision_information']['log']['#techcommons_form_id'] = $form_id; //type: textarea 
  
  //Move buttons to bottom: registering form id with theme registry sends to top by default
  $buttons = drupal_render($form['buttons']);    
  $output .= drupal_render($form) . $buttons;
  
  return $output;
}

function techcommons_support_team_node_form($form) {
  //Get form id
  $form_id = $form['form_id']['#value'];
  
  //For desired elements, add flag to switch help text positioning for this form only when calling techcommons_form_element()
  $form['field_support_team_email'][0]['email']['#techcommons_form_id'] = $form_id; //type: textfield
  $form['field_support_team_contact'][0]['value']['#techcommons_form_id'] = $form_id; //type: textarea
  
  //Move buttons to bottom: registering form id with theme registry sends to top by default
  $buttons = drupal_render($form['buttons']);    
  $output .= drupal_render($form) . $buttons;
  
  return $output;
  
} 

function techcommons_form_element($element, $value) {
  //If element is flagged with #techcommons_form_id
  if (!empty($element['#techcommons_form_id'])) {  
    
    //If #techcommons_form_id is set to 'product_node_form' or 'support_team_node_form'
    if (($element['#techcommons_form_id'] == 'product_node_form') || ($element['#techcommons_form_id'] == 'support_team_node_form')) {
            
      // From theme_form_element: This is also used in the installer, pre-database setup.
      $t = get_t();

      $output = '<div class="form-item"';
      if (!empty($element['#id'])) {
        $output .= ' id="'. $element['#id'] .'-wrapper"';
      }
      $output .= ">\n";
      $required = !empty($element['#required']) ? '<span class="form-required" title="'. $t('This field is required.') .'">*</span>' : '';

      if (!empty($element['#title'])) {
        $title = $element['#title'];
        if (!empty($element['#id'])) {
          $output .= ' <label for="'. $element['#id'] .'">'. $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) ."</label>\n";
        }
        else {
          $output .= ' <label>'. $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) ."</label>\n";
        }
      }

      /* Put help text immediately after label before field */
      if (!empty($element['#description'])) {
        $output .= ' <div class="description">'. $element['#description'] ."</div>\n";
      }

      $output .= " $value\n";

      $output .= "</div>\n";

      return $output;
    }
  }
  //Otherwise, use theme_form_element() 
  else {
    return theme_form_element($element, $value);
  }
}
