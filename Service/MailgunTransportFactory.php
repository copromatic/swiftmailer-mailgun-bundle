<?php

namespace cspoo\Swiftmailer\MailgunBundle\Service;

use Mailgun\Mailgun;

class MailgunTransportFactory {

    public static function createMailgunTransportService(\Swift_Events_EventDispatcher $eventDispatcher, Mailgun $mailgun, $domain) {
        return new MailgunTransport($eventDispatcher, $mailgun, $domain);
    }
}