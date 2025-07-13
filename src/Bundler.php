<?php

namespace CodesVault\Bundle;

use CodesVault\Bundle\Lib\Command;
use CodesVault\Bundle\Lib\Fs;
use CodesVault\Bundle\Lib\Notifier;
use Symfony\Component\Finder\Finder;

class Bundler extends Fs
{
    use Notifier;

    protected $path;
    protected $prod_path = null;
	protected $prod_repo;
	protected $exclude_permission = true;
	protected $start_execution_time;

    public function __construct($root_path)
    {
		echo $this->notifier("Let's burn it down...", 'info');
        $this->path = $root_path;
		$this->start_execution_time = microtime(true);
    }

    public function command($command)
    {
		echo $this->notifier("Running: {$command}");
        if ($this->prod_path === null) {
            (new Command())->create($command);
            return $this;
        }
        (new Command())->create("cd {$this->prod_path} && $command");
        return $this;
    }

    public function zip($zip_name)
    {
        echo $this->notifier("Making production zip...");
        system("cd {$this->path}/prod && zip -rq {$zip_name}.zip {$this->prod_repo}");
        echo $this->notifier("{$zip_name}.zip created...");

		echo $this->notifier("Smashed it! 💥", "warning");

		$this->restoreDefaultFiles($this->path);

        return $this;
    }

    public function createProductionRepo($prod_repo_name)
    {
		echo $this->notifier("$prod_repo_name: Creating Production repository with files...");

		$prod_dir_path = "{$this->path}/prod";
		$this->prod_path = "{$prod_dir_path}/{$prod_repo_name}";
		$this->prod_repo = $prod_repo_name;

        if (! is_dir($prod_dir_path)) {
            mkdir($prod_dir_path);
        }
        if (is_dir($this->prod_path)) {
            system("rm -rf {$this->prod_path}");
        }
        mkdir($this->prod_path);

        $this->makeProductionDirectory($this->path, $this->prod_path);

		echo "\n";

        return $this;
    }

    public function updateFileContent($intended_data)
    {
        echo $this->notifier("Updatating files data...");

		$data = (new SchemaParser($this->path, $intended_data))->parseData();
        $this->updateFile($this->path, $this->prod_repo, $data);

        return $this;
    }

    public function cleanUp()
    {
		if (! $this->exclude_permission) {
			echo $this->notifier("Permission denied", "warning");
			return;
		}
		if (! is_file("{$this->path}/.distignore")) {
			echo $this->notifier("'.distignore' file not found. Skipping cleanUp process...", "warning");
			return;
		}

		echo $this->notifier("Cleaning up production environment by removing unwanted files/folders...");

		$distignoreFileContent = file_get_contents("{$this->path}/.distignore");
		$ignoreFiles = explode( "\n", $distignoreFileContent );
		if (empty($ignoreFiles)) {
			echo $this->notifier("No files path found in .distignore", "warning");
			return;
		}

        foreach ($ignoreFiles as $path) {
            $path = trim($path);
			if (empty($path)) {
				continue;
			}

            $file_path =  "{$this->prod_path}/$path";
            if (is_dir($file_path)) {
                system("rm -r {$file_path}");
                continue;
            }

            if (is_file($file_path)) {
                unlink($file_path);
            }
        }

        return $this;
    }

	public function findAndReplace($data)
	{
		$finder = new Finder();
        $files = $finder->in($this->prod_path)->files()->name('*.php');

		$this->replaceAll($files, $data);

		return $this;
	}

	public function executionTime()
	{
		$end_timestamp = microtime(true);
		$duration = $end_timestamp - $this->start_execution_time;
		$message = "It took $duration seconds. \nHasta la vista 😎 \n";
		echo $this->notifier($message, 'info');
	}
}
