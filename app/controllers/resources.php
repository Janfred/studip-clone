<?php

/**
 * resources.php - for csv export for room managment
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jana Boehm <jaboehm@uos.de>inc
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'app/controllers/authenticated_controller.php';

class ResourcesController extends AuthenticatedController {

    function export_requestlist_action() {
        $data[] = array(_('V.-Nummer'), _('Titel'), _('Dozenten'), _('Anfrager'), _('Startsemester'), _('Datum der Erstellung'),
            _('Datum der letzten Änderung'), _('angeforderte Belegungszeiten'), _('gewünschte Raumeigenschaften'), _('angeforderter Raum'),
            _('Teilnehmeranzahl'), _('Kommentar des Anfragenden'));

        $resources_data = unserialize($_SESSION['resources_data']);

        //fill array
        foreach ($resources_data['requests_working_on'] as $key => $val) {
            if ($resources_data['requests_open'][$val['request_id']] || !$resources_data['skip_closed_requests']) {
                $reqObj = new RoomRequest($val['request_id']);
                $semObj = new Seminar($reqObj->getSeminarId());

                //number and name of course
                if ($semObj->getName() != "") {
                    $no = $semObj->seminar_number;
                    $title = $semObj->getName();
                }
                //lecturer
                $lec = array();
                foreach ($semObj->getMembers('dozent') as $doz) {
                    $lec[] = $doz['fullname'];
                }
                $lec = join(', ', $lec);

                //request created by:
                $rp = get_fullname($reqObj->user_id);

                // start of semester
                $semester = new SemesterData();
                $start = $semester->getSemesterDataByDate($semObj->semester_start_time);
                $start = $start['name'];

                // issued-date, last modified-date
                $date = strftime('%Y-%m-%d %H:%M:%S', $reqObj->mkdate);
                $dateChanged = strftime('%Y-%m-%d %H:%M:%S', $reqObj->chdate);

                // requested time slots:
                $timeslot = array();
                $dates = $semObj->getGroupedDates($reqObj->getTerminId(), $reqObj->getMetadateId());
                if ($dates['first_event']) {
                    if (is_array($dates['info']) && sizeof($dates['info']) > 0) {
                        foreach ($dates['info'] as $info) {
                            $timeslot[] = $info['export'];
                        }
                    }
                    if ($reqObj->getType() != 'date') {
                        $timeslot[] = _("regelmäßige Buchung ab: ") . strftime('%x', $dates['first_event']);
                    }
                }
                $time = join(', ', $timeslot);

                // room requirements
                $properties = $reqObj->getProperties();
                $size = sizeof($properties);
                $roomReq = '';

                if ($size) {
                    $i = 1;
                    foreach ($properties as $key => $val) {
                        switch ($val["type"]) {
                            case "bool":
                                $roomReq .= $val["name"];
                                break;
                            case "num":
                                $roomReq .= $val["name"] . ": " . $val["state"];
                                break;
                            case "select":
                                $options = explode(";", $val["options"]);
                                foreach ($options as $a) {
                                    if ($val["state"] == $a)
                                        $roomReq .= $a;
                                }
                                break;
                        }
                        if ($i < $size) {
                            $roomReq = $roomReq . ", ";
                        }
                        $i++;
                    }
                }

                // room request
                if ($request_resource_id = $reqObj->getResourceId()) {
                    $resObj = ResourceObject::Factory($request_resource_id);
                    $room = $resObj->getName();
                }

                // participants at the moment
                $people = $semObj->getNumberOfParticipants('total');

                //comment
                $comment = $reqObj->getComment();

                //csv export
                $data[] = array($no, $title, $lec, $rp, $start, $date, $dateChanged, $time, $roomReq, $room, $people, $comment);
            }
        }

        $this->response->add_header('Content-Type', 'text/csv');
        $this->response->add_header('Content-Disposition', 'attachment; filename=' . _('Anfragenliste') . '.csv');
        $this->render_text(array_to_csv($data));
    }
}
