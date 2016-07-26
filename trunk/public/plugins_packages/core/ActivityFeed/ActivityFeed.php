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

        return $template;
    }
}
