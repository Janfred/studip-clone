<?php

/*
 * DataFieldEntry.class.php - <short-description>
 *
 * Copyright (C) 2005 - Martin Gieseking  <mgieseki@uos.de>
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/functions.php';
require_once 'config/config.inc.php';
require_once 'lib/classes/DataFieldStructure.class.php';


class DataFieldEntry {
  var $value;
  var $structure;
  var $rangeID;


  function DataFieldEntry ($structure='') {
    $this->structure = $structure;
    $this->rangeID   = '';
    $this->value     = '';
  }

/*  function typeOfSecRangeID ($id) {
    if (!$id)
      return false;
    $db = new DB_Seminar;
    $db->query("SELECT count(*) AS c FROM Institute WHERE Institut_id='$id'");
    $db->next_record();
    if ($db->f('c') > 0)
      return 'inst';
    $db->query("SELECT count(*) AS c FROM statusgruppen WHERE statusgruppe_id='$id'");
    $db->next_record();
    if ($db->f('c') > 0)
      return 'statusgr';
    return false;
  }*/

  // @static
  function getDataFieldEntries ($rangeID, $objectClass='', $objectType='') {
    if (!$rangeID) // we necessarily need a range ID
      return false;

    if (is_array($rangeID)) {    // rangeID may be an array ("classic" rangeID and second rangeID used for user roles)
      $secRangeID = $rangeID[1];
      $rangeID = $rangeID[0];    // to keep compatible with following code
      if ('usersemdata' !== $objectClass && 'roleinstdata' !== $objectClass) {
        $objectClass = 'userinstrole';
      }
    }

    // TODO: diesen grottigen Code bereinigen (object_class = objectType???)
    $db = new DB_Seminar;
    if (!$objectClass)
      $objectClass = get_object_type($rangeID);
    if ($objectClass) {
      if (!$objectType) {
        switch ($objectClass) {
          case 'sem':
            $query = "SELECT status AS type FROM seminare WHERE seminar_id = '$rangeID'";
            break;

          case 'inst':
          case 'fak':
          case 'roleinstdata':
            $query = "SELECT type FROM Institute WHERE Institut_id = '$rangeID'";
            break;

          case 'user':
          case 'userinstrole':
          case 'usersemdata':
            $query = "SELECT perms FROM auth_user_md5 WHERE user_id = '$rangeID'";
            break;
        }
        $db->query($query);
        $db->next_record();
        $objectType = $db->f('type');
      }

      global $SEM_TYPE;
      $objectType = ($objectClass == 'sem') ? $SEM_TYPE[$objectType]['class'] : $objectType;
      if (strpos(' sem inst fak roleinstdata', $objectClass)) {
        if ($objectClass == 'roleinstdata') {
          $clause1 = "AND sec_range_id='$secRangeID'";
        }
        $clause2 = $objectType ? "object_class=$objectType OR object_class IS NULL" : "object_class IS NULL";
      } elseif ($objectClass == 'user') {
        $clause2 = "((object_class & ". DataFieldStructure::permMask($db->f("perms")).") OR object_class IS NULL)";
      } elseif (strpos(' userinstrole usersemdata', $objectClass)) {
        $clause1 = "AND sec_range_id='$secRangeID'";
        $clause2 = "((object_class & ". DataFieldStructure::permMask($db->f("perms")).") OR object_class IS NULL)";
      }

      if ($object_type == "fak")
        $object_type = "inst";

      if ($object_class == "fak")
        $object_class = "inst";

      $query  = "SELECT *, a.datafield_id AS id ";
      $query .= "FROM datafields a LEFT JOIN datafields_entries b ON (a.datafield_id=b.datafield_id AND range_id = '$rangeID' $clause1) ";
      $query .= "WHERE object_type ='$objectClass' AND ($clause2) ORDER BY object_class, priority";
      $db->query($query);

      $entries = array();
      while ($db->next_record()) {
        $data = array('datafield_id' => $db->f('id'), 'name' => $db->f('name'), 'type' => $db->f('type'),
            'typeparam' => $db->f('typeparam'), 'object_type' => $db->f('object_type'), 'object_class' => $db->f('object_class'),
            'edit_perms' => $db->f('edit_perms'), 'priority' => $db->f('priority'), 'view_perms' => $db->f('view_perms'));
        $struct = new DataFieldStructure($data);
        $entry = DataFieldEntry::createDataFieldEntry($struct, $rangeID, $db->f('content'));
        $entries[$db->f("id")] = $entry;
      }
    }
    return $entries;
  }


  // @static
  function getDataFieldEntriesBySecondRangeID ($secRangeID) {
    $db = new DB_Seminar;
    $query  = "SELECT *, a.datafield_id AS id ";
    $query .= "FROM datafields a JOIN datafields_entries b ON a.datafield_id=b.datafield_id ";
     $query .= "AND sec_range_id = '$secRangeID'";
    $db->query($query);
    while ($db->next_record()) {
      $data = array('datafield_id' => $db->f('id'), 'name' => $db->f('name'), 'type' => $db->f('type'),
          'typeparam' => $db->f('typeparam'), 'object_type' => $db->f('object_type'), 'object_class' => $db->f('object_class'),
          'edit_perms' => $db->f('edit_perms'), 'priority' => $db->f('priority'), 'view_perms' => $db->f('view_perms'));
      $struct = new DataFieldStructure($data);
      $entry = DataFieldEntry::createDataFieldEntry($struct, array($db->f('range_id'), $secRangeID), $db->f('content'));
      $entries[$db->f("id")] = $entry;
    }
    return $entries;
  }



  function store () {
    if (is_array($this->rangeID)) {
      $rangeID = "'" . $this->rangeID[0] . "'";
      $secRangeID = "'" . $this->rangeID[1] . "'";
    }
    else {
      $rangeID = "'" . $this->rangeID . "'";
      $secRangeID = "''";
    }

    $db = new DB_Seminar;
    $query = sprintf("SELECT mkdate FROM datafields_entries WHERE datafield_id='%s' AND range_id=$rangeID AND sec_range_id=$secRangeID", $this->structure->getID());
    $db->query($query);
    $db->next_record();
    $chdate = time();
    $mkdate = $db->f("mkdate") ? $db->f("mkdate") : $chdate;
    $query = sprintf("REPLACE INTO datafields_entries SET content='%s', datafield_id='%s', range_id=$rangeID, sec_range_id=$secRangeID, mkdate='$mkdate', chdate='$chdate'",
                $this->value, $this->structure->getID());
    $db->query($query);
    return $db->affected_rows() > 0;
  }


  // @static
  function removeAll ($rangeID) {
    if ($rangeID) {
      $db = new DB_Seminar;
      $query = "DELETE FROM datafields_entries WHERE range_id = '$rangeID'";
      $db->query($query);
      return $db->affected_rows() > 0;
    }
  }

  // @static
  function getSupportedTypes () {
    return array("bool", "textline", "textarea", "selectbox", "date", "time", "email", "phone");
  }

  // "statische" Methode: liefert neues Datenfeldobjekt zu gegebenem Typ
  // @static
  function createDataFieldEntry ($structure, $rangeID='', $value='') {
    if (!is_object($structure))
      return false;
    switch ($structure->getType()) {
      case 'bool'     : $entry = new DataFieldBoolEntry;      break;
      case 'textline' : $entry = new DataFieldTextlineEntry;  break;
      case 'textarea' : $entry = new DataFieldTextareaEntry;  break;
      case 'email'    : $entry = new DataFieldEmailEntry;    break;
      case 'phone'    : $entry = new DataFieldPhoneEntry;    break;
      case 'date'     : $entry = new DataFieldDateEntry;      break;
      case 'time'     : $entry = new DataFieldTimeEntry;      break;
      case 'selectbox': $entry = new DataFieldSelectboxEntry($structure); break;
      default         : return false;
    }
    $entry->structure   = $structure;
    $entry->rangeID     = $rangeID;
    if ($value || !$entry->value)
      $entry->value = $value;
    return $entry;
  }

  function getType () {
    $class = strtolower(get_class($this));
    return substr($class, 9, strpos($class, 'entry')-9);
  }

  function getDisplayValue ($entities = true) {
    if ($entities)
      return htmlentities($this->value, ENT_QUOTES);
    return $this->value;
  }

  function getValue ()           {return $this->value;}
  function getName ()            {return $this->structure->getName();}
  function getHTML ($name)       {return '';}
  function setValue ($v)         {$this->value = $v;}
  function setRangeID ($v)       {$this->rangeID = $v;}
  function setSecondRangeID ($v) {$this->rangeID = array(is_array($this->rangeID) ? $this->rangeID[0] : $this->rangeID, $v);}
  function isValid ()            {return true;}
  function numberOfHTMLFields () {return 1;}
}


