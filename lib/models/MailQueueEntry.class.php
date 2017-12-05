<?php

/*
 *  Copyright (c) 2013  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * Class to handle entries in the mail-queue in Stud.IP.
 * Use MailQueueEntry::add($mail, $message_id, $user_id) to add a mail to the queue
 * and MailQueueEntry::sendAll() or MailQueueEntry::sendNew() to flush the queue
 * and send the mails.
 * @property string mail_queue_id database column
 * @property string id alias column for mail_queue_id
 * @property string mail database column
 * @property string message_id database column
 * @property string user_id database column
 * @property string tries database column
 * @property string last_try database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class MailQueueEntry extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mail_queue_entries';
        $config['serialized_fields']['mail'] = "JSONArrayObject";

        parent::configure($config);
    }

    /**
     * Add an email to the queue.
     * @param StudipMail $mail : the mailobject that should be added and sent later.
     * @param string|null $message_id : the id of the Stud.IP internal message the
     * mail is related to. Leave this null if it isn't related to any internal message.
     * @param string|null $user_id : user_id of the receiver. Leave null if the
     * receiver has no account in Stud.IP.
     * @return MailQueueEntry : object in the mailqueue.
     */
    static public function add(StudipMail $mail, $message_id = null, $user_id = null)
    {
        $queue_entry = new MailQueueEntry();
        $queue_entry['mail'] = $mail->toArray();
        $queue_entry['message_id'] = $message_id;
        $queue_entry['user_id'] = $user_id;
        $queue_entry['tries'] = 0;
        $queue_entry->store();
        return $queue_entry;
    }

    /**
     * Sends all new mails in the mailqueue (which means they haven't been sent yet).
     */
    static public function sendNew()
    {
        self::findEachBySQL(function ($m) {
                $m->send();
        },
        "tries = '0' ORDER BY mkdate");
    }

    /**
     * Sends all mails in the mailqueue. Stud.IP will give each mail 24 tries to
     * deliver it. If the mail could not be sent after 24 tries (which are 24
     * hours) it will stay in the mailqueue table but won't be sent anymore.
     * Each mail will only be tried to deliver once per hour. So if it fails
     * Stud.IP will try again next hour.
     */
    static public function sendAll($limit = null)
    {
        self::findEachBySQL(function ($m) {
                $m->send();
        },
            "tries = '0' " .
            "OR (last_try > (UNIX_TIMESTAMP() - 60 * 60) AND tries < 25) ORDER BY mkdate".
            ($limit > 0 ? " LIMIT ". (int) $limit : "")
        );
    }

    /**
     * Sends the object in the mailqueue. If this succeeds, the object will be
     * deleted immediately. Otherwise the field "tries" in the mailqueue table
     * will be incremented by one.
     */
    public function send()
    {
        $mail = new StudipMail($this->mail);

        //The cronjob message will be included in the mail queue
        //cronjob's log. We must output the message via echo
        //to get it into the log.
        $cronjob_message = sprintf(
            'sending message %1$s (sender: %2$s, %3$d recipient(s))...',
            $this->message_id,
            $mail->getSenderName(),
            count($mail->getRecipients())
        );

        if (is_a($mail, "StudipMail")) {
            $success = $mail->send();
            if ($success) {
                $cronjob_message .= 'DONE';
                if ($this['message_id'] && $this['user_id']) {
                    //Noch in message_user als versendet vermerken?
                }
                $this->delete();
            } else {
                $cronjob_message .= 'FAILURE';
                $this['tries'] = $this['tries'] + 1;
                $this['last_try'] = time();
                $this->store();
            }
            if ($this['tries'] > 0) {
                //If sending the message has failed at least once
                //we add the amount of tries to the cronjob message.
                $cronjob_message .= sprintf(
                    '(t=%1$d)',
                    $this['tries']
                );
            }
        } else {
            $cronjob_message .= 'ERROR(no_studip_mail)';
        }

        echo $cronjob_message . "\n";
    }
}
