<?

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

function autocomplete_course_get_semesters() {
  $semdata = new SemesterData();
  $semesters = $semdata->getAllSemesterData();

  $result = array();
  foreach ($semesters as $semester) {
    $result[$semester['beginn']] = $semester['name'];
  }
  return $result;
}

function autocomplete_course_get_courses($search_term, $options) {

  /*$db = DBManager::get();

  # get semester starts
  $sem_start_times = array();
  foreach (SemesterData::GetSemesterArray() as $value) {
    if (isset($value['beginn']) && $value['beginn']) {
     $sem_start_times[] = $value['beginn'];
   }
  }
  $sem_start_times = join(',', $sem_start_times);
  $sem_number_sql     = "INTERVAL(start_time, {$sem_start_times})";
  $sem_number_end_sql = "IF(duration_time = -1, -1, INTERVAL(start_time + duration_time,{$sem_start_times}))";


  $semester_filter = isset($options['semester'])
    ? array(' HAVING (sem_number <= ? AND '.
            '(sem_number_end >= ? OR sem_number_end = -1))',
            array($options['semester'], $options['semester']))
    : array('', array());


  $category_filter = '1';
  if (isset($options['category'])) {
    $sem_types = array();
    foreach ($GLOBALS['SEM_TYPE'] as $key => $value) {
      if ($value['class'] == $options['category']) {
        $sem_types[] = $key;
      }
    }
    $category_filter = 'seminare.status IN('.join(',', $sem_types).')';
  }


  $result = array();

  # find lecturer
  if (!isset($options['what']) || $options['what'] === 'lecturer') {
    $stmt = $db->prepare(
      "SELECT seminar_user.seminar_id, ".
      $sem_number_sql . " AS sem_number, ".
      $sem_number_end_sql . " AS sem_number_end ".
      "FROM auth_user_md5 ".
      "INNER JOIN seminar_user ".
      "ON (auth_user_md5.user_id = seminar_user.user_id ".
      "AND seminar_user.status = 'dozent') ".
      "LEFT JOIN seminare USING (seminar_id) ".
      "WHERE seminare.visible = 1 AND ".
      "(auth_user_md5.username LIKE ? OR auth_user_md5.Vorname LIKE ? ".
      "OR auth_user_md5.Nachname LIKE ?) ".
      "AND " . $category_filter .
      $semester_filter[0]);
    $stmt->execute(array_merge(array_fill(0 , 3, "%{$search_term}%"),
                               $semester_filter[1]));
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
  }

  # find by title, name, comment
  $what_filter = array(array(), array());
  foreach (array('title'   => 'Name',
                 'number'  => 'VeranstaltungsNummer',
                 'comment' => 'Beschreibung') as $w => $f) {
    if (!isset($options['what']) || $options['what'] === $w) {
      $what_filter[0][] = $f.' LIKE ?';
      $what_filter[1][] = "%{$search_term}%";
    }
  }
  $what_filter[0] = join(' OR ', $what_filter[0]);

  $stmt = $db->prepare('SELECT seminar_id, ' .
                       $sem_number_sql . ' AS sem_number, ' .
                       $sem_number_end_sql . ' AS sem_number_end '.
                       'FROM seminare '.
                       'WHERE (' . $what_filter[0] . ') AND ' .
                       $category_filter . $semester_filter[0]);
  $stmt->execute(array_merge($what_filter[1], $semester_filter[1]));
  $result = array_unique(array_merge($result,
                                     $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));
*/

  require_once('lib/classes/StudipSemSearchHelper.class.php');
  $search_helper = new StudipSemSearchHelper();
  $search_helper->setParams(array('quick_search' => utf8_decode($search_term),
									'qs_choose' => $options['what'] ? $options['what'] : 'all',
									'sem' => isset($options['semester']) ? $options['semester'] : 'all',
									'category' => $options['category'],
									'scope_choose' => $options['scope'],
									'range_choose' => $options['range'])
							, !(is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm(Config::GetInstance()->getValue('SEM_VISIBILITY_PERM'))));
  $search_helper->doSearch();
  $result = $search_helper->getSearchResultAsArray();
  return empty($result)
    ? array() : autocomplete_course_get_courses_by_id($result);
}

function autocomplete_course_get_courses_by_id($ids) {
   $db = DBManager::get();

   return $db->query(
     'SELECT seminare.Name, '.
     'seminare.VeranstaltungsNummer, '.
     'seminare.Beschreibung, '.
     'seminare.start_time, '.
     'GROUP_CONCAT(CONCAT(auth_user_md5.Vorname, " ", auth_user_md5.Nachname) '.
     'ORDER BY auth_user_md5.Nachname SEPARATOR ", ") AS lecturer '.
     'FROM seminare '.
     'LEFT JOIN seminar_user '.
     'ON (seminare.seminar_id = seminar_user.seminar_id) '.
     'LEFT JOIN auth_user_md5 '.
     'ON (seminar_user.user_id = auth_user_md5.user_id) '.
     'WHERE seminare.seminar_id IN ("'.join('","', $ids).'") '.
     'AND seminar_user.status = "dozent" '.
     'GROUP BY seminare.seminar_id')
     ->fetchAll();
}

