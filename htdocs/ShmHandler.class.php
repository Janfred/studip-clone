<?php
/*
This file is part of StudIP -
ShmHandler.class.php
Simple Wrapper Klasse für PHP Shared Memory Funktionen
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

class ShmHandler {

      var $shmKey;         //Shared Memory Key
      var $shmSize;     //Shared Memory Size in Bytes
      var $shmid;                  //shared memory handle
      var $semid;                  //semaphore handle
      var $debug=0;
      
      function ShmHandler($key=98374,$size=131072) {
          $this->shmKey=$key;
          $this->shmSize=$size;
          if (!$this->shmid = shm_attach($this->shmKey, $this->shmSize, 0600))
               $this->halt("shm_attach fehlgeschlagen!");
          if (!$this->semid = sem_get($this->shmKey ,1))
               $this->halt("sem_get fehlgeschlagen!");
      }

      function getLock() {
          if (!sem_acquire($this->semid))
               $this->halt("sem_acquire fehlgeschlagen!");
      }

      function releaseLock() {
          if (!sem_release($this->semid))
               $this->halt("sem_release fehlgeschlagen!");
      }
      
      function store(&$what,$key) {
          $this->getLock();
          if (!@shm_put_var($this->shmid, $key, $what))
               $this->halt("Fehler beim Schreiben von $key");
          $this->releaseLock();
          return true;
      }
      
      function restore(&$what,$key) {
          //$this->getLock();
          $what = @shm_get_var($this->shmid, $key);
          //$this->releaseLock();
          return true;
     }

     function dispose(){
          if (!shm_remove($this->shmKey))
               $this->halt("shm_remove fehlgeschlagen!");
          }
          
     function halt($msg){
          echo $msg."<br>";
          die;
     }

}
?>
