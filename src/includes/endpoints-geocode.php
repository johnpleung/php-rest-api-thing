<?php
	$app->map('GET', '/v1/geocode', function() {
		global $geocoder;

		if(isset($_GET["query"])){
			$query = trim(strtolower($_GET["query"]));
			if($query!=""){
				$geocode_results = $geocoder->geocode($query);
				if($geocode_results){
					render_response(true, $geocode_results);
				}else{
					render_response(false, null, 0);
				}
			}
		}
	});

	$app->map('DELETE', '/v1/geocode/cache', function() {
		$cache = new cache_handler();
		$cache->clear_cache_files('geocode');
		render_response(true, null, 0);
	});