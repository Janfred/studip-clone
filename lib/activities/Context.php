<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

abstract class Context
{
    protected
        $provider,
        $observer;


    /**
     * return array, listing all active providers in this context
     *
     * @return array
     */
    abstract protected function getProvider();

    /**
     * get id denoting the context (user_id, course_id, institute_id, ...)
     *
     * @return string
     */
    abstract public function getRangeId();

    /**
     * get type of context (f.e. user, system, course, institute, ...)
     *
     * @return string
     */
    abstract public function getContextType();

    /**
     * get type of context (f.e. user, system, course, institute, ...)
     *
     * @return string
     */
    abstract public function getContextFullname($format = 'default');

    /**
     * Return user for who wants to watch his and related activities
     * 
     * @return object  a user object
     */
    public function getObserver()
    {
        return $this->observer;
    }

    /**
     * get list of activities as array for the current context
     *
     * @param \Studip\Activity\Filter $filter
     *
     * @return array
     */
    public function getActivities(Filter $filter)
    {
        $activities = Activity::findBySQL('context = ? AND context_id = ?  AND mkdate >= ? AND mkdate <= ? ORDER BY mkdate DESC',
            array($this->getContextType(), $this->getRangeId(), $filter->getStartDate(), $filter->getEndDate()));
        if (count($activities)) {
            $providers = $this->filterProvider($this->getProvider(), $filter);
            foreach ($activities as $key => $activity) {
                if (isset($providers[$activity->provider])) {                        // provider is available
                    $activity->setContextObject($this);
                    if (!$providers[$activity->provider]->getActivityDetails($activity)) {
                        unset($activities[$key]);
                    }
                } else {
                    unset($activities[$key]);
                }
            }
        }
        return array_flatten($activities);
    }

    /**
     * Add a provider to this context
     *
     * @param string $provider    the name for the provider
     * @param string $class_name  the class that belongs to the provider
     */
    protected function addProvider($class_name)
    {
        $reflectionClass = new \ReflectionClass($class_name);
        $this->provider[$class_name] =  $reflectionClass->newInstanceArgs();
    }

    /**
     *
     * @param type $providers
     * @param \Studip\Activity\Filter $filter
     * @return type
     */
    protected function filterProvider($providers, Filter $filter)
    {
        $filtered_providers = array();

        if (is_null($filter->getType())) {
            $filtered_providers = $providers;
        } else {
            foreach ($providers as $provider) {
                // $filtered_class = 'Studip\Activity\\' . ucfirst($filter->getType()) . 'Provider';
                $filtered_class = $filter->getType();

                if ($provider instanceof $filtered_class) {
                    $filtered_providers[] =  $provider;
                }
            }
        }

        return $filtered_providers;
    }
}
