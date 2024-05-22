<?php

namespace CodesVault\Bundle;

use CodesVault\Bundle\Lib\Notifier;

class SchemaParser
{
	use Notifier;

	protected $root_path;
	protected $intended_data;
	protected $file_content;

	public function __construct(string $root_path, array $intended_data = [])
	{
		$this->root_path = $root_path;
		$this->intended_data = $intended_data;

		$this->parseJsonFile();
	}

	public function parseData()
	{
		if (empty($this->file_content)) {
			return false;
		}

		foreach ($this->file_content as &$file_data) {
			foreach ($file_data['schema'] as $file_key => $block) {
				$get_var = $block['template'];
				preg_match('/{{(.*?)}}/', $get_var, $matches);

				$file_data['schema'][$file_key]['template'] = str_replace($matches[0], $this->intended_data[$matches[1]], $block['template']);
			}
		}

		return $this->file_content;
	}

	private function parseJsonFile()
	{
		if (! file_exists($this->root_path . '/bundler-schema.json')) {
			echo $this->notifier("bundler-schema.json file not found.\nExit", 'error');
			die();
		}

		$file = file_get_contents($this->root_path . '/bundler-schema.json');
		$this->file_content = json_decode($file, true);
	}

	public function getSchemaFilesPath()
	{
		if (empty($this->file_content)) {
			return false;
		}

		$paths = [];
		foreach ($this->file_content as $file_name => $data) {
			$paths[] = $data['path'] . '/' . $file_name . '.' . $data['extension'];
		}

		return $paths;
	}
}
