<?php
/**
* HeaderController.class.php
*
*
*
*
* @author	André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id: $
* @access	public
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2007 André Noack <noack@data-quest>,
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
require_once 'lib/visual.inc.php';
require_once 'lib/functions.php';
require_once 'lib/sms_functions.inc.php';
require_once 'vendor/flexi/flexi.php';

class HeaderController {

	var $help_keyword = 'Basis.Allgemeines';
	var $current_page = '';
	var $items = array();
	var $accesskey_enabled = false;
	
	function HeaderController(){
		global $user;
		if (is_object($user) && $user->cfg->getValue(null,"ACCESSKEY_ENABLE")){
			$this->accesskey_enabled = true;
		}
		foreach(array_map('strtolower', get_class_methods($this)) as $method){
			list( , $item) = explode('getheaderitem', $method);
			if($item) $this->items[] = $item;
		}
		
	}

	function fillTemplate(&$template){
		$template->set_attribute('current_page', $this->current_page);
		foreach($this->items as $item){
			$template->set_attribute($item, $this->getHeaderItem($item));
		}
	}
	
	function getListOfItems($list = array()){
		$ret = array();
		foreach($list as $list_item){
			$header_item = $this->getHeaderItem($list_item);
			if(!is_null($header_item)){
				if(isset($header_item[0])) $ret = array_merge($ret, $header_item);
				else $ret[] = $header_item;
			}
		}
		return $ret;
	}
	
	function getHeaderItem($item){
		$method = "getHeaderItem" . $item;
		if (method_exists($this, $method)){
			return $this->$method();
		} else {
			return null;
		}
	}
	
	function getHeaderItemChat(){
		global $user;
		if ($GLOBALS['CHAT_ENABLE'] && is_object($user) && $user->id != 'nobody'){
			require_once $GLOBALS['RELATIVE_PATH_CHAT'] . "/chat_func_inc.php";
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			$chatServer->caching = true;
			$sms = new messaging();
			$sms->delete_chatinv();
			$chatter = $chatServer->getAllChatUsers();
			$active_chats = count($chatServer->chatDetail);
			if (!$chatter){
				$chat_tip = _("Es ist niemand im Chat");
			} elseif ($chatter == 1 && $chatServer->chatUser[$user->id]){
				$chat_tip =_("Nur Sie sind im Chat");
			} elseif ($chatter == 1){
				$chat_tip =_("Es ist eine Person im Chat");
			} else {
				$chat_tip = sprintf(_("Es sind %s Personen im Chat"), $chatter);
			}
			if ($active_chats == 1){
				$chat_tip .= ", " . _("ein aktiver Chatraum");
			} elseif ($active_chats > 1){
				$chat_tip .= ", " . sprintf(_("%s aktive Chaträume"), $active_chats);
			}
			$db = new DB_Seminar("SELECT STRAIGHT_JOIN count(*) FROM message LEFT JOIN message_user USING (message_id) WHERE message_user.user_id = '{$user->id}' AND snd_rec = 'rec' AND chat_id IS NOT NULL");
			$db->next_record();
			$chatm = $db->f(0);
			if ($chatm){
				$chatimage = "chateinladung";
				$chat_tip = (($chatm == 1) ? _("Sie haben eine Chateinladung") : sprintf(_("Sie haben %s Chateinladungen"),$chatm)) . ", " . $chat_tip;
			} elseif ($chatter == 1 && $chatServer->chatUser[$user->id]){
				$chatimage = "chat3";
			} elseif ($chatter) {
				$chatimage = "chat2";
			} else {
				$chatimage = "chat1";
			}
			$ret['text'] = _("Chat");
			$ret['link'] = "chat_online.php";
			$ret['info'] = $chat_tip;
			$ret['image'] = $chatimage;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemOnline(){
		global $my_messaging_settings, $user;
		if(is_object($user) && $user->id != 'nobody'){
			$active_time = ($my_messaging_settings["active_time"] ? $my_messaging_settings["active_time"] : 5);
			$user_count = get_users_online_count($active_time);
			if (!$user_count) {
				$onlineimage = "nutzer";
				$onlinetip = _("Nur Sie sind online");
			} else {
				$onlineimage = "nutzeronline";
				if ($user_count == 1) {
					$onlinetip = _("Außer Ihnen ist eine Person online");
				} else {
					$onlinetip = sprintf(_("Es sind außer Ihnen %s Personen online"), $user_count);
				}
			}
			$ret['text'] = _("Online");
			$ret['link'] = "online.php";
			$ret['info'] = $onlinetip;
			$ret['image'] = $onlineimage;
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemHome(){
		global $user;
		if(is_object($user) && $user->id != 'nobody'){
			$db = new DB_Seminar();
			//globale Objekte zählen
			$db->query("SELECT  COUNT(nw.news_id) as count,
						COUNT(IF((chdate > IFNULL(b.visitdate,0) AND nw.user_id !='{$user->id}'),
						nw.news_id, NULL)) AS neue
						FROM   news_range a  LEFT JOIN news nw ON(a.news_id=nw.news_id AND UNIX_TIMESTAMP() BETWEEN date AND (date+expire))
						LEFT JOIN object_user_visits b ON (b.object_id = nw.news_id AND b.user_id = '{$user->id}' AND b.type ='news')
						WHERE a.range_id='studip' GROUP BY a.range_id");
			if ($db->next_record()){
				$global_obj['news']['neue'] = $db->f('neue');
				$global_obj['news']['gesamt'] = $db->f('count');
			}
			if ($GLOBALS['VOTE_ENABLE']) {
				$db->query("SELECT  COUNT(vote_id) as count,
							COUNT(IF((chdate > IFNULL(b.visitdate,0) AND a.author_id !='{$user->id}' AND a.state != 'stopvis'), vote_id, NULL)) AS neue
							FROM  vote a LEFT JOIN object_user_visits b ON (b.object_id = vote_id AND b.user_id = '{$user->id}' AND b.type='vote')
							WHERE a.range_id='studip'  AND a.state IN('active','stopvis')
							GROUP BY a.range_id");
				if ($db->next_record()){
					$global_obj['vote']['neue'] = $db->f('neue');
					$global_obj['vote']['gesamt'] = $db->f('count');
				}
			$db->query("SELECT  COUNT(a.eval_id) as count,
						COUNT(IF((chdate > IFNULL(b.visitdate,0) AND d.author_id !='{$user->id}' ), a.eval_id, NULL)) as neue
						FROM eval_range a INNER JOIN eval d ON ( a.eval_id = d.eval_id AND d.startdate < UNIX_TIMESTAMP( ) AND (d.stopdate > UNIX_TIMESTAMP( ) OR d.startdate + d.timespan > UNIX_TIMESTAMP( ) OR (d.stopdate IS NULL AND d.timespan IS NULL)))
						LEFT JOIN object_user_visits b ON (b.object_id = d.eval_id AND b.user_id = '{$user->id}' AND b.type='eval')
						WHERE a.range_id='studip' GROUP BY a.range_id");
				if ($db->next_record()){
					$global_obj['eval']['neue'] = $db->f('neue');
					$global_obj['eval']['gesamt'] = $db->f('count');
				}
			}
		}
		$homeimage = $global_obj['eval']['neue'] || $global_obj['vote']['neue'] || $global_obj['news']['neue'] ? "home_red" : "home";
		$homeinfo = _("Zur Startseite");
		$homeinfo .= $global_obj['news']['neue'] ? " - " . sprintf(_(" %s neue News"), $global_obj['news']['neue']) : "";
		$homeinfo .= ($global_obj['vote']['neue'] + $global_obj['eval']['neue']) ? " - " . sprintf(_(" %s neue Umfrage(n)"), ($global_obj['vote']['neue'] + $global_obj['eval']['neue'])) : "";

		$ret['text'] = _("Start");
		$ret['link'] = "index.php";
		$ret['info'] = $homeinfo;
		$ret['image'] = $homeimage;
		$ret['accesskey'] = $this->accesskey_enabled;
		return $ret;
	}
	
	function getHeaderItemCourses(){
		global $user, $perm;
		if(!is_object($user) || $user->id == 'nobody'){
			if(!$GLOBALS['ENABLE_FREE_ACCESS']) return null;
			$courseinfo = _("Freie Veranstaltungen");
			$coursetext = _("Freie");
			$courselink = "freie.php";
		} else {
			$courseinfo = _("Meine Veranstaltungen & Einrichtungen");
			$coursetext = _("Veranstaltungen");
			$courselink = $perm->have_perm("root") ? "sem_portal.php" : "meine_seminare.php";
		}
		$ret['text'] = $coursetext;
		$ret['link'] = $courselink;
		$ret['info'] = $courseinfo;
		$ret['image'] = "meinesem";
		$ret['accesskey'] = $this->accesskey_enabled;
		return $ret;
	}
	
	function getHeaderItemImprint(){
		$ret['text'] = _("Impressum");
		$ret['link'] = "impressum.php";
		return $ret;
	}
	
	function getHeaderItemSearch(){
		global $user;
		if(is_object($user) && $user->id != 'nobody'){
			$ret['text'] = _("Suche");
			$ret['link'] = "auswahl_suche.php";
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemHelp(){
		global $perm;
		if (get_config("EXTERNAL_HELP")) {
			$helppage = $this->help_keyword;
			// encode current user's global perms for help wiki
			$helppage .= "?setstudipview=" . (is_object($perm) ? $perm->get_perm() : '');
			// encode locationid for help wiki if set
			$locationid = get_config("EXTERNAL_HELP_LOCATIONID");
			if ($locationid) {
				$helppage .= "&setstudiplocationid=" . $locationid;
			}
			// insert into URL-Template from config
			$help_query = sprintf(get_config("EXTERNAL_HELP_URL"), $helppage);
			$ret['text'] = _("Hilfe");
			$ret['link'] = $help_query;
			$ret['target'] = '_blank';
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemLoginLogout(){
		global $user;
		if(is_object($user) && $user->id != 'nobody'){
			$ret['text'] = _("Logout");
			$ret['link'] = "logout.php";
			$ret['accesskey'] = $this->accesskey_enabled;
		} else {
			$ret['text'] = _("Login");
			$ret['link'] = "index.php?again=yes";
		}
		return $ret;
	}
	
	function getHeaderItemSSOLogin(){
		global $user;
		if((!is_object($user) || $user->id == 'nobody') && array_search("CAS", $GLOBALS["STUDIP_AUTH_PLUGIN"])){
			$ret['text'] = _("Login CAS");
			$ret['link'] = "index.php?again=yes&sso=cas";
		} else {
			$ret = null;
		}
		return $ret;
	}
	
	function getHeaderItemMessages(){
		global $user;
		if(is_object($user) && $user->id != 'nobody'){
			$neum = count_messages_from_user('in', " AND message_user.readed = 0 ");
			$altm = count_messages_from_user('in', " AND message_user.readed = 1 ");
			$neux = count_x_messages_from_user('in', 'all', "AND mkdate > ".(int)$my_messaging_settings["last_box_visit"]." AND message_user.readed = 0 ");
			if ($neum) {
				$icon = "nachricht2";
			} else if (!$neum) {
				$icon = "nachricht";
			}
			if ($neux >= "1") {
				$tip = sprintf(_("Sie haben %s neue ungelesene Nachrichten!"), $neum);
			} else if ($neux == "1") {
				$tip = _("Sie haben eine neue ungelesene Nachricht!");
			}
			if ($neum > "1" && !$neux) {
				$tip = sprintf(_("Sie haben %s ungelesene Nachrichten!"), $neum);
			} else if ($neum == "1" && !$neux) {
				$tip = _("Sie haben eine ungelesene Nachricht!");
			}
			if ($altm > "1" && !$neum) {
				$tip = sprintf(_("Sie haben %s alte empfangene Nachrichten."), $altm);
			} else if ($altm == "1" && !$neum) {
				$tip = _("Sie haben eine alte empfangene Nachricht.");
			} else if (!$neum) {
				$tip = _("Sie haben keine alten empfangenen Nachrichten.");
			}
			$ret['text'] = _("Post");
			$ret['link'] = "sms_box.php?sms_inout=in";
			$ret['info'] = $tip;
			$ret['image'] = $icon;
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemHomepage(){
		global $user, $perm, $homepage_cache_own, $LastLogin;
		if (is_object($user) && $perm->have_perm("autor")) {
			$db = new DB_Seminar();
			if ($homepage_cache_own) $time = $homepage_cache_own;
			else $time = $LastLogin;
			$picture = "einst";
			$hp_txt = _("Zu Ihrer Einstellungsseite");
			$hp_link = "about.php";
			$db->query("SELECT COUNT(post_id) AS count
						FROM guestbook
						WHERE range_id='".$user->id."'
						AND user_id!='".$user->id."'
						AND mkdate > '".$time."'");
			if ($db->next_record() && $db->f('count')) {
				if ($db->f("count") == 1) {
					$hp_txt .= sprintf(_(", Sie haben %s neuen Eintrag im Gästebuch."), $db->f("count"));
				} else {
					$hp_txt .= sprintf(_(", Sie haben %s neue Einträge im Gästebuch."), $db->f("count"));
				}
				$picture = "einst2";
				$hp_link .= "?guestbook=open#guest";
			}
			$ret['text'] = _("Homepage");
			$ret['link'] = $hp_link;
			$ret['info'] = $hp_txt;
			$ret['image'] = $picture;
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemPlanner(){
		global $perm, $user;
		if (is_object($user) && $user->id != 'nobody' && !$perm->have_perm("admin")) {
			if ($GLOBALS['CALENDAR_ENABLE']) {
				$planerlink = "calendar.php?caluserid=self";
				$planerinfo = _("Termine und Kontakte");
			  } else {
				$planerlink = "mein_stundenplan.php";
				$planerinfo = _("Stundenplan und Kontakte");
			  }
		  	$ret['text'] = _("Planer");
			$ret['link'] = $planerlink;
			$ret['info'] = $planerinfo;
			$ret['image'] = 'planer';
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemAdmin(){
		global $perm, $user;
		if (is_object($user) && $perm->have_perm("tutor")) {
			$ret['text'] = _("Admin");
			$ret['link'] = "adminarea_start.php?list=TRUE";
			$ret['info'] = _("Zu Ihrer Administrationsseite");
			$ret['image'] = 'admin';
			$ret['accesskey'] = $this->accesskey_enabled;
			return $ret;
		} else {
			return null;
		}
	}
	
	function getHeaderItemPlugins(){
		global $user;
		if ($GLOBALS["PLUGINS_ENABLE"] && is_object($user) && $user->id != 'nobody'){
			$pluginengine = PluginEngine::getPluginPersistence("System");
			foreach ($pluginengine->getAllActivatedPlugins() as $header_plugin){
				// does the plugin have a navigation entry?
				if ($header_plugin->hasNavigation()){
					$navi = $header_plugin->getNavigation();
					if ($navi->hasIcon()){
					 	$pluginicon = $header_plugin->getPluginpath() . "/" . $navi->getIcon();
					} else {
						$pluginicon = $header_plugin->getPluginiconname();
					}
					$pluginlink['text'] = $navi->getDisplayname();
					$pluginlink['link'] = PluginEngine::getLink($header_plugin, $navi->getLinkParams());
					$pluginlink['image'] = $pluginicon;
					$pluginlink['info'] = $navi->getDisplayname();
					$pluginlink['is_plugin'] = true;
					$ret[$header_plugin->getPluginid()] = $pluginlink;
				}
		 		// now ask for background tasks
		 		if ($header_plugin->hasBackgroundTasks()){
		 			// and run them
		 			$header_plugin->doBackgroundTasks();
		 		}
			}
			return $ret;
		} else {
			return $ret;
		}
	}
}
?>
