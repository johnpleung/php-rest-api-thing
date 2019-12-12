<?php
	class geocode_handler {

		private $settings;
		private $cache_handler;

		public function __construct($token) {
			$this->settings = [
				"base_url" => "https://api.geocod.io/v1.3/geocode",
				"token" => $token
			];
			$this->cache_handler = new cache_handler();
		}

		private function get_address_component(&$components, $name){
			$ret = null;
			foreach ($components as $component){
				$index = array_search($name, $component->types);
				if($index!==false){
					$ret = $component->short_name;
					break;
				}
			}
			return $ret;
		}

		public function geocode($address){
			$ret = null;
			$url = $this->settings['base_url'] . '?q=' . urlencode($address) . '&api_key=' . $this->settings['token'];
			$cache_key = trim(strtolower(urlencode($address)));

			$data = $this->cache_handler->get_cache_item('geocode', 'response', $cache_key);
			if($data){
				return $data;
			}

			$response = null;
			$raw = @file_get_contents($url);
			if($raw!==false){
				$response = @json_decode($raw);
			}

			if($response){
				if(!is_null($response) && isset($response->results) && isset($response->results[0]) && isset($response->results[0]->address_components) && isset($response->results[0]->location)){
					$data = $response->results[0];
					$ret = [];

					if(isset($data->address_components->city)){
						$ret["city"] = $data->address_components->city;
					}
					if(isset($data->address_components->state)){
						$ret["state"] = $data->address_components->state;
					}
					if(isset($data->address_components->zip)){
						$ret["zip"] = $data->address_components->zip;
					}
					if(isset($data->location->lat)){
						$ret["latitude"] = $data->location->lat;
					}
					if(isset($data->location->lng)){
						$ret["longitude"] = $data->location->lng;
					}

					$this->cache_handler->save_cache_item('geocode', 'response', $cache_key, $ret);
				}else{
					_log('log', [
						'message' => 'Geocoding failed',
						'notes' => $address . "; Raw: " . $raw
					]);
				}
			}

			return $ret;
		}

	}