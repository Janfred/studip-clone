<?php

/*
 * StatusgroupController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require_once 'app/models/statusgroups.php';
require_once 'lib/messaging.inc.php'; //Funktionen des Nachrichtensystems
require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionen für den Export
require_once 'lib/export/export_linking_func.inc.php';

class Course_StatusgroupsController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        global $perm;

        checkObject();
        checkObjectModule("participants");

        $course = Course::findCurrent();
        $this->course_id = $course->id;
        $this->course_title = $course->getFullname();

        // Check perms
        $this->is_dozent = $perm->have_studip_perm('dozent', $this->course_id);
        $this->is_tutor  = $perm->have_studip_perm('tutor', $this->course_id);
        $this->is_autor  = $perm->have_studip_perm('autor', $this->course_id);

        // Check lock rules
        $this->is_locked = LockRules::Check($this->course_id, 'groups');
        $this->is_participants_locked = LockRules::Check($this->course_id, 'participants');

        PageLayout::setTitle(sprintf('%s - %s', Course::findCurrent()->getFullname(), _('Gruppen')));

    }

    /**
     * Lists all available statusgroups.
     */
    public function index_action()
    {
        PageLayout::addSqueezePackage('statusgroups');
        Navigation::activateItem('/course/members/statusgroups');

        if ($this->is_locked && $this->is_tutor) {
            $lockdata = LockRules::getObjectRule($this->course_id);
            if ($lockdata['description']) {
                PageLayout::postMessage(MessageBox::info(formatLinks($lockdata['description'])));
            }
        }

        // Sorting as given by Request parameters
        $this->sort_by = Request::option('sortby', 'nachname');
        $this->order = Request::option('order', 'desc');
        $this->sort_group = Request::get('sort_group', '');

        // Get all course members (needed for mkdate).
        $this->allmembers = SimpleCollection::createFromArray(
            CourseMember::findByCourse($this->course_id));

        // Find all statusgroups for this course.
        $groups = Statusgruppen::findBySeminar_id($this->course_id);

        /*
         * Check if the current user may join any group at all. This is needed
         * for deciding if a Sidebar action for joining a group will be
         * displayed.
         */
        $joinable = false;

        // Fetch membercounts (all at once for performance)
        $membercounts = array_column(DBManager::get()->fetchAll(
            "SELECT u.`statusgruppe_id`, COUNT(u.`user_id`) as membercount
                FROM `statusgruppen` s
    	            JOIN `statusgruppe_user` u USING (`statusgruppe_id`)
                WHERE s.`range_id` = ?
                GROUP BY u.`statusgruppe_id`",
                [$this->course_id]),
            'membercount',
            'statusgruppe_id'
        );


        // Now build actual groups.
        $this->groups = array();
        foreach ($groups as $g) {

            $groupdata = [
                'group' => $g,
                'membercount' => $membercounts[$g->id] ?: 0
            ];

            /*
             * We only need to load members for a group that shall be sorted
             * explicitly, as this group will be loaded at once and not via AJAX.
             */
            if ($g->id == $this->sort_group) {
                $groupmembers = $g->members->pluck('user_id');
                if ($this->sort_group == $g->id) {
                    $sorted = StatusgroupsModel::sortGroupMembers(
                        $this->allmembers->findBy('user_id', $groupmembers),
                        $this->sort_by, $this->order);
                } else {
                    $sorted = StatusgroupsModel::sortGroupMembers(
                        $this->allmembers->findBy('user_id', $groupmembers));
                }

                $groupdata['members'] = $sorted;
                $groupdata['load'] = true;
            }

            if (!$this->is_tutor && $g->userMayJoin($GLOBALS['user']->id)) {
                $groupdata['joinable'] = true;
                $joinable = true;
            }

            $this->groups[] = $groupdata;
        }

        /*
         * Get number of users that are in no group, this is needed
         * for displaying in group header.
         */
        $ungrouped_count = DBManager::get()->fetchFirst(
            "SELECT COUNT(s.`user_id`) FROM `seminar_user` s WHERE s.`Seminar_id` = :course AND NOT EXISTS (
                    SELECT u.`user_id` FROM `statusgruppe_user` u
                    WHERE u.`statusgruppe_id` IN (:groups) AND u.`user_id` = s.`user_id`)",
            [
                'course' => $this->course_id,
                'groups' => DBManager::get()->fetchFirst(
                    "SELECT `statusgruppe_id` FROM `statusgruppen` WHERE `range_id` = ?",
                    [$this->course_id])
            ]);
        $ungrouped_count = $ungrouped_count[0];
        if ($ungrouped_count > 0) {
            // Create dummy entry for "no group" users.
            $no_group = new StdClass();
            $no_group->id = 'nogroup';
            $no_group->name = _('keiner Gruppe zugeordnet');
            $no_group->size = 0;
            $no_group->selfassign = 0;

            $groupdata = [
                'group' => $no_group,
                'membercount' => $ungrouped_count,
                'joinable' => false
            ];

            if ($this->sort_group == 'nogroup') {
                $nogroupmembers = DBManager::get()->fetchAll("SELECT s.* FROM `seminar_user` s
                    WHERE `Seminar_id` = :course AND NOT EXISTS (
                        SELECT `user_id` FROM `statusgruppe_user`
                        WHERE `statusgruppe_id` IN (:groups) AND `user_id` = s.`user_id`)",
                    [
                        'course' => $this->course_id,
                        'groups' => array_map(function ($g) { return $g->id; }, $groups)
                    ]);

                $members = new SimpleCollection();
                foreach ($nogroupmembers as $m) {
                    $members->append(CourseMember::build($m));
                }

                $groupdata['members'] = StatusgroupsModel::sortGroupMembers($members, $this->sort_by, $this->order);

                $groupdata['load'] = true;
            }
            $this->groups[] = $groupdata;
        }

        // Prepare search object for MultiPersonSearch.
        $this->memberSearch = new PermissionSearch(
            'user',
            _('Personen suchen'),
            'user_id',
            array(
                'permission' => array('user', 'autor', 'tutor', 'dozent'),
                'exclude_user' => array()
            )
        );

        /*
         * Setup sidebar.
         */
        $sidebar = Sidebar::get();
        // Set default sidebar image
        $sidebar->setImage('sidebar/person-sidebar.png');

        if ($this->is_tutor) {
            if (!$this->is_locked) {
                $actions = new ActionsWidget();
                $actions->addLink(_('Neue Gruppe anlegen'),
                    $this->url_for('course/statusgroups/edit'),
                    Icon::create('add', 'clickable'))->asDialog('size=auto');
                $actions->addLink(_('Mehrere Gruppen anlegen'),
                    $this->url_for('course/statusgroups/create_groups'),
                    Icon::create('group2+add', 'clickable'))->asDialog('size=auto');
                $actions->addLink(_('Gruppenreihenfolge ändern'),
                    $this->url_for('course/statusgroups/sortgroups'),
                    Icon::create('arr_2down', 'clickable'))->asDialog('size=auto');

                $sidebar->addWidget($actions);
            }
            if (Config::get()->EXPORT_ENABLE) {

                $export = new ExportWidget();

                // create csv-export link
                $csvExport = export_link($this->course_id, 'person',
                    sprintf('%s %s', _('Gruppenliste'), htmlReady($this->course_title)),
                    'csv', 'csv-gruppen', 'status',
                    _('Gruppen als CSV-Dokument exportieren'),
                    'passthrough');
                $element = LinkElement::fromHTML($csvExport, Icon::create('file-office', 'clickable'));
                $export->addElement($element);

                // create rtf-export link
                $rtfExport = export_link($this->course_id, 'person',
                    sprintf('%s %s', _('Gruppenliste'), htmlReady($this->course_title)),
                    'rtf', 'rtf-gruppen', 'status',
                    _('Gruppen als RTF-Dokument exportieren'),
                    'passthrough');
                $element = LinkElement::fromHTML($rtfExport, Icon::create('file-text', 'clickable'));
                $export->addElement($element);

                $sidebar->addWidget($export);
            }
        // Current user may join at least one group => show sidebar action.
        } else if ($joinable) {
            $actions = new ActionsWidget();
            $actions->addLink(_('In eine Gruppe eintragen'),
                $this->url_for('course/statusgroups/joinables'),
                    Icon::create('door-enter', 'clickable'))->asDialog('size=auto');
            $sidebar->addWidget($actions);
        }
    }

    /**
     * Fetches the members of the given group.
     *
     * @param String $group_id the statusgroup to get members for.
     */
    public function getgroup_action($group_id)
    {
        if ($group_id != 'nogroup') {
            $this->group = Statusgruppen::find($group_id);
            if (count($this->group->members) > 0) {
                $this->members = StatusgroupsModel::sortGroupMembers(
                    SimpleCollection::createFromArray(
                        CourseMember::findBySQL("`Seminar_id` = ? AND `user_id` IN (?)",
                        [$this->course_id, $this->group->members->pluck('user_id')])
                    )
                );
            } else {
                $this->members = [];
            }
        } else {
            // Create dummy entry for "no group" users.
            $no_group = new StdClass();
            $no_group->id = 'nogroup';
            $no_group->name = _('keiner Gruppe zugeordnet');
            $no_group->size = 0;
            $no_group->selfassign = 0;

            $members = DBManager::get()->fetchAll("SELECT s.* FROM `seminar_user` s
                    WHERE `Seminar_id` = :course AND NOT EXISTS (
                        SELECT `user_id` FROM `statusgruppe_user`
                        WHERE `statusgruppe_id` IN (:groups) AND `user_id` = s.`user_id`)",
                [
                    'course' => $this->course_id,
                    'groups' => DBManager::get()->fetchFirst(
                        "SELECT `statusgruppe_id` FROM `statusgruppen` WHERE `range_id` = ?",
                        [$this->course_id])
                ]);

            $this->members = new SimpleCollection();
            foreach ($members as $m) {
                $this->members->append(CourseMember::build($m));
            }
            $this->members = StatusgroupsModel::sortGroupMembers($this->members);

            $this->group = $no_group;
        }
    }

    /**
     * Provides extended info about a status group, like maximum number of
     * participants, selfassign, exclusive entry, selfassign start and end
     * times.
     *
     * @param String $group_id The group to show info for.
     */
    public function groupinfo_action($group_id)
    {
        $this->group = Statusgruppen::find($group_id);

        // Topics can be implicitly assigned via course dates.
        $this->topics = $this->group->findTopics();

        // Lecturers can be implicitly assigned via course dates.
        $this->lecturers = $this->group->findLecturers();
    }

    /**
     * Shows a list of all groups that can be joined by current user
     * and allows the user to select one.
     */
    public function joinables_action()
    {
        $this->joinables = SimpleCollection::createFromArray(
            Statusgruppen::findJoinableGroups($this->course_id, $GLOBALS['user']->id))
            ->orderBy('position asc, name asc');
    }

    /**
     * Adds selected persons to given group. user_ids to add come from a
     * MultiPersonSearch object which was triggered in group actions.
     *
     * @param String $group_id
     */
    public function add_member_action($group_id)
    {
        $g = Statusgruppen::find($group_id);

        // Get selected persons.
        $mp = MultiPersonSearch::load('add_statusgroup_member' . $group_id);

        $success = 0;
        $fail = 0;

        foreach ($mp->getAddedUsers() as $a) {

            if (!CourseMember::exists(array($this->course_id, $a))) {
                $m = new CourseMember();
                $m->seminar_id = $this->course_id;
                $m->user_id = $a;
                $m->status = User::find($a)->perms == 'user' ? 'user' : 'autor';
                $m->store();
            }

            $s = new StatusgruppeUser();
            $s->statusgruppe_id = $group_id;
            $s->user_id = $a;
            if ($s->store() !== false) {
                $success++;
            } else {
                $fail++;
            }
        }

        if ($success > 0 && $fail == 0) {
            PageLayout::postSuccess(sprintf(ngettext(
                '%u Person wurde zu %s hinzugefügt.',
                '%u Personen wurden zu %s hinzugefügt.',
                $success), $success, htmlReady($g->name)
            ));
        } else if ($success > 0 && $fail > 0) {
            $successMsg = sprintf(ngettext(
                '%u Person wurde zu %s hinzugefügt.',
                '%u Personen wurden zu %s hinzugefügt.',
                $success), $success, htmlReady($g->name)
            );
            $failMsg = sprintf(ngettext(
                '%u Person konnte nicht zu %s hinzugefügt werden.',
                '%u Personen konnten nicht zu %s hinzugefügt werden.',
                $fail), $fail, htmlReady($g->name)
            );
            PageLayout::postWarning($successMsg . ' ' . $failMsg);
        } else if ($success == 0 && $fail > 0) {
            PageLayout::postError(sprintf(ngettext(
                '%u Person konnte nicht zu %s hinzugefügt werden.',
                '%u Personen konnten nicht zu %s hinzugefügt werden.',
                $success), $success, htmlReady($g->name)
            ));
        }

        $this->relocate('course/statusgroups');

    }

    /**
     * Allows editing of a given statusgroup or creating a new one.
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function edit_action($group_id = '')
    {
        if ($this->is_tutor) {

            // Fetch group with given ID or create a new one.
            $this->group = new Statusgruppen($group_id);

            // Check if course has regular times.
            $this->cycles = SeminarCycleDate::findBySeminar_id($this->course_id);

            // Check if course has single dates, not belonging to a regular cycle.
            $dates = CourseDate::findBySeminar_id($this->course_id);
            $this->singledates = array_filter($dates, function ($d) {
                return !((bool) $d->metadate_id);
            });

        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Saves changes to given statusgroup or creates a new entry.
     *
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function save_action($group_id = '')
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();

            $warn = false;

            /*
             * Check if a valid end time was given.
             */
            if (Request::int('selfassign', 0)) {
                $endtime = strtotime(Request::get('selfassign_end', 'now'));
                $starttime = strtotime(Request::get('selfassign_start', 'now'));
                if ($endtime <= $starttime) {
                    $endtime = 0;
                    $warn = true;
                }
            }

            $group = StatusgroupsModel::updateGroup($group_id, Request::get('name'),
                0, $this->course_id, Request::int('size', 0),
                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                strtotime(Request::get('selfassign_start', 'now')),
                Request::get('selfassign_end') ? strtotime(Request::get('selfassign_end')) : 0,
                Request::int('makefolder', 0),
                Request::getArray('dates'));

            if (!$group_id) {
                PageLayout::postSuccess(sprintf(
                    _('Die Gruppe "%s" wurde angelegt.'),
                    htmlReady($group->name)));
            } else {
                PageLayout::postSuccess(sprintf(
                    _('Die Daten der Gruppe "%s" wurden gespeichert.'),
                    htmlReady($group->name)));
            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Deletes the given statusgroup.
     *
     * @param String $group_id ID of the group to delete
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function delete_action($group_id)
    {
        if ($this->is_tutor) {
            $group = Statusgruppen::find($group_id);
            $groupname = $group->name;
            $group->delete();
            PageLayout::postSuccess(sprintf(
                _('Die Gruppe "%s" wurde gelöscht.'),
                htmlReady($groupname)));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Removes the given user from the given statusgroup.
     *
     * @param String $user_id user to remove
     * @param String $group_id affected group
     */
    public function delete_member_action($user_id, $group_id)
    {
        $g = Statusgruppen::find($group_id);
        if ($this->is_tutor || ($user_id == $GLOBALS['user']->id && $g->userMayLeave($user_id))) {
            $s = StatusgruppeUser::find(array($group_id, $user_id));
            $name = $s->user->getFullname();
            if ($s->delete()) {
                if ($user_id == $GLOBALS['user']->id) {
                    PageLayout::postSuccess(sprintf(
                        _('Sie wurden aus der Gruppe %s ausgetragen.'),
                        htmlReady($g->name)));
                } else {
                    PageLayout::postSuccess(sprintf(
                        _('%s wurde aus der Gruppe %s ausgetragen.'),
                        htmlReady($name), htmlReady($g->name)));
                }
            } else {
                if ($user_id == $GLOBALS['user']->id) {
                    PageLayout::postError(sprintf(
                        _('Sie konnten nicht aus der Gruppe %s ausgetragen werden.'),
                        htmlReady($g->name)));
                } else {
                    PageLayout::postError(sprintf(
                        _('%s konnte nicht aus der Gruppe %s ausgetragen werden.'),
                        htmlReady($name), htmlReady($g->name)));
                }
            }
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    public function move_member_action($user_id, $group_id)
    {
        if ($this->is_tutor) {
            $this->source_group = $group_id;

            $this->members = array($user_id);

            // Find possible target groups.
            $this->target_groups = SimpleCollection::createFromArray(
                Statusgruppen::findByRange_id($this->course_id))
                ->orderBy('position, name')
                ->filter(function ($g) use ($group_id) { return $g->id != $group_id; });
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Provides the possibility to batch create several groups at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function create_groups_action()
    {
        if ($this->is_tutor) {
            // Check if course has regular times.
            $this->has_cycles = count(SeminarCycleDate::findBySeminar_id($this->course_id)) > 0;

            // Check if course has single dates, not belonging to a regular cycle.
            $dates = CourseDate::findBySeminar_id($this->course_id);
            $this->has_singledates = count(array_filter($dates, function ($d) {
                return !((bool) $d->metadate_id);
            })) > 0;

            // Check if course has topics.
            $this->has_topics = count(CourseTopic::findBySeminar_id($this->course_id)) > 0;
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Adds the current user to the given group.
     *
     * @throws Trails_Exception 403 if current user may not join the given group.
     */
    public function join_action($group_id = '')
    {

        // group_id can also be given per request.
        if (!$group_id) {
            CSRFProtection::verifyUnsafeRequest();
            $group_id = Request::option('target_group');

            // Safety check if no group_id at all.
            if (!$group_id) {
                throw new Trails_Exception(400);
            }
        }

        $g = Statusgruppen::find($group_id);

        if ($g->userMayJoin($GLOBALS['user']->id)) {
            $s = new StatusgruppeUser();
            $s->user_id = $GLOBALS['user']->id;
            $s->statusgruppe_id = $group_id;
            if ($s->store()) {
                PageLayout::postSuccess(sprintf(
                    _('Sie wurden als Mitglied der Gruppe %s eingetragen.'), htmlReady($g->name)));
            } else {
                PageLayout::postError(sprintf(
                    _('Sie konnten nicht als Mitglied der Gruppe %s eingetragen werden.'), htmlReady($g->name)));
            }
        } else {
            throw new Trails_Exception(403);
        }

        $this->relocate('course/statusgroups');
    }

    /**
     * Removes the current user from the given group.
     *
     * @throws Trails_Exception 403 if current user may not join the given group.
     */
    public function leave_action($group_id)
    {

        $g = Statusgruppen::find($group_id);

        if ($g->userMayLeave($GLOBALS['user']->id)) {
            $s = StatusgruppeUser::find(array($group_id, $GLOBALS['user']->id));
            if ($s->delete()) {
                PageLayout::postSuccess(sprintf(
                    _('Sie wurden aus der Gruppe %s ausgetragen.'), htmlReady($g->name)));
            } else {
                PageLayout::postError(sprintf(
                    _('Sie konnten nicht aus der Gruppe %s ausgetragen werden.'), htmlReady($g->name)));
            }
        } else {
            throw new Trails_Exception(403);
        }

        $this->relocate('course/statusgroups');
    }

    /**
     * Batch creation of statusgroups according to given settings.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_create_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();

            // Create a number of groups, sequentially named.
            if (Request::option('mode') == 'numbering') {

                $counter = 0;
                for ($i = 0 ; $i < Request::int('number') ; $i++) {
                    $group = StatusgroupsModel::updateGroup('', Request::get('prefix').' '.
                        (Request::int('startnumber', 1) + $i),
                        $counter + 1, $this->course_id, Request::int('size', 0),
                        Request::int('selfassign', 0) + Request::int('exclusive', 0),
                        strtotime(Request::get('selfassign_start', 'now')),
                        strtotime(Request::get('selfassign_end', 0)),
                        Request::int('makefolder', 0));
                    $counter++;
                }
                PageLayout::postSuccess(sprintf(
                    ngettext('Eine Gruppe wurde angelegt.', '%u Gruppen wurden angelegt.', $counter),
                    $counter));

            // Create groups by course metadata, like topics, dates or lecturers.
            } else if (Request::option('mode') == 'coursedata') {

                switch (Request::option('createmode')) {

                    // Create groups per topic.
                    case 'topics':
                        $topics = SimpleCollection::createFromArray(
                            CourseTopic::findBySeminar_id($this->course_id))->orderBy('priority');
                        $counter = 0;

                        foreach ($topics as $t) {
                            $group = StatusgroupsModel::updateGroup('', _('Thema:').' '.$t->title,
                                $t->priority, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
                                Request::int('makefolder', 0));

                            // Connect group to dates that are assigned to the given topic.
                            $dates = CourseDate::findByIssue_id($t->id);
                            foreach ($dates as $d) {
                                $d->statusgruppen->append($group);
                                $d->store();
                            }

                            $counter++;
                        }

                        PageLayout::postSuccess(sprintf(
                            ngettext('Eine Gruppe wurde angelegt.', '%u Gruppen wurden angelegt.', count($topics)),
                            $counter));
                        break;

                    // Create groups per (regular and irregular) dates.
                    case 'dates':

                        // Find regular cycles first and create corresponding groups.
                        $cycles = SimpleCollection::createFromArray(
                            SeminarCycleDate::findBySeminar_id($this->course_id));

                        $counter = 0;
                        foreach ($cycles as $c) {
                            $cd = new CycleData($c);

                            $name = $c->toString();

                            // Append description to group title if applicable.
                            if ($c->description) {
                                $name .= ' ' . mila($c->description, 30);
                            }

                            // Get name of most used room and append to group title.
                            if ($rooms = $cd->getPredominantRoom()) {
                                $room_name = DBManager::get()->fetchOne(
                                    "SELECT `name` FROM `resources_objects` WHERE `resource_id` = ?",
                                    array(array_pop(array_keys($rooms))));
                                $name .= ' (' . $room_name['name'] . ')';
                            } else {
                                $room = trim(array_pop(array_keys($cd->getFreeTextPredominantRoom())));
                                if ($room) {
                                    $name .= ' (' . $room . ')';
                                }
                            }

                            $group = StatusgroupsModel::updateGroup('', $name,
                                $counter + 1, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
                                Request::int('makefolder', 0));

                            // Connect group to dates that are assigned to the given cycle.
                            foreach ($c->dates as $d) {
                                $d->statusgruppen->append($group);
                                $d->store();
                            }

                            $counter++;
                        }

                        // Now find irregular dates and create groups.
                        $dates = CourseDate::findBySeminar_id($this->course_id);
                        $singledates = array_filter($dates, function ($d) { return !((bool) $d->metadate_id); });
                        foreach ($singledates as $d) {
                            $name = $d->toString();

                            // Append description to group title if applicable.
                            if ($d->description) {
                                $name .= ' ' . mila($d->description, 30);
                            }

                            // Get room name and append to group title.
                            if ($room = $d->getRoomName()) {
                                $name .= ' (' . $room . ')';
                            }

                            $group = StatusgroupsModel::updateGroup('', $name,
                                $counter + 1, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
                                Request::int('makefolder', 0));

                            $d->statusgruppen->append($group);
                            $d->store();

                            $counter++;
                        }

                        PageLayout::postSuccess(sprintf(
                            ngettext('Eine Gruppe wurde angelegt.', '%u Gruppen wurden angelegt.', $counter),
                            $counter));
                        break;


                    // Create groups per lecturer.
                    case 'lecturers':
                        $lecturers = SimpleCollection::createFromArray(
                            CourseMember::findByCourseAndStatus($this->course_id, 'dozent'))->orderBy('position');
                        $counter = 0;

                        foreach ($lecturers as $l) {
                            StatusgroupsModel::updateGroup('', $l->getUserFullname('full'),
                                $l->position, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
                                Request::int('makefolder', 0));
                            $counter++;
                        }

                        PageLayout::postSuccess(sprintf(
                            ngettext('Eine Gruppe wurde angelegt.', '%u Gruppen wurden angelegt.', count($lecturers)),
                            $counter));
                        break;
                }

            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Batch action for several groups or group members at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_action_action()
    {
        if ($this->is_tutor) {

            // Actions for selected groups.
            if (Request::submitted('batch_groups')) {
                if ($groups = Request::getArray('groups')) {
                    $this->groups = SimpleCollection::createFromArray(
                        Statusgruppen::findMany($groups))->orderBy('position, name');
                    switch (Request::option('groups_action')) {
                        case 'edit':
                            PageLayout::setTitle(_('Einstellungen bearbeiten'));
                            $this->edit = true;
                            $sizes = array();
                            $selfassign = 0;
                            $exclusive = 0;
                            $selfassign_start = array();
                            $selfassign_end = array();

                            // Check for diverging values on all groups.
                            foreach ($this->groups as $group) {
                                $sizes[$group->size] = true;
                                if ($group->selfassign == 1) {
                                    $selfassign++;
                                }
                                if ($group->selfassign == 2) {
                                    $selfassign++;
                                    $exclusive++;
                                }
                                if ($group->selfassign_start) {
                                    $selfassign_start[$group->selfassign_start] = true;
                                }
                                if ($group->selfassign_end) {
                                    $selfassign_end[$group->selfassign_end] = true;
                                }
                            }

                            // Get default group size
                            $this->size = max(array_keys($sizes));

                            // Only one entry => all groups have same size.
                            if (count($sizes) == 1) {
                                $this->different_sizes = 0;
                            } else {
                                $this->different_sizes = 1;
                            }

                            // Selfassign enabled for all selected groups?
                            if ($selfassign == 0) {
                                $this->selfassign = 0;
                            } else if ($selfassign == count($groups)) {
                                $this->selfassign = 1;
                            } else {
                                $this->selfassign = -1;
                            }

                            // Exclusive entry set for all selected groups?
                            if ($exclusive == 0) {
                                $this->exclusive = 0;
                            } else if ($exclusive == count($groups)) {
                                $this->exclusive = 1;
                            } else {
                                $this->exclusive = -1;
                            }

                            // Selfassign start time set for all selected groups?
                            if (count($selfassign_start) == 1) {
                                // Just one entry, take it as value for all.
                                $start = array_pop(array_keys($selfassign_start));
                                $this->selfassign_start = $start ? date('d.m.Y H:i', $start) : date('d.m.Y H:i');
                            } else {
                                // Different entries, mark this.
                                $this->selfassign_start = -1;
                            }

                            // Selfassign end time set for all selected groups?
                            if (count($selfassign_end) == 1) {
                                // Just one entry, take it as value for all.
                                $end = array_pop(array_keys($selfassign_end));
                                $this->selfassign_end = $end ? date('d.m.Y H:i', $end) : date('d.m.Y H:i');
                            } else {
                                // Different entries, mark this.
                                $this->selfassign_end = -1;
                            }

                            break;
                        case 'delete':
                            PageLayout::setTitle(_('Gruppe(n) löschen?'));
                            $this->askdelete = true;
                            break;
                        default:
                            $this->relocate('course/statusgroups');
                    }
                } else {
                    PageLayout::postError(_('Sie haben keine Gruppe ausgewählt.'));
                }
            // Actions for selected group members.
            } else if (Request::submitted('batch_members')) {
                // Which group is selected?
                $group_id = key(Request::getArray('batch_members'));

                // Get selected group members.
                $group = Request::getArray('group');
                $this->members = array_keys($group[$group_id]);

                // Get selected action for group members.
                $actions = Request::getArray('members_action');
                $action = $actions[$group_id];

                switch ($action) {
                    case 'move':
                        PageLayout::setTitle(_('Gruppenmitglieder verschieben'));
                        $this->movemembers = true;
                        $this->source_group = $group_id;
                        // Find possible target groups.
                        $this->target_groups = SimpleCollection::createFromArray(
                            Statusgruppen::findByRange_id($this->course_id))
                            ->orderBy('position, name')
                            ->filter(function ($g) use ($group_id) { return $g->id != $group_id; });
                        break;
                    case 'delete':
                        PageLayout::setTitle(_('Gruppenmitglieder entfernen'));
                        $this->deletemembers = true;
                        $this->source_group = Statusgruppen::find($group_id);
                        break;
                }

            }

        } else {
            // Non-tutors may not access this.
            throw new Trails_Exception(403);
        }
    }

    /**
     * Deletes several groups at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_delete_groups_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $groups = Statusgruppen::findMany(Request::getArray('groups'));
            foreach ($groups as $g) {
                $g->delete();
            }
            PageLayout::postSuccess(_('Die ausgewählten Gruppen wurden gelöscht.'));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Sets data for several groups at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_save_groups_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $groups = Statusgruppen::findMany(Request::getArray('groups'));
            foreach ($groups as $g) {
                StatusgroupsModel::updateGroup($g->id, $g->name,
                    $g->position, $this->course_id,
                    Request::int('size', 0),
                    Request::int('selfassign', 0) + Request::int('exclusive', 0),
                    strtotime(Request::get('selfassign_start', 'now')),
                    strtotime(Request::get('selfassign_end', 0)),
                    false);
            }
            PageLayout::postSuccess(_('Die Einstellungen der ausgewählten Gruppen wurden gespeichert.'));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Moves selected group members to another group.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_move_members_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $success = 0;
            $error = 0;
            $members = Request::getArray('members');
            foreach ($members as $m) {

                $stored = false;

                // Add user to target statusgroup (if not already in there).
                if (!StatusgruppeUser::exists(array(Request::option('target_group'), $m))) {
                    $s = new StatusgruppeUser();
                    $s->user_id = $m;
                    $s->statusgruppe_id = Request::option('target_group');
                    if ($s->store()) {
                        $stored = true;
                    }
                }

                // Delete old group membership.
                $source = Request::option('source');
                if ($stored) {
                    if ($source != 'nogroup') {
                        $old = StatusgruppeUser::find(array($source, $m));
                        if ($old->delete()) {
                            $success++;
                        } else {
                            $error++;
                        }
                    } else {
                        $success++;
                    }
                }
            }
            $groupname = Statusgruppen::find(Request::option('target_group'))->name;

            // Everything completed successfully => success message.
            if ($success && !$error) {
                PageLayout::postSuccess(sprintf(ngettext('%u Person wurde in die Gruppe %s verschoben.',
                    '%u Personen wurden in die Gruppe %s verschoben.',
                    $success), $success, htmlReady($groupname)));

            // Some entries worked, some didn't => warning message.
            } else if ($success && $error) {
                PageLayout::postWarning(
                    sprintf(ngettext('%u Person wurde in die Gruppe %s verschoben.',
                    '%u Personen wurden in die Gruppe %s verschoben.',
                    $success), $success, htmlReady($groupname)) . '<br>' .
                    sprintf(ngettext('%u Person konnte nicht in die Gruppe %s verschoben werden.',
                        '%u Personen konnten nicht in die Gruppe %s verschoben werden.',
                        $error), $error, htmlReady($groupname))
                );

            // All is lost => error message.
            } else if ($error) {
                PageLayout::postError(sprintf(ngettext('%u Person konnte nicht in die Gruppe %s verschoben werden.',
                    '%u Personen konnten nicht in die Gruppe %s verschoben werden.',
                    $error), $error, htmlReady($groupname)));
            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Removes selected group members from given group.
     *
     * @param String $group_id group to remove members from.
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_delete_members_action($group_id)
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $success = 0;
            $error = 0;
            $members = Request::getArray('members');
            foreach ($members as $m) {

                // Remove user from target statusgroup.
                $s = StatusgruppeUser::find(array($group_id, $m));
                if ($s->delete()) {
                    $success++;
                } else {
                    $error++;
                }

            }
            $groupname = Statusgruppen::find($group_id)->name;

            // Everything completed successfully => success message.
            if ($success && !$error) {
                PageLayout::postSuccess(sprintf(ngettext('%u Person wurde aus der Gruppe %s entfernt.',
                    '%u Personen wurden aus der Gruppe %s entfernt.',
                    $success), $success, htmlReady($groupname)));

            // Some entries worked, some didn't => warning message.
            } else if ($success && $error) {
                PageLayout::postWarning(
                    sprintf(ngettext('%u Person wurde aus der Gruppe %s entfernt.',
                        '%u Personen wurden aus der Gruppe %s entfernt.',
                        $success), $success, htmlReady($groupname)) . '<br>' .
                    sprintf(ngettext('%u Person konnte nicht aus der Gruppe %s entfernt werden.',
                        '%u Personen konnten nicht aus der Gruppe %s entfernt werden.',
                        $error), $error, htmlReady($groupname))
                );

            // All is lost => error message.
            } else if ($error) {
                PageLayout::postError(sprintf(ngettext('%u Person konnte nicht aus der Gruppe %s entfernt werden.',
                    '%u Personen konnten nicht aus der Gruppe %s entfernt werden.',
                    $error), $error, htmlReady($groupname)));
            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    public function sortgroups_action()
    {
        if ($this->is_tutor && !$this->is_locked) {
            if (Request::submitted('order')) {
                $ordered = studip_json_decode(Request::get('ordering'));
                if (is_array($ordered)) {
                    $ok = false;
                    foreach ($ordered as $p => $g) {
                        if ($group = Statusgruppen::find($g['id'])) {
                            $group->position = $p + 1;
                            $ok += $group->store();
                        }
                    }
                    if ($ok) {
                        PageLayout::postSuccess(_('Die Gruppenreihenfolge wurde gespeichert.'));
                    }
                    return $this->redirect($this->url_for('/index'));
                }
            }
            $this->groups = Statusgruppen::findBySeminar_id($this->course_id);
        }
    }
}
