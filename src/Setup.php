<?php

namespace CodesVault\Bundle;

class Setup
{
    public static function loadEnv($path, $file_name)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable($path, $file_name);
        $dotenv->load();

        return new self();
    }

    public function getEnv($key = '')
    {
        if ($key) {
            return isset($_ENV[$key]) ? $_ENV[$key] : null;
        }

        return $_ENV;
    }

	public function kv($env)
	{
		$kv_list = explode(',', $env);
		$maped_data = array_map(function($list_item) {
			if (strpos($list_item, ':') === false) {
				return false;
			}
			$item = explode(':', $list_item);

			return [
				'key'	=> $item[0] ?? '',
				'value'	=> $item[1] ?? '',
			];
		}, $kv_list);

		return $maped_data;
	}
}
