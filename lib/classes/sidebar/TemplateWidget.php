<?php
class TemplateWidget extends SidebarWidget
{
    protected $template;

    public function __construct($title, Flexi_Template $template, array $variables = [])
    {
        parent::__construct();

        $this->title    = $title;
        $this->template = $template;
        $this->template->set_attributes($variables);
    }

    public function render($variables = [])
    {
        $this->template->set_attributes($variables);
        return $this->template->render(
            $this->template_variables,
            $GLOBALS['template_factory']->open($this->layout)
        );
    }
}
