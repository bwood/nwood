<?php

/**
 * @file
 * Checks for security_review module.
 *
 */

/**
 * Check that files aren't writeable by the server.
 */
function security_review_check_file_perms() {
  $result = TRUE;
  // Extract ending folder for file directory path.
  $file_path = rtrim(file_directory_path(), '/');
  $file_path = substr($file_path, strrpos($file_path, '/') + 1);
  // Set files to ignore.
  $ignore = array('..', 'CVS', $file_path);
  $files = _security_review_check_file_perms_scan('.', $ignore);
  if (count($files)) {
    $result = FALSE;
  }
  return array('result' => $result, 'value' => $files);
}

function _security_review_check_file_perms_scan($directory, $ignore) {
  $items = array();
  if (is_readable($directory) && $handle = opendir($directory)) {
    while (($file = readdir($handle)) !== FALSE) {
      // Don't check hidden files or ones we said to ignore.
      if ($file[0] != "." && !in_array($file, $ignore)) {
        $file = $directory . "/" . $file;
        if (is_dir($file)) {
          $items = array_merge($items, _security_review_check_file_perms_scan($file, $ignore));
          if (is_writable( $file)) {
            $items[] = preg_replace("/\/\//si", "/", $file);
          }
        }
        elseif (is_writable( $file)) {
          $items[] = preg_replace("/\/\//si", "/", $file);
        }
      }
    }
    closedir($handle);
  }
  return $items;
}

function security_review_check_file_perms_help($result = NULL) {
  $element['title'] =  t("Web server file system permissions");
  $element['descriptions'][] = t("It is dangerous to allow the web server to write to files inside the document root of your server. Doing so would allow Drupal to write files that could then be executed. An attacker might use such a vulnerability to take control of your site. An exception is the files directory which Drupal needs permission to write to in order to provide features like file attachments.");
  $element['descriptions'][] = t("<a href='!link'>Read more about file system permissions in the handbooks.</a>", array('!link' => url('http://drupal.org/node/244924')));

  $last_check = security_review_get_last_check('security_review', 'file_perms');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    if (is_null($result)) {
      $result = security_review_check_file_perms();
    }
    $element['findings']['descriptions'][] = t('It is recommended that the following files or directories be corrected.');
    foreach ($result['value'] as $file) {
      $element['findings']['items'][] = array(
        'safe' => check_plain($file),
        'raw' => $file,
      );
    }
  }
  return $element;
}

/**
 * Check for formats that do not have HTML filter that can be used by untrusted users.
 */
function security_review_check_input_formats() {
  $result = TRUE;
  $formats = filter_formats();
  $check_return_value = array();
  // Check formats that are accessible by untrusted users.
  $untrusted_roles = security_review_untrusted_roles();
  // The default format is usable by all users even if no roles are listed on it.
  $default_format = variable_get('filter_default_format', FILTER_FORMAT_DEFAULT);
  foreach ($formats as $id => $format) {
    $format_roles = array_filter(explode(',', $format->roles));
    if ($format->format == $default_format) {
      // The default format is available to all roles.
      $intersect = drupal_map_assoc(array_keys(user_roles()));
    }
    else {
      $intersect = array_intersect($format_roles, $untrusted_roles);
    }
    if (!empty($intersect)) {
      // Untrusted users can use this format.
      $filters = filter_list_format($format->format);
      // Check format for HTML filter.
      if (in_array('filter/0', array_keys($filters))) {
        // Using HTML Filter, good! Now check allowed tags if the filter is stripping instead of escaping.
        $setting = variable_get("filter_html_" . $format->format, FILTER_HTML_STRIP);
        if ($setting == FILTER_HTML_STRIP) {
          // Check for unsafe tags in allowed tags.
          $allowed_tags = variable_get("allowed_html_" . $format->format, '');
          $unsafe_tags = security_review_unsafe_tags();
          foreach ($unsafe_tags as $tag) {
            if (strpos($allowed_tags, '<' . $tag . '>') !== FALSE) {
              // Found an unsafe tag
              $check_return_value['tags'][$id] = $tag;
            }
          }
        }
      }
      else {
        // Format is usable by untrusted users but does not contain the HTML Filter.
        $check_return_value['formats'][$id] = $format->name;
      }
    }
  }

  if (!empty($check_return_value)) {
    $result = FALSE;
  }
  return array('result' => $result, 'value' => $check_return_value);
}

