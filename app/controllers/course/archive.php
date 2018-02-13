<?php

/**
 * archive.php - contains Course_ArchiveController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require_once('lib/archiv.inc.php'); //needed in archive_action


/**
 * Course_ArchiveController is a controller that allows users
 * which have the required permissions to archive a course.
 */
class Course_ArchiveController extends AuthenticatedController
{

    /**
     * This method checks if the current user has the required
     * permissions to archive a course.
     *
     * @param string courseId The ID of the course that is going to be archived
     * in case the user has sufficent permissions to do so.
     *
     * @return bool True, if the user has the required permissions to archive
     * a course, false otherwise.
     */
    private function userHasPermission($courseId)
    {
        //check permissions: user has to be an administrator of the course:
        $requiredPermission = 'admin';
        if (Config::get()->ALLOW_DOZENT_ARCHIV) {
            //members of the "dozent" role may also archive the course:
            $requiredPermission = 'dozent';
        }
        return $GLOBALS['perm']->have_studip_perm($requiredPermission, $courseId);
    }


    /**
     * A helper method that creates an HTML table out of an
     * archived course's basic data.
     *
     * The generated HTML table provides basic information about the
     * archived course. It exists for compatibility reasons with public/archiv.php
     * which creates the same output and can be used when the public/archiv.php
     * script is converted to a Trails controller.
     *
     * @param ArchivedCourse course The archived course whose HTML table shall be generated.
     *
     * @return string The HTML code for the table that displays
     */
    private function createArchivedCourseHTMLTable($course = null)
    {
        $table = '<table class="default">'
               . '<caption>' . $course->name . '</caption>'
               . '<tbody>'
               . '<tr><th>' . _('Untertitel') . ':</th><td>' . $course->untertitel . '</td></tr>'
               //. '<tr><th>' . _("Zeit") . ':</th><td>' . INSERT_ZEIT_HERE . '</td></tr>'
               . '<tr><th>' . _('Semester') . ':</th><td>' . $course->start_semester . '</td></tr>' //TODO: check if start_semester is right
               . '<tr><th>' . _('Erster Temin') . ':</th><td>' . $course->untertitel . '</td></tr>'
               //. '<tr><th>' . _("Vorbesprechung") . ':</th><td>' . INSERT_VORBESPRECHUNG_HERE . '</td></tr>'
               . '<tr><th>' . _('Ort') . ':</th><td>' . $course->ort . '</td></tr>'
               . '<tr><th>' . _('Typ der Veranstaltung') . ':</th><td>'
                    . $course->start_semester . '</td></tr>';

        $table .= '</tbody></table>';
        return $table;
    }


