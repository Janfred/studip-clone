<?php

require_once 'lib/raumzeit/raumzeit_functions.inc.php';

class Course_DatesController extends AuthenticatedController
{
    protected $allow_nobody = true;
    protected $utf8decode_xhr = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        checkObject();
        checkObjectModule("schedule");
        $course = Course::findCurrent();
        if ($course) {
            PageLayout::setTitle(sprintf('%s - %s', $course->getFullname(), _("Termine")));
        } else {
            PageLayout::setTitle(_("Termine"));
        }

        PageLayout::addSqueezePackage('tablesorter');
        PageLayout::addScript('raumzeit');
        $this->show_raumzeit = $course->getSemClass()->offsetGet('show_raumzeit');
    }

    public function index_action()
    {
        $this->course = Course::findCurrent();
        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->course->id) && Request::isPost() && Request::option("termin_id") && Request::get("topic_title")) {
            $date = new CourseDate(Request::option("termin_id"));
            $seminar_id = $date['range_id'];
            $title = Request::get("topic_title");
            $topic = CourseTopic::findByTitle($seminar_id, $title);
            if (!$topic) {
                $topic = new CourseTopic();
                $topic['title'] = $title;
                $topic['seminar_id'] = $seminar_id;
                $topic['author_id'] = $GLOBALS['user']->id;
                $topic['description'] = "";
                $topic->store();
            }
            $success = $date->addTopic($topic);
            if ($success) {
                PageLayout::postMessage(MessageBox::success(_("Thema wurde hinzugefügt.")));
            } else {
                PageLayout::postMessage(MessageBox::info(_("Thema war schon mit dem Termin verknüpft.")));
            }
        }
        Navigation::activateItem('/course/schedule/dates');

        object_set_visit_module("schedule");
        $this->last_visitdate = object_get_visit($this->course->id, 'schedule');
        $this->dates = $this->course->getDatesWithExdates();
        $this->lecturer_count = $this->course->countMembersWithStatus('dozent');

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/date-sidebar.png');

        $actions = new ActionsWidget();

        if (!$this->show_raumzeit && $GLOBALS['perm']->have_studip_perm('tutor', $this->course->id)) {
            $actions->addLink(
                _('Neuer Einzeltermin'),
                $this->url_for('course/dates/singledate'),
                Icon::create('add', 'clickable')
            )->asDialog("size=auto");
        }

        $actions->addLink(
            _('Als Doc-Datei runterladen'),
            $this->url_for('course/dates/export'),
            Icon::create('file-word', 'clickable')
        );

        $sidebar->addWidget($actions);

    }


    /**
     * This method is called to show the dialog to edit a date for a course.
     *
     * @param String $termin_id    The id of the date
     * @return void
     */
    public function details_action($termin_id)
    {
        Navigation::activateItem('/course/schedule/dates');

        $this->date = new CourseDate($termin_id);
        $this->cancelled_dates_locked = LockRules::Check($this->date->range_id, 'cancelled_dates');
        $this->dates_locked = LockRules::Check($this->date->range_id, 'room_time');
        PageLayout::setTitle($this->date->getTypeName() . ": ".
            $this->date->getFullname());
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/date-sidebar.png');

    }

    /**
     * This method is called to show the dialog to edit a singledate for a studygroup.
     *
     * @param String $termin_id The id of the date
     * @return void
     */
    public function singledate_action($termin_id = null)
    {
        Navigation::activateItem('/course/schedule/dates');
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/date-sidebar.png');

        $course = Course::findCurrent();

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $course->id)) {
            throw new AccessDeniedException();
        }

        if (Request::submitted("editSingleDate_button")) {
            CSRFProtection::verifyUnsafeRequest();

            $start_date = strtotime(Request::get('startDate'));
            $errors = array();
            if (!$start_date) {
                $errors[] = _('Bitte geben Sie ein korretes Datum an!');
            } else {
                $start_time = strtotime((Request::get('start_stunde') ?: '0') . ':' . (Request::get('start_minute') ?: '0'), $start_date);
                $end_time = strtotime((Request::get('end_stunde') ?: '0') . ':' . (Request::get('end_minute') ?: '0'), $start_date);
                if (!($start_time && $end_time && ($start_time < $end_time))) {
                    $errors[] = _('Bitte geben Sie korrekte Werte für Start- und Endzeit an!');
                }
            }
            $termin = CourseDate::find(Request::option('singleDateID'));
            if ($termin == null) {
                $termin = new CourseDate();
            }
            $termin->raum = Request::get("freeRoomText_sd");
            $termin->autor_id = User::findCurrent()->user_id;
            $termin->range_id = $course->getId();
            $termin->date = $start_time;
            $termin->end_time = $end_time;
            $termin->date_typ = Request::int('dateType');
            if (!count($errors)) {
                if ($termin->store()) {
                    PageLayout::postSuccess(_('Der Termin wurde geändert.'));
                }
                return $this->relocate('course/dates');
            } else {
                PageLayout::postError(_('Bitte korrigieren Sie Ihre Eingaben:'), $errors);
                $this->date = $termin;
            }
        } else {
            if ($termin_id) {
                $this->date = new CourseDate($termin_id);
                $xtitle = $this->date->getTypeName() . ": " . $this->date->getFullname();

            } else {
                $this->date = new CourseDate();
                $xtitle = _('Einzeltermin anlegen');
            }
        }
        $this->cancelled_dates_locked = LockRules::Check($this->date->range_id, 'cancelled_dates');
        $this->dates_locked = LockRules::Check($this->date->range_id, 'room_time');
        PageLayout::setTitle($xtitle);
    }

    /**
     * This method is called to save a singledate for a studygroup.
     *
     * @return void
     */
    public function save_details_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException();
        }
        $termin = CourseDate::find(Request::option('singleDateID'));
        if ($termin) {
            $termin->date_typ = Request::get('dateType');

            $related_groups = Request::get('related_statusgruppen');
            $termin->statusgruppen = array();
            if (!empty($related_groups)) {
                $related_groups = explode(',', $related_groups);
                $termin->statusgruppen = Statusgruppen::findMany($related_groups);
            }

            $related_users = Request::get('related_teachers');
            $related_users = explode(',', $related_users);
            $current_count = CourseMember::countByCourseAndStatus($termin->course->id, 'dozent');
            if ($related_users && count($related_users) != $current_count) {
                $termin->dozenten = User::findMany($related_users);
            } else {
                $termin->dozenten = array();
            }

            if ($termin->store()) {
                PageLayout::postSuccess(_('Der Termin wurde geändert.'));
            }

        }
        $this->relocate('course/dates');
    }

    public function new_topic_action()
    {
        Navigation::activateItem('/course/schedule/dates');
        if (Request::isAjax()) {
            PageLayout::setTitle(_("Thema hinzufügen"));
        }
        $this->date = new CourseDate(Request::option("termin_id"));
        $this->course = Course::findCurrent();
    }

    public function add_topic_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException();
        }
        if (!Request::get("title")) {
            throw new Exception("Geben Sie einen Titel an.");
        }
        $date = new CourseDate(Request::option("termin_id"));
        $seminar_id = $date['range_id'];
        $title = studip_utf8decode(Request::get("title"));
        $topic = CourseTopic::findByTitle($seminar_id, $title);
        if (!$topic) {
            $topic = new CourseTopic();
            $topic['title'] = $title;
            $topic['seminar_id'] = $seminar_id;
            $topic['author_id'] = $GLOBALS['user']->id;
            $topic['description'] = "";
            $topic->store();
        }
        $date->addTopic($topic);

        $factory = $this->get_template_factory();
        $output = array('topic_id' => $topic->getId());

        $template = $factory->open($this->get_default_template("_topic_li"));
        $template->set_attribute("topic", $topic);
        $template->set_attribute("date", $date);
        $output['li'] = $template->render();

        $this->render_json($output);
    }

    /**
     * Moves a topic from one date to another.
     * This action will be called from an ajax request and will return only
     * the neccessary output for a single topic element.
     *
     * @param String $topic_id    The id of the topic
     * @param String $old_date_id The id of the original date of the topic
     * @param String $new_date_id The id of the new date of the topic
     * @throws MethodNotAllowedException if request method is not post
     * @throws AccessDeniedException if the user is not allowed to execute the
     *                               action (at least tutor of the course)
     */
    public function move_topic_action($topic_id, $old_date_id, $new_date_id)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException();
        }

        $this->topic = CourseTopic::find($topic_id);
        $this->date = new CourseDate($new_date_id);

        $this->topic->dates->unsetByPK($old_date_id);
        if (!$this->topic->dates->findOneBy('termin_id', $new_date_id)) {
            $this->topic->dates[] = CourseDate::find($new_date_id);
        }
        $this->topic->store();

        $this->set_content_type('text/html;charset=windows-1252');
        $this->render_template('course/dates/_topic_li.php');
    }

    public function remove_topic_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException();
        }
        $topic = new CourseTopic(Request::option("issue_id"));
        $date = new CourseDate(Request::option("termin_id"));
        $date->removeTopic($topic);

        $output = array();
        $this->render_json($output);
    }

    public function export_action()
    {
        $course = new Course($_SESSION['SessionSeminar']);
        $sem = new Seminar($_SESSION['SessionSeminar']);
        $themen =& $sem->getIssues();

        $termine = getAllSortedSingleDates($sem);

        $dates = array();

        if (is_array($termine) && sizeof($termine) > 0) {
            foreach ($termine as $singledate_id => $singledate) {
                if (!$singledate->isExTermin()) {
                    $tmp_ids = $singledate->getIssueIDs();
                    $title = $description = '';
                    if (is_array($tmp_ids)) {
                        $title = trim(join("\n", array_map(function ($tid) use ($themen) {return $themen[$tid]->getTitle();}, $tmp_ids)));
                        $description = trim(join("\n\n", array_map(function ($tid) use ($themen) {return $themen[$tid]->getDescription();}, $tmp_ids)));
                    }

                    $dates[] = array(
                        'date'  => $singledate->toString(),
                        'title' => $title,
                        'description' => $description,
                        'start' => $singledate->getStartTime(),
                        'related_persons' => $singledate->getRelatedPersons()
                    );
                } elseif ($singledate->getComment()) {
                    $dates[] = array(
                        'date'  => $singledate->toString(),
                        'title' => _('fällt aus') . ' (' . _('Kommentar:') . ' ' . $singledate->getComment() . ')',
                        'description' => '',
                        'start' => $singledate->getStartTime(),
                        'related_persons' => array()
                    );
                }
            }
        }

        $factory = $this->get_template_factory();
        $template = $factory->open($this->get_default_template("export"));

        $template->set_attribute('dates', $dates);
        $content = $template->render();

        $content = mb_encode_numericentity($content, array(0x80, 0xffff, 0, 0xffff), 'cp1252');
        $filename = prepareFilename($course['name'] . '-' . _("Ablaufplan")) . '.doc';

        $this->set_content_type(get_mime_type($filename));
        $this->response->add_header('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $this->response->add_header('Expires', 0);
        $this->response->add_header('Cache-Control', 'private');
        $this->response->add_header('Pragma', 'cache');
        $this->render_text($content);
    }
}
