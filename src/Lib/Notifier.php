<?php

namespace CodesVault\Bundle\Lib;

use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;

trait Notifier
{
    private function notifier($msg, $type = 'success')
    {
        $neutral = $this->text_colors()['neutral'];
        $color = $this->text_colors()[$type];

        return "\n{$color}{$msg}{$neutral}\n";
    }

    private function desktopNotifier($body, $subtitle, $title = "WP Bundler")
    {
        $notifier = NotifierFactory::create();
        $notify = new Notification();

        $notification =
        $notify
        ->setTitle($title)
        ->setBody($body)
        ->setIcon(dirname(__DIR__, 2) . '/img/icon.png')
        ->addOption('subtitle', $subtitle);

        $notifier->send($notification);
    }

    private function text_colors()
    {
        $colors = [
            'neutral'   => "\033[0m",
            'success'   => "\033[0;32m",
            'error'     => "\033[31m",
            'warning'   => "\033[33m",
			'info'		=> "\033[34m",
        ];

        return $colors;
    }
}
