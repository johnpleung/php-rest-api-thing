<?php
	class instagram_handler {

		private $settings;
		private $cache_handler;

		public function __construct($params) {
			$this->settings = [
				"base_url" => "https://api.instagram.com/v1/",
				"token" => $params['token']
			];
			$this->cache_handler = new cache_handler();
		}

		public function get_recent_media(){

			global $db;

			// Prevent concurrent identical requests
			$hash_key = md5("get_recent_media" . $this->settings['token']);
			if($db->in_queue($hash_key)){
				$db->wait_until_complete($hash_key);
				return $this->get_recent_media();
			}
			$db->add_to_queue($hash_key);

			try {
				$url = $this->settings['base_url'] . 'users/self/media/recent/?access_token=' . $this->settings['token'];

				$resp = @json_decode(file_get_contents($url));
				if($resp && isset($resp->data)){
					$resp = $resp->data;
				}

				return $resp;
			} catch(Exception $err){

			} finally {
				// Remove from queue
				$db->remove_from_queue($hash_key);
			}

		}
	}