function security_review_check_input_formats_help($result = NULL) {
  $element['title'] = t('Allowed HTML tags in input formats');
  $element['descriptions'][] = t("Certain HTML tags can allow an attacker to take control of your site. Drupal's input format system makes use of a set filters to run on incoming text. The 'HTML Filter' strips out harmful tags and Javascript events and should be used on all formats accessible by untrusted users.");
  $element['descriptions'][] = t("<a href='!link'>Read more about Drupal's input formats in the handbooks.</a>", array('!link' => url('http://drupal.org/node/224921')));
  $last_check = security_review_get_last_check('security_review', 'input_formats');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    if (is_null($result)) {
      $result = security_review_check_input_formats();
    }
    if (!empty($result['value']['tags'])) {
      $element['findings']['descriptions'][] = t('<a href="!link">Review your input formats.</a>', array('!link' => url('admin/settings/filters')));
      $element['findings']['descriptions'][] = t('It is recommended you remove the following tags from roles accessible by untrusted users.');
      foreach ($result['value']['tags'] as $tag) {
        $element['findings']['items'][] = array(
          'safe' => $tag, // Tag doesn't need filtering cause it's not user-defined.
          'raw' => $tag,
        );
      }
    }
    elseif (!empty($result['value']['formats'])) {
      $element['findings']['descriptions'][] = t('The following formats are usable by untrusted roles and do not filter allowed HTML tags. The default filter will have all roles checked.');
      foreach ($result['value']['formats'] as $id => $name) {
        $element['findings']['items'][] = array(
          'html' => l($name, 'admin/settings/filters/' . $id),
          'safe' => check_plain($name),
          'raw' => $name,
        );
      }
    }
  }
  return $element;
}

function security_review_check_error_reporting() {
  $error_level = variable_get('error_level', NULL);
  if (is_null($error_level) || intval($error_level) == 1) {
    // When the variable isn't set, or its set to 1 errors are printed to the screen.
    $result = FALSE;
  }
  else {
    $result = TRUE;
  }
  return array('result' => $result);
}

function security_review_check_error_reporting_help($result = NULL) {
  $element['title'] = t('Error reporting');
  $element['descriptions'][] = t('As a form of hardening your site you should avoid information disclosure. Drupal by default prints errors to the screen and writes them to the log. Error messages disclose the full path to the file where the error occured.');
  if (is_null($result)) {
    $result = security_review_check_error_reporting();
  }
  if ($result['result'] === FALSE) {
    $element['findings']['descriptions'][] = t('You have error reporting set to both the screen and the log.');
    $element['findings']['descriptions'][] = t('<a href="!link">Alter error reporting settings.</a>', array('!link' => url('admin/settings/error-reporting')));
  }
  return $element;
}

/**
 * If private files is enabled check that the directory is not under the web root.
 *
 * There is ample room for the user to get around this check. @TODO get more sophisticated?
 */
function security_review_check_private_files() {
  $file_downloads = variable_get('file_downloads', FILE_DOWNLOADS_PUBLIC);
  if ($file_downloads == FILE_DOWNLOADS_PRIVATE) {
    $file_directory_path = file_directory_path();
    if (strpos($file_directory_path, '/') === 0) {
      // Path begins at root.
      $result = TRUE;
    }
    elseif (strpos($file_directory_path, '../') === 0) {
      // Path begins by moving up the system.
      $result = FALSE;
    }
    else {
      // Directory is relative (or crafty).
      $result = FALSE;
    }
  }
  else {
    return NULL;
  }
  return array('result' => $result);
}

