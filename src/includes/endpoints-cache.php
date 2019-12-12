<?php

	$app->map('DELETE', '/v1/cache', function() {
		$cache = new cache_handler();
		$cache->clear_cache_files();
	});