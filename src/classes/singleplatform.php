<?php
	class singleplatform_handler {

		private $settings;
		private $cache_handler;

		public function __construct($config) {
			$this->settings = [
				"base_url" => "https://publishing-api.singleplatform.com",
				"api_key" => $config['api_key'],
				"client_id" => $config['client_id'],
				"client_secret" => $config['client_secret']
			];
			$this->cache_handler = new cache_handler();
		}

		public function get_menus($sp_location_id, $include_details){
			$ret = null;
			$err = false;
			$cache_key_request = strtolower($sp_location_id);
			$cache_key_response = strtolower($sp_location_id) . '-' . $include_details;
			$data = $this->cache_handler->get_cache_item('menus', 'response', $cache_key_response);
			if($data){
				return $data;
			}

			// Get the SP location's menus, either from cache or from SP
			$menu_response = $this->cache_handler->get_cache_item('menus', 'request', $cache_key_request);
			if(!$menu_response){
				// Response is not cached, so we make the request to SP
				$base_url = '/locations/' . $sp_location_id . '/menus/?client=' . $this->settings['client_id'];
				$url = hash_hmac('sha1', $base_url, $this->settings['client_secret'], TRUE);
				$signature = urlencode(base64_encode($url));
				$signed_url = $this->settings['base_url'] . $base_url . '&signature=' . $signature;

				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $signed_url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "GET"
				));

				$menu_response = json_decode(curl_exec($curl));
				$err = curl_error($curl);
				curl_close($curl);

				// Cache the SP response only if the response is valid
				if(!is_null($menu_response) && $menu_response->code==200 && isset($menu_response->data)){
					$this->cache_handler->save_cache_item('menus', 'request', $cache_key_request, $menu_response);
					$ret = $menu_response->data;
				}
			} else {
				$ret = $menu_response->data;
			}

			if(!is_null($ret) && is_array($ret) && count($ret)){

				// Strip out menu items
				if(!$include_details){
					foreach($ret as &$menu){
						if(isset($menu->sections) && count($menu->sections)){
							foreach($menu->sections as &$section){
								unset($section->items);
							}
						}
					}
				}

				// Cache the response only if the response returns a resource
				$this->cache_handler->save_cache_item('menus', 'response', $cache_key_response, $ret);
			}

			return $err ? null : $ret;
		}

		public function get_menu($sp_location_id, $id){
			$ret = null;
			$cache_key = strtolower($sp_location_id) . ':' . $id;
			$data = $this->cache_handler->get_cache_item('menus', 'response', $cache_key);
			if($data){
				return $data;
			}

			// Get menus
			$menus = $this->get_menus($sp_location_id, true);
			if(!is_null($menus)){
				// Locate requested menu
				foreach($menus as &$menu){
					if($menu->id==$id){
						$ret = &$menu;
						break;
					}
				}

				// Cache the response only if the response returns a resource
				if(!is_null($ret)){
					$this->cache_handler->save_cache_item('menus', 'response', $cache_key, $ret);
				}
			}

			return $ret;
		}
	}