function security_review_check_private_files_help($result = NULL) {
  $element['title'] = t('Private files');
  $element['descriptions'][] = t("If you have Drupal's private files feature enabled you should move the files directory outside of the web server's document root. While Drupal will control serving files when requested by way of content if a user knows the actual system path they can circumvent Drupal's private files feature. You can protect against this by specifying a files directory outside of the webserver root.");
  $last_check = security_review_get_last_check('security_review', 'private_files');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('Your files directory is not outside of the server root.');
    $element['findings']['descriptions'][] = t('<a href="!link">Edit the files directory path.</a>', array('!link' => url('admin/settings/file-system')));
  }
  return $element;
}

/**
 * Check for unsafe allowed extensions.
 */
function security_review_check_upload_extensions() {
  $result = TRUE;
  $check_return_value = array();
  if (module_exists('upload')) {
    // First, check if any unsafe extensions are even allowed.
    $extensions = variable_get('upload_extensions_default', NULL);
    if (!is_null($extensions)) {
      $unsafe_extensions = security_review_unsafe_extensions();
      foreach ($unsafe_extensions as $unsafe_extension) {
        if (strpos($extensions, $unsafe_extension) !== FALSE) {
          // Found an unsafe extension.
          $check_return_value[] = $unsafe_extension;
        }
      }
    }
    if (!empty($check_return_value)) {
      $result = FALSE;
    }
  }
  else {
    return NULL;
  }
  return array('result' => $result, 'value' => $check_return_value);
}

function security_review_check_upload_extensions_help($result = NULL) {
  $element['title'] = t('Allowed upload extensions');
  $element['descriptions'][] = t("The upload module allows users to attach files to content. Some extensions are considered dangerous because the files can be evaluated and then executued in the browser. A malicious user could use this opening to gain control of your site.");
  $last_check = security_review_get_last_check('security_review', 'upload_extensions');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    if (is_null($result)) {
      $result = security_review_check_upload_extensions();
    }
    $element['findings']['descriptions'][] = t('<a href="!link">Alter file upload settings.</a>', array('!link' => url('admin/settings/uploads')));
    $element['findings']['descriptions'][] = t('The following extensions are considered unsafe and should be removed or limited from use. Or, be sure you are not granting untrusted users the ability to upload files.');
    foreach ($result['value'] as $extension) {
      $element['findings']['items'][] = array(
        'raw' => $extension,
        'safe' => check_plain($extension),
      );
    }
  }
  return $element;
}

function security_review_check_query_errors($last_check = NULL) {
  $timestamp = NULL;
  $check_result_value = array();
  $sql = "SELECT message, hostname FROM {watchdog} WHERE type = 'php' AND severity = %d";
  if (!is_null($last_check)) {
    $sql .= " AND timestamp >= %d";
    $timestamp = $last_check['lastrun'];
  }
  $results = db_query($sql, WATCHDOG_ERROR, $timestamp);
  while ($row = db_fetch_array($results)) {
    if (strpos($row['message'], 'SELECT') !== FALSE) {
      $entries[$row['hostname']][] = $row;
    }
  }
  $result = TRUE;
  if (!empty($entries)) {
    foreach ($entries as $ip => $records) {
      if (count($records) > 10) {
        $check_result_value[] = $ip;
      }
    }
  }
  if (!empty($check_result_value)) {
    $result = FALSE;
  }
  else {
    // Rather than worrying the user about the idea of query errors we skip reporting a pass.
    return NULL;
  }
  return array('result' => $result, 'value' => $check_result_value);
}

function security_review_check_query_errors_help($result = NULL) {
  $element['title'] = t('Abundant query errors from the same IP');
  $element['descriptions'][] = t("Database errors triggered from the same IP may be an artifact of a malicious user attempting to probe the system for weaknesses like SQL injection or information disclosure.");
  $last_check = security_review_get_last_check('security_review', 'query_errors');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following IPs were observed with an abundance of query errors.');
    if (is_null($result)) {
      $result = security_review_check_query_errors();
    }
    foreach ($result['value'] as $ip) {
      $element['findings']['items'][] = array(
        'safe' => check_plain($ip),
        'raw' => $ip,
      );
    }
  }
  return $element;
}

