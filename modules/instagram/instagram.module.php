<?php
	class instagram_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
			$this->url_accessible = array('authorize', 'save_token', 'save_images');

			$this->token_path = $this->opus->config->base_path_absolute . "/cache/instagram/access_token";
			$this->image_path = $this->opus->config->base_path_absolute . "/assets/images/instagram/";

			$this->user_id = '1336819';
			$this->client_id = '3df4102f06814637b7f660639b409fa0';
			$this->client_secret = '0a6febb1ba7d4d18a34719210971184a';
			$this->redirect_uri = 'http://www.tonyg.se/projects/opusframe/instagram/save_token/';
			$this->response_type = 'code';

			$this->auth_url = 'https://api.instagram.com/oauth/authorize/?client_id=' . $this->client_id . '&redirect_uri=' . urlencode($this->redirect_uri) . '&response_type=' . $this->response_type;
			$this->token_url = 'https://api.instagram.com/oauth/access_token/';
			$this->media_url = 'https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent';
		}

		public function authorize()
		{
			//Use credentials to auth and redirect to save_token()
			$ch = curl_init($this->auth_url);

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);

			echo '<pre>'; print_r( curl_getinfo($ch) ); 
			echo htmlspecialchars($data);
			exit;

			curl_close($ch);

			echo $data;
		}

		public function save_token()
		{
			//Get the token and save to disk
			$code = $_GET['code'];

			$query = array( 'client_id' => $this->client_id,
							'client_secret' => $this->client_secret,
							'grant_type' => 'authorization_code',
							'redirect_uri' => urlencode($this->redirect_uri),
							'code' => $code
						);

			$ch = curl_init($this->token_url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);

			curl_close($ch);

			$json = json_decode($data);

			$access_token = $json->access_token;

			if (! empty($access_token))
			{
				$file = fopen($this->token_path, "w");
				fwrite($file, $access_token);
				fclose($file);
			}
		}

		public function get_token()
		{
			//Get token from disk
			$file = fopen($this->token_path, "r");
			$token = fread($file, filesize($file));
			fclose($file);

			return trim($token);
		}

		public function save_images()
		{
			//Get the JSON from the API and save images that is not already saved
			$query = array( 'access_token' => get_token());

			$ch = curl_init($this->media_url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);

			curl_close($ch);

			$json = json_decode($data);

			echo $json;
		}

		public function get_images()
		{
			//Get already saved images from disk
			$images = glob($this->image_path . "/*.jpg");

			return $images;
		}

	}
?>