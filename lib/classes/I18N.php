<?php

/**
 * Translation class
 *
 * Automatic generation of inputs and textareas for i18n
 */
class I18N
{

    /**
     * Create a set of HTML input elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param string $name HTML name of the Inputfild
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted input
     */
    public static function input($name, $value, $attributes = array())
    {

        $languages = $GLOBALS['CONTENT_LANGUAGES'];
        $base_lang = Config::get()->DEFAULT_LANGUAGE;
        if (!($value instanceof I18NString)) {
            $value = new I18NString($value);
        }

        $result = "<div class=\"i18n_group normal-input " . (!self::isEnabled() ? 'single_lang' : '') . "\">";
        foreach ($languages as $locale => $lang) {
            if ($locale === $base_lang) {
                $attr = array(
                    'name' => $name,
                    'value' => $value->original(),
                    'id' => $attributes['id']
                );
            } else {
                $attr = array(
                    'name' => $name . '_i18n[' . $locale . ']',
                    'value' => $value->translation($locale),
                    'id' => NULL
                );
            }
            $attr += array(
                'class' => $attributes['class'] . ' i18n',
                'style' => sprintf('%s background-image: url(%s);', $attributes['style'],
                                   Assets::image_path('languages/' . $lang['picture'])),
                'data-lang_desc' => $lang['name']
            );

            $attr = array_merge($attr, $attributes);
            if (isset($attr['required']) && empty($attr['value']) && $locale !== $base_lang) {
                unset($attr['required']);
            }

            $result .= '<input type="text"';
            foreach ($attr as $key => $val) {
                if ($val === true) {
                    $result .= sprintf(' %s', $key);
                } else if (isset($val)) {
                    $result .= sprintf(' %s="%s"', $key, htmlReady($val));
                }
            }
            $result .= ">\n";
        }
        $result .= "</div>";

        return $result;
    }

    /**
     * Create a set of HTML textarea elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param string $name HTML name of the Textarea
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted textarea
     * @throws i18nException If given value was not an i18n Object
     */
    public static function textarea($name, $value, $attributes = array())
    {
        $languages = $GLOBALS['CONTENT_LANGUAGES'];
        $base_lang = Config::get()->DEFAULT_LANGUAGE;
        $wysiwyg = in_array('wysiwyg', words($attributes['class']));
        $value instanceOf I18NString or $value = new I18NString($value);

        $result = "<div class=\"i18n_group textarea-input " . (!self::isEnabled() ? 'single_lang' : '') . "\">";
        foreach ($languages as $locale => $lang) {
            if ($locale === $base_lang) {
                $attr = array(
                    'name' => $name,
                    'id' => $attributes['id']
                );
                $text = $value->original();
            } else {
                $attr = array(
                    'name' => $name . '_i18n[' . $locale . ']',
                    'id' => NULL
                );
                $text = $value->translation($locale);
            }
            $attr += array(
                'class' => $attributes['class'] . ' i18n',
                'style' => sprintf('%s background-image: url(%s);', $attributes['style'],
                    Assets::image_path('languages/' . $lang['picture'])),
                'data-lang_desc' => $lang['name']
            );

            $attr = array_merge($attr, $attributes);
            if (isset($attr['required']) && empty($text) && $locale !== $base_lang) {
                unset($attr['required']);
            }

            if ($wysiwyg) {
                $result .= '<div class="i18n" style="' . htmlReady($attr['style']) .
                           '" data-lang_desc="' . htmlReady($attr['data-lang_desc']) . '">';
            }
            $result .= '<textarea';
            foreach ($attr as $key => $val) {
                if ($val === true) {
                    $result .= sprintf(' %s', $key);
                } else if (isset($val)) {
                    $result .= sprintf(' %s="%s"', $key, htmlReady($val));
                }
            }
            $result .= '>' . htmlReady($text) . "</textarea>\n";
            if ($wysiwyg) {
                $result .= '</div>';
            }
        }
        $result .= "</div>";
        return $result;
    }

    /**
     * is more than the default language configured
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return count($GLOBALS['CONTENT_LANGUAGES']) > 1;
    }
}
