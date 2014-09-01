<?php
	class instagram_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
			$this->url_accessible = array('authorize', 'save_token', 'save_images');

			$this->image_path = $this->opus->config->base_path_absolute . "/assets/images/instagram/";
			$this->user_id = '1336819';
			$this->client_id = '3df4102f06814637b7f660639b409fa0';
			$this->media_url = 'https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent?client_id=' . $this->client_id;
		}

		public function save_images()
		{
			//Get the JSON from the API and save images that is not already saved
			$ch = curl_init($this->media_url);

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);

			curl_close($ch);

			$json = json_decode($data);

			foreach ($json->data as $image)
			{
				$thumbnail_url = $image->images->thumbnail->url;
				$large_image_url = $image->images->standard_resolution->url;

				$thumbnail_path = $this->image_path . basename($thumbnail_url);
				$large_image_path = $this->image_path . basename($large_image_url);
				
				$this->save_image($thumbnail_url, $thumbnail_path);
				$this->save_image($large_image_url, $large_image_path);
			}
		}

		public function save_image($url, $path)
		{
			if (! file_exists($path))
			{
				//Image is not saved yet
				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

				$raw = curl_exec($ch);

				curl_close($ch);

				$fp = fopen($path, 'x');
				fwrite($fp, $raw);
				fclose($fp);
			}
		}

		public function get_images()
		{
			//Get already saved images from disk
			$images = glob($this->image_path . "/*.jpg");

			return $images;
		}

	}
?>