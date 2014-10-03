<?php
	class instagram_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
			$this->url_accessible = array('save_images', 'get_images');

			$this->image_path = $this->opus->config->base_path_absolute . "/assets/images/instagram/";
			$this->user_id = '1336819';
			$this->no_instagram_images = 20; //From Instagram API, Max 33
			$this->client_id = '3df4102f06814637b7f660639b409fa0';
			$this->media_url = 'https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent?client_id=' . $this->client_id . '&count=' . $this->no_instagram_images;
		
			$this->save_small_images = TRUE;
			$this->save_medium_images = TRUE;
			$this->save_large_images = TRUE;

			$this->no_images = 10; //Images to get from cache
		}

		public function save_images()
		{
			$media_url = $this->media_url;

			if (isset($_GET['min_id']))
				$media_url .= '&min_id=' . $_GET['min_id'];

			if (isset($_GET['max_id']))
				$media_url .= '&max_id=' . $_GET['max_id'];

			//Get the JSON from the API and save images that is not already saved
			$ch = curl_init($media_url);

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);

			curl_close($ch);

			$json = json_decode($data);

			foreach ($json->data as $image)
			{
				//Ignore videos
				if ($image->type != "image")
					continue;

				$image_id = $image->id;
				$image_small_url = $image->images->thumbnail->url;
				$image_medium_url = $image->images->low_resolution->url;
				$image_large_url = $image->images->standard_resolution->url;
				$created_time = $image->created_time;
				$caption = (isset($image->caption->text)) ? $image->caption->text : "";
				$metadata = $created_time . '###' . $caption;

				$metadata_path = $this->image_path . $image_id . '.txt';
				$image_small_path = $this->image_path . $image_id . '-small.jpg';
				$image_medium_path = $this->image_path . $image_id . '-medium.jpg';
				$image_large_path = $this->image_path . $image_id . '-large.jpg';
				
				if ($this->save_small_images)
						$this->save_image($image_small_url, $image_small_path);

				if ($this->save_medium_images)
						$this->save_image($image_medium_url, $image_medium_path);
				
				if ($this->save_large_images)
						$this->save_image($image_large_url, $image_large_path);

				$this->save_metadata($metadata_path, $metadata);
			}

			echo count($json->data) . ' new photos were downloaded.';
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

		public function save_metadata($path, $metadata)
		{
			if (! file_exists($path))
			{
				//Image is not saved yet
				$fp = fopen($path, 'x');
				fwrite($fp, $metadata);
				fclose($fp);
			}
		}

		public function get_images($count = FALSE, $offset = FALSE)
		{
			$metadata = array();
			$count = ($count !== FALSE) ? $count : $this->no_images;
			$offset = ($offset !== FALSE) ? $offset : 0;

			//Get already saved images from disk
			$metadata_files = glob($this->image_path . "/*.txt");
			krsort($metadata_files);
			$metadata_files = array_slice($metadata_files, $offset, $count);

			foreach ($metadata_files as $metadata_file) {
				$id = substr(basename($metadata_file), 0, strlen(basename($metadata_file)) - 4);

				$fp = fopen($metadata_file, "r");
				$content = fread($fp, filesize($metadata_file));
				fclose($fp);

				$content = explode("###", $content);
				$current_data['created_time'] = $content[0];
				$current_data['caption'] = $content[1];

				$large_image_path = $this->image_path . $id . '-large.jpg';
				$medium_image_path = $this->image_path . $id . '-medium.jpg';
				$small_image_path = $this->image_path . $id . '-small.jpg';

				if ($this->save_small_images && file_exists($small_image_path))
					$current_data['small_image_url'] = $this->opus->config->path_to_url($small_image_path);

				if ($this->save_medium_images && file_exists($medium_image_path))
					$current_data['medium_image_url'] = $this->opus->config->path_to_url($medium_image_path);

				if ($this->save_large_images && file_exists($large_image_path))
					$current_data['large_image_url'] = $this->opus->config->path_to_url($large_image_path);

				$metadata[] = $current_data;
			}

			return $metadata;
		}

	}
?>