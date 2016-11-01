<?php
/**
 * @author Till Glöggler <tgloeggl@uos.de>
 */
class StudipVersion
{
    /**
     * Returns the current Stud.IP-version
     *
     * @return string
     */
    private static function getStudipVersion()
    {
        return $GLOBALS['SOFTWARE_VERSION'];
    }

    /**
     * Returns true if passed version is newer than the current Stud.IP version
     *
     * @param string $version
     * @return bool
     */
    public static function newerThan($version)
    {
        return (version_compare(self::getStudipVersion(), $version, '>'));
    }

    /**
     * Returns true if passed version is older than the current Stud.IP version
     *
     * @param string $version
     * @return bool
     */
    public static function olderThan($version)
    {
        return (version_compare(self::getStudipVersion(), $version, '<'));
    }

    /**
     * Returns true if passed version matches the current Stud.IP version
     *
     * @param string $version
     * @return bool
     */
    public static function matches($version)
    {
        return (version_compare(self::getStudipVersion(), $version, '='));
    }

    /**
     * Returns true if version equals or is between the two passed versions
     *
     * @param string $from_version
     * @param string $to_version
     *
     * @return bool
     */
    public static function range($from_version, $to_version)
    {
        return version_compare(self::getStudipVersion(), $from_version, '>=')
                && version_compare(self::getStudipVersion(), $to_version, '<=');
    }
}
