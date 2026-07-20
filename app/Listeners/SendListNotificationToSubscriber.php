<?php

namespace Acelle\Listeners;

use Acelle\Events\MailListSubscription;
use Acelle\Events\MailListUnsubscription;

class SendListNotificationToSubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MailListSubscription  $event
     * @return void
     */
    public function handleMailListSubscription(MailListSubscription $event)
    {
        $subscriber = $event->subscriber;
        $list = $subscriber->mailList;

        if ($list->send_welcome_email) {
            $list->sendSubscriptionWelcomeEmail($subscriber);
            applog('list-subscription')->info(sprintf('LIST #%s (%s) Event SendListNotificationToSubscriber (sent), %s', $list->id, $list->name, $event->subscriber->email));
        } else {
            applog('list-subscription')->info(sprintf('LIST #%s (%s) Event SendListNotificationToSubscriber (do not send because send_welcome_email=false), %s', $list->id, $list->name, $event->subscriber->email));
        }
    }

    /**
     * Handle the event.
     *
     * @param  MailListSubscription  $event
     * @return void
     */
    public function handleMailListUnsubscription(MailListUnsubscription $event)
    {
        $subscriber = $event->subscriber;
        $list = $subscriber->mailList;

        if ($list->unsubscribe_notification) {
            $list->sendUnsubscriptionNotificationEmail($subscriber);
        }
    }

    /**
     * Handle the event.
     *
     * @param  AdminLoggedIn  $event
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            'Acelle\Events\MailListSubscription',
            [SendListNotificationToSubscriber::class, 'handleMailListSubscription']
        );

        $events->listen(
            'Acelle\Events\MailListUnsubscription',
            [SendListNotificationToSubscriber::class, 'handleMailListUnsubscription']
        );
    }
}
