<?php
	class instagram_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
			
			$this->url_accessible = array('save_images', 'get_images');
		}

		public function save_images()
		{
			$media_url = $this->opus->config->instagram->media_url;

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

				$metadata_path = $this->opus->config->instagram->image_path . $image_id . '.txt';
				$image_small_path = $this->opus->config->instagram->image_path . $image_id . '-small.jpg';
				$image_medium_path = $this->opus->config->instagram->image_path . $image_id . '-medium.jpg';
				$image_large_path = $this->opus->config->instagram->image_path . $image_id . '-large.jpg';
				
				if ($this->opus->config->instagram->save_small_images)
						$this->save_image($image_small_url, $image_small_path);

				if ($this->opus->config->instagram->save_medium_images)
						$this->save_image($image_medium_url, $image_medium_path);
				
				if ($this->opus->config->instagram->save_large_images)
						$this->save_image($image_large_url, $image_large_path);

				$this->save_metadata($metadata_path, $metadata);
			}

            $this->opus->log->write('info', 'Downloading images from instagram with IP: ' . $_SERVER['REMOTE_ADDR']);
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

		public function new_get_images($count = FALSE, $offset = FALSE)
		{
			$metadata = array();
			$count = ($count !== FALSE) ? $count : $this->opus->config->instagram->no_images;
			$offset = ($offset !== FALSE) ? $offset : 0;

			//Get already saved images from disk
			$counter = 0;
			$metadata_files = array();

			$dir = $this->opus->config->instagram->image_path;

			if (is_dir($dir) && $dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== FALSE) {
					if (substr($file, -4) == '.txt')
						$metadata_files[] = $dir . $file;
				}
			}

			closedir($dh);

			$metadata_files = array_reverse(array_slice($metadata_files, -20));

			foreach ($metadata_files as $metadata_file) {
				$id = substr(basename($metadata_file), 0, strlen(basename($metadata_file)) - 4);

				$fp = fopen($metadata_file, "r");
				$content = fread($fp, filesize($metadata_file));
				fclose($fp);

				$content = explode("###", $content);
				$current_data['created_time'] = $content[0];
				$current_data['caption'] = $content[1];

				$large_image_path = $this->opus->config->instagram->image_path . $id . '-large.jpg';
				$medium_image_path = $this->opus->config->instagram->image_path . $id . '-medium.jpg';
				$small_image_path = $this->opus->config->instagram->image_path . $id . '-small.jpg';

				if ($this->opus->config->instagram->save_small_images && file_exists($small_image_path))
					$current_data['small_image_url'] = $this->opus->path_to_url($small_image_path);

				if ($this->opus->config->instagram->save_medium_images && file_exists($medium_image_path))
					$current_data['medium_image_url'] = $this->opus->path_to_url($medium_image_path);

				if ($this->opus->config->instagram->save_large_images && file_exists($large_image_path))
					$current_data['large_image_url'] = $this->opus->path_to_url($large_image_path);

				$metadata[] = $current_data;
			}

			return $metadata;
		}

		public function get_images($count = FALSE, $offset = FALSE)
		{
			$metadata = array();
			$count = ($count !== FALSE) ? $count : $this->opus->config->instagram->no_images;
			$offset = ($offset !== FALSE) ? $offset : 0;

			//Get already saved images from disk
			$metadata_files = glob($this->opus->config->instagram->image_path . "/*.txt", GLOB_NOSORT);
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

				$large_image_path = $this->opus->config->instagram->image_path . $id . '-large.jpg';
				$medium_image_path = $this->opus->config->instagram->image_path . $id . '-medium.jpg';
				$small_image_path = $this->opus->config->instagram->image_path . $id . '-small.jpg';

				if ($this->opus->config->instagram->save_small_images && file_exists($small_image_path))
					$current_data['small_image_url'] = $this->opus->path_to_url($small_image_path);

				if ($this->opus->config->instagram->save_medium_images && file_exists($medium_image_path))
					$current_data['medium_image_url'] = $this->opus->path_to_url($medium_image_path);

				if ($this->opus->config->instagram->save_large_images && file_exists($large_image_path))
					$current_data['large_image_url'] = $this->opus->path_to_url($large_image_path);

				$metadata[] = $current_data;
			}

			return $metadata;
		}

	}
?>