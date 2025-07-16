<?php

namespace CodesVault\Bundle\Lib;

class Command
{
    use Notifier;

    public function create($command)
    {
		system($command);
    }

    public function execute($command, $prodPath = null)
    {
        if (! $prodPath) {
            $this->create($command);
            return;
        }

        if (is_callable($command)) {
            $notification_msg = "Running: command as callable";
            if (is_string($command)) {
                $notification_msg .= " `{$command}`";
            }

            echo $this->notifier($notification_msg);
            $result = $command();
            
            if (is_string($result)) {
                echo $this->notifier("{$result}");
            }

            return;
        }

		system("cd {$prodPath} && {$command}");
    }
}