class DataFieldBoolEntry extends DataFieldEntry {
  function getHTML ($name) {
    if (func_num_args() > 1)
      $valattr = 'value="' . func_get_arg(1) . '"';
    if ($this->value)
      $checked = 'checked';
    return "<input type=\"checkbox\" name=\"$name\" $valattr $checked>";
  }

  function getDisplayValue ($entities = true) {
    return $this->value ? _('Ja') : _('Nein');
  }
}


class DataFieldTextlineEntry extends DataFieldEntry {
  function getHTML ($name) {
    if ($this->value)
      $valattr = 'value="' . $this->getDisplayValue() . '"';
    return "<input name=\"$name\" $valattr>";
  }
}


class DataFieldTextareaEntry extends DataFieldEntry {
  function getHTML ($name) {
    return sprintf('<textarea name="%s" rows="6" cols="58">%s</textarea>', $name, $this->getDisplayValue());
  }
}


class DataFieldEmailEntry extends DataFieldEntry {
  function setValue ($value) {
    $this->value = trim($value);
  }

  function getHTML ($name) {
    return sprintf('<input name="%s" value="%s" size="30">', $name, $this->getDisplayValue());
  }

  function isValid () {
    if ($this->value)
      return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", strtolower($this->value));
    return true;
  }
}


