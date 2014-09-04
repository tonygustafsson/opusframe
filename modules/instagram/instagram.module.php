<?php
	class instagram_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
			$this->url_accessible = array('authorize', 'save_token', 'save_images', 'get_images');

			$this->image_path = $this->opus->config->base_path_absolute . "/assets/images/instagram/";
			$this->user_id = '1336819';
			$this->number_of_images = 20; //Max 33
			$this->client_id = '3df4102f06814637b7f660639b409fa0';
			$this->media_url = 'https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent?client_id=' . $this->client_id . '&count=' . $this->number_of_images;
		}

		public function save_images()
		{
			$media_url = $this->media_url;

			if (isset($_GET['min_id']))
				$media_url .= '&min_id=' . $_GET['min_id'];

			if (isset($_GET['max_id']))
				$media_url .= '&max_id=' . $_GET['max_id'];

			echo $media_url;

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
				if ($image->type != "image")
					continue;

				$image_id = $image->id;
				$thumbnail_url = $image->images->thumbnail->url;
				$large_image_url = $image->images->standard_resolution->url;
				$created_time = $image->created_time;
				$caption = $image->caption->text;
				$metadata = $created_time . '###' . $caption;

				$metadata_path = $this->image_path . $image_id . '.txt';
				$thumbnail_path = $this->image_path . $image_id . '-thumb.jpg';
				$large_image_path = $this->image_path . $image_id . '.jpg';
				
				$this->save_image($thumbnail_url, $thumbnail_path);
				$this->save_image($large_image_url, $large_image_path);
				$this->save_metadata($metadata_path, $metadata);
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

		public function get_images()
		{
			$metadata = array();
			$count = 20;
			$offset = 0;

			//How many images should be delivered?
			if (isset($_GET['count']) && is_numeric($_GET['count']) && $_GET['count'] > 0 && $_GET['count'] < 51)
				$count = $_GET['count'];

			//From which file do we want to start to count?
			if (isset($_GET['offset']) && is_numeric($_GET['offset']) && $_GET['offset'] > 0)
				$offset = $_GET['offset'];

			//Get already saved images from disk
			$metadata_files = glob($this->image_path . "/*.txt");
			$metadata_files = array_slice($metadata_files, $offset, $count);

			foreach ($metadata_files as $metadata_file) {
				$id = substr(basename($metadata_file), 0, strlen(basename($metadata_file)) - 4);

				$fp = fopen($metadata_file, "r");
				$content = fread($fp, filesize($metadata_file));
				fclose($fp);

				$content = explode("###", $content);
				$current_data['created_time'] = $content[0];
				$current_data['caption'] = $content[1];

				$image_path = $this->image_path . $id . '.jpg';
				$thumbnail_path = $this->image_path . $id . '-thumb.jpg';

				if (file_exists($image_path) && file_exists($thumbnail_path))
				{
					$current_data['image_url'] = $this->opus->config->path_to_url($image_path);
					$current_data['thumbnail_url'] = $this->opus->config->path_to_url($thumbnail_path);

					$metadata[] = $current_data;
				}			
			}

			echo '<pre>';
			print_r($metadata);

		}

	}
?>