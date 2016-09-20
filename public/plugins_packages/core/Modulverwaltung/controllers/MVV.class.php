<?php
/**
 * MVV.class.php - MVV main controller class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class MVVController extends StudipController
{

    public static $items_per_page;
    public $search_result = array();
    public $search_term = '';
    public $search_id = null;
    protected $sidebar_rendered = false;
    public $param_suffix = '';
    protected $sssion_key;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->plugin = $this->dispatcher->current_plugin;
        if (!$this->isVisible()) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle($this->plugin->getDisplayTitle());
        $this->url = $this->plugin->getPluginUrl(). '/public/';
        // Setup flash instance
        $this->flash = Trails_Flash::instance();

        $this->me = 'mvvplugin';
        self::$items_per_page = Config::get()->getValue('ENTRIES_PER_PAGE');

        $this->session_key = $this->me . '_'
                . substr(get_class($this), 0, -10);
    }

    /**
     * Returns a controller based (considers name of action if given)
     * suffix for url parameters.
     *
     * @param string $action The name of the action (optional).
     * @return string Suffix for parameters.
     */
    public function paramSuffix($action = '')
    {
        $param_suffix = mb_strtolower(preg_filter(
                array('/^.*_/', '/Controller$/'), '', get_called_class(), 1));
        return $action ? '_' . $param_suffix . '_' . $action : '_' . $param_suffix;
    }

    /**
     * Initialzes the controller (considers name of action if given) based
     * parameters for search and bind them to url.
     *
     * @param string $action The name of the action (optional).
     */
    protected function initSearchParams($action = '')
    {
        $this->search_params_suffix = $this->paramSuffix($action);
        $this->search_term = Request::get('search_term' . $this->search_params_suffix,
                $this->sessGet('search_term'));
        URLHelper::bindLinkParam('search_term' . $this->search_params_suffix, $this->search_term);
        $this->search_id = Request::option('search_id' . $this->search_params_suffix,
                $this->sessGet('search_id'));
        URLHelper::bindLinkParam('search_id' . $this->search_params_suffix, $this->search_id);
    }

    /**
     * Initialzes the controller (considers name of action if given) based
     * parameters for page navigation and bind them to url.
     *
     * @param string $action The name of the action (optional).
     */
    protected function initPageParams($action = null)
    {
        $this->page_params_suffix = $this->paramSuffix($action);
        $action = $action ? '_' . $action : '';

        $this->page = Request::int('page' . $this->page_params_suffix,
                $this->sessGet('page' . $this->page_params_suffix));
        $this->page = $this->page ?: 1;
        $this->sessSet('page' . $this->page_params_suffix, $this->page);
        URLHelper::bindLinkParam('page' . $this->page_params_suffix, $this->page);

        $this->sortby = Request::get('sortby' . $this->page_params_suffix,
                $this->sessGet('sortby' . $this->page_params_suffix));
        $this->sortby = $this->sortby ?: 'mkdate';
        $this->sessSet('sortby' . $this->page_params_suffix, $this->sortby);
        URLHelper::bindLinkParam('sortby' . $this->page_params_suffix, $this->sortby);

        $this->order = Request::get('order' . $this->page_params_suffix,
                $this->sessGet('order' . $this->page_params_suffix));
        $this->order = $this->order ?: 'mkdate';
        $this->sessSet('order' . $this->page_params_suffix, $this->order);
        URLHelper::bindLinkParam('order' . $this->page_params_suffix, $this->order);
    }

    protected function isVisible()
    {
        return $this->plugin->isVisible();
    }

    protected function createQuestion($question, $params_yes, $params_no = null,
            $token = null)
    {
        if (is_array($params_yes)) {
            $params_yes = implode('/', $params_yes);
        }
        if (is_array($params_no)) {
            $params_no = implode('/', $params_no);
        }
        $template = $GLOBALS['template_factory']->open('shared/question2');
        $template->set_attribute('approvalLink', $this->url_for($params_yes,
                $token ? array('token' => $token) : array()));
        $template->set_attribute('disapprovalLink', $this->url_for($params_no));
        $template->set_attribute('question', $question);
        $template->set_attribute('approvParams', array());
        $template->set_attribute('disapproveParams', array());

        return $template->render();
    }

    public function renderMessages()
    {
        $messages = $this->flash->get('msg');
        $message_html = '';
        if ($messages && is_array($messages)) {
            foreach ($messages as $message) {
                if ($message[0] == 'dialog') {
                    $message_html .= $this->createQuestion($message[1],
                            $message[2], $message[3], $message[4]);
                } else {
                    $message_html .= MessageBox::$message[0]($message[1],
                            $message[2]);
                }
            }
        }
        $this->flash->discard('msg');
        return $message_html;
    }

    public function renderSortLink($action, $text, $field, $attributes = null)
    {
        $template = $this->get_template_factory()->open('shared/sort_link');
        $template->set_attributes(
                array('controller' => $this,
                    'action' => $action,
                    'text' => $text,
                    'field' => $field,
                    'attributes' => (array) $attributes));
        return $template->render();
    }

    protected function setSidebar()
    {
        $this->sidebar_rendered = true;
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');
    }

    public function jsUrl($to = '', $params = array())
    {
        if($to === '') {
            $to = str_replace('_', '/', substr(mb_strtolower(get_class($this)),
                    0, -10)) . '/';
        }
        $url = PluginEngine::getUrl($this->plugin, $params, $to, true);

        $template = $this->get_template_factory()->open('shared/js_url');
        $template->set_attributes(array('url' => $url));
        return $template->render();
    }

    public function qs_result_action()
    {
        if (Request::isPost()) {
            $this->render_json(self::getQsResult(
                    Request::option('qs_id'),
                    Request::get('qs_term')));
        } else {
            throw new Trails_Exception(404);
        }
    }

    private static function getQsResult($qs_id, $qs_term)
    {
        $result = array();
        $search = self::getSearch($qs_id);
        if ($search) {
            $results[] = array('id' => '',
                    'name' => _('-- bitte wählen --'));
            foreach ($search->getResults($qs_term)
                    as $result) {
                $results[] = [
                    'id' => $result[0],
                    'name' => $result[1]];
            }
            return $results;
        }
        return null;
    }

    /**
     * Retrieves a quick search sql object from session by its id
     * (md5 of serialized object).
     *
     * @param string $qs_id The quick search id of the search object.
     * @return object A search object.
     */
    private static function getSearch($qs_id) {
        $search = null;
        if ($qs_id) {
            try {
                $search = unserialize($_SESSION['QuickSearches'][$qs_id]['object']);
            } catch (Exception $e) {
                return null;
            }
        }
        return is_object($search) ? $search : null;
    }

    protected function do_search($class_name, $search_term = null,
            $search_id = null, $filter = null)
    {
        if (!sizeof($this->search_result)) {
            $search_id = $search_id ? $search_id : $this->search_id;
            if ($search_id) {
                $found_object = $class_name::find($search_id);
                if ($found_object) {
                    $this->search_result = array($found_object->getId());
                    $this->search_term = $found_object->getDisplayName();
                    if (!$this->search_id) {
                        PageLayout::postInfo(sprintf(_('"%s" ausgewählt.'),
                                htmlReady($found_object->getDisplayName())));
                    }
                    $this->search_id = $search_id;
                    $this->sessSet('search_id', $this->search_id);
                }
            } else {
                $search_term = $search_term ? $search_term : $this->search_term;
                if ($search_term) {
                    $this->search_result =
                            $class_name::findBySearchTerm($search_term, $filter)->pluck('id');
                    if ($this->current_action == 'search') {
                        if (sizeof($this->search_result)) {
                            PageLayout::postInfo(
                                    sprintf(_('%s Treffer für die Suche nach "%s".'),
                                            sizeof($this->search_result),
                                            htmlReady($search_term)));
                            $this->search_term = $search_term;
                        } else {
                            PageLayout::postInfo(
                                    sprintf(_('Keine Treffer für die Suche nach "%s".'),
                                            htmlReady($search_term)));
                        }
                        unset($this->search_id);
                        $this->sessRemove('search_id');
                    }
                }
            }
        }
        $this->sessSet('search_term', $this->search_term);
    }

    /**
     * Returns the current search result of the given class. The search result
     * is an array of object ids.
     *
     * @param string The class name of the found objects.
     * @return array Array of search results.
     */
    protected function getSearchResult($class_name)
    {
        $this->do_search($class_name);

        return $this->search_result;
    }

    /**
     * Deletes the search results stored in $this->search_result for the
     * given class. If no class name is given, deletes all search results.
     *
     * @param string $action
     */
    protected function reset_search($action = '')
    {
        $this->search_params_suffix = $this->paramSuffix($action);
        // reset filter
        $this->filter = array();
        $this->sessRemove('filter');

        // reset search
        $this->search_result = array();
        unset($this->search_term);
        URLHelper::removeLinkParam('search_term' . $this->search_params_suffix);
        $this->sessRemove('search_term');
        unset($this->search_id);
        URLHelper::removeLinkParam('search_id' . $this->search_params_suffix);
        $this->sessRemove('search_id');
    }

    protected function reset_page($action = '')
    {
        $this->page_params_suffix = $this->paramSuffix($action);

        // reset page chooser
        $this->page = 1;
        $this->sessRemove('page');
        URLHelper::removeLinkParam('page' . $this->page_params_suffix);

        // reset sorting
        $this->sortby = '';
        $this->sessRemove('sortby');
        URLHelper::removeLinkParam('sortby' . $this->page_params_suffix);
        $this->order = 'DESC';
        $this->sessRemove('order');
        URLHelper::removeLinkParam('order' . $this->page_params_suffix);
    }


    /**
     * Stores a modal dialog into flash storage.
     *
     * @param string $message The message of the dialog
     * @param string/array Parameters for the approval link (yes) as array or string
     * @param string/array Parameters for the non approval link (no) as array or string
     */
    protected function flash_dialog($message, $params_yes, $params_no)
    {
        $this->flash_set('dialog', $message, $params_yes, $params_no);
    }

    protected function flash_set($type, $message, $param1 = array(), $param2 = array())
    {
        $old = (array)$this->flash->get('msg');
        if ($type == 'dialog') {
            $new = array_merge($old, array(array($type, $message,
                (array) $param1, (array) $param2)));
        } else {
            $new = array_merge($old, array(array($type, $message,
                (array) $param1)));
        }
        $this->flash->set('msg', $new);
    }

    /**
     * Stores a value with the given key in the session.
     *
     * @param string $key The key of the value.
     * @param mixed $value The value to store under the given key.
     * @return the stored value
     */
    protected function sessSet($key, $value)
    {
        $_SESSION[$this->session_key][$key] = $value;
        return $value;
    }

    /**
     * Returns the value of the given key from the session.
     *
     * @param string $key The key of the value to return.
     * @return mixed The value from session with the given key.
     */
    protected function sessGet($key, $default = null)
    {
        return (isset($_SESSION[$this->session_key][$key])
                ? $_SESSION[$this->session_key][$key]
                : $default);
    }

    /**
     * Removes the value with the given key from the session.
     *
     * @param string|array $keys The key of the value to remove from session.
     */
    protected function sessRemove($keys)
    {
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        foreach ($keys as $key) {
            unset($_SESSION[$this->session_key][$key]);
        }
    }

    /**
     * Deletes all values from the session used in this controller.
     *
     */
    protected function sessDelete()
    {
        unset($_SESSION[$this->session_key]);
    }


    /**
     * This weird WYSIWIG-Editor stores an empty string as an empty diff-element.
     * Use this function to check whether the field has no content
     * (no input by the user).
     *
     * @param string $text The text from db to check.
     * @return string An empty string, if the content is an empty diff
     * or an empty string
     */
    public static function trim($text) {
        $text = trim($text);
        return preg_match('%\<div.*?\>\</div\>%', $text) ? '' : $text;
    }
}
