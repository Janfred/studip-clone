<?

/*
DbDay.class.php - 0.8.20020520
Klassen fuer Persoenlichen Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthienel@web.de>

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

//****************************************************************************

require_once "config.inc.php";
require_once "../Day.class.php";

class DB_Day extends Day{

        var $app;                 // Termine (Object[])
        var $app_del;       // Termine, die gelöscht werden (Object[])
        var $arr_pntr;            // "private" function getTermin
        var $user_id;       // User-ID aus PphLib (String)
        
        // Konstruktor
        function DB_Day($tmstamp){
                global $user;
                $this->user_id = $user->id;
                Day::Day($tmstamp);
                $this->restore();
                $this->sort();
                $this->arr_pntr = 0;
        }
        
        // Anzahl von Terminen innerhalb eines bestimmten Zeitabschnitts
        // default one day
        // public
        function numberOfApps($start = 0, $end = 86400){
                $i = 0;
                $count = 0;
                while($aterm = $this->app[$i]){
                        if($aterm->getStart() >= $this->getStart() + $start && $aterm->getStart() <= $this->getStart() + $end)
                                $count++;
                        $i++;
                }
                return $count - 1;
        }
        
        // public
        function numberOfSimultaneousApps($term){
                $i = 0;
                $count = 0;
                while($aterm = $this->app[$i]){
                        if($aterm->getStart() >= $term->getStart() && $aterm->getStart() < $term->getEnd())
                                $count++;
                        $i++;
                }
                return ($count);
        }
        
        // Termin hinzufügen
        // Der Termin wird gleich richtig einsortiert
        // public
        function addTermin($term){
                $this->app[] = $term;
                $this->sort();
        //        return TRUE;
        }
        
        // Termin löschen
        // public
        function delTermin($id){
                for($i = 0;$i < sizeof($this->app);$i++)
                        if($id != $this->app[$i]->getId())
                                $app_bck[] = $this->app[$i];
                        else
                                $this->app_del[] = $this->app[$i];
                                
                if(sizeof($app_bck) == sizeof($this->app))
                        return FALSE;
                
                $this->app = $app_bck;
                return TRUE;
        }
        
        // ersetzt vorhandenen Termin mit übergebenen Termin, wenn ID gleich
        // public
        function replaceTermin($term){
                for($i = 0;$i < sizeof($this->app);$i++)
                        if($this->app[$i]->getId() == $term->getId()){
                                $this->app[$i] = $term;
                                $this->sort();
                                return TRUE;
                        }
                return FALSE;
        }
        
        // Abrufen der Termine innerhalb eines best. Zeitraums
        // default 1 hour
        // public
        function nextTermin($start = -1, $step = 3600){
                if($start < 0)
                        $start = $this->start;
                while($this->arr_pntr < sizeof($this->app)){
                        if($this->app[$this->arr_pntr]->getStart() >= $start && $this->app[$this->arr_pntr]->getStart() < $start + $step)
                                return $this->app[$this->arr_pntr++];
                        $this->arr_pntr++;
                }
                $this->arr_pntr = 0;
                return FALSE;
        }
        
        // Termine in Datenbank speichern.
        // public
        function save(){
                // Je nachdem, ob die Beschreibung eines Termins verändert wurde oder nicht,
                // ist es erforderlich das description Feld in der DB zu überschreiben.
                // Es werden also zwei unterschiedliche REPLACEs benötigt.
                $db = new DB_Seminar();
                if($size = sizeof($this->app)){
                        $query1 = "REPLACE termine (termin_id,range_id,autor_id,content,description,"
                                . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES";
                        $query2 = "REPLACE termine (termin_id,range_id,autor_id,content,"
                                      . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES";
                        $sep1 = FALSE;
                        $sep2 = FALSE;
                        $chdate = time();
                        if($this->mkd == -1)
                                $mkdate = $chdate;
                        else
                                $mkdate = $this->mkd;
                        
                        for($i = 0;$i < $size;$i++){
                                if($this->app[$i]->type == -1 || $this->app[$i]->type == -2){
                                        if(is_string($this->app[$i]->desc)){
                                                if($sep1)
                                                        $values1 .= ",";
                                                $values1 .= sprintf("('%s','%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
                                                                                 , $this->app[$i]->id, $this->user_id, $this->user_id, $this->app[$i]->txt
                                                                                 , $this->app[$i]->desc, $this->app[$i]->start, $this->app[$i]->end, $mkdate
                                                                                 , $chdate, $this->app[$i]->type, $this->app[$i]->exp, $this->app[$i]->rep
                                                                                 , $this->app[$i]->cat, $this->app[$i]->prio, $this->app[$i]->loc);
                                                $sep1 = TRUE;
                                        }
                                        else if($this->app[$i]->chng_flag){
                                                if($sep2)
                                                        $values2 .= ",";
                                                $values2 .= sprintf("('%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
                                                                                 , $this->app[$i]->id, $this->user_id, $this->user_id, $this->app[$i]->txt
                                                                                 , $this->app[$i]->start, $this->app[$i]->end, $mkdate, $chdate, $this->app[$i]->type
                                                                                 , $this->app[$i]->exp, $this->app[$i]->rep, $this->app[$i]->cat, $this->app[$i]->prio
                                                                                 , $this->app[$i]->loc);
                                                $sep2 = TRUE;
                                        }
                                }
                        }
                        if($values1){
                                $query1 .= $values1;
                                $db->query($query1);
                        }
                        if($values2){
                                $query2 .= $values2;
                                $db->query($query2);
                        }
                }
                if($size = sizeof($this->app_del)){
                        $query = sprintf("DELETE FROM termine WHERE range_id = '%s' AND autor_id = '%s' AND termin_id IN ("
                                                                                        , $this->user_id, $this->user_id);
                        for($i = 0;$i < $size;$i++){
                                if($this->app[$i]->type == -1 || $this->app[$i]->type == -2){
                                        if($i > 0)
                                                $values .= ",";
                                        $values .= "'" . $this->app_del[$i]->getId() . "'";
                                }
                                $query .= $values . ")";
                                $db->query($query);
                        }
                }
        }
        
        // public
        function existTermin(){
                if(sizeof($this->app) > 0)
                        return TRUE;
                return FALSE;
        }

        // Wiederholungstermine, die in der Vergangenheit angelegt wurden belegen in
        // app[] die ersten Positionen und werden hier in den "Tagesablauf" einsortiert
        // Termine, die sich über die Tagesgrenzen erstrecken, muessen anhand ihrer
        // "absoluten" Anfangszeit einsortiert werden.
        // private
        function sort(){
                if(sizeof($this->app))
                        usort($this->app, "cmp_list");
        }                                        

        // Termine aus Datenbank holen
        // private
        function restore(){
                $db = new DB_Seminar;
                // die Abfrage grenzt das Trefferset weitgehend ein
                $query = sprintf("SELECT termin_id,content,date,end_time,date_typ,expire,repeat,color,priority,raum"
                       . " FROM termine WHERE range_id='%s' AND autor_id='%s' AND ((date BETWEEN %s AND %s OR "
                                         . "end_time BETWEEN %s AND %s) OR (%s BETWEEN date AND end_time) OR (date <= %s AND expire > %s AND"
                                         . " repeat REGEXP '(.+,,,.*%s.*,,,DAYLY)|(.+,.+,,,,,DAYLY)|"
                                         . "(.+,.+,,.*%s.*,,,WEEKLY)|(.+,.+,,,,%s,MONTHLY)|"
                                         . "(.+,.+,.+,%s,,,MONTHLY)|(.+,1,,,%s,%s,YEARLY)|"
                                         . "(.+,1,.+,%s,%s,,YEARLY)|(^.*,[^#]+$)'))"
                                         . " ORDER BY date ASC"
                                         , $this->user_id, $this->user_id, $this->getStart(), $this->getEnd(), $this->getStart()
                                         , $this->getEnd(), $this->getStart(), $this->getEnd(), $this->getStart(), $this->dow, $this->dow
                                         , $this->dom, $this->dow, $this->mon, $this->dom, $this->dow, $this->mon);
                $db->query($query);
                
                while($db->next_record()){
                        $time_range = 0;
                        $is_in_day = FALSE;
                        list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
                             $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $db->f("repeat"));
                        
                        // der "Ursprungstermin"
                        if($db->f("date") >= $this->getStart() && $db->f("end_time") <= $this->getEnd()){
                                        $is_in_day = TRUE;
                        }
                        elseif($db->f("date") >= $this->getStart() && $db->f("date") <= $this->getEnd()){
                                $is_in_day = TRUE;
                                $time_range = 1;
                        }
                        elseif($db->f("date") < $this->getStart() && $db->f("end_time") > $this->getEnd()){
                                $is_in_day = TRUE;
                                $time_range = 2;
                        }
                        elseif($db->f("end_time") >= $this->getStart() && $db->f("end_time") <= $this->getEnd()){
                                $is_in_day = TRUE;
                                $time_range = 3;
                        }
                        else{
                                
                                switch($rep["type"]){
                                        case "DAYLY":
                                                
                                                // täglich wiederholte Termine sind eh drin
                                                if($rep["lintervall"] == 1){
                                                        $is_in_day = TRUE;
                                                        break;
                                                }
                                                
                                                $pos = (($this->ts - $rep["ts"]) / 86400) % $rep["lintervall"];
                                                if($pos == 0){
                                                        $is_in_day = TRUE;
                                                        $time_range = 1;
                                                        break;
                                                }
                                                if($pos < $rep["duration"]){
                                                        $is_in_day = TRUE;
                                                        if($pos == $rep["duration"] - 1)
                                                                $time_range = 3;
                                                        else
                                                                $time_range = 2;
                                                }
                                                break;
                                                
                                        case "WEEKLY":
                                                if($rep["duration"] == "#"){
                                                        // für die anderen berechne erst mal den Montag in dieser Woche...
                                                        $adate = $this->ts - ($this->dow - 1) * 86400;
                                                        if(ceil(($adate - $rep["ts"]) / 604800) % $rep["lintervall"] == 0){
                                                                $is_in_day = TRUE;
                                                                break;
                                                        }
                                                }
                                                else{
                                                        $adate = $this->ts - ($this->dow - 1) * 86400;
                                                        if($adate + 1 > $rep["ts"] - ($this->dow - 1) * 86400){
                                                                for($i = 0;$i < strlen($rep["wdays"]);$i++){
                                                                        $pos = (($adate - $rep["ts"]) / 86400 - $rep["wdays"][$i] + $this->dow) % ($rep["lintervall"] * 7);
                                                                        if($pos == 0){
                                                                                $is_in_day = TRUE;
                                                                                $time_range = 1;
                                                                                break;
                                                                        }
                                                                        if($pos < $rep["duration"]){
                                                                                $is_in_day = TRUE;
                                                                                if($pos == $rep["duration"] - 1)
                                                                                        $time_range = 3;
                                                                                else
                                                                                        $time_range = 2;
                                                                                break 2;
                                                                        }
                                                                }
                                                        }
                                                }
                                                break;
                                        case "MONTHLY":
                                                if($rep["duration"] == "#"){
                                                        // liegt dieser Tag nach der ersten Wiederholung und gehört der Monat zur Wiederholungsreihe?
                                                        if($rep["ts"] < $this->ts + 1 && abs(date("n", $rep["ts"]) - $this->mon) % $rep["lintervall"] == 0){
                                                                // es ist ein Termin am X. Tag des Monats, den hat die Datenbankabfrage schon richtig erkannt
                                                                if($rep["sintervall"] == ""){
                                                                        $is_in_day = TRUE;
                                                                        break;
                                                                }
                                                                // Termine an einem bestimmten Wochentag in der X. Woche
                                                                if(ceil($this->dom / 7) == $rep["sintervall"]){
                                                                        $is_in_day = TRUE;
                                                                        break;
                                                                }
                                                                if($rep["sintervall"] == 5 && (($this->dom / 7) > 3))
                                                                        $is_in_day = TRUE;
                                                        }
                                                }
                                                else{
                                                        $amonth = ($rep["lintervall"] - ((($this->year - date("Y",$rep["ts"])) * 12) - (date("n",$rep["ts"]))) % $rep["lintervall"]) % $rep["lintervall"];
                                                        if($rep["day"]){
                                                                $lwst = mktime(12,0,0,$amonth,$rep["day"],$this->year,0);
                                                                $hgst = $lwst + ($rep["duration"] - 1) * 86400;

                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 3;
                                                                        break;
                                                                }
                                                                
                                                                $lwst = mktime(12,0,0,$amonth - $rep["lintervall"],$rep["day"],$this->year,0);
                                                                $hgst = $lwst + $rep["duration"] * 86400;
                                                                
                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 3;
                                                                        break;
                                                                }
                                                                
                                                        }
                                                        if($rep["sintervall"]){
                                                        
                                                                if($rep["sintervall"] == 5)
                                                                        $cor = 0;
                                                                else
                                                                        $cor = 1;
                                                                
                                                                $lwst = mktime(12,0,0,$amonth,1,$this->year,0) + ($rep["sintervall"] - $cor) * 604800;
                                                                $aday = strftime("%u",$lwst);
                                                                $lwst -= ($aday - $rep["wdays"]) * 86400;
                                                                if($rep["sintervall"] == 5){
                                                                        if(date("j",$lwst) < 10)
                                                                                $lwst -= 604800;
                                                                        if(date("n",$lwst) == date("n",$lwst + 604800))
                                                                                $lwst += 604800;
                                                                }
                                                                else{
                                                                        if($aday > $rep["wdays"])
                                                                                $lwst += 604800;
                                                                }
                                                                
                                                                $hgst = $lwst + ($rep["duration"] - 1) * 86400;
                                                                
                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range =3;
                                                                        break;
                                                                }
                                                                
                                                                $lwst = mktime(12,0,0,$amonth - $rep["lintervall"],1,$this->year,0) + ($rep["sintervall"] - $cor) * 604800;;
                                                                $aday = strftime("%u",$lwst);
                                                                $lwst -= ($aday - $rep["wdays"]) * 86400;
                                                                if($rep["sintervall"] == 5){
                                                                        if(date("j",$lwst) < 10)
                                                                                $lwst -= 604800;
                                                                        if(date("n",$lwst) == date("n",$lwst + 604800))
                                                                                $lwst += 604800;
                                                                }
                                                                else{
                                                                        if($aday > $rep["wdays"])
                                                                                $lwst += 604800;
                                                                }
                                                                
                                                                $hgst = $lwst + $rep["duration"] * 86400;
                                                                $lwst += 86400;
                                                                
                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range =3;
                                                                        break;
                                                                }
                                                        }
                                                        
                                                }
                                                        
                                                break;
                                        case "YEARLY":
                                        
                                                if($rep["duration"] == "#"){
                                                        if($rep["ts"] > $this->getStart() && $rep["ts"] < $this->getEnd()){
                                                                $is_in_day = TRUE;
                                                                break;
                                                        }
                                                                
                                                        // liegt der Wiederholungstermin überhaupt in diesem Jahr?
                                                        if($this->year == date("Y", $rep["ts"]) || ($this->year - date("Y", $rep["ts"])) % $rep["lintervall"] == 0){
                                                                // siehe "MONTHLY"
                                                                if($rep["sintervall"] == ""){
                                                                        $is_in_day = TRUE;
                                                                        break;
                                                                }
                                                                if(ceil($this->dom / 7) == $rep["sintervall"]){
                                                                        $is_in_day = TRUE;
                                                                        break;
                                                                }
                                                                if($rep["sintervall"] == 5 && (($this->dom / 7) > 3)){
                                                                        $is_in_day = TRUE;
                                                                        break;
                                                                }
                                                        }
                                                }
                                                else{
                                                
                                                        // der erste Wiederholungstermin
                                                        $lwst = $rep["ts"];
                                                        $hgst = $rep["ts"] + $rep["duration"] * 86400;
                                                        if($lwst == $this->ts){
                                                                $is_in_day = TRUE;
                                                                $time_range = 1;
                                                                break;
                                                        }
                                                        
                                                        if($this->ts > $lwst && $this->ts < $hgst){
                                                                $is_in_day = TRUE;
                                                                $time_range = 2;
                                                                break;
                                                        }
                                                
                                                        if($this->ts == $hgst){
                                                                $is_in_day = TRUE;
                                                                $time_range = 3;
                                                                break;
                                                        }
                                                        
                                                        if($rep["day"]){
                                                                $lwst = mktime(12,0,0,$rep["month"],$rep["day"],$this->year,0);
                                                                $hgst = $lwst + ($rep["duration"] - 1) * 86400;

                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 3;
                                                                        break;
                                                                }
                                                                
                                                                $lwst = mktime(12,0,0,$rep["month"],$rep["day"] - 1,$this->year - 1,0);
                                                                $hgst = $lwst + $rep["duration"] * 86400;
                                                                
                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 3;
                                                                        break;
                                                                }
                                                                
                                                        }
                                                        
                                                        if($rep["sintervall"]){
                                                                $lwst = mktime(12,0,0,$rep["month"],1,$this->year,0) + ($rep["sintervall"] - $cor) * 604800;
                                                                $aday = strftime("%u",$lwst);
                                                                $lwst -= ($aday - $rep["wdays"]) * 86400;
                                                                if($rep["sintervall"] == 5){
                                                                        if(date("j",$lwst) < 10)
                                                                                $lwst -= 604800;
                                                                        if(date("n",$lwst) == date("n",$lwst + 604800))
                                                                                $lwst += 604800;
                                                                }
                                                                else
                                                                        if($aday > $rep["wdays"])
                                                                                $lwst += 604800;
                                                
                                                                $hgst = $lwst + ($rep["duration"] - 1) * 86400;
                                                
                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 3;
                                                                        break;
                                                                }
                                                                
                                                                $lwst = mktime(12,0,0,$rep["$month"],1,$this->year - 1,0) + ($rep["sintervall"] - $cor) * 604800;;
                                                                $aday = strftime("%u",$lwst);
                                                                $lwst -= ($aday - $rep["wdays"]) * 86400;
                                                                if($rep["sintervall"] == 5){
                                                                        if(date("j",$lwst) < 10)
                                                                                $lwst -= 604800;
                                                                        if(date("n",$lwst) == date("n",$lwst + 604800))
                                                                                $lwst += 604800;
                                                                }
                                                                else{
                                                                        if($aday > $rep["wdays"])
                                                                                $lwst += 604800;
                                                                }
                                                                
                                                                $hgst = $lwst + $rep["duration"] * 86400;
                                                                $lwst += 86400;
                                                                
                                                                if($this->ts == $lwst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 1;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts > $lwst && $this->ts < $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 2;
                                                                        break;
                                                                }
                                                                
                                                                if($this->ts == $hgst){
                                                                        $is_in_day = TRUE;
                                                                        $time_range = 3;
                                                                        break;
                                                                }
                                                                
                                                        }
                                                }
                                }
                        }
                        
                        if($is_in_day==TRUE){                
                                switch($time_range){
                                        case 0: // Einzeltermin
                                                $start = mktime(date("G",$db->f("date")),date("i",$db->f("date")),0,$this->mon,$this->dom,$this->year);
                                                $end = mktime(date("G",$db->f("end_time")),date("i",$db->f("end_time")),0,$this->mon,$this->dom,$this->year);
                                                break;
                                        case 1: // Start
                                                $start = mktime(date("G",$db->f("date")),date("i",$db->f("date")),0,$this->mon,$this->dom,$this->year);
                                                $end = $this->getEnd();
                                                break;
                                        case 2: // Mitte
                                                $start = $this->getStart();
                                                $end = $this->getEnd();
                                                break;
                                        case 3: // Ende
                                                $start = $this->getStart();
                                                $end = mktime(date("G",$db->f("end_time")),date("i",$db->f("end_time")),0,$this->mon,$this->dom,$this->year);
                                }
                                $termin = new Termin($start, $end, $db->f("content"), $db->f("repeat"), $db->f("expire"),
                                                     $db->f("color"), $db->f("priority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
                                if($time_range == 2)
                                        $termin->setDayEvent(TRUE);
                                $termin->chng_flag = FALSE;
                                $this->app[] = $termin;
                        }
                }
        }
        
        // public
        function bindSeminarTermine(){
                if(func_num_args() == 0)
                        $query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user s ON Seminar_id=range_id WHERE "
                               . "user_id = '%s' AND date BETWEEN %s AND %s"
                                                 , $this->user_id, $this->getStart(), $this->getEnd());
                else if(func_num_args() == 1 && $seminar_ids = func_get_arg(0)){
                        if(is_array($seminar_ids))
                                $seminar_ids = implode("','", $seminar_ids);
                        $query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user s ON Seminar_id=range_id WHERE "
                               . "user_id = '%s' AND Seminar_id IN ('%s') AND date_typ!=6"
                                                 . " AND date_typ!=7 AND date BETWEEN %s AND %s"
                                                 , $this->user_id, $seminar_ids, $this->getStart(), $this->getEnd());
                }
                else
                        return FALSE;
                        
                $db = new DB_Seminar;        
                $db->query($query);
                $color = array("#000000","#FF0000","#FF9933","#FFCC66","#99FF99","#66CC66","#6699CC","#666699");
                
                if($db->num_rows() != 0){
                        while($db->next_record()){
                                $repeat = $db->f("date").",,,,,,SINGLE,#";
                                $expire = 2114377200; //01.01.2037 00:00:00 Uhr
                                $app = new Termin($db->f("date"), $db->f("end_time"), $db->f("content"), $repeat, $expire,
                                                  $db->f("date_typ"), $db->f("priority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
                                $app->setSeminarId($db->f("Seminar_id"));
                                $app->setColor($color[$db->f("gruppe")]);
                                $app->setKategorie($db->f("date_typ"));
                                $this->app[] = $app;
                        }
                        $this->sort();
                        return TRUE;
                }
                return FALSE;
        }
        
        // public
        function serialisiere(){
                $size_app = sizeof($this->app);
                $size_app_del = sizeof($this->app_del);
                
                for($i = 0;$i < $size_app;$i++)
                        $ser_app .= 'i:'.$i.';'.$this->app[$i]->serialisiere();
                for($i = 0;$i < $size_app_del;$i++)
                        $ser_app_del .= 'i:'.$i.';'.$this->app_del[$i]->serialisiere();
                
                $pattern[0] = "/s:3:\"app\";a:".$size_app.":\{\}/";
                $pattern[1] = "/s:7:\"app_del\";a:".$size_app_del.":\{\}/";
                
                $replace[0] = "s:3:\"app\";a:".$size_app.":{".$ser_app."}";
                $replace[1] = "s:7:\"app_del\";a:".$size_app_del.":{".$ser_app_del."}";
                
                $serialized = preg_replace($pattern, $replace, serialize($this));
                
                return $serialized;
        }

}

// class Day

?>
