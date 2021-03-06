<?php
/**
 * DatafieldEntryModel
 * model class for table datafields_entries
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string datafield_id database column
 * @property string range_id database column
 * @property string content database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string sec_range_id database column
 * @property string name computed column read/write
 * @property string id computed column read/write
 * @property Datafield datafield belongs_to Datafield
 */

class DatafieldEntryModel extends SimpleORMap
{
    /**
     * returns datafields belonging to given model
     * if a datafield entry not exists yet, a new DatafieldEntryModel is returned
     * second param filters for a given datafield id
     *
     * @param SimpleORMap $model Course,Institute,User,CourseMember or InstituteMember
     * @param string $datafield_id
     * @return array of DatafieldEntryModel
     */
    public static function findByModel(SimpleORMap $model, $datafield_id = null)
    {
        $mask = array("user" => 1, "autor" => 2, "tutor" => 4, "dozent" => 8, "admin" => 16, "root" => 32);

        // is the module administration enabled?
        $mv_plugin = PluginEngine::getPlugin('MVVPlugin');
        
        if (is_a($model, "Course")) {
            $object_class = SeminarCategories::GetByTypeId($model->status)->id;
            $object_type = 'sem';
            $range_id = $model->getId();
        } elseif(is_a($model, "Institute")) {
            $object_class = $model->type;
            $object_type = 'inst';
            $range_id = $model->getId();
        } elseif(is_a($model, "User")) {
            $object_class = $mask[$model->perms];
            $object_type = 'user';
            $range_id = $model->getId();
        } elseif(is_a($model, "CourseMember")) {
            $object_class = $mask[$model->status];
            $object_type = 'usersemdata';
            $range_id = $model->user_id;
            $sec_range_id = $model->seminar_id;
        } elseif(is_a($model, "InstituteMember")) {
            $object_class = $mask[$model->inst_perms];
            $object_type = 'userinstrole';
            $range_id = $model->user_id;
            $sec_range_id = $model->institut_id;
        } elseif ($mv_plugin && is_a($model, 'ModulDeskriptor')) {
            $object_class = $model->sprache;
            $object_type = 'moduldeskriptor';
            $range_id = $model->deskriptor_id;
        } elseif ($mv_plugin && is_a($model, 'ModulteilDeskriptor')) {
            $object_class = $model->sprache;
            $object_type = 'modulteildeskriptor';
            $range_id = $model->deskriptor_id;
        } elseif ($model instanceof StatusgruppeUser) {
            $object_class = 255;
            $object_type = 'userinstrole';
            $range_id = $model->user_id;
            $sec_range_id = $model->statusgruppe_id;
        }

        if (!$object_type) {
            throw new InvalidArgumentException('Wrong type of model: ' . get_class($model));
        }
        if ($datafield_id !== null) {
            $one_datafield = " AND a.datafield_id = " .DBManager::get()->quote($datafield_id);
        }
        
        $query = "SELECT a.*, b.*,a.datafield_id,b.datafield_id as isset_content ";
        $query .= "FROM datafields a LEFT JOIN datafields_entries b ON (a.datafield_id=b.datafield_id AND range_id = ? AND sec_range_id = ?) ";
        $query .= "WHERE object_type = ? ";
        
        if ($object_type === 'moduldeskriptor' || $object_type === 'modulteildeskriptor') {
            // find datafields by language (string)
            $query .= "AND (LOCATE(?, object_class) OR object_class IS NULL) $one_datafield ORDER BY priority";
            $params = array(
                (string) $range_id,
                (string) $sec_range_id,
                $object_type,
                (string) $object_class);
        } else {
            // find datafields by perms or status (int)
            $query .= "AND ((object_class & ?) OR object_class IS NULL) $one_datafield ORDER BY priority";
            $params = array(
                (string) $range_id,
                (string) $sec_range_id,
                $object_type,
                (int) $object_class);
        }
        
        $st = DBManager::get()->prepare($query);
        $st->execute($params);
        $ret = array();
        $c = 0;
        $df_entry = new DatafieldEntryModel();
        $df = new DataField();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $ret[$c] = clone $df_entry;
            $ret[$c]->setData($row, true);
            if (!$row['isset_content']) {
                $ret[$c]->setValue('range_id', (string)$range_id);
                $ret[$c]->setValue('sec_range_id', (string)$sec_range_id);
            }
            $ret[$c]->setNew(!$row['isset_content']);
            $cloned_df = clone $df;
            $cloned_df->setData($row, true);
            $cloned_df->setNew(false);
            $ret[$c]->setValue('datafield', $cloned_df);
            ++$c;
        }
        return $ret;
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'datafields_entries';
        $config['belongs_to']['datafield'] = array(
            'class_name' => 'DataField',
            'foreign_key' => 'datafield_id'
        );
        $config['additional_fields']['name'] = array('datafield', 'name');
        parent::configure($config);
    }

    /**
     * returns matching "old-style" DataFieldEntry object
     *
     * @return DataFieldEntry
     */
    public function getTypedDatafield()
    {
        $range_id = $this->sec_range_id
                  ? [$this->range_id, $this->sec_range_id]
                  : $this->range_id;

        $df = DataFieldEntry::createDataFieldEntry($this->datafield, $range_id, $this->getValue('content'));
        $observer = function ($event, $object, $user_data) {
            if ($user_data['changed']) {
                $this->restore();
            }
        };
        NotificationCenter::addObserver($observer, '__invoke', 'DatafieldDidUpdate', $df);

        return $df;
    }
}
