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
        $enabled = self::isEnabled();
        $result = '';

        if (!($value instanceof I18NString)) {
            $value = new I18NString($value);
        }

        if ($enabled) {
            $result .= '<div class="i18n_group">';
        }

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

            $result .= sprintf('<div class="i18n" data-lang="%s" data-icon="url(%s)">', $lang['name'], Assets::image_path('languages/' . $lang['picture']));

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
            $result .= "></div>\n";
        }

        if ($enabled) {
            $result .= "</div>";
        }

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
        $enabled = self::isEnabled();
        $result = '';

        if (!($value instanceof I18NString)) {
            $value = new I18NString($value);
        }

        if ($enabled) {
            $result .= '<div class="i18n_group">';
        }

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

            $result .= sprintf('<div class="i18n" data-lang="%s" data-icon="url(%s)">', $lang['name'], Assets::image_path('languages/' . $lang['picture']));

            $attr = array_merge($attr, $attributes);
            if (isset($attr['required']) && empty($text) && $locale !== $base_lang) {
                unset($attr['required']);
            }

            $result .= '<textarea';
            foreach ($attr as $key => $val) {
                if ($val === true) {
                    $result .= sprintf(' %s', $key);
                } else if (isset($val)) {
                    $result .= sprintf(' %s="%s"', $key, htmlReady($val));
                }
            }
            $result .= '>' . htmlReady($text) . "</textarea></div>\n";
        }

        if ($enabled) {
            $result .= "</div>";
        }

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