    /**
     * This action collects all required data about the course.
     *
     * @return null This method does not return any value.
     */
    public function confirm_action()
    {
        PageLayout::setHelpKeyword('Veranstaltungen.Archivieren');

        /*
            NOTE: confirm_action will be called from admin/courses
            with an array in HTTP POST that is called archiv_sem,
            having the following form:
            [ "_id_courseID", "_id_courseID", "on", "_id_courseID", ...]

            Every courseID followed by "on" is an ID of a course
            that was selected for archiving.
        */

        //check the archiv_sem array and extract the relevant course IDs:
        $courseIds = [];

        $archiv_sem = Request::getArray('archiv_sem');

        /*
            $archiv_sem may be empty. If this controller is called
            out of a course the POST parameter archiv_sem won't be set.
            So we can skip a lot of code if we check for its existence first.
        */
        if ($archiv_sem) {
            for ($i = 0; $i < count($archiv_sem); $i += 1) {
                if (($i > 0) && $archiv_sem[$i] === 'on') {
                    //the previous array item is a relevant course ID:
                    $id = explode('_', $archiv_sem[$i - 1])[2];
                    $courseIds[] = $id;
                }
                //check if the user has the required permission
                //to archive the selected course:
                if (!$this->userHasPermission($id)) {
                    //no permission: access denied!
                    throw new AccessDeniedException();
                }
            }
            $this->courses = Course::findMany($courseIds);
            //TODO: enable navigation items, depending whether the user
            // is in the admin role or not.
        } else {
            // $archiv_sem is empty: We check the current course now.

            $currentCourse = Course::findCurrent();
            if ($currentCourse) {
                $this->courses[] = $currentCourse;
            }
        }


        //check if at least one course was selected:
        if (!$this->courses) {
            //courses not found: display the "no course selected" message
            //from the view.
            return;
        }

        //activate navigation elements if they exist:
        if ($GLOBALS['perm']->have_perm('admin')) {
            if (Navigation::hasItem('/browse/my_courses/list')) {
                Navigation::activateItem('/browse/my_courses/list');
            }
        } else {
            if (Navigation::hasItem('/course/admin/main/archive')) {
                Navigation::activateItem('/course/admin/main/archive');
            }
        }

        //set the page title with the area of Stud.IP:
        PageLayout::setTitle(_('Archivieren von Veranstaltungen'));

        //get the list of "dozenten" and the last activity for each course (if any course):
        $this->dozenten = [];
        $this->lastActivities = [];

        foreach ($this->courses as $course) {
            $this->dozenten[$course->id] = $course->members->filter(function ($member) {
                return $member['status'] === 'dozent';
            });
            $this->lastActivities[$course->id] = date('d.m.Y, G:i', lastActivity($course->id));
        }
    }


    /**
     * This action does the actual archiving of a course.
     *
     * @return null This method does not return any value.
     */
    public function archive_action()
    {
        //now pick the courses IDs:
        $courseIds = Request::getArray('courseIds');

        //check if the user has the required permission
        //to archive all selected courses:

        $this->deletedCourses = [];
        foreach ($courseIds as $courseId) {
            if (!$this->userHasPermission($courseId)) {
                //no permission for one of the selected courses: access denied!
                throw new AccessDeniedException();
            }

            // to be replaced when archive.inc.php is replaced:
            in_archiv($courseId);

            $course = Course::find($courseId);
            if ($course) {
                $course->delete();
                $archivedCourse = ArchivedCourse::find($courseId);
                if ($archivedCourse) {
                    $this->archivedCourses[] = $archivedCourse;
                }
            } else {
                throw new Exception(_('Veranstaltung nicht in Datenbank gefunden!'));
            }
        }

        /*
        // enable the following code when archive.inc.php is replaced

        //get all courses:
        $courses = Course::findMany($courseIds);

        //now create ArchivedCourse objects out of the Course objects:

        foreach ($courses as $course) {
            in_archiv($course->id);
        }
            $archivedCourse = new ArchivedCourse();
            $archivedCourse->id = $course->id;
            $archivedCourse->name = $course->name;
            $archivedCourse->untertitel = $course->untertitel;
            $archivedCourse->beschreibung = $course->beschreibung;
            $archivedCourse->start_time = $course->start_time;
            $archivedCourse->semester = $course->end_semester; //TODO: maybe start_semester is better
            $archivedCourse->heimat_inst_id = $course->home_institut->id;
            $archivedCourse->institute = $course->institutes;

            //get "dozenten":
            $archivedCourse->dozenten = $course->members->filter(
                                function ($member) {
                                    return $member['status'] === "dozent";
                                }
                            );

            $archivedCourse->fakultaet = $course->home_institut->faculty;


            //dump is an HTML table with the seminar data
            $archivedCourse->dump = $this->createArchivedCourseHTMLTable($course);

            //TODO:
            //$archivedCourse->archiv_file_id =
            //$archivedCourse->archiv_protected_file_id =
            $archivedCourse->mkdate = time();
            //$archivedCourse->forumdump =
            //$archivedCourse->wikidump =
            $archivedCourse->studienbereiche = $course->study_areas;
            $archivedCourse->veranstaltungsnummer = $course->veranstaltungsnummer;
            $archivedCourse->members = $course->members;
            $archivedCourse->home_institut = $course->home_institut;
        }
        */
    }
}
