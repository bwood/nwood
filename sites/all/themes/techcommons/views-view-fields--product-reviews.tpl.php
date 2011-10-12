<?php
// $Id: views-view-fields.tpl.php,v 1.6 2008/09/24 22:48:21 merlinofchaos Exp $
/**
 * @file views-view-fields.tpl.php
 * Default simple view template to all the fields as a row.
 *
 * - $view: The view in use.
 * - $fields: an array of $field objects. Each one contains:
 *   - $field->content: The output of the field.
 *   - $field->raw: The raw data for the field, if it exists. This is NOT output safe.
 *   - $field->class: The safe class id to use.
 *   - $field->handler: The Views field handler object controlling this field. Do not use
 *     var_export to dump this object, as it can't handle the recursion.
 *   - $field->inline: Whether or not the field should be inline.
 *   - $field->inline_html: either div or span based on the above flag.
 *   - $field->separator: an optional separator that may appear before a field.
 * - $row: The raw result object from the query, with all data it fetched.
 *
 * @ingroup views_templates
 */
?>
<?php print $fields['title']->content ?>


<?php foreach ($fields as $id => $field): ?>
  <?php if ($field->class == 'nid' || $field->class == 'field-review-comments-vname') continue; //skip nid field and comments, comments printed last?>
  <?php if (!empty($field->separator)): ?>
    <?php print $field->separator; ?>
  <?php endif; ?>

  <<?php print $field->inline_html;?> class="views-field-<?php print $field->class; ?>">
    <?php if ($field->label): ?>
      <label class="views-label-<?php print $field->class; ?>">
        <?php print $field->label; ?>:
      </label>
    <?php endif; ?>
      <?php
      // $field->element_type is either SPAN or DIV depending upon whether or not
      // the field is a 'block' element type or 'inline' element type.
      ?>
      <<?php print $field->element_type; ?> class="field-content"><?php print $field->content; ?></<?php print $field->element_type; ?>>
  </<?php print $field->inline_html;?>>
<?php endforeach; ?>
<?php if (user_access('post comments')): ?>
<div class="comment-review">
<fieldset class="collapsible collapsed">
  <legend class="comment-review-link">Comment on this review</legend>
  <div>
    <?php print drupal_get_form('comment_form', array('nid' => $row->nid)); ?>
  </div>
</fieldset>
</div>
<?php else: ?>
<div class="login-comment-review">
  <a href="<?php
              $tabs = variable_get('tsr_tab_fieldset', array()); 
              $tabid = $tabs['review_tab_id']; 
              print url(variable_get("cas_uri"), array('query' => "destination=node/" . $view->argument['nid']->value[0]. '?quicktabs_2='.$tabid.'#quicktabs-2', 'absolute' => TRUE)); 
           ?>">Login to comment on this review</a>
</div>
<?php endif; ?>
<div class="field-review-comments">
<?php print $fields['field_review_comments_vname']->content; ?>
</div>
