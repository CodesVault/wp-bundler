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

	public function mapTiersAndProductIds($env)
	{
		$tier_pid_list = explode(',', $env);
		$maped_data = array_map(function($list_item) {
			$tier_pid = explode(':', $list_item);

			return [
				'tier'			=> $tier_pid[0],
				'product_id'	=> $tier_pid[1]
			];
		}, $tier_pid_list);

		return $maped_data;
	}
}
