<?php

/**
 * GlobalSearchModule for messages
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchMessages extends GlobalSearchModule
{
    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Nachrichten');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search)
    {
        if (!$search) {
            return null;
        }

        $query = DBManager::get()->quote("%{$search}%");
        $user_id = DBManager::get()->quote($GLOBALS['user']->id);
        $sql = "SELECT `message`.*
                FROM `message`
                    JOIN `message_user` USING (`message_id`)
                WHERE `user_id` = {$user_id}
                    AND (`subject` LIKE {$query} OR `message` LIKE {$query})
                ORDER BY `message`.`mkdate` DESC
                LIMIT " . (4 * Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE);
        return $sql;
    }

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param $id
     * @param $search
     * @return mixed
     */
    public static function filter($message_id, $search)
    {
        $message = Message::buildExisting($message_id);

        $additional = $message->autor_id === '____%system%____'
                    ? _('Systemnachricht')
                    : $message->author->getFullname();

        $result = [
            'name'        => self::mark($message->subject, $search),
            'url'         => URLHelper::getURL('dispatch.php/messages/overview/' . $message->id),
            'img'         => Icon::create('mail', 'clickable')->asImagePath(),
            'date'        => strftime('%x %X', $message->mkdate),
            'description' => self::mark($message->message, $search, true),
            'additional'  => htmlReady($additional),
            'expand'      => self::getSearchURL($search),
            'user'        => $message->author ? $message->author->getFullName() : _('unbekannt')
        ];
        return $result;
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * This could become obsolete when we have a real global search page.
     *
     * @param string $searchterm what to search for?
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL("dispatch.php/messages/overview", [
            'search' => $searchterm,
            'search_subject' => 1,
            'search_content' => 1,
            'search_autor' => 1
        ]);
    }

}
