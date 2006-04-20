<?php
/*
help/switcher.inc - Ermitteln des Kontext fr die Hilfe in Stud.IP
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

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

switch($referrer_page) {
	case "freie.php":
		$help_page = "freie.htm";
		break;
	case "auswahl_suche.php":
		$help_page = "xii_suchen1.htm";
		break;
	
	case "score.php" :
		if ($perm->have_perm("user"))
		    $help_page = "score.htm";
		break;
		
	case "index.php" :
		if ($perm->have_perm("user"))
		    $help_page = "startseite.htm";
		    if ($perm->have_perm("dozent"))
		    $help_page = "startdozenten.htm";
		break;
	
	case "admin_metadates.php" :
		if ($perm->have_perm("tutor"))
		    $help_page = "x_metadates.htm";
		break;

	case "admin_modules.php" :
		if ($perm->have_perm("tutor"))
		    $help_page = "x_admin_modules.htm";
		break;

	case "admin_admission.php" :
		if ($perm->have_perm("tutor"))
		    $help_page = "x_admission.htm";
		break;
		
	case "register1.php" :
	case "register2.php" :
		$help_page = "ii_anmeldeformular.htm";
		break;
	
	case "forumsend.php": 
		$help_page = "iii_homepagef2.htm";
		break;
	
	case "chatlogin.php": 
		$help_page = "iv_chat.htm";
		break;
		
	case "sms_box.php" :
	case "sms_snd.php" :
		if ($change_view == TRUE)
			$help_page = "iii_homepagef5.htm";
		else
			$help_page = "iv_sms.htm";
		break;
	
	case "email_validation.php" :
		$help_page = "ii_bestaetigungsmail.htm";
		break;
	
	case "admin_evaluation.php" :
                $help_page = "iii_homeeval.htm";
                break;

        case "eval_summary.php" :
                $help_page = "iii_homeeval.htm";
                break;

        case "eval_config.php" :
                $help_page = "iii_homeeval.htm";
                break;

	case "about.php" :
		$help_page = "iii_homepage.htm";
		break;
	
	case "suchen.php":
		$help_page = "auswahl_suche.html";
		break;
		
		case "institut_browse.php":
		$help_page = "xii_suche_einr.htm";
		break;
		
	case "archiv.php" :
		$help_page = "xii_suchen3.htm";
		break;
		
	case "mein_stundenplan.php" :
		if ($change_view == TRUE)
			$help_page = "iii_homepagef4.htm";
		else
			$help_page = "stupla.htm";
		break;
		
	case "admin_metadates.php" :
		$help_page = "x_aendern.htm";
		break;
		
	case "edit_about.php" :
		switch($view) {	
			case "Bild":
				$help_page = "iii_homepagea.htm";
				break;
			case "Daten":
				$help_page = "iii_homepageb.htm";
				break;
			case "Karriere":
				$help_page = "iii_homepagec.htm";
				break;			
			case "Lebenslauf":
				$help_page = "iii_homepaged.htm";
				break;			
			case "Sonstiges":
				$help_page = "iii_homepagee.htm";
				break;			
			case "Login":
				$help_page = "iii_homepageh.htm";
				break;		
				case "allgemein":
				$help_page = "iii_homepagef1.htm";
				break;			
			case "Forum":
				$help_page = "iii_homepagef2.htm";
				break;			
			case "Terminkalender":
				$help_page = "iii_homepagef3.htm";
				break;			
			case "Tools":
				$help_page = "iii_homepageg.htm";
				break;			
					
			case "Stundenplan":
				$help_page = "iii_homepagef4.htm";
				break;			
			case "Messaging":
				$help_page = "iii_homepagef5.htm";
				break;			
				}
		break;
		
	case "forum.php" :
	$help_page = "ix_forum1.htm";
		switch($view) {	
			case "neue":
				$help_page = "ix_forum2.htm";
				break;
			case "letzte":
				$help_page = "ix_forum3.htm";
				break;
			case "neuesthema":
				$help_page = "ix_forum5.htm";
				break;			
				}
		break;

	case "calendar.php" :
		switch($cmd) {	
			case "edit":
				$help_page = "termin2.htm";
				break;
			case "bind":
				$help_page = "termin3.htm";
				break;
			case "changeview":
				$help_page = "iii_homepagef3.htm";
				break;
			default:
				$help_page = "termin1.htm";
				break;
				}
		break;
		
	case "about.php" :
		$help_page = "iv_interaktion.htm";
		break;
		
	case "institut_main.php" :
		$help_page = "institut_main.htm";
		break;

	case "browse.php" :
		$help_page = "personensuche.htm";
		break;
	
	case "online.php" :
		$help_page = "iv_online.htm";
		break;
		
		case "admin_seminare_assi.php" :
		switch ($sem_create_data["level"]) {
			case "1":
				$help_page = "va_assi1.htm";
			break;
			case "2":
				$help_page = "va_assi2.htm";
			break;
			case "3":
				if  (!$sem_create_data["term_art"])
					$help_page = "va_assi3.htm";
				else
					$help_page = "va_assi3b.htm";
			break;
			case "4":
				$help_page = "va_assi4.htm";
			break;
			case "5":
				$help_page = "va_assi5.htm";
			break;
			case "6":
				$help_page = "va_assi5b";
			break;
			case "7":
				$help_page = "va_assi6.htm";
			break;
			default:
				$help_page = "va_assi1.htm";
			break;
			}
		break;
		
	case "admin_news.php" :
	if ($perm->have_perm("tutor"))
		$help_page = "iii_homepageg.htm";
		break;
	
	case "logout.php" :
		$help_page = "logout.htm";
		break;
	
	
	case "view_global_msg.php" :
		$help_page = "iv_sms.htm";
		break;
	
	case "sem_portal.php" :
		$help_page = "v_abonnieren.htm";
		break;
	
	case "meine_seminare.php" :
		$help_page = "v_neu.htm";
		break;

	case "gruppe.php" :
		$help_page = "v_ordnen.htm";
		break;
	
	case "seminar_main.php" :
		$help_page = "vi_kurz.htm";
		break;
	
	case "details.php" :
		$help_page = "vi_detail.htm";
		break;
	
	case "teilnehmer.php" :
		if ($perm->have_perm("tutor"))
		    $help_page = "x_teil.htm";
		else
		    $help_page = "vi_teilnehmer.htm";
		break;
	
	case "dates.php" :
		$help_page = "vi_ablauf.htm";
		break;
	
	case "literatur.php" :
		$help_page = "vi_literatur.htm";
		break;
	
	case "admin_seminare1.php" :
	if ($perm->have_perm("tutor"))
		$help_page = "x_aendern.htm";
		break;
	
	case "admin_literatur.php" :
	if ($perm->have_perm("tutor"))
		$help_page = "x_literatur.htm";
		break;
		
	case "adminarea_start.php" :
		if ($perm->have_perm("tutor"))
		$help_page = "x_adminarea.htm";
		break;
	
	case "admin_lit_list.php": 
	case "admin_lit_element.php": 
	case "lit_search.php": 
		$help_page = "iii_homelit.htm";
		break;

	case "admin_lit_element.php": 
		$help_page = "iii_homelit.htm";
		break;
		
	case "lit_search.php": 
		$help_page = "iii_homelit.htm";
		break;

	case "adminvote.php": 
		$help_page = "iii_homevote.htm";
		break;
	
	
	
	case "admin_dates.php" :
		if ($admin_dates_data["assi"]) 
			$help_page = "va_assi7.htm";
		else
			$help_page = "x_ablauf.htm";
		break;
		
	
	case "display_topic.php" :
		if ($perm->have_perm("tutor"))
		    $help_page = "x_themen.htm";
		break;
	
	case "folder.php" :
		$help_page = "vii_download.htm";
		break;
	
	case "datei.inc.php" :
		if ($doc == TRUE)
			$help_page = "upload_doc.htm";
		break;
	
	case "datei_upload.php" :
		$help_page = "vii_upload.htm";
		break;

	case "statusgruppen.php" :
		$help_page = "vi_statusgruppen_show.htm";
		break;

	case "admin_statusgruppe.php" :
		$help_page = "x_statusgruppen_admin.htm";
		break;

	case "resources.php" :
		switch ($view) {
			default:
				$help_page = "resources_intro.htm";
			break;
		}
		break;

	case "export.php" :
		$help_page = "export_intro.htm";
		break;

	case "seminar_lernmodule.php" :
	case "migration2studip.php":
		$help_page = "what_is_ilias.php";
	break;

	case "wiki.php":
		$help_page = "wiki_all.htm";
		break;

	default :
		;
}

unset($referrer_page);

?>
