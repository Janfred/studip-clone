<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// integrity.view.php
// Integrity checks for the Stud.IP database
// This file contains only SQL Queries
// Copyright (c) 2002 André Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+
// $Id$
// User
$_views["USER_USERINFO"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.user_id FROM user_info a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_SEMUSER"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id FROM seminar_user a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_INSTUSER"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id FROM user_inst a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_FAKUSER"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id FROM fakultaet_user a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_STUDUSER"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id FROM user_studiengang a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_ARCHIVUSER"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id FROM archiv_user a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_ADMISSIONUSER"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id FROM admission_seminar_user a LEFT JOIN auth_user_md5 b USING(user_id) WHERE ISNULL(b.user_id)");
$_views["USER_KATEGORIEN"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.range_id FROM kategorien a LEFT JOIN auth_user_md5 b ON(a.range_id=b.user_id) WHERE ISNULL(b.user_id)");
$_views["USER_MESSAGES"]= array("pk"=>"user_id_rec","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.user_id_rec FROM globalmessages a LEFT JOIN auth_user_md5 b ON(a.user_id_rec=b.username) WHERE ISNULL(b.username)");
$_views["USER_SESSION"]= array("pk"=>"sid","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.sid FROM active_sessions a LEFT JOIN auth_user_md5 b ON(a.sid=b.user_id) WHERE ISNULL(b.user_id) AND a.sid NOT LIKE 'nobody' AND name='Seminar_User'");

//Seminar
$_views["SEM_SEMUSER"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Seminar_id FROM seminar_user a LEFT JOIN seminare b USING(Seminar_id) WHERE ISNULL(b.Seminar_id)");
$_views["SEM_ADMISSIONSTUD"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Seminar_id FROM admission_seminar_studiengang a LEFT JOIN seminare b USING(Seminar_id) WHERE ISNULL(b.Seminar_id)");
$_views["SEM_ADMISSIONUSER"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Seminar_id FROM admission_seminar_user a LEFT JOIN seminare b USING(Seminar_id) WHERE ISNULL(b.Seminar_id)");
$_views["SEM_BEREICH"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Seminar_id FROM seminar_bereich a LEFT JOIN seminare b USING(Seminar_id) WHERE ISNULL(b.Seminar_id)");
$_views["SEM_SEMINST"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Seminar_id FROM seminar_inst a LEFT JOIN seminare b USING(Seminar_id) WHERE ISNULL(b.Seminar_id)");

//Institut
$_views["INST_USER"]= array("pk"=>"Institut_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Institut_id FROM user_inst a LEFT JOIN Institute b USING(Institut_id) WHERE ISNULL(b.Institut_id)");
$_views["INST_FACH"]= array("pk"=>"Institut_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Institut_id FROM fach_inst a LEFT JOIN Institute b USING(Institut_id) WHERE ISNULL(b.Institut_id)");
$_views["INST_SEM"]= array("pk"=>"Institut_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Institut_id FROM seminar_inst a LEFT JOIN Institute b USING(Institut_id) WHERE ISNULL(b.Institut_id)");

//Fakultät
$_views["FAK_USER"]= array("pk"=>"Fakultaets_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Fakultaets_id FROM fakultaet_user a LEFT JOIN Fakultaeten b USING(Fakultaets_id) WHERE ISNULL(b.Fakultaets_id)");
$_views["FAK_INST"]= array("pk"=>"Fakultaets_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Fakultaets_id FROM Institute a LEFT JOIN Fakultaeten b USING(Fakultaets_id) WHERE ISNULL(b.Fakultaets_id)");

//Archiv
$_views["ARCHIV_USER"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.Seminar_id FROM archiv_user a LEFT JOIN archiv b USING(Seminar_id) WHERE ISNULL(b.Seminar_id)");

//Studiengang
$_views["STUD_ADMISSONSEM"]= array("pk"=>"studiengang_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.studiengang_id FROM admission_seminar_studiengang a LEFT JOIN studiengaenge b USING(studiengang_id) WHERE ISNULL(b.studiengang_id) AND a.studiengang_id NOT LIKE 'all'");
$_views["STUD_ADMISSONUSER"]= array("pk"=>"studiengang_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.studiengang_id FROM admission_seminar_user a LEFT JOIN studiengaenge b USING(studiengang_id) WHERE ISNULL(b.studiengang_id) AND a.studiengang_id NOT LIKE 'all'");
$_views["STUD_USER"]= array("pk"=>"studiengang_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.studiengang_id FROM user_studiengang a LEFT JOIN studiengaenge b USING(studiengang_id) WHERE ISNULL(b.studiengang_id)");

//Bereich
$_views["BEREICH_FACH"]= array("pk"=>"bereich_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.bereich_id FROM bereich_fach a LEFT JOIN bereiche b USING(bereich_id) WHERE ISNULL(b.bereich_id)");
$_views["BEREICH_SEM"]= array("pk"=>"bereich_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.bereich_id FROM seminar_bereich a LEFT JOIN bereiche b USING(bereich_id) WHERE ISNULL(b.bereich_id)");

//Fach
$_views["FACH_BEREICH"]= array("pk"=>"fach_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.fach_id FROM bereich_fach a LEFT JOIN faecher b USING(fach_id) WHERE ISNULL(b.fach_id)");

$_views["FACH_INST"]= array("pk"=>"fach_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT   a.fach_id FROM fach_inst a LEFT JOIN faecher b USING(fach_id) WHERE ISNULL(b.fach_id)");


//UNION Termine

$_views["TERMINE_USER"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT range_id FROM termine  INNER JOIN  auth_user_md5  ON (range_id=user_id)");
$_views["TERMINE_SEM"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT range_id FROM termine  INNER JOIN  seminare  ON (range_id=Seminar_id)");


//$_views["DOCS_FAK"]= array("pk"=>"dokument_id","temp_table_type"=>"HEAP",
//							"query"=>"SELECT dokument_id FROM dokumente  INNER JOIN  Fakultaeten b ON (range_id=b.fakultaets_id)");
//$_views["DOCS_USER"]= array("pk"=>"dokument_id","temp_table_type"=>"HEAP",
//							"query"=>"SELECT dokument_id FROM dokumente  INNER JOIN  auth_user_md5 b ON (range_id=b.user_id)");
$_views["DOCS_SEM"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT a.Seminar_id from dokumente a INNER JOIN seminare USING(Seminar_id)");
$_views["DOCS_INST"]= array("pk"=>"Seminar_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT a.Seminar_id from dokumente a INNER JOIN Institute b ON(a.Seminar_id=b.Institut_id)");

$_views["FOLDER_SEM"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT a.range_id from folder a INNER JOIN seminare b ON(b.Seminar_id=a.range_id)");
$_views["FOLDER_INST"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT a.range_id from folder a INNER JOIN Institute b ON(b.Institut_id=a.range_id)");
$_views["FOLDER_TERM"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT a.range_id from folder a INNER JOIN termine b ON(b.termin_id=a.range_id)");
$_views["FOLDER_FOLD"]= array("pk"=>"range_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT DISTINCT a.range_id from folder a INNER JOIN folder b ON(b.folder_id=a.range_id)");
?>

