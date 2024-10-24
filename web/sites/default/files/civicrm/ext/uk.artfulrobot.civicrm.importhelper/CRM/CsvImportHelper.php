<?php

class CRM_CsvImportHelper {
  protected static $titles_regexp;
  /**
   * Process an upload.
   *
   * @param array $params 'data' is a data URL.
   */
  public static function upload($params) {
    // This could take a while...
    set_time_limit(0);

    if (empty($params['data'])) {
      throw new InvalidArgumentException('No file sent.');
    }

    // Strip off MIME type; it's usually missing/meaningless from Windows machines anyway. :-(
    if (!preg_match('@^data:[^;]*;base64,@', $params['data'], $matches)) {
      throw new InvalidArgumentException('Expected URL-encoded data but got: ' . htmlspecialchars(substr($params['data'], 0, 16)));
    }
    $file = base64_decode(substr($params['data'], strlen($matches[0])), TRUE);

    if (empty($file)) {
      throw new InvalidArgumentException('Decoding base64 data failed.');
    }

    // OK, we have a file!

    // Detect encoding.
    $preferredEncodingList = [];
    $supportedEncodings = mb_list_encodings();
    foreach (['ISO-8859-1', 'cp1252', 'Windows-1252', 'UTF-8'] as $desiredEncoding) {
      if (in_array($desiredEncoding, $supportedEncodings)) {
        $preferredEncodingList[] = $desiredEncoding;
      }
    }
    if (!$preferredEncodingList) {
      $preferredEncodingList = NULL;
    }

    $enc = mb_detect_encoding($file, $preferredEncodingList, TRUE);
    if ($enc !== 'UTF-8') {
      // default to assuming input is latin1 if unable to detect, if that is available.
      if (!$enc && in_array('ISO-8859-1', $supportedEncodings)) {
        $enc = $enc ? $enc : 'ISO-8859-1';
        $file = mb_convert_encoding($file, 'UTF-8', $enc);
      }
    }

    // Parse into rows.
    // Originally we did the following, based on a note on php.net.
    // $file = str_getcsv($file, "\n");
    // However this fails for all but the simplest case because of the way it parses the enclosures (")
    // Use array_filter to remove empty lines, e.g. an empty line at the end of the file.
    $file = array_filter(static::parseCsvString($file));

    // open the file and import the data.
    $header = 1;
    $clean_only = FALSE;
    $skipped_blanks = 0;
    $rows = 0;

    // Drop all existing data.
    static::truncate();

    // Insert new data.
    foreach ($file as $line) {
      $line = array(
        'contact_id' => 0,
        'title' => trim($line[0]),
        'fname' => trim($line[1]),
        'lname' => trim($line[2]),
        'email' => trim($line[3]),
        'state' => '',
        'resolution' => [],
        'data' => serialize($line),
      );
      if ("$line[fname]$line[lname]$line[email]" == '') {
        $skipped_blanks++;
        continue;
      }
      if ($header) {
        $line['state'] = 'header';
        $header = 0;
        // Check that the header does not include 'Internal ID'
        if (in_array('Internal ID', unserialize($line['data']))) {
          throw new \InvalidArgumentException('The header line includes an Internal ID column, suggesting that this file has already been processed. Rename that column then re-upload it if you really want to process it.');
        }
        $rows--; // Don't count the header.
      }
      else {
        if (!$line['lname'] && !$line['title'] && $line['fname']) {
          // Only the 'fname' column has anything in. Try to split a name out from this.
          static::cleanName($line);
        }
        if ($clean_only) {
          $line['state'] = 'clean-only';
        }
        else {
          static::findContact($line);
        }
      }
      $line['resolution'] = serialize($line['resolution']);

      // Insert into table.
      $insertQuery = "INSERT INTO `civicrm_csv_match_cache` (
        `contact_id`, `fname`, `lname`, `email`, `title`,
        `state`, `resolution`, `data`)
        VALUES (%1, %2, %3, %4, %5, %6, %7, %8)";
      $queryParams = [
        1 => [ $line['contact_id'], 'Integer'],
        2 => [ $line['fname'], 'String'],
        3 => [ $line['lname'], 'String'],
        4 => [ $line['email'], 'String'],
        5 => [ $line['title'], 'String'],
        6 => [ $line['state'], 'String'],
        7 => [ $line['resolution'], 'String'],
        8 => [ $line['data'], 'String'],
      ];
      CRM_Core_DAO::executeQuery($insertQuery, $queryParams);
      $rows++;
    }
    if ($skipped_blanks) {
      //drupal_set_message("Warning: the file contained $skipped_blanks blank rows (i.e. did not have name or email). These were ignored, however the job would have been much quicker if they weren't included in the upload :-)", 'warning');
    }

