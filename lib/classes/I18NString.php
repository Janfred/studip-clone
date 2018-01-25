<?php

/**
 * I18NString class
 */
class I18NString
{
    // Fields we will need
    /**
     * @var string
     */
    /**
     * @var array|null
     */
    /**
     * @var array
     */
    private $base, $lang, $metadata;

    /**
     * Initialize a new I18NString instance.
     *
     * @param string    $base   text in default content language
     * @param array     $lang   text in additional languages
     * @param array     $metadata database info for id, table, field
     */
    public function __construct($base, $lang = null, $metadata = array())
    {
        $this->base = $base;
        $this->lang = $lang;
        $this->metadata = $metadata;
    }

    /**
     * Return the text representation of this object.
     */
    public function __toString()
    {
        $language = $_SESSION['_language'];

        if (isset($language) && $language != key($GLOBALS['CONTENT_LANGUAGES'])) {
            return $this->translation($language) ?: (string)$this->base;
        }

        return (string)$this->base;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setOriginal($text)
    {
        return $this->base = $text;
    }

    /**
     * @param array $lang
     * @return array
     */
    public function setTranslations($lang)
    {
        return $this->lang = $lang;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Return the string in the default content language.
     * @return string
     */
    public function original()
    {
        return $this->base;
    }

    /**
     * Return the string in the specified additional language.
     *
     * @param string
     * @return string
     */
    public function translation($lang)
    {
        return $this->toArray()[$lang];
    }

    /**
     * Return an array containg the text in all additional languages.
     *
     * @return array
     */
    public function toArray()
    {
        if (is_null($this->lang)) {
            $object_id = $this->metadata['object_id'];
            $table = $this->metadata['table'];
            $field =  $this->metadata['field'];
            if (!$table || !$field) {
                throw new RuntimeException('fetching translations not possible, metadata is missing');
            }
            $this->lang = self::fetchDataForField($object_id, $table, $field);
        }
        return $this->lang;
    }

    /**
     * Trim all language strings
     *
     * @param string $symbols All symbols to trim.
     * @return I18NString
     */
    public function trim($symbols = " \t\n\r\0\x0B")
    {
        foreach ($this->lang as &$lang) {
            $lang = trim($lang, $symbols);
            $this->base = trim($this->base, $symbols);
        }
        return $this;
    }

    /**
     * Stores the i18n String manually in the database
     *
     */
    public function storeTranslations()
    {
        if (is_array($this->lang)) {
            $db = DBManager::get();
            $object_id = $this->metadata['object_id'];
            $table = $this->metadata['table'];
            $field = $this->metadata['field'];
            if (!$object_id || !$table || !$field) {
                throw new RuntimeException('store not possible, metadata is missing');
            }
            /* Replace translations */
            $deleted = $db->execute("DELETE FROM i18n WHERE object_id = ? AND `table` = ? AND field = ?", array($object_id, $table, $field));
            $i18nSQL = $db->prepare("INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) VALUES (?,?,?,?,?)");
            foreach ($this->lang as $lang => $value) {
                if (mb_strlen($value)) {
                    $i18nSQL->execute(array($object_id, $table, $field, $lang, (string)$value));
                }
            }
        }
    }

    /**
     *
     */
    public function removeTranslations()
    {
        $this->lang = array();
        $this->storeTranslations();
    }

    /**
     * @param $object_id
     * @param $table
     * @param $field
     * @param null $base
     * @return I18NString
     */
    public static function load($object_id, $table, $field, $base = null)
    {
        $db = DBManager::get();
        if (is_null($base)) {
            // Find pk
            SimpleORMap::tableScheme($table);
            if (count(SimpleORMap::$schemes[$table]['pk']) > 1) {
                throw new RuntimeException(sprintf('table %s has multiple primary key, not implemented yet', $table));
            } else {
                $pk = SimpleORMap::$schemes[$table]['pk'][0];
            }
            $base = $db->fetchColumn("SELECT `$field` FROM `$table` WHERE `$pk` = ?", array($object_id));
        }
        return new self($base, self::fetchDataForField($object_id, $table, $field), compact('object_id', 'table', 'field'));
    }

    /**
     * @param $object_id
     * @param $table
     * @param $field
     * @return array
     */
    public static function fetchDataForField($object_id, $table, $field)
    {
        $db = DBManager::get();
        return $db->fetchPairs("SELECT `lang`, `value` FROM `i18n` WHERE `object_id` = ? AND `table` = ? AND `field` = ?", array($object_id, $table, $field));
    }

    /**
     * @param $object_id
     * @param $table
     * @return array
     */
    public static function fetchDataForRow($object_id, $table)
    {
        $db = DBManager::get();
        return $db->fetchGrouped("SELECT `field`, `lang`, `value` FROM `i18n` WHERE `object_id` = ? AND `table` = ?", array($object_id, $table));
    }
}