class DataFieldSelectboxEntry extends DataFieldEntry {
  function DataFieldSelectboxEntry ($struct) {
    parent::DataFieldEntry($struct);
    $values = explode("\n", $this->structure->getTypeParam());
    $this->value = trim($values[0]);  // first selectbox entry is default
  }

  function getHTML ($name) {
    $values = explode("\n", $this->structure->getTypeParam());
    $ret = "<select name=\"$name\">";
    foreach ($values as $val) {
      $val = trim(htmlentities($val, ENT_QUOTES));
      $sel = $val == $this->getDisplayValue() ? 'selected' : '';
      $ret .= "<option value=\"$val\" $sel>$val</option>";
    }
    return $ret . "</select>";
  }
}


class DataFieldPhoneEntry extends DataFieldEntry {
  function numberOfHTMLFields () {return 3;}

  function setValue ($value) {
    if (is_array($value))
      $this->value = str_replace(' ', '', implode("\n", array_slice($value, 0, 3)));
  }

  function getDisplayValue ($entities = true) {
    list($country, $area, $phone) = explode("\n", $this->value);
    if ($country!='' || $area!='' || $phone!='')
    {
    if ($country)
      $country = "+$country";
    if ($area) {
      $area = "(0)$area";
      if ($phone)
        $area .= '/';
    }
    return "$country $area$phone";
    } else
    {
      return '';
    }
  }

