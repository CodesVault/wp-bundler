<?php

namespace CodesVault\Bundle\Lib;

class Command
{
    use Notifier;

    public function create($command)
    {
		system($command);
    }

    public function execute($command, $callback_arg = null, $prodPath = null)
    {
        if (is_callable($command)) {
            $notification_msg = "Running: command as callable";
            if (is_string($command)) {
                $notification_msg .= " `{$command}`";
            }

            echo $this->notifier($notification_msg);
            $result = call_user_func($command, $callback_arg);
            
            if (is_string($result)) {
                echo $this->notifier("{$result}");
            }

            return;
        }

        if (! $prodPath) {
            $this->create($command);
            return;
        }

		system("cd {$prodPath} && {$command}");
    }
}
