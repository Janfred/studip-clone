<?
# Lifter002: TODO
/**
* log_events.inc.php
*
* Functions to create log events
*
* @author               Tobias Thelen <tthelen@uni-osnabrueck.de>
* @version              $Id$
* @access               public
* @module               log_events.inc.php
* @modulegroup		library
* @package              studip_core
*/

function get_log_action_id($db, $action) {
	$db->query("SELECT action_id, active FROM log_actions WHERE name='$action'");
	if ($db->next_record()) {
		if (!$db->f("active")) return -1; // inactive
		return $db->f("action_id");
	} elseif ($action=="LOG_ERROR") { // prevent from inf. looping if LOG_ERROR is unknown
		return 99999;
	}
	return 0;
}

function log_event($action, $affected=NULL, $coaffected=NULL, $info=NULL, $dbg_info=NULL, $user=NULL) {
	global $auth, $LOG_ENABLE;
	//print "logging... $action $affected $coaffected $info $dbg_info $user <p>";
	if (!$LOG_ENABLE) return; // don't log if logging is disabled
	$db=new DB_Seminar;
	$action_id=get_log_action_id($db,$action);
	if ($action_id==-1) return; // inactive action
	$timestamp=time();
	if (!$user) { // automagically set current user as agent
		$user=$auth->auth['uid'];
	}
	if (!$action_id) { // Action doesn't exist -> LOG_ERROR
		log_event("LOG_ERROR",NULL,NULL,NULL,"log_event($action,$affected,$coaffected,$info,$dbg_info) for user $user");
		return;
	}
	$eventid=md5(uniqid("Ay!Captain!",1));
	$q="INSERT INTO log_events SET event_id='$eventid', action_id='$action_id', user_id='$user', affected_range_id='$affected', coaffected_range_id='$coaffected', info='".addslashes($info)."', dbg_info='".addslashes($dbg_info)."', mkdate='$timestamp'";
	$db->query($q);
	return;
}

function cleanup_log_events() {
	global $auth, $LOG_ENABLE;
	if (!$LOG_ENABLE) return; // do nothing if logging is disabled
	$db=new DB_Seminar;
	$q2="DELETE log_events.* FROM log_events LEFT JOIN log_actions ON (log_events.action_id=log_actions.action_id) WHERE expires IS NOT NULL AND (log_events.mkdate + log_actions.expires < UNIX_TIMESTAMP())";
	/*
	// Debug: Zu löschende Events / Query ausgeben
	echo "<p>$q2";
	$db->query($q);
	$q="SELECT log_actions.name as action, mkdate, event_id, UNIX_TIMESTAMP() as now FROM log_events LEFT JOIN log_actions ON (log_events.action_id=log_actions.action_id) WHERE expires IS NOT NULL AND ((log_events.mkdate + log_actions.expires) < UNIX_TIMESTAMP())";
	echo "<p>".$db->nf()." eintraege werden gelöscht.";
	while ($db->next_record()) {
		echo "<p>now=".$db->f("now")." - ".$db->f("mkdate")." - ".$db->f("action")." - ".date("H:i:s d.m.Y",$db->f("mkdate"));
	}
	*/
	$db->query($q2);
	return $db->nf();
}

?>
