<?php

class AdminCourseFilter {

    static protected $instance = null;
    public $settings = array();

    /**
     * returns an AdminCourseFilter singleton object
     * @return AdminCourseFilter or derived-class object
     */
    static public function get($reset_settings = false) {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class($reset_settings);
        }
        return self::$instance;
    }

    /**
     * Constructor of the singleton-object. The settings might come from the session
     * if $reset_settings is false.
     * @param bool $reset_settings : should the session settings of the singleton be reset?
     */
    public function __construct($reset_settings = false)
    {
        $this->settings = array();

        $this->settings['query']['select'] = array(
            'Institut' => "Institute.Name",
            'teilnehmer' => "COUNT(seminar_user.user_id)",
            'prelim' => "(SELECT COUNT(seminar_id)
                          FROM admission_seminar_user
                          WHERE seminar_id = seminare.Seminar_id AND status = 'accepted')",
            'waiting' => "(SELECT COUNT(seminar_id)
                          FROM admission_seminar_user
                          WHERE seminar_id = seminare.Seminar_id AND status = 'awaiting')"
        );
        $this->settings['query']['joins'] = array(
            'Institute' => array(
                'join' => "INNER JOIN",
                'on' => "seminare.Institut_id = Institute.Institut_id"
            ),
            'seminar_user' => array(
                'join' => "LEFT JOIN",
                'on' => "seminare.seminar_id=seminar_user.seminar_id AND seminar_user.status != 'dozent' and seminar_user.status != 'tutor'"
            ),
            'sem_types' => array(
                'join' => "LEFT JOIN",
                'on' => "sem_types.id = seminare.status"
            ),
            'sem_classes' => array(
                'join' => "LEFT JOIN",
                'on' => "sem_classes.id = sem_types.class"
            )
        );
        $this->settings['query']['where'] = array();
        $this->settings['query']['orderby'] = "seminare.name";

        if ($_SESSION['AdminCourseFilter_settings'] && !$reset_settings) {
            $this->settings = $_SESSION['AdminCourseFilter_settings'];
        }
    }

    /**
     * Adds a filter for all courses of the given semester.
     * @param string $semester_id : ID of the given semester.
     * @return $this
     * @throws Exception if semester_id does not exist
     */
    public function filterBySemester($semester_id) {
        $semester = Semester::find($semester_id);
        if (!$semester) {
            throw new Exception("Das ausgew�hlte Semester scheint nicht zu existieren.");
        }
        $this->settings['query']['where']['semester'] = "(seminare.start_time <= :semester_beginn AND ((:semester_beginn <= seminare.start_time + seminare.duration_time) OR (seminare.duration_time = -1)))";
        $this->settings['parameter']['semester_beginn'] = $semester['beginn'];
        return $this;
    }

    /**
     * Adds a filter for a sem_type or many sem_types if the parameter is an array.
     * @param array|integer $type : id or ids of sem_types
     * @return $this
     */
    public function filterByType($type) {
        if (is_array($type)) {
            $this->settings['query']['where']['status'] = "seminare.status IN (:types)";
            $this->settings['parameter']['types'] = $type;
        } else {
            $this->settings['query']['where']['status'] = "seminare.status = :type";
            $this->settings['parameter']['type'] = (int) $type;
        }
        return $this;
    }

    /**
     * Adds a filter for an institut_id or many institut_ids if the parameter is an array.
     * @param array|integer $institut_ids : id or ids of institutes
     * @return $this
     */
    public function filterByInstitute($institut_ids) {
        if (is_array($institut_ids)) {
            $this->settings['query']['where']['institute'] = "seminare.Institut_id IN (:institut_ids)";
            $this->settings['parameter']['institut_ids'] = $institut_ids;
        } else {
            $this->settings['query']['where']['status'] = "seminare.Institut_id = :institut_id";
            $this->settings['parameter']['institut_id'] = (string) $institut_ids;
        }
        return $this;
    }

    /**
     * Adds a filter for a textstring, that can be the coursenumber, the name of the course
     * or the last name of one of the dozenten.
     * @param string $text : the searchstring
     * @return $this
     */
    public function filterBySearchstring($text) {
        $this->settings['query']['joins']['dozenten'] = array(
            'join' => "INNER JOIN",
            'table' => "seminar_user",
            'on' => "dozenten.Seminar_id = seminare.Seminar_id AND dozenten.status = 'dozent'"
        );
        $this->settings['query']['joins']['dozentendata'] = array(
            'join' => "INNER JOIN",
            'table' => "auth_user_md5",
            'on' => "dozenten.user_id = dozentendata.user_id"
        );
        $this->settings['query']['where']['search'] = "CONCAT(seminare.VeranstaltungsNummer, ' ', seminare.name, ' ', dozentendata.Nachname) LIKE :search";
        $this->settings['parameter']['search'] = "%".$text."%";
        return $this;
    }

    /**
     * @param string $attribute : column, name of the column, yb whcih we should order the results
     * @param string $flag : "ASC" or "DESC for ascending order or descending order,
     * @return $this
     * @throws Exception if $flag does not exist
     */
    public function orderBy($attribute, $flag = "ASC") {
        if (!in_array($flag, words("ASC DESC"))) {
            throw new Exception("Sortierreihenfolge undefiniert.");
        }
        if (in_array($attribute, words('VeranstaltungsNummer Name status teilnehmer waiting prelim')) && in_array($flag, words("ASC, DESC"))) {
            $this->settings['query']['orderby'] = $attribute." ".$flag;
        }
        return $this;
    }

    /**
     * Adds a where filter.
     * @param string $where : any where condition like "sem_classes.overview = 'CoreOverview'"
     * @param array $parameter : an array of parameter that appear in the $where query.
     * @param null|string $id : an id of the where-query. Use this to possibly
     *                          avoid double where conditions or allow deleting the condition
     *                          by plugins if necessary. Can be omitted.
     * @return $this
     */
    public function where($where, $parameter = array(), $id = null) {
        if (!$id) {
            $id = md5($where);
        }
        $this->settings['query']['where'][$id] = $where;
        $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $parameter);
        return $this;
    }

    /**
     * Returns the data of the resultset of the AdminCourseFilter.
     * Also saves the settings in the session.
     * Note that a notification AdminCourseFilterWillQuery will be posted, before the result is computed.
     * Plugins may register at this event to fully alter this AdminCourseFilter-object and so the resultset.
     * @return array : associative array with seminar_ids as keys and seminar-data-arrays as values.
     */
    public function getCourses()
    {
        NotificationCenter::postNotification("AdminCourseFilterWillQuery", $this);
        $statement = DBManager::get()->prepare($this->createQuery());
        $statement->execute($this->settings['parameter']);
        $_SESSION['AdminCourseFilter_settings'] = $this->settings;
        return $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
    }

    /**
     * Creates the sql-query from the $this->settings['query']
     * @return string : the big query
     */
    public function createQuery()
    {
        $select_query = "seminare.* ";
        foreach ((array) $this->settings['query']['select'] as $alias => $select) {
            $select_query .= ", ".$select." AS ".$alias." ";
        }

        $join_query = "";
        foreach ((array) $this->settings['query']['joins'] as $alias => $joininfo) {
            $table = isset($joininfo['table']) ? $joininfo['table']." AS ".$alias : $alias;
            $on = isset($joininfo['on']) ? " ON (".$joininfo['on'].")" : "";
            $join_query .= " ".(isset($joininfo['join']) ? $joininfo['join'] : "INNER JOIN")." ".$table.$on." ";
        }

        $where_query = "";
        if (count($this->settings['query']['where']) > 0) {
            $where_query .= implode(" AND ", $this->settings['query']['where']);
        }

        $query = "
            SELECT ".$select_query."
            FROM seminare
                ".$join_query."
            ".($where_query ? "WHERE ".$where_query : "")."
            GROUP BY seminare.Seminar_id
            ORDER BY ".$this->settings['query']['orderby'].($this->settings['query']['orderby'] !== "seminare.name" ? ", seminare.name" : "")."
        ";
        return $query;
    }

}