<?
/*
This file is part of StudIP -
chat_config.php
Konfigurationsdatei für den StudIP Chat
Copyright (c) 2002 André Noack <andre.noack@gmx.net>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
define("CHAT_SHM_KEY",98374);    //muss eindeutig sein!!!
define("CHAT_SHM_SIZE",512);     //in Kbyte
define("CHAT_USER_KEY",1);       //am besten nicht ändern
define("CHAT_DETAIL_KEY",2);     //dito
define("CHAT_ENTRY_MSG","hat den Chat betreten!");
define("CHAT_EXIT_MSG","hat den Chat verlassen!");
define("CHAT_IDLE_TIMEOUT",600);       //in Sekunden
define("CHAT_SLEEP_TIME",200000);       //in usleep(micro s)
define("CHAT_TO_PREV_TIME",3.5);       //in Sekunden

$chatColors=array("black","blue","green","orange","indigo","darkred","red","darkblue","maroon","pink");

$chatCmd=array("quit" => " [msg] - Du verlässt den Chat mit der Botschaft [msg]",
               "color" => " [colorcode] - Deine Schriftfarbe wird auf [colorcode] gesetzt",
               "me" => " [msg] - Dein Name wird zusammen mit [msg] vom Chatbot ausgegeben",
               "private" => " [username][msg] - Die Botschaft [msg] wird geheim an [username] übermittelt",
               "help" => " - Zeigt diesen Hilfetext",
               "kick" => " [username] - Wirft [username] aus dem Chat wenn du Chatadmin bist",
               "sms" => " [username][msg] - Verschickt eine Studip SMS [msg] an [username]");

?>