function security_review_check_failed_logins($last_check = NULL) {
  $result = TRUE;
  $timestamp = NULL;
  $check_result_value = array();
  $sql = "SELECT message, hostname FROM {watchdog} WHERE type = 'user' AND severity = %d";
  if (!is_null($last_check)) {
    $sql .= " AND timestamp >= %d";
    $timestamp = $last_check['lastrun'];
  }
  $results = db_query($sql, WATCHDOG_NOTICE, $timestamp);
  while ($row = db_fetch_array($results)) {
    if (strpos($row['message'], 'Login attempt failed') !== FALSE) {
      $entries[$row['hostname']][] = $row;
    }
  }
  if (!empty($entries)) {
    foreach ($entries as $ip => $records) {
      if (count($records) > 10) {
        $check_result_value[] = $ip;
      }
    }
  }
  if (!empty($check_result_value)) {
    $result = FALSE;
  }
  else {
    // Rather than worrying the user about the idea of failed logins we skip reporting a pass.
    return NULL;
  }
  return array('result' => $result, 'value' => $check_result_value);
}

function security_review_check_failed_logins_help($results = NULL) {
  $element['title'] = t('Abundant failed logins from the same IP');
  $element['descriptions'][] = t("Failed login attempts from the same IP may be an artifact of a malicous user attempting to brute-force their way onto your site as an authenticated user to carry out nefarious deeds. ");

  $last_check = security_review_get_last_check('security_review', 'failed_logins');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following IPs were observed with an abundance of failed login attempts.');
    if (is_null($results)) {
      $results = security_review_check_failed_logins();
    }
    foreach ($results['value'] as $ip) {
      $element['findings']['items'][] = array(
        'safe' => check_plain($ip),
        'raw' => $ip,
      );
    }
  }
  return $element;
}

/**
 * Look for admin permissions granted to untrusted roles.
 */
function security_review_check_admin_permissions() {
  $result = TRUE;
  $check_result_value = array();
  // Collect the permissions untrusted roles have.
  $untrusted = security_review_untrusteds_permissions();
  // Admin permissions.
  $admin_perms = security_review_admin_permissions();
  // Check for intersections.
  foreach ($untrusted as $rid => $permissions) {
    $intersect = array_intersect($permissions, $admin_perms);
    if (!empty($intersect)) {
      $check_result_value[$rid] = $intersect;
    }
  }
  if (!empty($check_result_value)) {
    $result = FALSE;
  }
  return array('result' => $result, 'value' => $check_result_value);
}

function security_review_check_admin_permissions_help($results = NULL) {
  $element['title'] = t('Admin permissions');
  $element['descriptions'][] = t("Drupal's permission system is extensive and allows for varying degrees of control. Certain permissions would allow a user total control, or the ability to escalate their control, over your site and should only be granted to trusted users.");
  $element['descriptions'][] = t('<a href="!link">Read more about trusted vs. untrusted roles and permissions on DrupalScout.com.</a>', array('!link' => url('http://drupalscout.com/knowledge-base/importance-user-roles-and-permissions-site-security')));
  $last_check = security_review_get_last_check('security_review', 'admin_permissions');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    if (is_null($results)) {
      $results = security_review_check_admin_permissions();
    }
    $element['findings']['descriptions'][] = t('You have granted untrusted roles the following administrative permissions that you should revoke.');
    foreach ($results['value'] as $rid => $permissions) {
      $role = db_fetch_array(db_query("SELECT name FROM {role} WHERE rid = %d", $rid));
      $permissions = implode(', ', $permissions);
      $item = t('<a href="!link">@name</a> has %permissions', array('!link' => url('admin/user/permissions/' . $rid), '@name' => $role['name'], '%permissions' => $permissions));
      $safe = t('@name has %permissions', array('@name' => $role['name'], $permissions));
      $element['findings']['items'][] = array(
        'html' => $item,
        'safe' => $safe,
        'raw' => $role['name'] . ':' . $permissions,
      );
    }
  }
  return $element;
}

