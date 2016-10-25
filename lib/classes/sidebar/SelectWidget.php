<?php
/**
 * Sidebar widget for lists of selectable items.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   3.1
 */
class SelectWidget extends SidebarWidget
{
    /**
     * Constructs the widget by defining a special template.
     */
    public function __construct($title, $url, $name, $method = 'get')
    {
        $this->template = 'sidebar/select-widget';

        $this->setTitle($title);
        $this->setUrl($url);
        $this->setSelectParameterName($name);
        $this->setRequestMethod($method);

        $this->template_variables['max_length'] = 30;
    }

    public function setUrl($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $url = str_replace('?' . $query , '', $url);
            parse_str(html_entity_decode($query) ?: '', $query_params);
        } else {
            $query_params = array();
        }

        $this->template_variables['url']    = URLHelper::getLink($url);
        $this->template_variables['params'] = $query_params;
    }

    public function setMaxLength($length)
    {
        $this->template_variables['max_length'] = $length;
    }

    public function setSelectParameterName($name)
    {
        $this->template_variables['name'] = $name;
    }

    public function setSelection($value)
    {
        $this->template_variables['value'] = $value;
    }

    public function setRequestMethod($method)
    {
        $this->template_variables['method'] = $method;
    }

    public function setOptions(Array $options, $selected = false)
    {
        $selected = $selected ?: Request::get($this->template_variables['name']);
        foreach ($options as $key => $label) {
            $element = new SelectElement($key, $label, $selected === $key);
            $this->addElement($element);
        }
    }

    public function render($variables = array())
    {
        $attributes = array();
        foreach ((array) $this->template_variables['attributes'] as $key => $value) {
            $attributes[] = sprintf('%s="%s"', htmlReady($key), htmlReady($value));
        }
        $this->template_variables['attributes'] = implode(' ', $attributes) ?: '';

        $variables['__is_nested'] = $this->hasNestedElements();

        $this->template_variables['class'] .= ' submit-upon-select';

        return parent::render($variables);
    }

    protected function hasNestedElements()
    {
        foreach ($this->elements as $element) {
            if ($element instanceof SelectElement
                && ($element->isHeader() || $element->getIndentLevel() > 0))
            {
                return true;
            }
        }
        return false;
    }
}