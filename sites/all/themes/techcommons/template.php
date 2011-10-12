<?php
// $Id$

/* techcommons - template.php (Drupal 6) - 9/6/11 */

/*
 * Add any conditional stylesheets you will need for this sub-theme.
 *
 * To add stylesheets that ALWAYS need to be included, you should add them to
 * your .info file instead. Only use this section if you are including
 * stylesheets based on certain conditions.
 */
/* -- Delete this line if you want to use and modify this code
// Example: optionally add a fixed width CSS file.
if (theme_get_setting('techcommons_fixed')) {
  drupal_add_css(path_to_theme() . '/layout-fixed.css', 'theme', 'all');
}
// */

/* Include files */
include_once('template-forms.php');

/**
 * Implementation of HOOK_theme().
 */
function techcommons_theme(&$existing, $type, $theme, $path) {
  $hooks = zen_theme($existing, $type, $theme, $path);
  // Add your theme hooks like this:
  /*
  $hooks['hook_name_here'] = array( // Details go here );
  */
  
  $hooks['product_node_form'] = array (
    'arguments' => array('form' => NULL),
  );
  
  $hooks['support_team_node_form'] = array (
    'arguments' => array('form' => NULL),
  );
  
  return $hooks;
}

/* 
function techcommons_preprocess(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

function techcommons_preprocess_page(&$vars) {
  
  global $user;
    
  // Check to see if $user has the administrator or curator role.
  if (in_array('administrator', array_values($user->roles)) || in_array('curator', array_values($user->roles))) { 
     
    //Create curator array
    $curator['curator'] = array (
      'title' => t('Curator'),
      'href' => t('/curator'),
    );
    
    //Add curator key-value pair to front of secondary links array
    $vars['secondary_links'] = array_merge($curator, $vars['secondary_links']);
  }
  
  
  //If user is logged in
  if ($vars['logged_in']) {

    //Create logout array
    $logout['logout'] = array (
      'title' => t('Logout'),
      'href' => t('/logout'),
    );
   
    //Add logout key-value pair to front of secondary links array
    $vars['secondary_links'] = array_merge($logout, $vars['secondary_links']);
    
    //Create logname array
    $logname['logname'] = array (
      'title' => t('Logged in as @user_name', array(
        '@user_name' => $vars['user']->name,
      ))
    );
    
    //Add logname key-value pair to front of secondary links array
    $vars['secondary_links'] = array_merge($logname, $vars['secondary_links']);
  } //endif
  
  //Banner image(s)
/*
  $vars['banner'] = t('<img alt="@alt" id="banner" src="@src" width="@width" height="@height" />', array(
    '@alt' => '',
    '@src' => base_path() . path_to_theme() . '/banners/Leek_Bloom.jpg',
    '@width' => '960',
    '@height' => '118',
  ));
  */
  $vars['banner'] = t('<img alt="@alt" id="banner" src="@src" width="@width" height="@height" />', array(
    '@alt' => '',
    '@src' => base_path() . path_to_theme() . '/banners/spectacles_nitobe.png',
    '@width' => '960',
    '@height' => '200',
  ));

  //Remove title for Apache Solr Search
  $item = menu_get_item(); //Get router item: http://api.lullabot.com/menu_get_item
  
  //If the router item is apache solr
  if ($item['path'] == 'search/apachesolr_search' || $item['path'] == 'search/apachesolr_search/%') {
    unset($vars['title']); //Unset title for apache solr search results page
    }  
  
  // -- Apply title effect - adopted from Nitobe theme: http://drupal.org/project/nitobe
  if (isset($vars['site_name']) && ((boolean)theme_get_setting('techcommons_title_effect') == TRUE)) {
    $vars['techcommons_title'] = techcommons_title_effect(check_plain($vars['site_name']));
  } else {
    $vars['techcommons_title'] = check_plain($vars['site_name']);
  }
}

