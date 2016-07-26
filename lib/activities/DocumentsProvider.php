<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class DocumentsProvider implements ActivityProvider
{

    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $document = \StudipDocument::find($activity->object_id);

        // check, if current observer has access to document
        if (!$document || !$document->checkAccess($activity->getContextObject()->getObserver()->id)) {
            return false;
        }

        if ($activity->context == "course") {
            $url = \URLHelper::getUrl("folder.php?cid={$activity->context_id}&cmd=tree&open={$activity->object_id}");
            $route = \URLHelper::getURL('api.php/file/' . $activity->object_id, NULL, true);

            $activity->object_url = array(
                $url => _('Zum Dateibereich der Veranstaltung')
            );
        } elseif ($activity->context == "institute") {
            $url = \URLHelper::getUrl("folder.php?cid={$activity->context_id}&cmd=tree&open={$activity->object_id}");
            $route= null;

            $activity->object_url = array(
                $url => _('Zum Dateibereich der Einrichtung')
            );
        }

        $activity->object_route = $route;

        return true;
    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param Array  $document information which a relevant for the activity
     */
    public function postActivity($event, $document)
    {
        $document_info = $document->toArray();

        $user_id = $document_info['user_id'];
        $file_name = $document_info['name'];
        $course_id = $document_info['seminar_id'];
        $file_id = $document_info['dokument_id'];

        $type     = get_object_type($course_id);
        if ($type == 'sem') {
            $course = \Course::find($course_id );
        } else {
            $course = \Institute::find($course_id );
        }

        if ($event == 'DocumentDidCreate') {
            $verb = 'created';
            if ($type == 'sem') {
                $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" hochgeladen.');
            } else {
                $summary = _('Die Datei %s wurde von %s in der Einrichtung "%s" hochgeladen.');
            }
            $summary = sprintf($summary,$file_name, get_fullname($user_id) ,$course->name);
            $mkdate = $document_info['mkdate'];
        } elseif ($event == 'DocumentDidUpdate') {
            $verb = 'edited';
            if ($type == 'sem') {
                $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" aktualisiert.');
            } else {
                $summary = _('Die Datei %s wurde von %s in der Einrichtung "%s" aktualisiert.');
            }
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $document_info['chdate'];
        } elseif ($event == 'DocumentDidDelete') {
            $verb = 'voided';
            if ($type == 'sem') {
                $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" gelöscht.');
            } else {
                $summary = _('Die Datei %s wurde von %s in der Einrichtung "%s" gelöscht.');
            }
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $document_info['chdate'];
        }


        $activity = Activity::create(
            array(
                'provider'     => __CLASS__,
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $course_id,
                'content'      => $summary,
                'actor_type'   => 'user',      // who initiated the activity?
                'actor_id'     => $user_id,    // id of initiator
                'verb'         => $verb,       // the activity type
                'object_id'    => $file_id,    // the id of the referenced object
                'object_type'  => 'documents', // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('eine Datei');
    }
}
