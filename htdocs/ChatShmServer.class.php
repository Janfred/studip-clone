<?php
/*
This file is part of StudIP -
ChatShmServer.class.php
Klassendefinition für die zentralen Funktionen des Chats
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
require ($ABSOLUTE_PATH_STUDIP . "chat_config.php");
require ($ABSOLUTE_PATH_STUDIP . "ShmHandler.class.php");

class ChatShmServer {

     var $shmCt; // Container Objekt
     var $chatUser=array();
     var $chatDetail=array();
     
     function ChatShmServer(){
          $this->shmCt=new ShmHandler($key=CHAT_SHM_KEY,$size=CHAT_SHM_SIZE*1024);
          $this->restore();
     }
     
     function restore(){
          $this->shmCt->restore(&$this->chatUser,CHAT_USER_KEY);
          if (!is_array($this->chatUser)) $this->chatUser=array();
          $this->shmCt->restore(&$this->chatDetail,CHAT_DETAIL_KEY);
          if (!is_array($this->chatDetail)) $this->chatDetail=array();
     }

     function addChat($rangeid,$chatname="StudIP Global Chat"){
          if ($this->isActiveChat($rangeid)) return false;
          $this->chatDetail[$rangeid]["name"]=$chatname;
          $this->chatDetail[$rangeid]["messages"]=array();
          $this->shmCt->store(&$this->chatDetail,CHAT_DETAIL_KEY);
          return true;
     }
     
     function removeChat($rangeid){
          $this->chatDetail[$rangeid]["name"]="";
          $this->chatDetail[$rangeid]["messages"]="";
          $this->shmCt->store(&$this->chatDetail,CHAT_DETAIL_KEY);
          return true;
     }
     
     function isActiveChat($rangeid){
          $this->restore();
          return $this->chatDetail[$rangeid]["name"];
     }
     
     function getActiveUsers($rangeid){
          $this->restore();
          $anzahl=0;
          foreach($this->chatUser as $userid => $detail){
                    if ($detail[$rangeid]["action"]) $anzahl++;
          }
          return $anzahl;
     }
     
     function getIdFromNick($rangeid,$nick){
          $this->restore();
          foreach($this->chatUser as $userid => $detail){
                    if ($detail[$rangeid]["nick"]==$nick) return $userid;
          }
          return "nobody";
     }

     function addUser($userid,$rangeid,$nick,$fullname,$chatperm){
          if ($this->isActiveUser($userid,$rangeid)) return false;
          $this->chatUser[$userid][$rangeid]["action"]=time();
          $this->chatUser[$userid][$rangeid]["nick"]=$nick;
          $this->chatUser[$userid][$rangeid]["fullname"]=$fullname;
          $this->chatUser[$userid][$rangeid]["perm"]=$chatperm;
          if (!$this->chatUser[$userid][$rangeid]["color"]) $this->chatUser[$userid][$rangeid]["color"]="black";
          $this->shmCt->store(&$this->chatUser,CHAT_USER_KEY);
          $this->addMsg("system",$rangeid,$fullname." (".$nick.") ".CHAT_ENTRY_MSG);
          return true;
     }
     
     function removeUser($userid,$rangeid){
          if (!$this->isActiveUser($userid,$rangeid)) return false;
          $this->removeCmdMsg($userid,$rangeid);
          $this->addMsg("system",$rangeid,$this->chatUser[$userid][$rangeid]["fullname"]." (".$this->chatUser[$userid][$rangeid]["nick"].") ".CHAT_EXIT_MSG);
          $this->chatUser[$userid][$rangeid]["action"]=0;
          $this->shmCt->store(&$this->chatUser,CHAT_USER_KEY);
          return true;
     }

     function isActiveUser($userid,$rangeid){
          $this->restore();
          return $this->chatUser[$userid][$rangeid]["action"];
     }

     function addMsg($userid,$rangeid,$msg){
          $this->restore();
          $anzahl=count($this->chatDetail[$rangeid]["messages"]);
          if ($anzahl > 20) {
               array_shift($this->chatDetail[$rangeid]["messages"]);
               $anzahl--;
          }
          $this->chatDetail[$rangeid]["messages"][$anzahl]=array($userid,$msg,$this->getMsTime());
          $this->shmCt->store(&$this->chatDetail,CHAT_DETAIL_KEY);
          if (substr($userid,0,6)!="system"){
               $this->chatUser[$userid][$rangeid]["action"]=time();
               $this->shmCt->store(&$this->chatUser,CHAT_USER_KEY);
          }
     }
     
     function getMsg($rangeid,$msStamp=0){
          $this->restore();
          if ($msStamp) {
          $anzahl=count($this->chatDetail[$rangeid]["messages"]);
               for ($i=0;$i<$anzahl;$i++){
                    if ($this->chatDetail[$rangeid]["messages"][$i][2]>$msStamp) break;}
          }
          else $i=0;
          if ($i==$anzahl AND $i!=0) return false;
          else return array_slice($this->chatDetail[$rangeid]["messages"],$i);
     }
     
     function removeCmdMsg($userid,$rangeid){
          $this->restore();
          $anzahl=count($this->chatDetail[$rangeid]["messages"]);
          for ($i=0;$i<$anzahl;$i++){
                    if ($this->chatDetail[$rangeid]["messages"][$i][0]==$userid){
                         if (substr($this->chatDetail[$rangeid]["messages"][$i][1],0,1)=="/") {
                              $this->chatDetail[$rangeid]["messages"][$i]=array("system:system"," ",0);
                         }
                    }
          }
          $this->shmCt->store(&$this->chatDetail,CHAT_DETAIL_KEY);
     }

     function logoutUser($userid){
          if($this->chatUser[$userid]){
               foreach($this->chatUser[$userid] as $chatid => $detail)
                    if ($this->removeUser($userid,$chatid))
                         $this->addMsg("system",$chatid,$detail["fullname"]." (".$detail["nick"].") hat sich aus StudIP ausgeloggt!");
          }
          return true;
     }
     
     function getMsTime(){
          $microtime = explode(' ', microtime());
          return $microtime[1].substr($microtime[0],1);
     }


}
     


?>
