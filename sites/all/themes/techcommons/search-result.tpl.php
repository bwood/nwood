<?php
// $Id$

 /* techcommons - search-result.tpl.php (Drupal 6) - 6/30/11 */

/**
 * @file search-result.tpl.php
 * Default theme implementation for displaying a single search result.
 *
 * This template renders a single search result and is collected into
 * search-results.tpl.php. This and the parent template are
 * dependent to one another sharing the markup for definition lists.
 *
 * Available variables:
 * - $url: URL of the result.
 * - $title: Title of the result.
 * - $snippet: A small preview of the result. Does not apply to user searches.
 * - $info: String of all the meta information ready for print. Does not apply
 *   to user searches.
 * - $info_split: Contains same data as $info, split into a keyed array.
 * - $type: The type of search, e.g., "node" or "user".
 *
 * Default keys within $info_split:
 * - $info_split['type']: Node type.
 * - $info_split['user']: Author of the node linked to users profile. Depends
 *   on permission.
 * - $info_split['date']: Last update of the node. Short formatted.
 * - $info_split['comment']: Number of comments output as "% comments", %
 *   being the count. Depends on comment.module.
 * - $info_split['upload']: Number of attachments output as "% attachments", %
 *   being the count. Depends on upload.module.
 *
 * Since $info_split is keyed, a direct print of the item is possible.
 * This array does not apply to user searches so it is recommended to check
 * for their existance before printing. The default keys of 'type', 'user' and
 * 'date' always exist for node searches. Modules may provide other data.
 *
 *   <?php if (isset($info_split['comment'])) : ?>
 *     <span class="info-comment">
 *       <?php print $info_split['comment']; ?>
 *     </span>
 *   <?php endif; ?>
 *
 * To check for all available data within $info_split, use the code below.
 *
 *   <?php print '<pre>'. check_plain(print_r($info_split, 1)) .'</pre>'; ?>
 *
 * @see template_preprocess_search_result()
 */
?>
<dt class="title">
  <a href="<?php print $url; ?>"><?php print $title; ?></a>
</dt>

<!-- Display time stamp for last update of node - per RTM move under title -->
<div class="solr-lastupdated">
  <!-- If rich want's time back use 'g:i a' -->
  <?php print 'Modified on ' . format_date($result['fields']['changed'], 'custom', 'F j, Y'); ?>
</div>

<dd>
  <?php if ($snippet) : ?>
    <p class="search-snippet"><?php print $snippet; ?>
	</p>
  <?php endif; ?>
  <?php 

	/*
 	* List taxonomy terms for each product in search results
 	* https://beartracks.berkeley.edu/browse/UCTSR-57
 	*/

    if (count($result['fields']['sm_tid_link']) > 0) {
      print '<div class="solr-categories">';
        print '<div class="label-solr-categories">Categories:</div>';
        print '<ul class="list-solr-categories">'; 
          foreach ($result['fields']['sm_tid_link'] as $link) {
          print '<li class="list-item-solr-categories">' .  $link . '</li>';
          }
        print '</ul>';
      print '</div>';
    }
	/*
 	* Display fivestar rating
 	* https://beartracks.berkeley.edu/browse/UCTSR-57
 	*/
    if (module_exists("fivestar")) {
      print '<div class="solr-rating-count">'; 
        print "({$result['fields']['sis_rating_count']})";
      print '</div>';
      print '<div class="solr-rating">';    
      print theme('fivestar_static', $result['fields']['sis_rating_average'], variable_get('fivestar_stars_resource', 5)); 
      //print "<div class=\"fivestar-widget-static\">({$result['fields']['sis_rating_count']})</div>"; //not sure if we want that fivestar class...
      //FIXME rating_count should appear just to right of the fivestar images: See attached screen shot: https://beartracks.berkeley.edu/browse/UCTSR-57
      print '</div>';
    }    

  ?>

</dd>