function techcommons_preprocess_node(&$vars) {
    
  /* To create revision variable and output for node */
  if ($vars['created'] != $vars['changed']) {
    if ($vars['uid'] == $vars['revision_uid']) {
      $rev_name = $vars['name'];
    } else { 
      
      //if revision author != node creater, load user
      $user = user_load(array('uid' => $vars['revision_uid']));
      $rev_name = theme('username', $user);
    }

    // create new revision variable 
    $vars['revision'] =
      t('Last modified by !username on @datetime',
        array(
          '@datetime' => format_date($vars['changed'], 'custom', 'F j, Y'),
          '!username' => $rev_name
        )
      );
  }     
  
  $vars['revisions_link'] = l('View Revisions', 'node/' . $vars['nid'] . '/revisions');
  $vars['add_review_link'] = l('Add a Review', 'node/add/review?edit[field_review_ref_product][nid][nid]=' . $vars['nid']);

  /* To allow creation of node-XX.tpl.php files as workaround for disabled php input filter */
  $vars['template_files'][] = 'node-' . $vars['nid'];

}

/* Format output of $submitted */
function techcommons_node_submitted($node) {
  return t('Created by !username on @datetime', 
    array(
    '!username' => theme('username', $node), 
    '@datetime' => format_date($node->created, 'custom', 'F j, Y'),
  ));
}

function techcommons_menu_local_tasks() {
  $output = '';

  if (arg(0) == 'node' && is_numeric(arg(1))) {
    $node = new stdClass();  
    $node = node_load(arg(1));
    
    // Hide view/edit/revisions tab for page, product, service_provider
    if (in_array($node->type, array('page', 'product', 'service_provider'))) {
    return;
    }
  }

  if ($primary = menu_primary_local_tasks()) {
    $output .= "<ul class=\"tabs primary\">\n". $primary ."</ul>\n";
  }
  if ($secondary = menu_secondary_local_tasks()) {
    $output .= "<ul class=\"tabs secondary\">\n". $secondary ."</ul>\n";
  }

  return $output;
}


function techcommons_preprocess_comment(&$vars, $hook) {

  $comment = $vars['comment'];

  $indent_class = 'indent';
  if ($comment->depth < 5) {
    $indent_depth = $comment->depth; 
  } 
  else { 
    $indent_depth = 5;
  }  
  $indent_class .= $indent_depth;

  $vars['indent_class'] = $indent_class;

}


/* 
function techcommons_preprocess_block(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * techcommons_breadcrumb -- override zen_breadcrumb
 *
 * Return a themed breadcrumb trail. 
 *
 * @param $breadcrumb
 *   An array containing the breadcrumb links.
 * @return
 *   A string containing the breadcrumb output.
 */
function techcommons_breadcrumb($breadcrumb) {
  // Determine if we are to display the breadcrumb.
  $show_breadcrumb = theme_get_setting('zen_breadcrumb');
  if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {

    // Optionally get rid of the homepage link.
    $show_breadcrumb_home = theme_get_setting('zen_breadcrumb_home');
    if (!$show_breadcrumb_home) {
      array_shift($breadcrumb);
    }

    //Breadcrumb override
    $item = menu_get_item(); //Get router item: http://api.lullabot.com/menu_get_item
    
    //dsm($item['path']); //If necessary, check path to make sure alias is not different
    
    //If the router item is apache solr
    if ($item['path'] == 'search/apachesolr_search' || $item['path'] == 'search/apachesolr_search/%') {
      //Remove $breadcrumb[1] from array   
      array_splice($breadcrumb, 1, 1); 
    }
    
    //If the router item is taxonomy term
    if ($item['path'] == 'taxonomy/term/%') {
      //Remove $breadcrumb[1] from array   
      $breadcrumb[0] = l(t('Browse'), 'browse');
      array_splice($breadcrumb, 1, 1); 
    }
    
    //If the router item is node/add/product
    if ($item['path'] == 'node/add/product') {
      //Remove $breadcrumb[1] from array   
      array_splice($breadcrumb, 1, 1); 
    }
    
    // Return the breadcrumb with separators.
    if (!empty($breadcrumb)) {
      $breadcrumb_separator = theme_get_setting('zen_breadcrumb_separator');
      $trailing_separator = $title = '';
      if (theme_get_setting('zen_breadcrumb_title')) {
        if ($title = drupal_get_title()) {
          $trailing_separator = $breadcrumb_separator;
        }
      }
      elseif (theme_get_setting('zen_breadcrumb_trailing')) {
        $trailing_separator = $breadcrumb_separator;
      }
      return '<div class="breadcrumb">' . implode($breadcrumb_separator, $breadcrumb) . "$trailing_separator$title</div>";
    }
  }
  // Otherwise, return an empty string.
  return '';
}

