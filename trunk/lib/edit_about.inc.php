<?php
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// edit_about.inc.php
// administration of personal home page, helper functions
//
// Copyright (C) 2008 Till Glöggler <tgloeggl@uos.de>
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

require_once('lib/messaging.inc.php');
require_once('lib/evaluation/classes/db/EvaluationDB.class.php');

function edit_email($user, $email, $force=False) {
    $query = "SELECT email, username, auth_plugin
              FROM auth_user_md5
              WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($user->user_id));
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    $email_cur   = $row['email'];
    $username    = $row['username'];
    $auth_plugin = $row['auth_plugin'];

    if ($email_cur == $email && !$force) {
        return true;
    }

    if (StudipAuthAbstract::CheckField("auth_user_md5.Email", $auth_plugin) || LockRules::check($user->user_id, 'email')) {
        return false;
    }

    if (!$GLOBALS['ALLOW_CHANGE_EMAIL']) {
        return false;
    }

    $validator = new email_validation_class; ## Klasse zum Ueberpruefen der Eingaben
    $validator->timeout = 10;
    $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
    $Zeit = date("H:i:s, d.m.Y",time());

    // accept only registered domains if set
    $email_restriction = trim(get_config('EMAIL_DOMAIN_RESTRICTION'));
    if (!$validator->ValidateEmailAddress($email, $email_restriction)) {
        if ($email_restriction) {
            $email_restriction_msg_part = '';
            $email_restriction_parts = explode(',', $email_restriction);
            for ($email_restriction_count = 0; $email_restriction_count < count($email_restriction_parts); $email_restriction_count++) {
                if ($email_restriction_count == count($email_restriction_parts) - 1) {
                    $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . '<br>';
                } else if (($email_restriction_count + 1) % 3) {
                    $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ', ';
                } else {
                    $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ',<br>';
                }
            }
            PageLayout::postError(sprintf(_("Die E-Mail-Adresse fehlt, ist falsch geschrieben oder gehört nicht zu folgenden Domains:%s"),
                '<br>' . $email_restriction_msg_part));
        } else {
            PageLayout::postError(_("Die E-Mail-Adresse fehlt oder ist falsch geschrieben!"));
        }
        return false;
    }

    if (!$validator->ValidateEmailHost($email)) {     // Mailserver nicht erreichbar, ablehnen
        PageLayout::postError(_("Der Mailserver ist nicht erreichbar. Bitte überprüfen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken können!"));
        return false;
    } else {       // Server ereichbar
        if (!$validator->ValidateEmailBox($email)) {    // aber user unbekannt. Mail an abuse!
            StudipMail::sendAbuseMessage("edit_about", "Emailbox unbekannt\n\nUser: ". $username ."\nEmail: $email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
            PageLayout::postError(_("Die angegebene E-Mail-Adresse ist nicht erreichbar. Bitte überprüfen Sie Ihre Angaben!"));
            return false;
        }
    }

    $query = "SELECT Vorname, Nachname
              FROM auth_user_md5
              WHERE Email = ? AND user_id != ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($email, $user->user_id));
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        PageLayout::postError(sprintf(_("Die angegebene E-Mail-Adresse wird bereits von einem anderen Benutzer (%s %s) verwendet. Bitte geben Sie eine andere E-Mail-Adresse an."), htmlReady($row['Vorname']), htmlReady($row['Nachname'])));
        return false;
    }
    
    if (StudipAuthAbstract::CheckField("auth_user_md5.validation_key", $auth_plugin)) {
        PageLayout::postSuccess(_("Ihre E-Mail-Adresse wurde geändert!"));
    } else {
        // auth_plugin does not map validation_key (what if...?)

        // generate 10 char activation key
        $key = '';
        mt_srand((double)microtime()*1000000);
        for ($i=1;$i<= 10;$i++) {
            $temp = mt_rand() % 36;
            if ($temp < 10)
                $temp += 48;   // 0 = chr(48), 9 = chr(57)
            else
                $temp += 87;   // a = chr(97), z = chr(122)
            $key .= chr($temp);
        }
        $user->validation_key = $key;

        $activatation_url = $GLOBALS['ABSOLUTE_URI_STUDIP']
                            .'activate_email.php?uid='. $user->user_id
                            .'&key='. $user->validation_key;

        // include language-specific subject and mailbody with fallback to german
        $lang = $GLOBALS['_language_path']; // workaround
        if($lang == '') {
            $lang = 'de';
        }
        include_once("locale/$lang/LC_MAILS/change_self_mail.inc.php");

        $mail = StudipMail::sendMessage($email, $subject, $mailbody);

        if(!$mail) {
            return true;
        }

        $query = "UPDATE auth_user_md5 SET validation_key = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user->validation_key, $user->user_id));

        PageLayout::postInfo(sprintf(_('An Ihre neue E-Mail-Adresse <b>%s</b> wurde ein Aktivierungslink geschickt, dem Sie folgen müssen bevor Sie sich das nächste mal einloggen können.'), $email));
        StudipLog::log("USER_NEWPWD",$user->user_id);
    }
    return true;
}
