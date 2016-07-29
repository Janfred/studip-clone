<?php
/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */
class ActivityFeed extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Aktivitäten');
    }

    public function getPortalTemplate()
    {
        $this->addStylesheet('css/style.less');
        PageLayout::addScript($this->getPluginUrl() . '/javascript/activityfeed.js');

        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('activity_feed');

        $template->user_id = $GLOBALS['user']->id;
        $template->scrolledfrom = strtotime('+1 day');

        $navigation = new Navigation('', '#', array('cid' => null));
        $navigation->setImage(Icon::create('headache+visibility-visible', 'clickable', ["title" => _('Eigene Aktivitäten ausblenden'), "id" => "toggle-user-activities", "data-toggled" => "false"]));
        $icons[] = $navigation;

        $navigation = new Navigation('', '#', array('cid' => null));
        $navigation->setImage(Icon::create('no-activity', 'clickable', ["title" => _('Aktivitätsdetails ein-/ausblenden'), "id" => "toggle-all-activities", "data-toggled" => "false"]));
        $icons[] = $navigation;

        $template->icons = $icons;

        return $template;
    }

    public static function onEnable($plugin_id)
    {
        $errors = [];
        if (!Config::get()->API_ENABLED) {
            $errors[] = sprintf(
                _('Die REST-API ist nicht aktiviert (%s "API_ENABLED")'),
                formatReady(sprintf('[%s]%s',
                    _('Konfiguration'),
                    URLHelper::getLink('dispatch.php/admin/configuration/configuration')
                ))
            );
        } elseif (!RESTAPI\ConsumerPermissions::get('global')->check('/user/:user_id/activitystream', 'get')) {
            $errors[] = sprintf(
                _('Die REST-API-Route ist nicht aktiviert (%s "/user/:user_id/activitystream"")'),
                formatReady(sprintf('[%s]%s',
                    _('Konfiguration'),
                    URLHelper::getLink('dispatch.php/admin/api/permissions')
                ))
            );
        }

        if (count($errors) > 0) {
            PageLayout::postInfo(
                _('Das Aktivitäten-Plugin konnte nicht vollständig aktiviert werden.'),
                $errors
            );
        }
    }
}
