<?
/*
user_visible.inc.php - Functions for determining a users visibility
Copyright (C) 2004 Till Glöggler <virtuos@snowysoft.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require_once 'functions.php';

/*
 * A function to determine a users visibility
 *
 * @param	$user_id	user-id
 * @returns boolean	true: user is visible, false: user is not visible
 */
function get_visibility_by_id ($user_id) {
	global $perm;
	if ($perm->have_perm("root")) return true;

	$db = new DB_Seminar("SELECT visible FROM auth_user_md5 WHERE user_id = '$user_id'");
	$db->next_record();
	return get_visibility_by_state($db->f("visible"));
}

/*
 * A function to determine a users visibility
 *
 * @param	$username	username
 * @returns boolean	true: user is visible, false: user is not visible
 */
function get_visibility_by_username ($username) {
	global $perm;
	if ($perm->have_perm("root")) return true;

	$db = new DB_Seminar("SELECT visible FROM auth_user_md5 WHERE username = '$username'");
	$db->next_record();
	return get_visibility_by_state($db->f("visible"));
}

/*
 * A function to determine, whether a given state means 'visible' or 'unvisible'
 *
 * @param	$stat	['always', 'yes', 'unknown', 'no', 'never']
 * @returns boolean	true: state means 'visible', false: state means 'unvisible'
 */
function get_visibility_by_state ($state) {
	switch ($state) {
		case "yes":
		case "always":
			return true;
			break;
		case "unknown":
			return (bool)get_config('USER_VISIBILITY_UNKNOWN');
			break;
		case "no":
		case "never":
			return false;
			break;

		default:
			return false;
			break;
	}
	return false;
}

/*
 * This function returns a query-snip for selecting with current visibility rights
 * @returns	string	returns a query string
 */
function get_vis_query($table_alias = 'auth_user_md5') {
	global $perm;
	if ($perm->have_perm("root")) return "1";
	return "($table_alias.visible = 'yes' OR $table_alias.visible = 'always' OR ($table_alias.visible = 'unknown' AND ".(int)get_config('USER_VISIBILITY_UNKNOWN')."))";
}

function get_ext_vis_query($table_alias = 'aum') {
	return "($table_alias.visible = 'yes' OR $table_alias.visible = 'always' OR ($table_alias.visible = 'unknown' AND ".(int)get_config('USER_VISIBILITY_UNKNOWN')."))";
}

/*
 * A function to create a chooser for a users visibility
 *
 * @param	$vis	visibility-state
 * @returns string	gives back a string with the chooser
 */
function vis_chooser($vis, $new = false) {
	if ($vis == '') $vis = 'unknown';
	$txt = array();
	$txt[] = '<SELECT name="visible">';
	if (!$new) $txt[] = '<OPTION value="'.$vis.'">'._("keine &Auml;nderung").'</OPTION>';
	$txt[] = '<OPTION value="always">'._("immer").'</OPTION>';
	/* $txt[] = '<OPTION value="yes">'._("ja").'</OPTION>'; */
	$txt[] = '<OPTION value="unknown"'.(($new)? ' selected="selected"':'').'>'._("unbekannt").'</OPTION>';
	/* $txt[] = '<OPTION value="no">'._("nein").'</OPTION>'; */
	$txt[] = '<OPTION value="never">'._("niemals").'</OPTION>';
	$txt[] = '</SELECT>';
	return implode("\n", $txt);
}

function first_decision($userid) {
	global $PHP_SELF, $vis_cmd, $vis_state, $auth;

	if ($vis_cmd == "apply" && ($vis_state == "yes" || $vis_state == "no")) {
		$db = new DB_Seminar("UPDATE auth_user_md5 SET visible = '$vis_state' WHERE user_id = '$userid'");
		return;
	}

	$db = new DB_Seminar("SELECT auth_user_md5.visible, user_info.preferred_language as pl FROM auth_user_md5, user_info WHERE auth_user_md5.user_id = '$userid' AND auth_user_md5.user_id = user_info.user_id");
	$db->next_record();
	if ($db->f("visible") != "unknown") return;
	if ($db->f("pl") == "en_EN") $slang = true; else $slang = false;

	?>
	<table width="80%" align="center" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan="3" valign="top">
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/login.gif" border="0"><b>&nbsp;<?=($slang) ? "Please choose your visibility!" : "Bitte wählen Sie ihren Sichtbarkeitsstatus aus!"?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td class="blank" width="1%"></td>
		<td class="blank">
			<center>
			<?
			$db->query("SELECT preferred_language FROM user_info WHERE user_id = '".$auth->auth['uid']."'");
	    $db->next_record();
			$lang = $db->f("preferred_language");
			if ($lang[0] == "e" || $lang[1] == "n") {
				include("visibility_decision_en.php");
			} else {
				include("visibility_decision_de.php");
			}
			/*
			?>
				<form action="<?=$PHP_SELF?>" method="post">
					<select name="vis_state">
						<option value="nothing">- <?=($slang) ? "Please choose" : "Bitte ausw&auml;hlen"?> -</option>s
						<option value="yes">Sichtbar</option>
						<option value="no">Unsichtbar</option>
					</select><br/>
					<input type="submit" value="<?=($slang) ? "Apply change" : "Änderung übernehmen"?>">
					<input type="hidden" name="vis_cmd" value="apply">
				</form>
			<? */ ?>
			</center>
		</td>
	</tr>
	</table>
	<?
	page_close();
	die;
}
?>
