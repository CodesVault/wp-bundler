<?php

namespace CodesVault\Bundle\Lib;

class Command
{
    use Notifier;

    public function create($command)
    {
		  system($command);
    }
}
