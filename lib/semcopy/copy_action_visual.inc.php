<?php
/*
* copy_action_visual.inc.php
* used by /public/copy_assi.php
* part of alternative copy mechanism (ACM)
* written by Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

	if ($auth->auth['perm'] == 'dozent' || $auth->auth['perm'] == 'admin' || $auth->auth['perm'] == 'root')
	{
		require_once('lib/semcopy/DataCopyTool.class.php');

		$user_id = $auth->auth['uid'];

		$source_seminar_id = $SessSemName[1];

		$copyTool = new DataCopyTool($source_seminar_id,null,$user_id);

		$newSemData["Name"]=$newSemName;

		$target_seminar_id = $copyTool->copySeminar( $newSemData );

		if ( $copy_scm == "true" ) // simple to copy needs no tree structure, so we do it diectly
		{
			$copyTool->copyScm();
		}

		if ( $copy_wiki == "true" ) // simple to copy needs no tree structure, so we do it diectly
		{
			$copyTool->copyWiki();
		}

		if ( $want_download_folder  || $want_schedules || $want_status_group || $want_discussion || $want_issue_folder_as_download_folder )
		{
			if 	( $want_download_folder )
			{
				$wantFlags["want_download_folder"] = $want_download_folder=="true";
			}

			if 	( $want_discussion )
			{
				$wantFlags["want_discussion"] = $want_discussion=="true";
			}
			
			if ( $want_issue_folder_as_download_folder )
			{
				$wantFlags["want_issue_folder_as_download_folder"] = $want_issue_folder_as_download_folder=="true";
			}
			
			if 	( $want_schedules )
			{
				$wantFlags["want_schedules"] = $want_schedules=="true";

				if 	( $want_folder_issue )
				{
					$wantFlags["want_folder_issue"] = $want_folder_issue=="true";
				}
				if 	( $want_discussion_issue )
				{
					$wantFlags["want_discussion_issue"] = $want_discussion_issue=="true";
				}
			}
			if 	( $want_status_group )
			{
				$wantFlags["want_status_group"] = $want_status_group=="true";

				if 	( $want_status_group_folder )
				{
					$wantFlags["want_status_group_folder"] = $want_status_group_folder=="true";
				}
			}
			$copyTool->copyContentData($wantFlags);
		}
		
		echo "<br><br><a href='seminar_main.php?auswahl=$target_seminar_id'>"._("Zur neuen Veranstaltung")."</a>";

	}
	else
	{
		echo _("Sie haben nicht die Berechtigung diese Veranstaltung zu kopieren");
	}

?>

