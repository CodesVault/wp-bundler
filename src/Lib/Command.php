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
            echo $this->notifier("Running: command as callable `{$command}`");
            $command = $command();
            
            if (is_string($command)) {
                echo $this->notifier("{$command}");
            }

            return;
        }

		system("cd {$prodPath} && {$command}");
    }
}