function security_review_check_nodes($last_check = NULL) {
  $result = TRUE;
  $check_result_value = array();
  $timestamp = NULL;
  
  $sql = "SELECT n.nid FROM {node} n INNER JOIN {node_revisions} r ON n.vid = r.vid WHERE r.body LIKE '%s' ";
  // If the check passed before only look at nodes since the last run.
  if (!is_null($last_check) && $last_check['result'] == '1') {
    $sql .= " AND n.changed >= %d";
    $timestamp = $last_check['lastrun'];
  }
  foreach (array('Javascript' => '%<script%', 'PHP' => '%<?php%') as $description => $comparison) {
    $results = pager_query($sql, 50, 0, NULL, $comparison, $timestamp);
    while ($row = db_fetch_array($results)) {
      $check_result_value[] = array($description => $row['nid']);
    }
    if (!empty($check_result_value)) {
      $result = FALSE;
    }
  }

  return array('result' => $result, 'value' => $check_result_value);
}

function security_review_check_nodes_help($results = NULL) {
  $element['title'] = t('Dangerous tags in nodes');
  $element['descriptions'][] = t("Script and PHP code in the body of nodes does not align with Drupal best practices and may be a vulnerability if an untrusted user is allowed to edit such content. It is recommended you remove such content from the body of nodes.");

  $last_check = security_review_get_last_check('security_review', 'nodes');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following nodes potentially have dangerous tags. The links go to the edit page.');
    if (is_null($results)) {
      $results = security_review_check_nodes(); // Don't pass $last_check because timestamp is wrong now.
    }
    $destination = drupal_get_destination();
    foreach ($results['value'] as $problem_nid) {
      // There is no access checking. We state that the use of this module should be granted to trusted users only.
      $node = node_load(current($problem_nid));
      $description = key($problem_nid);
      $html = t('@description found in <a href="!link">@title</a>', array('@description' => $description, '!link' => url('node/' . $node->nid . '/edit', array('query' => $destination)), '@title' => $node->title));
      $url = url('node/' . $node->nid . '/edit');
      $element['findings']['items'][] = array(
        'html' => $html, 
        'safe' => t('@description in !url', array('@description' => $description, '!url' => $url)),
        'raw' => $description . ':' . $url,
      );
    }
    $element['findings']['pager'] = theme('pager', NULL, 50);
  }

  return $element;
}

function security_review_check_comments($last_check = NULL) {
  $result = TRUE;
  $check_result_value = array();
  $timestamp = NULL;
  if (module_exists('comment')) {
    $sql = "SELECT nid, cid FROM {comments} WHERE comment LIKE '%s'" ;
    // If the check passed before only look at comments since the last run.
    if (!is_null($last_check) && $last_check['result'] == '1') {
      $sql .= " AND timestamp >= %d";
      $timestamp = $last_check['lastrun'];
    }
    foreach (array('Javascript' => '%<script%', 'PHP' => '%<?php%') as $description => $comparison) {
      $results = pager_query($sql, 20, 0, NULL, $comparison, $timestamp);
      while ($row = db_fetch_array($results)) {
        $check_result_value[$row['cid']] = array($description => $row['nid']);
      }
      if (!empty($check_result_value)) {
        $result = FALSE;
      }
    }
  }
  else {
    return NULL;
  }
  return array('result' => $result, 'value' => $check_result_value);
}

function security_review_check_comments_help($results = NULL) {
  $element['title'] = t('Dangerous tags in comments');
  $element['descriptions'][] = t("There is little reason for script and PHP tags to be in comments (unless they are code examples) and could be in use maliciously.");

  $last_check = security_review_get_last_check('security_review', 'comments');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following comments have dangerous tags. The links go to the edit page.');
    if (is_null($results)) {
      $results = security_review_check_comments(); // Don't pass $last_check because timestamp is wrong now.
    }
    $destination = drupal_get_destination();
    foreach ($results['value'] as $cid => $nid) {
      $comment = _comment_load($cid);
      // There is no access checking. We state that the use of this module should be granted to trusted users only.
      $node = node_load($nid);
      $title = t('!subject on !title', array('!subject' => $comment->subject, '!title' => $node->title));
      $element['findings']['items'][] = array(
        'html' => l($title, 'comment/edit/' . $cid, array('query' => $destination)),
        'safe' => check_plain($title),
        'raw' => $title . ':' . url('comment/edit/' . $cid),
      );
    }
    $element['findings']['pager'] = theme('pager', NULL, 20);
  }

  return $element;
}