/**
 * Produces the title effect. -- adopted from Nitobe theme: http://drupal.org/project/nitobe
 *
 * Removes the spaces between words in the given string and returns an HTML
 * string with every other word wrapped in a span with the class "alt-color".
 *
 * @param string $title
 *   The text to render.
 *
 * @return string
 *   The rendered HTML.
 */
function techcommons_title_effect($title = '') {
  $words  = explode(' ', $title);
  $result = '';

  if (is_array($words)) {
    $alt = FALSE;
    foreach ($words as $word) {
      if ($alt) {
        $result .= '<span class="alt-color">' . $word . '</span>';
      } else {
        $result .= $word;
      }

      $alt = !$alt;
    }
  }

  return $result;
}


/*
 * Begin: Theme the apache solr block
 */
function techcommons_apachesolr_unclick_link($facet_text, $path, $options = array()) {
  //apachesolr_js(); //FIXME what provides this?

  // Determine if we are dealing with ratings output
  if ( strpos($options['query']['filters'], 'sis_rating_average') !== false ) {  //FIXME not quite loosing stars
    $options['html'] = TRUE;
    $text = theme('fivestar_static', $facet_text, variable_get('fivestar_stars_resource', 5));
  }

  if (empty($options['html'])) {
    $text = check_plain($facet_text);
  }
  else {
    // Don't pass this option as TRUE into apachesolr_l().
    unset($options['html']);
  }
  $options['attributes']['class'] = 'apachesolr-unclick';
  return apachesolr_l("(-)", $path, $options) . ' '. $text;
}
/*
  * Override the theme output for facet links - specifically ratings
  *
  * $facet_text - text for link
  * $path - path for link
  * $options - various class / styling attributes
  * $count - count of solr results
  * $active - active link?
  * $num_found
  *
  * @return - link output
**/
function techcommons_apachesolr_facet_link($facet_text, $path, $options = array(), $count, $active = FALSE, $num_found = NULL) {
  // Determine if we are dealing with ratings output
  if ( strpos($options['query']['filters'], 'sis_rating_average') !== false ) { //FIXME not quite loosing stars
    $options['html'] = TRUE;
    $text = theme('fivestar_static', $facet_text, variable_get('fivestar_stars_resource', 5)) . "<div class=\"fivestar-widget-static\">($count)</div>";
  } else {
    $text = $facet_text ." ($count)";
  }

  // Setup other variables before output
  $options['attributes']['class'][] = 'apachesolr-facet';
  if ($active) {
    $options['attributes']['class'][] = 'active';
  }
  $options['attributes']['class'] = implode(' ', $options['attributes']['class']);
  return apachesolr_l($text,  $path, $options);
}
function techcommons_apachesolr_facet_list($items, $display_limit = 0) {

  $admin_link = '';
  if (user_access('administer search')) {
    $admin_link = l(t('Configure enabled filters'), 'admin/settings/apachesolr/enabled-filters');
  }
  return theme('item_list', $items) . $admin_link;
}
/*
 * End: Theme the apache solr block
 */
