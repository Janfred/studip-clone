<?php

class Utf8Conversion extends Migration
{
    public function description()
    {
        return 'Convert database to utf8mb4';
    }

    public function up()
    {
        // create a separate connection to the db to create the needed function in MySQL
        // since StudipPDO prevents this kind of query
        $pdo = new PDO('mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] .
            ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'] .
            ';charset=utf8',
            $GLOBALS['DB_STUDIP_USER'],
            $GLOBALS['DB_STUDIP_PASSWORD']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // check if the necessary MySQL-settings are present
        $result = $pdo->query("SHOW VARIABLES LIKE 'innodb_file_format'");
        $var = $result->fetch(PDO::FETCH_KEY_PAIR);
        if (mb_strtolower($var['innodb_file_format']) != 'barracuda') {
            throw new Exception('Could not convert Database: You need to set \'innodb_file_format\' = \'Barracuda\'');
        }

        $result = $pdo->query("SHOW VARIABLES LIKE 'innodb_large_prefix'");
        $var = $result->fetch(PDO::FETCH_KEY_PAIR);
        if (mb_strtolower($var['innodb_large_prefix']) != 'on') {
            throw new Exception('Could not convert Database: You need to set \'innodb_large_prefix\' = 1');
        }

        // create a helper-function in MySQL
        $pdo->exec("
        CREATE FUNCTION entity_decode(txt TEXT CHARSET utf8mb4) RETURNS TEXT
            CHARSET utf8mb4
                NO SQL
                DETERMINISTIC
            BEGIN

                DECLARE tmp TEXT    CHARSET utf8mb4 DEFAULT txt;
                DECLARE entity  TEXT CHARSET utf8mb4;
                DECLARE pos1    INT DEFAULT 1;
                DECLARE pos2    INT;
                DECLARE codepoint   INT;

                IF txt IS NULL THEN
                    RETURN NULL;
                END IF;
                LOOP
                    SET pos1 = LOCATE('&#', tmp, pos1);
                    IF pos1 = 0 THEN
                        RETURN tmp;
                    END IF;
                    SET pos2 = LOCATE(';', tmp, pos1 + 2);
                    IF pos2 > pos1 THEN
                        SET entity = SUBSTRING(tmp, pos1, pos2 - pos1 + 1);
                        IF entity REGEXP '^&#[[:digit:]]+;$' THEN
                            SET codepoint = CAST(SUBSTRING(entity, 3, pos2 - pos1 -
            2) AS UNSIGNED);
                            IF codepoint > 31 THEN
                                SET tmp = CONCAT(LEFT(tmp, pos1 - 1), CONVERT(CONVERT(UNHEX(HEX(codepoint)) USING utf32) USING utf8mb4), SUBSTRING(tmp, pos2 + 1));
                            END IF;
                        END IF;
                    END IF;
                    SET pos1 = pos1 + 1;
                END LOOP;
            END
        ");

        // close connection again
        $pdo = null;

        $db = DBManager::get();


        // convert database to utf-8
        $db->exec("ALTER DATABASE `{$GLOBALS['DB_STUDIP_DATABASE']}`
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<pre>\n";

        // convert tables and columns to utf-8
        foreach($db->query("SHOW TABLES")->fetchAll() as $data) {
            try {
                // TODO: check EVERY column for the current collation and keep the correct type (bin, etc.)
                // echo 'Converting table:' . $data[0] ."\n";

                $query = 'ALTER TABLE `'. $data[0] .'` ';
                $change_query = array();
                $table_data = $db->query("SHOW FULL COLUMNS FROM `{$data[0]}`")->fetchAll();

                foreach ($table_data as $column) {
                    $collation = false;

                    // convert index columns to latin1_bin to save space and speed things up
                    if (mb_strpos($column['Type'], 'char') !== false) {
                        $matches = array();
                        preg_match('/char\((.*)\)/', $column['Type'], $matches);

                        if ((int)$matches[1] <= 32) {
                            $charset = 'latin1';
                            $collation = 'latin1_bin';
                        }
                    }

                    if (mb_strpos($column['Type'], 'enum') !== false) {
                        $charset = 'latin1';
                        $collation = 'latin1_bin';
                    }

                    if (!$collation) {
                        if (mb_strpos($column['Collation'], '_bin') !== false) {    // if we hav a bin column, preserve it
                            $charset = 'utf8mb4';
                            $collation = 'utf8mb4_bin';
                        } else if ($column['Collation']) {                          // only convert if there is a collation at all (int f.e. has no collation!)
                            $charset = 'utf8mb4';
                            $collation = 'utf8mb4_unicode_ci';
                        }
                    }

                    if ($collation) {
                        $null = $column['Null'] === 'YES' ? ' NULL' : ' NOT NULL';
                        $default = isset($column['Default']) ? ' DEFAULT ' . $db->quote($column['Default']) : '';
                        $change_query[] = ' CHANGE `'. $column[0] .'` `'. $column[0] .'` '
                           . $column[1] . ' CHARACTER SET '. $charset .' COLLATE ' . $collation . $null . $default;
                   }
                }

                // do all changes at once, or multi-column-indexes will prevent conversion
                $db->exec($query . implode(',', $change_query) .';');

                foreach ($table_data as $column) {
                    if ($column['Collation']) {
                        // convert htmlentities
                        $db->exec("UPDATE `{$data[0]}` SET `{$column['Field']}` = entity_decode(`{$column['Field']}`)");
                    }
                }

                // change default encoding of table itself
                $db->exec($query = "ALTER TABLE `{$data[0]}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            } catch (PDOException $e) {
                echo $query ."\n";
                echo $e->getMessage();
                echo "\n\n";
            }
        }

        // drop helper-function
        $db->exec("DROP FUNCTION entity_decode");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
    }
}