function security_review_check_name_passwords($last_check = NULL) {
  $result = TRUE;
  $check_result_value = array();
  $timestamp = NULL;

  // We check whether trusted roles have weak passwords.
  $trusted_roles = security_review_trusted_roles();
  if (!empty($trusted_roles)) {
    $trusted_roles = array_keys($trusted_roles);
    $check_result_value = _security_review_weak_passwords($trusted_roles);
  }
  if (!empty($check_result_value)) {
    $result = FALSE;
  }

  return array('result' => $result, 'value' => $check_result_value);
}

function _security_review_weak_passwords($trusted_roles) {
  $weak_users = array();

  // Select users with a trusted role whose password is their username.
  $sql = "SELECT u.uid, u.name, COUNT(rid) AS count FROM {users} u LEFT JOIN
    {users_roles} ur ON u.uid = ur.uid AND ur.rid in (" . db_placeholders($trusted_roles) . ")
    WHERE pass = md5(name) GROUP BY uid";
  $results = db_query($sql, $trusted_roles); // @todo pager_query?
  while ($row = db_fetch_object($results)) {
    $record[] = $row;
    if ($row->count > 0) {
      $weak_users[$row->uid] = $row->name;
    }
  }

  // Explicitly check uid 1 in case they have no roles.
  $weak_uid1 = db_fetch_object(db_query("SELECT u.uid, u.name, 1 AS count FROM {users} u WHERE pass = md5(name) AND uid = 1"));
  if (!empty($weak_uid1->count)) {
    $weak_users[$weak_uid1->uid] = $weak_uid1->name; 
  }

  return $weak_users;
}

function security_review_check_name_passwords_help($results = NULL) {
  $element['title'] = t('Username as password');
  $element['descriptions'][] = t("Users with elevated access on the site (trusted users) who have a their account password the same as their username. It is recommended you enforce a password strength policy to avoid an attacker easily gaining access to your site.");

  $last_check = security_review_get_last_check('security_review', 'name_passwords');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following users have extremely weak passwords. The links go to the edit page.');
    if (is_null($results)) {
      $results = security_review_check_name_passwords(); // Don't pass $last_check because timestamp is wrong now.
    }
    foreach ($results['value'] as $uid => $name) {
      $element['findings']['items'][] = array(
        'html' => l($name, 'user/' . $uid . '/edit'),
        'safe' => check_plain($name),
        'raw' => $name,
      );
    }
    $element['findings']['pager'] = theme('pager', NULL, 20);
  }

  return $element;
}

/**
 * A Security Review check for allowed extensions on Filefield fields.
 */
function security_review_check_filefield_extensions($last_check = NULL) {
  $result = TRUE;
  $check_return_value = array();
  $unsafe_extensions = security_review_unsafe_extensions();

  // Collect list of untrusted roles' permissions.
  $untrusted = security_review_untrusteds_permissions();
  // Get Filefields and check widget file_extensions.
  $fields = filefield_get_field_list();
  foreach ($fields as $field) {
    /* @TODO Add this back in when also checking upload use on content types.
    // Initialize an empty array of roles IDs that can use this field.
    $rids = array();
    // Check if untrusted users can create or edit the content type that holds
    // this field.
    $field_type_permissions = array(
      'create ' . $field['type_name'] . ' content',
      'edit own ' . $field['type_name'] . ' content',
      'edit any ' . $field['type_name'] . ' content',
    );
    foreach ($untrusted as $rid => $permissions) {
      $intersect = array_intersect($permissions, $field_type_permissions);
      if (empty($intersect)) {
        // No intersection is good.
        continue;
      }
      else {
        $rids[] = $rid;
      }
    }
    // Do not continue check if no untrusted roles can use this field.
    if (empty($rids)) {
      continue;
    }*/

    $extensions = $field['widget']['file_extensions'];
    if (empty($extensions)) {
      // No extensions set, so field allows all unsafe extensions.
      $check_return_value[$field['field_name']]['empty'] = TRUE;
    }
    else {
      // Check if specific unsafe extensions are allowed.
      foreach ($unsafe_extensions as $unsafe_extension) {
        if (strpos($extensions, $unsafe_extension) !== FALSE) {
          // Found an unsafe extension.
          $check_return_value[$field['field_name']]['extensions'][] = $unsafe_extension;
        }
      }
    }
  }
  if (!empty($check_return_value)) {
    $result = FALSE;
  }
  return array('result' => $result, 'value' => $check_return_value);
}