  function getHTML ($name) {
    $parts = explode("\n", $this->value);
    for ($i=3-count($parts); $i > 0; $i--)
      array_unshift($parts, '');
    $size   = array(3, 6, 10);
    $title  = array(_('Landesvorwahl ohne f&uuml;hrende Nullen'), _('Ortsvorwahl ohne f&uuml;hrende Null'), _('Rufnummer'));
    $prefix = array('+', '(0)', '&nbsp;/&nbsp;');
    $ret = '';
    foreach ($parts as $i => $part) {
//      $part = preg_replace('/^0+(.*)$/', '\1', $part);
      $ret .= sprintf('%s<input name="%s" maxlength="%d" size="%d" value="%s" title="%s">',
              $prefix[$i], $name, $size[$i], $size[$i]-1, htmlentities($part, ENT_QUOTES), $title[$i]);
    }
    $ret .= '<font size="-1">';
    $ret .= '&nbsp;'._('z.B.:').' +<span style="{border-style:inset; border-width:2px;}">&nbsp;49&nbsp;</span>';
    $ret .= '&nbsp;(0)<span style="{border-style:inset; border-width:2px;}">&nbsp;541&nbsp;</span>';
    $ret .= '&nbsp;/&nbsp;<span style="{border-style:inset; border-width:2px;}">&nbsp;969-0000&nbsp;</span>';
    $ret .= '</font>';
    return $ret;
  }

  function isValid () {
    if (trim($this->value) == '')
      return true;
    return preg_match('/^[1-9][0-9]*\n[1-9][0-9]+\n[1-9][0-9]+(-[0-9]+)?$/', $this->value);
  }
}


class DataFieldDateEntry extends DataFieldEntry {
  function numberOfHTMLFields () {return 3;}

  function setValue ($value) {
    if (is_array($value) && $value[0] != '' && $value[1] != '' && $value[2] != '') {
      $this->value = "$value[2]-$value[1]-$value[0]";
    }
  }

  function getDisplayValue ($entries = true) {
    if (preg_match('/(\d+)-(\d+)-(\d+)/', $this->value, $m))
      return "$m[3].$m[2].$m[1]";
    return '';
  }

  function getHTML ($name) {
    $parts = split('-', $this->value);
    $ret = sprintf('<input name="%s" maxlength="2" size="1" value="%s" title="Tag">', $name, $parts[2]);
    $ret .= ".&nbsp;";
    $months = array('', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'Novemember', 'Dezember');
    $ret .= "<select name=\"$name\" title=\"Monat\">";
    foreach ($months as $i=>$m)
      $ret .= sprintf('<option %s value="%s">%s</option>', ($parts[1] == $i ? 'selected' : ''), $i, $m);
    $ret .= "</select>&nbsp;";
    $ret .= sprintf('<input name="%s" maxlength="4" size="3" value="%s" title="Jahr">',  $name, $parts[0]);
    return $ret;
  }

  function isValid () {
    if (trim($this->value) == '')
      return true;
    $parts = split("-", $this->value);
    $valid = preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/', $this->value);
    return trim($this->value) != '' && $valid && checkdate($parts[1], $parts[2], $parts[0]);
  }
}


class DataFieldTimeEntry extends DataFieldEntry {
  function numberOfHTMLFields () {return 2;}

  function setValue ($value) {
    if (is_array($value)) {
      $this->value = "$value[0]:$value[1]";
    }
  }

  function getHTML ($name) {
    $parts = split(':', $this->value);
    $ret = sprintf('<input name="%s" maxlength="2" size="1" value="%s" title="Stunden">:', $name, $parts[0]);
    $ret .= sprintf('<input name="%s" maxlength="2" size="1" value="%s" title="Minuten">', $name, $parts[1]);
    return $ret;
  }

  function isValid () {
    $parts = split(':', $this->value);
    return $parts[0] >= 0 && $parts[0] <= 24 && $parts[1] >= 0 && $parts[1] <= 59;
  }
}

?>
