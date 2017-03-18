<?php

class RefactorConfigLocal extends Migration
{

    public function description()
    {
        return 'Inserts some entries from the config_local.inc.php and config.inc.php into database.';
    }

    public function up()
    {
        $global_boolean_parameter = array(
            "UNI_NAME_CLEAN" => array(
                'description' => "Name der Stud.IP-Installation bzw. Hochschule.",
                'type' => "string"
            ),
            "MEDIA_CACHE_MAX_LENGTH" => array(
                'description' => "Wie große Dateien sollen maximal gecached werden (in Bytes)?",
                'type' => "integer"
            ),
            "MEDIA_CACHE_LIFETIME" => array(
                'description' => "Wieviele Sekunden soll cecached werden?",
                'type' => "integer"
            ),
            "MEDIA_CACHE_MAX_FILES" => array(
                'description' => "Wieviele Dateien sollen maximal cecached werden?",
                'type' => "integer"
            ),
            "XSLT_ENABLE" => array(
                'description' => "Soll Export mit XSLT angeschaltet sein?",
                'type' => "boolean"
            ),
            "FOP_ENABLE" => array(
                'description' => "Soll Export mit FOP erlaubt sein?",
                'type' => "boolean"
            ),
            "EXTERN_SRI_ENABLE" => array(
                'description' => "allow the usage of SRI-interface (Stud.IP Remote Include)",
                'type' => "boolean"
            ),
            "EXTERN_SRI_ENABLE_BY_ROOT" => array(
                'description' => "only root allows the usage of SRI-interface for specific institutes",
                'type' => "boolean"
            ),
            "EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG" => array(
                'description' => "free access to external pages (without the need of a configuration), independent of SRI settings above",
                'type' => "boolean"
            ),
            "SOAP_ENABLE" => array(
                'description' => "Schaltet die SOAP-Schnittstelle an.",
                'type' => "boolean"
            ),
            "SOAP_USE_PHP5" => array(
                'description' => "Sollen PHP-Bibliotheken für SOAP verwendet werden?",
                'type' => "boolean"
            ),
            "ALLOW_SELFASSIGN_STUDYCOURSE" => array(
                'description' => "if true, students are allowed to set or change their studycourse (studiengang)",
                'type' => "boolean"
            ),
            "SHOW_TERMS_ON_FIRST_LOGIN" => array(
                'description' => "if true, the user has to accept the terms on his first login (this feature makes only sense, if you use disable ENABLE_SELF_REGISTRATION).",
                'type' => "boolean"
            ),
            "CONVERT_IDNA_URL" => array(
                'description' => "if true, urls with german \"umlauts\" are converted",
                'type' => "boolean"
            ),
            "USER_VISIBILITY_CHECK" => array(
                'description' => "enable presentation of visibility decision texts for users after first login. see lib/include/header.php and lib/user_visible.inc.php for further info",
                'type' => "boolean"
            ),
            "USERNAME_REGULAR_EXPRESSION" => array(
                'description' => "regex for allowed characters in usernames",
                'type' => "string"
            ),
            "DEFAULT_TIMEZONE" => array(
                'description' => "What timezone should be used (default: Europe/Berlin)?",
                'type' => "string"
            ),
            "DEFAULT_LANGUAGE" => array(
                'description' => "Which language should we use if we can gather no information from user?",
                'type' => "string"
            ),
            "ALLOW_CHANGE_USERNAME" => array(
                'description' => "if true, users are allowed to change their username",
                'type' => "boolean"
            ),
            "ALLOW_CHANGE_EMAIL" => array(
                'description' => "if true, users are allowed to change their username",
                'type' => "boolean"
            ),
            "ALLOW_CHANGE_NAME" => array(
                'description' => "if true, users are allowed to change their name",
                'type' => "boolean"
            ),
            "ALLOW_CHANGE_TITLE" => array(
                'description' => "if true, users are allowed to change their titles",
                'type' => "boolean"
            ),
            "ENABLE_SELF_REGISTRATION" => array(
                'description' => "should it be possible for an user to register himself",
                'type' => "boolean"
            ),
            "ENABLE_REQUEST_NEW_PASSWORD_BY_USER" => array(
                'description' => "if true, users are able to request a new password themselves",
                'type' => "boolean"
            ),
            "PHPASS_USE_PORTABLE_HASH" => array(
                'description' => "PHPASS_USE_PORTABLE_HASH",
                'type' => "boolean"
            ),
            "WEBSERVICES_ENABLE" => array(
                'description' => "Schaltet die Webservice-Schnittstelle an.",
                'type' => "boolean"
            ),
            "ENABLE_FREE_ACCESS" => array(
                'description' => "if true, courses with public access are available",
                'type' => "boolean"
            ),
        );

        $stmt = DBManager::get()->prepare("
            INSERT IGNORE INTO config
                (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
            VALUES
                (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
        ");

        foreach ($global_boolean_parameter as $name => $data) {
            $option = array(
                'name'        => $name,
                'type'        => $data['type'],
                'value'       => $GLOBALS[$name],
                'range'       => 'global',
                'section'     => 'config_local',
                'description' => $data['description']
            );
            $stmt->execute($option);
        }
    }

}