/**
 * Help for the Security Review check for allowed extensions on Filefield fields.
 */
function security_review_check_filefield_extensions_help($results = NULL) {
  $element['title'] = t('Filefield allowed uploads');
  $element['descriptions'][] = t("The Filefield module allows users to attach files to content. Some extensions are considered dangerous because the files can be evaluated and then executued in the browser. A malicious user could use this opening to gain control of your site.");
  $last_check = security_review_get_last_check('filefield', 'filefield_extensions');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following Filefield fields have unsafe extensions allowed for uploaded files.');
    if (is_null($results)) {
      $results = security_review_check_filefield_extensions();
    }
    $fields = filefield_get_field_list();
    foreach ($results['value'] as $field_name => $value) {
      $edit_url = "admin/content/node-type/" . $fields[$field_name]['type_name'] . "/fields/$field_name";
      if (isset($value['empty']) && $value['empty'] == TRUE) {
        $html = t('<a href="!url">%field_name</a> has all extensions allowed and should be corrected to limit uploads to safe extensions only', array('%field_name' => $field_name, '!url' => url($edit_url)));
        $item = t('%field_name has all extensions allowed and should be corrected to limit uploads to safe extensions only', array('%field_name' => $field_name));
        $element['findings']['items'][] = array(
          'html' => $html, 
          'safe' => $item,
          'raw' => $field_name
        );
      }
      else {
        $extensions = implode(', ', $value['extensions']);
        $html = t('<a href="!url">%field_name</a> has the unsafe extensions: @extensions', array('%field_name' => $field_name, '@extensions' => $extensions, '!url' => url($edit_url)));
        $item = t('%field_name has the unsafe extensions: @extensions', array('%field_name' => $field_name, '@extensions' => $extensions));
        $element['findings']['items'][] = array(
          'html' => $html,
          'safe' => $item,
          'raw' => $field_name . ' : ' . $extensions 
        );
      }
    }
  }
  return $element;
}

function security_review_check_views_access($last_check = NULL) {
  $result = TRUE;
  $check_result_value = array();
  $timestamp = NULL;
  // Load and loop through every view, checking the access type in displays.
  $views = views_get_all_views();
  foreach ($views as $view) {
    if ($view->disabled !== TRUE) {
      // Access is set in display options of a display.
      foreach ($view->display as $display_name => $display) {
        if (isset($display->display_options['access']) && $display->display_options['access']['type'] == 'none') {
          $check_result_value[$view->name][] = $display_name;
        }
      }
    }
  }
  if (!empty($check_result_value)) {
    $result = FALSE;
  }
  return array('result' => $result, 'value' => $check_result_value);
}
 
function security_review_check_views_access_help($results = NULL) {
  $element['title'] = t('Views access');
  $element['descriptions'][] = t("Views can check if the user is allowed access to the content. It is recommended that all Views implement some amount of access control, at a minimum checking for the permission 'access content'.");

  $last_check = security_review_get_last_check('views', 'access');
  if ($last_check['skip'] == '1') {
    $element['findings']['descriptions'][] = _security_review_check_skipped($last_check);
  }
  elseif ($last_check['result'] == '0') {
    $element['findings']['descriptions'][] = t('The following View displays do not check access.');
    if (is_null($results)) {
      $results = security_review_check_views_access();
    }
    foreach ($results['value'] as $view => $displays) {
      $url = 'admin/build/views/edit/' . $view;
      foreach ($displays as $display) {
        $item = $view . ': ' . $display;
        $element['findings']['items'][] = array(
          'html' => l($item, $url, array('fragment' => $display)),
          'safe' => $item, // View names are safe.
          'raw' => $item,
        );
      }
    }
  }

  return $element;
}
