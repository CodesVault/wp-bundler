<?php

namespace CodesVault\Bundle\Lib;

use Symfony\Component\Finder\Finder;

class Fs
{
    protected function makeProductionDirectory($path, $prod_path)
    {
        $finder = new Finder();
        $finder->in($path);

        foreach ($finder as $file) {
            if (
                strpos($file->getRealPath(), 'vendor') ||
                strpos($file->getRealPath(), 'prod') ||
                strpos($file->getRealPath(), 'node_modules')
            ) {
                continue;
            }

            // creating directories
            $this->makeProdDirs($prod_path, $file);

            // creating files
            $this->makeProdFiles($path, $prod_path, $file);
        }
    }

	protected function makeProdDirs($prod_path, $file)
	{
		$prod_dir_path = "$prod_path/" . $file->getRelativePathname();
		if (is_dir($file->getRealPath()) && ! is_dir($prod_dir_path)) {
			mkdir($prod_dir_path);
		}
	}

	protected function makeProdFiles($path, $prod_path, $file)
	{
		if (is_dir($file->getRealPath())) {
			return;
		}

		$file_path = $file->getRelativePathname();

		copy(
			"$path/" . $file_path,
			"$prod_path/" . $file_path
		);
	}

    protected function updateFile($root_path, $prod_repo, $data)
    {
		foreach ($data as $file_name => $file_data) {
			$file_relative_path = $file_data['path'] . '/' . $file_name . "." . $file_data['extension'];
			$resource_file_path = $root_path . $file_relative_path;
			$intended_file_path =  "$root_path/prod/$prod_repo" . $file_relative_path;

			$intended_file_content = file_get_contents($resource_file_path);
			$intended_file = fopen($intended_file_path, 'w');

			foreach ($file_data['schema'] as $schema) {
				$intended_file_content = str_replace($schema['target'], $schema['template'], $intended_file_content);
			}

			fwrite($intended_file, $intended_file_content, strlen($intended_file_content));
		}
    }

	public function copy($resource, $destination)
	{
		copy($resource, $destination);
		return $this;
	}

	public function makeDir($path)
	{
		mkdir($path);
		return $this;
	}

	public function buildIterator($meta_data, $callback)
	{
		foreach ($meta_data as $data) {
			$callback($data, $this);
		}

		return $this;
	}

	public function findAndReplace($path, $data_ref)
	{
		$finder = new Finder();
        $files = $finder->in($path)->files()->name('*.php');

		foreach ($files as $file) {
			$file_path = $file->getRealPath();

			$this->updateFile($file_path, $data_ref);
		}

		return $this;
	}
}
