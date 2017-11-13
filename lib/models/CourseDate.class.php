<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string termin_id database column
 * @property string id alias column for termin_id
 * @property string range_id database column
 * @property string autor_id database column
 * @property string content database column
 * @property string description database column
 * @property string date database column
 * @property string end_time database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string date_typ database column
 * @property string topic_id database column
 * @property string raum database column
 * @property string metadate_id database column
 * @property User author belongs_to User
 * @property Course course belongs_to Course
 * @property SeminarCycleDate cycle belongs_to SeminarCycleDate
 * @property ResourceAssignment room_assignment has_one ResourceAssignment
 * @property SimpleORMapCollection topics has_and_belongs_to_many CourseTopic
 * @property SimpleORMapCollection statusgruppen has_and_belongs_to_many Statusgruppen
 * @property SimpleORMapCollection dozenten has_and_belongs_to_many User
 */

class CourseDate extends SimpleORMap
{
    const FORMAT_DEFAULT = 'default';
    const FORMAT_VERBOSE = 'verbose';

    /**
     * Returns course dates by issue id.
     *
     * @param String $issue_id Id of the issue
     * @return array with the associated dates
     */
    public static function findByIssue_id($issue_id)
    {
        return self::findBySQL("INNER JOIN themen_termine USING (termin_id)
            WHERE themen_termine.issue_id = ?
            ORDER BY date ASC",
            array($issue_id)
        );
    }

    /**
     * Returns course dates by course id
     *
     * @param String $seminar_id Id of the course
     * @return array with the associated dates
     */
    public static function findBySeminar_id($seminar_id)
    {
        return self::findByRange_id($seminar_id);
    }

    /**
     * Return course dates by range id (which is in many cases the course id)
     *
     * @param String $seminar_id Id of the course
     * @param String $order_by   Optional order definition
     * @return array with the associated dates
     */
    public static function findByRange_id($seminar_id, $order_by = 'ORDER BY date')
    {
        return parent::findByRange_id($seminar_id, $order_by);
    }

    /**
     * Returns course dates by issue id.
     *
     * @param String $issue_id Id of the issue
     * @return array with the associated dates
     */
    public static function findByStatusgruppe_id($group_id)
    {
        return self::findBySQL("INNER JOIN `termin_related_groups` USING (`termin_id`)
            WHERE `termin_related_groups`.`statusgruppe_id` = ?
            ORDER BY `date` ASC",
            array($group_id)
        );
    }

    /**
     * Configures this model.
     *
     * @param Array $config Configuration array
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'termine';
        $config['has_and_belongs_to_many']['topics'] = array(
            'class_name' => 'CourseTopic',
            'thru_table' => 'themen_termine',
            'order_by' => 'ORDER BY priority',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_and_belongs_to_many']['statusgruppen'] = array(
            'class_name' => 'Statusgruppen',
            'thru_table' => 'termin_related_groups',
            'order_by' => 'ORDER BY position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_and_belongs_to_many']['dozenten'] = array(
            'class_name' => 'User',
            'thru_table' => 'termin_related_persons',
            'foreign_key' => 'termin_id',
            'thru_key' => 'range_id',
            'order_by' => 'ORDER BY Nachname, Vorname',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['belongs_to']['author'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'autor_id'
        );
        $config['belongs_to']['course'] = array(
            'class_name'  => 'Course',
            'foreign_key' => 'range_id'
        );
        $config['belongs_to']['cycle'] = array(
            'class_name'  => 'SeminarCycleDate',
            'foreign_key' => 'metadate_id'
        );
        $config['has_one']['room_assignment'] = array(
            'class_name'  => 'ResourceAssignment',
            'foreign_key' => 'termin_id',
            'assoc_foreign_key' => 'assign_user_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_one']['room_request'] = array(
            'class_name'        => 'RoomRequest',
            'assoc_foreign_key' => 'termin_id',
            'on_delete'          => 'delete',
        );
        $config['default_values']['date_typ'] = 1;
        parent::configure($config);
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->registerCallback('before_store after_create', 'cbStudipLog');
    }

    /**
     * Adds a topic to this date.
     *
     * @param mixed $topic Topic definition (might be an id, an array or an
     *                     object)
     * @return number addition of all return values, false if none was called
     */
    public function addTopic($topic)
    {
        $topic = CourseTopic::toObject($topic);
        if (!$this->topics->find($topic->id)) {
            $this->topics[] = $topic;
            return $this->storeRelations('topics');
        }
    }