    return ['imported' => $rows, 'skipped' => $skipped_blanks];
  }
  /**
   * This is called in the case that there is something in the first name
   * field, but nothing in last name or title.
   *
   * It attempts to separate the data in first name out into title, first and last name fields.
   */
  public static function cleanName(&$record) {
    $names = trim($record['fname']);
    $titles = static::getTitleRegex();

    if (preg_match('/^([^,]+)\s*,\s*([^,]+)$/', $names, $matches)) {
      // got name in form: Last, First.
      $record['lname'] = $matches[1];
      $record['fname'] = trim($matches[2]);
      if (preg_match("/^($titles)\s+(.+)$/", $record['fname'], $matches)) {
        $record['title'] = $matches[1];
        $record['fname'] = $matches[2];
      }
    }
    else {
      $names = preg_split('/\s+/', $names);
      if (count($names) > 1) {
        // prefix?
        if (preg_match("/^$titles$/", $names[0])) {
          $record['title'] = array_shift($names);
        }
        // Let's assume the first word is the first name
        $record['fname'] = array_shift($names);
        $record['lname'] = implode(' ', $names);
      }
    }

    // if all lowercase or all uppercase, then tidy the case.
    foreach (array('fname', 'lname', 'title') as $_) {
      $name = $record[$_];
      if (strtolower($name) == $name || strtoupper($name) == $name) {
        $record[$_] = ucfirst($name);
      }
    }
  }
  /**
   * Attempt to find the contact in the database.
   *
   * Returns an array of candidate contacts, keyed by contact id, each line is an array with keys:
   * - contact_id
   * - 'match' why this was a candidate (e.g. email match)
   * - 'name' just the name
   *
   */
  public static function findContact(&$record) {
    $record['resolution'] = [];

    // email is most unique. if we have that, start there.
    if ($record['email']) {

      // got email look it up in the email table
      $result = civicrm_api3('Email', 'get', array('sequential' => 1, 'email' => $record['email']));
      if ($result['count'] > 0) {
        // We need to join the contact name details onto our email matches array.
        $contact_ids = array();
        foreach ($result['values'] as $_) {
          $contact_ids[$_['contact_id']] = TRUE;
        }
        // Get unique contacts, keyed by contact_id
        $contacts = civicrm_api3('Contact', 'get', [
          'id' => ['IN' => array_keys($contact_ids)],
          'is_deleted' => 0,
          'sequential' => 0,
        ]);

        // Make list of unique candidate contacts.
        foreach ($contacts['values'] as $contact_id => $contact) {
          $record['resolution'][] = [
            'contact_id' => (string) $contact_id,
            'match' => ts('Same email'),
            'name'  => $contact['display_name'],
          ];
        }

        // Nb. there is the case that there is no resolution here, e.g. if
        // email is found but belongs to deleted contact. This case rolls
        // through to the email not found below.
        if (count($record['resolution']) == 1) {
          // Single winner.
          $record['contact_id'] = (string) $contact_id;
          $record['state'] = 'found';
          return;

        }
        elseif (count($record['resolution']) > 1) {
          // More than one contact matched.
          // quick scan to see if there's only one that matches first name
          $m = array_filter($record['resolution'], function ($_) use ($contacts, $record) {
            $contact = $contacts['values'][$_['contact_id']];
            return ($contact['first_name'] && $record['fname'] && $contact['first_name'] == $record['fname']);
          });
          if (count($m) == 1) {
            // Only one of these matches on (email and) first name, use that.
            $record['resolution'] = $m;
            $record['contact_id'] = (string) reset($m)['contact_id'];
            $record['state'] = 'found';
            return;
          }

          // quick scan to see if there's only one that matches last name
          $m = array_filter($record['resolution'], function ($_) use ($contacts, $record) {
            $contact = $contacts['values'][$_['contact_id']];
            return ($contact['last_name'] && $record['lname'] && $contact['last_name'] == $record['lname']);
          });
          if (count($m) == 1) {
            // Only one of these matches on (email and) last name, use that.
            $record['resolution'] = $m;
            $record['contact_id'] = (string) reset($m)['contact_id'];
            $record['state'] = 'found';
            return;
          }

          // Don't look wider than matched emails.
          $record['state'] = '';
          $record['contact_id'] = 0;
          return;
        }
      }
    }

    // Now left with cases where the email was not found in the database (or
    // not given in input), so names only.

    if ($record['fname'] && $record['lname']) {
      // see if we can find them by name.
      $params = [
        'sequential' => 1,
        'is_deleted' => 0,
        'first_name' => $record['fname'],
        'last_name' => $record['lname'],
        'return' => 'display_name'];
      $result = civicrm_api3('Contact', 'get', $params);
      if ($result['count'] == 1) {
        // winner
        // Not the winner: see https://github.com/artfulrobot/uk.artfulrobot.civicrm.importhelper/issues/15
        // $record['contact_id'] = (string) $result['values'][0]['contact_id'];
        // $record['state'] = 'found';
        $record['contact_id'] = 0;
        $record['state'] = 'multiple'; // not strictly true, but helpful.
        $record['resolution'] = [[
          'contact_id' => (string) $result['values'][0]['contact_id'],
          'match' => 'Only name match',
          'name' => $result['values'][0]['display_name'],
        ]];
        return;
      }

      if ($result['count'] > 1) {
        // could be any of these contacts
        foreach ($result['values'] as $contact) {
          $record['resolution'][] = [
            'contact_id' => (string) $contact['contact_id'],
            'match' => 'Same name',
            'name'  => $contact['display_name'],
          ];
        }
        $record['state'] = 'multiple';
        $record['contact_id'] = 0;
        return;
      }

      // Still not found? OK, probably something weird with the first name.
      // Let's try last name, with first name as a substring match
      // see if we can find them by name.
      $params = [
        'sequential' => 1,
        'is_deleted' => 0,
        'first_name' => '%' . $record['fname'] . '%',
        'last_name' => $record['lname']];
      $result = civicrm_api3('Contact', 'get', $params);
      if ($result['count'] == 1) {
        // winner
        $record['contact_id'] = (string) $result['values'][0]['contact_id'];
        $record['resolution'] = [[
          'contact_id' => (string) $result['values'][0]['contact_id'],
          'match' => 'Only similar match',
          'name' => $result['values'][0]['display_name'],
        ]];
        $record['state'] = 'found';
        return;
      }

      if ($result['count'] > 1) {
        // could be any of these contacts
        $record['resolution'] = array();
        foreach ($result['values'] as $contact) {
          $record['resolution'][] = [
            'contact_id' => (string) $contact['contact_id'],
            'match' => 'Similar name',
            'name'  => $contact['display_name'],
          ];
        }
        $record['state'] = 'multiple';
        $record['contact_id'] = 0;
        return;
      }

      // Still not found, let's try first initial.
      $params = [
        'sequential' => 1,
        'first_name' => substr($record['fname'], 0, 1) . '%',
        'last_name' => $record['lname'],
        'return' => 'display_name'];
      $result = civicrm_api3('Contact', 'get', $params);
      if ($result['count'] > 0) {
        // Can't assume from the initial, even if just one person.
        // could be any of these contacts
        foreach ($result['values'] as $contact) {
          $record['resolution'][] = [
            'contact_id' => (string) $contact['contact_id'],
            'match' => 'Similar name',
            'name'  => $contact['display_name'],
          ];
        }
        $record['state'] = 'multiple';
        $record['contact_id'] = 0;
        return;
      }
    }

    // OK, maybe the last name is particularly unique?
    if ($record['lname']) {
      $params = ['sequential' => 1, 'last_name' => $record['lname'], 'is_deleted' => 0];
      $result = civicrm_api3('Contact', 'get', $params);
      if ($result['count'] > 10) {
        $record['state'] = 'multiple';
        $record['contact_id'] = 0;
        return;
      }
      elseif ($result['count'] > 0) {
        // could be any of these contacts
        $record['resolution'] = array();
        foreach ($result['values'] as $contact) {
          $record['resolution'][] = [
            'contact_id' => (string) $contact['contact_id'],
            'match' => 'Same last name',
            'name'  => $contact['display_name'],
          ];
        }
        $record['state'] = 'multiple';
        $record['contact_id'] = 0;
        return;
      }
    }

    // if we're here, we only have one name, so let's not bother.
    $record['state'] = 'impossible';
    $record['contact_id'] = 0;
    $record['resolution'] = [];
  }
  /**
   * Get the regex to identify titles.
   *
   * Currently this just uses some used in UK, ideally it would fetch a list of
   * configured prefixes from CiviCRM. PRs welcome.
   */
  public static function getTitleRegex() {
    if (empty(static::$titles_regexp)) {
      static::$titles_regexp = '(?:Ms|Miss|Mrs|Mr|Dr|Prof|Rev|Cllr|Rt Hon).?';
    }
    return static::$titles_regexp;
  }
  /**
   * Update the civicrm_csv_match_cache table.
   */
  public static function update($record_id, $updates) {

    $record = static::loadCacheRecord($record_id);

    if (isset($updates['contact_id'])) {
      // Setting contact ID, either to something in the resolutions list,
      // something NOT in the resolutions list (contact search)
      // or to something zero like.

      if ($updates['contact_id']) {
        // Got contact ID.
        if (!in_array($updates['contact_id'], array_map(
          function ($_) {return $_['contact_id'];}, $record['resolution']))) {
          // It's not a contact that we guessed in our resolutions array. So we need to add it in now.
          $contact = civicrm_api3('Contact', 'getsingle', ['id' => (int) $updates['contact_id'], 'return' => 'display_name']);
          $_ = $record['resolution'];
          $_[] = [
            'contact_id' => (string) $updates['contact_id'],
            'match' => ts('Chosen by you'),
            'name'  => $contact['display_name'],
          ];
          $updates['resolution'] = serialize($_);
        }
      }
      else {
        // User is un-choosing someone. Clean out the resolutions.
        $record['resolution'] = array_filter($record['resolution'], function ($_) {
          return $_['match'] != ts('Chosen by you');
        });
        $updates['resolution'] = serialize($record['resolution']);
      }
    }
    if (empty($updates['state'])) {
      $updates['state'] = $record['resolution'] ? 'multiple' : 'impossible';
    }

    static::updateSet(
      [
        'fname' => $record['fname'],
        'lname' => $record['lname'],
        'email' => $record['email'],
      ]
      + $updates
    );

    // Reload and return.
    $record = static::loadCacheRecords([
        'fname' => $record['fname'],
        'lname' => $record['lname'],
        'email' => $record['email'],
    ]);
    return reset($record);
  }
  /**
   * Update the civicrm_csv_match_cache table for all records with given name and email.
   *
   * Nb. expects resolution key to be a serialized string, if it is given.
   */
  public static function updateSet($updates) {

    // Update all records for the same name and email.
    $sql = "UPDATE `civicrm_csv_match_cache`
      SET state = %1, contact_id = %2 "
      . (isset($updates['resolution']) ? ', resolution = %6 ' : '')
      . "WHERE fname = %3 AND lname = %4 AND email = %5";

    $queryParams = [
      1 => [ $updates['state'], 'String'],
      2 => [ $updates['contact_id'], 'Integer'],
      3 => [ $updates['fname'], 'String' ],
      4 => [ $updates['lname'], 'String' ],
      5 => [ $updates['email'], 'String' ],
    ];
    if (isset($updates['resolution'])) {
      $queryParams[6] = [ $updates['resolution'], 'String' ];
    }
    $result = CRM_Core_DAO::executeQuery($sql, $queryParams);
  }
  public static function csvSafe($string) {
    return '"' . str_replace('"', '""', $string) . '"';
  }
  /**
   * Load grouped rows from civicrm_csv_match_cache.
   *
   * @param array $filters with the following optional keys:
   * - id
   * - fname
   * - lname
   * - email
   *
   * @return array of records.
   */
  public static function loadCacheRecords($filters = []) {

    $params = [];
    $wheres = [];
    $i = 1;

    if (isset($filters['id'])) {
      // Shortcut for the sake of the API.
      // Nb. this extension never uses loadCacheRecords with an 'id' filter.
      return [static::loadCacheRecord($filters['id'])];
    }

    foreach (['fname', 'lname', 'email'] as $_) {
      if (isset($filters[$_])) {
        $wheres[] = "a.$_ = %$i";
        $params[$i++] = [ $filters[$_], 'String' ];
      }
    }

    $wheres = $wheres ? ('AND ' . implode(' AND ', $wheres)) : '';

    // Select every column except 'data'. Keep original input order (id)
    // Nb. Fix issue #2
    // Certain MySQL engines (e.g. 5.7) don't allow selecting a field that is
    // not in a GROUP BY or aggregate function. So we have to use a subquery +
    // join to simulate FIRST() sort of thing. Of course this would be nicer if
    // the groups were in one table and the resolutions in another (normalised
    // data), but that's an optimisation I've not as yet bothered with, since
    // the data is temporary and not expected to be massive.
    $sql = "
      SELECT a.fname, a.lname, a.email, a.id, set_count,
             b.contact_id, b.title, b.state, b.resolution
      FROM (
        SELECT fname, lname, email, min(a.id) id, COUNT(a.id) set_count
        FROM civicrm_csv_match_cache a
        WHERE a.state != 'header' $wheres
        GROUP BY a.fname, a.lname, a.email
        ) a
      INNER JOIN civicrm_csv_match_cache b ON (b.id=a.id)
      ORDER BY a.id
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $return_values = $dao->fetchAll();
    $dao->free();

    static::loadCacheRecordsPost($return_values);

    return $return_values;
  }
  /**
   * Unpack the resolution field, stored serialize()-ed.
   *
   * @param &array
   */
  public static function loadCacheRecordsPost(&$return_values) {
    foreach ($return_values as &$row) {
      // Nb. we have to turn 0 into '' here because crmEntityref angular widget
      // thing does not recognise 0 as unselected, and merrily selects a random
      // contact(!)
      $row['contact_id'] = $row['contact_id'] ? $row['contact_id'] : '';
      $row['resolution'] = $row['resolution'] ? unserialize($row['resolution']) : [];
    }
  }
  /**
   * Load single row from civicrm_csv_match_cache.
   *
   * @param integer $id
   * @return array
   */
  public static function loadCacheRecord($id) {

    // Select every column except 'data'. Keep original input order (id)
    $sql = "
      SELECT fname, lname, email, id,  contact_id, title, state, resolution
      FROM civicrm_csv_match_cache
      WHERE id = %1
    ";
    $params = [1 => [$id, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $return_values = $dao->fetchAll();
    $dao->free();
    if (count($return_values) != 1) {
      throw new InvalidArgumentException("Failed to load the record. Try reloading the page.");
    }
    static::loadCacheRecordsPost($return_values);
    return reset($return_values);
  }
  /**
   * Spit a CSV file out.
   */
  public static function spewCsv() {
    // Select everything except 'data'.
    $sql = "
      SELECT contact_id, title, fname, lname, data FROM civicrm_csv_match_cache
      ORDER BY id
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $dao->fetch();

    // Add the headers needed to let the browser know this is a csv file download.
    header('Content-Type: text/csv; utf-8');
    header('Content-Disposition: attachment; filename = civicrm-import-data.csv');

    // Output Headers for CSV:
    //
    // prepend contact ID and new name fields - just so we're not overwriting the old data.
    print '"Internal ID","Title", "First Name", "Last Name"';
    $data = unserialize($dao->data);
    // unpack original header line
    $data[0] = "Orig: $data[0]";
    $data[1] = "Orig: $data[1]";
    $data[2] = "Orig: $data[2]";
    foreach ($data as $_) {
      print "," . static::csvSafe($_);
    }
    print "\n";

    // Now output rest of data.
    while ($dao->fetch()) {
      $data = unserialize($dao->data);
      // prepend contact ID and name fields
      print ($dao->contact_id ? $dao->contact_id : '""');
      print "," . static::csvSafe($dao->title);
      print "," . static::csvSafe($dao->fname);
      print "," . static::csvSafe($dao->lname);
      foreach ($data as $_) {
        print "," . static::csvSafe($_);
      }
      print "\n";
    }
    exit;
  }
  /**
   * Spit a CSV file out.
   */
  public static function truncate() {
    CRM_Core_DAO::executeQuery('TRUNCATE civicrm_csv_match_cache;');
  }
  /**
   * Rescan all un-selected contacts.
   */
  public static function rescan() {

    // Select everything except 'data'.
    $sql = "
      SELECT MIN(id) id, fname, lname, email FROM civicrm_csv_match_cache
      WHERE contact_id = 0 AND state != 'header'
      GROUP BY fname, lname, email
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while ($dao->fetch()) {
      $line = [
        'fname' => $dao->fname,
        'lname' => $dao->lname,
        'email' => $dao->email,
      ];
      static::findContact($line);
      $line['resolution'] = serialize($line['resolution']);
      static::updateSet($line);
    }

  }
  /**
   * Create new contacts (Individuals) where the state is 'impossible' meaning
   * either no matches exist or the user has said explicitly that it is a new
   * contact.
   */
  public static function createMissingContacts() {

    $sql = "
      SELECT MIN(id) id, fname, lname, email FROM civicrm_csv_match_cache
      WHERE contact_id = 0 AND state = 'impossible'
      GROUP BY fname, lname, email
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while ($dao->fetch()) {
      $params = [
        'contact_type' => 'Individual',
        'first_name'   => $dao->fname,
        'last_name'    => $dao->lname,
      ];

      $contact = civicrm_api3('Contact', 'create', $params);

      if ($dao->email) {
        $params = [
          'contact_id' => $contact['id'],
          'email'      => $dao->email,
        ];
        $email = civicrm_api3('Email', 'create', $params);
      }

      // Now update record.
      static::updateSet([
        'fname' => $dao->fname,
        'lname' => $dao->lname,
        'email' => $dao->email,
        'state' => 'found',
        'contact_id' => $contact['id'],
        'resolution' => serialize(
          [[
            'contact_id' => (string) $contact['id'],
            'match' => ts('Created'),
            'name'  => "$dao->lname, $dao->fname",
          ]]),
      ]);
    }

    $dao->free();

  }
  /**
   * Create new contact (Individual) for the specified row.
   *
   * @param int $record_id id from our cache table.
   * @return int new Contact ID.
   */
  public static function createMissingContact($record_id) {
    $record = CRM_CsvImportHelper::loadCacheRecord($record_id);

    $params = [
      'contact_type' => 'Individual',
      'first_name'   => $record['fname'],
      'last_name'    => $record['lname'],
    ];

    $contact = civicrm_api3('Contact', 'create', $params);

    if (!empty($record['email'])) {
      $params = [
        'contact_id' => $contact['id'],
        'email'      => $record['email'],
      ];
      $email = civicrm_api3('Email', 'create', $params);
    }

    // Now update record.
    static::updateSet([
      'fname' => $record['fname'],
      'lname' => $record['lname'],
      'email' => $record['email'],
      'state' => 'found',
      'contact_id' => $contact['id'],
      'resolution' => serialize(
        [[
          'contact_id' => (string) $contact['id'],
          'match' => ts('Created'),
          'name'  => "$record[lname], $record[fname]",
        ]]),
    ]);
    return $contact['id'];
  }
  static public function getSummary($counts = NULL) {
    // Summarise data

    if ($counts === NULL) {
      $rows = db_query("
        SELECT *, COUNT(id) set_count FROM {civicrm_csv_match_cache} todo
        WHERE state != 'header'
        GROUP BY fname, lname, email");

      $counts = array('impossible' => 0, 'multiple' => 0, 'chosen' => 0, 'found' => 0);
      while ($row = $rows->fetchAssoc()) {
        switch ($row['state']) {
          case 'chosen':
            if ($row['contact_id']) {
              $counts['chosen']++;
            }
            else {
              $counts['impossible']++;
            }
            break;

          default:
            $counts[$row['state']]++;
        }
      }
    }

    return "<div id='csv-match-summary'><h2>Data</h2><p>Here is the data that you uploaded. "
    . ($counts['found'] > 0 ? "$counts[found] contact(s) were automatically matched. " : "")
    . ($counts['chosen'] > 0 ? "$counts[chosen] ambiguous match(es) have been resolved by you. " : "")
    . ($counts['multiple'] > 0 ? "$counts[multiple] contact(s) could not be automatically matched because the data
   is ambiguous, e.g. two contacts with same email or name. With these you should choose from the possibilities below. " : "")
    . ($counts['impossible'] > 0 ? "<p>There are $counts[impossible] contacts below for which no contact record could be found. You can <a href='/civicrm/csvmatch/create' >create contact records for them now</a> if you like. You won't be able to import contributions (activities etc.) until these contacts do exist.</p>" : "")
    . ($counts['impossible'] == 0 && $counts['multiple'] == 0 ? "<p><strong>All the rows have a contact match so this dataset looks ready for you to download now.</strong></p>" : "")
    . '</div>';
  }

  /**
   * Takes a whole CSV file and outputs it as an array of rows.
   */
  public static function parseCsvString($string) {
    // Ensure line endings are \n
    $string = str_replace("\r", "\n", str_replace("\r\n", "\n", $string));

    $i = 0;
    $line = '';
    $len = strlen($string);
    $parsed = [];

    while ($i < $len) {
      $next_delim = strpos($string, '"', $i);

      $next_newline = strpos($string, "\n", $i);
      if ($next_newline === FALSE) {
        // EOF.
        $next_newline = strlen($string);
      }

      if ($next_delim === FALSE
        || ($next_delim > $next_newline)) {
        // There is no next delim; or it is on the next record.
        // Therefore the rest of the characters up to
        // the new line (or EOF) are part of this line.
        $line .= substr($string, $i, $next_newline - $i + 1);
        $i = $next_newline;
      }
      else {
        // Got a deliminator.
        // Add everything up to this point.
        // Scan ahead for closing deliminator that is not also followd by "
        $closing_delim = $next_delim;
        do {
          $closing_delim = strpos($string, '"', $closing_delim+1);
          $not_a_delim = ($closing_delim < $len && substr($string, $closing_delim+1, 1) == '"');
          if ($not_a_delim) {
            $closing_delim++;
          }
        } while ($not_a_delim);
        // Add to line.
        $line .= substr($string, $i, $closing_delim - $i + 1);
        $i = $closing_delim + 1;
      }

      if ($i == $len || substr($string, $i, 1) == "\n") {
        // EOL.

        // Detect empty lines.
        if ($line === "\n") {
          $parsed []= [];
        }
        else {
          $parsed []= str_getcsv($line);
        }
        $line = '';
        $i++;
      }
    }
    return $parsed;
  }
}