    /**
     * Removes a topic from this date.
     *
     * @param mixed $topic Topic definition (might be an id, an array or an
     *                     object)
     * @return number addition of all return values, false if none was called
     */
    public function removeTopic($topic)
    {
        $this->topics->unsetByPk(is_string($topic) ? $topic : $topic->id);
        return $this->storeRelations('topics');
    }

    /**
     * Returns the name of the assigned room for this date.
     *
     * @return String containing the room name
     */
    public function getRoomName()
    {
        if (Config::get()->RESOURCES_ENABLE && $this->room_assignment->resource_id) {
            return $this->room_assignment->resource->getName();
        }
        return $this['raum'];
    }

    /**
     * Returns the assigned room for this date as an object.
     *
     * @return mixed Either the object or null if no room is assigned
     */
    public function getRoom()
    {
        if (Config::get()->RESOURCES_ENABLE && $this->room_assignment->resource_id) {
           return $this->room_assignment->resource;
        }
        return null;
    }

    /**
     * Returns the name of the type of this date.
     *
     * @param String containing the type name
     */
    public function getTypeName()
    {
        return $GLOBALS['TERMIN_TYP'][$this->date_typ]['name'];
    }

    /**
     * Returns the full qualified name of this date.
     *
     * @param String $format Optional format type (only 'default' and
     *                       'verbose' are supported by now)
     * @return String containing the full name of this date.
     */
    public function getFullname($format = 'default')
    {
        if (!$this->date || !in_array($format, ['default', 'verbose'])) {
            return '';
        }

        $latter_template = $format === 'verbose'
                         ? _('%R Uhr')
                         : '%R';

        if (($this->end_time - $this->date) / 60 / 60 > 23) {
            return strftime('%a., %x (' . _('ganztägig') . ')' , $this->date);
        }

        return strftime('%a., %x, %R', $this->date) . ' - '
             . strftime($latter_template, $this->end_time);
    }

    /**
     * Returns the full qualified name of this date
     * raumzeit_send_cancel_message needs the toString()-Method in this class
     *
     * @deprecated since version 3.4
     * @return String containing the full name of this date
     */
    public function toString()
    {
        return $this->getFullname();
    }

    /**
     * Converts a CourseDate Entry to a CourseExDate Entry
     * returns instance of the new CourseExDate or NULL
     *
     * @return Object CourseExDate
     */
    public function cancelDate()
    {
        $date = $this->toArray();

        $ex_date = new CourseExDate();
        $ex_date->setData($date);
        if ($room = $this->getRoom()) {
            $ex_date['resource_id'] = $room->getId();
        }
        $ex_date->setId($ex_date->getNewId());

        if ($ex_date->store()) {
            $this->delete();
            return $ex_date;
        }
        return null;
    }

    /**
     * saves this object and expires the cache
     *
     * @see SimpleORMap::store()
     */
    public function store()
    {
        // load room-assignment, if any
        $this->room_assignment;

        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/'. $this->range_id);
        return parent::store();
    }

    /**
     * deletes this object and expires the cache
     *
     * @see SimpleORMap::delete()
     */
    public function delete()
    {
        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/'. $this->range_id);
        return parent::delete();
    }

    /**
     * @param $type string type of callback
     */
    protected function cbStudipLog($type)
    {
        if (!$this->metadate_id) {
            if ($type == 'after_create') {
                StudipLog::log('SEM_ADD_SINGLEDATE', $this->range_id, $this->getFullname());
            }
            if ($type == 'before_store' && !$this->isNew() && ($this->isFieldDirty('date') || $this->isFieldDirty('end_time'))) {
                $old_entry = self::build($this->content_db);
                StudipLog::log('SINGLEDATE_CHANGE_TIME', $this->range_id, $this->getFullname(), $old_entry->getFullname() . ' -> ' . $this->getFullname());
            }
        }
    }
}
