<?php
	class config
	{
		public static $site_name;

		function __construct()
		{
			$this->site_name = "Movie Database";
			$this->site_email = "movie.database@test.com";
			$this->debug = FALSE;

			//Installed modules: database, auth, cache
			$this->autoload_modules = array(
					'log',
					'session',
					'form',
					'database',
					'auth',
					'url'
				);

			$this->cache = array(
				'tony' => 10
			);
			
			$this->routes = array(
				'default' => 'movies',
				'404' =>	'404'
			);

			$this->database = array(
				'host' => 'localhost',
				'username' => 'root',
				'password' => '',
				'database' => 'opusframe'
			);

			$this->set_parameters();
		}

		public function set_parameters()
		{
			$this->base_url = $this->remove_trailing_slash('http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['PHP_SELF']), "", $_SERVER['PHP_SELF']));
			$this->base_path = $this->remove_trailing_slash(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']));
			$this->base_path_absolute = $this->remove_trailing_slash($_SERVER['DOCUMENT_ROOT'] . $this->base_path);

			$path = str_replace($this->base_path, "", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
			$path = $this->remove_leading_slash($path);	
			$path = $this->remove_trailing_slash($path);
			$path = ($path == "") ? $this->routes['default'] : $path;
			$this->path = $path;

			$url_segments = explode("/", $this->path);
		
			$router_match = (isset($url_segments[1])) ? $url_segments[0] . '/'  . $url_segments[1] : $url_segments[0];
			$router_segments = (isset($this->routes[$router_match])) ? explode("/", $this->routes[$router_match]) : FALSE;
		
			$area_name = (isset($url_segments[0]) && ! strpos($url_segments[0], '=')) ? $url_segments[0] : $this->routes['default'];
			
			if (! $router_segments)
			{
				//Not routed
				$method_name = (isset($url_segments[1]) && ! strpos($url_segments[1], '=')) ? $url_segments[1] : 'index';
			}
			else
			{
				$method_name = (isset($router_segments[1]) && ! strpos($router_segments[1], '=')) ? $router_segments[1] : 'index';
			}
			
			$url_args = array_slice($url_segments, 2);
			$area_name = (array_key_exists($area_name, $this->routes)) ? $this->routes[$area_name] : $area_name;
			$area_name = (strpos($area_name, '/')) ? substr($area_name, 0, strpos($area_name, '/')) : $area_name;

			$this->area_name = $area_name;
			$this->area_url = $this->base_url . '/' .  $area_name;
			$this->method_name = $method_name;
			$this->url_args = $url_args;
			$this->modulesPath = "./modules/";
			$this->controller_path = 'areas/' . $this->area_name . '/' . $this->area_name . '.controller.php';
			$this->style_path = $this->base_path . '/assets/css/';
			$this->js_path = $this->base_path . '/assets/js/';
		}

		private function remove_leading_slash($input)
		{
			if (substr($input, 0, 1) == '/') 
			{
				return substr($input, 1, strlen($input));
			}
			else
			{
				return $input;
			}
		}

		private function remove_trailing_slash($input)
		{
			if (substr($input, -1) == '/') 
			{
				return substr($input, 0, strlen($input) - 1);
			}
			else
			{
				return $input;
			}
		}

		public function base_url($url)
		{
			return $this->base_url . '/' . $url;
		}
	}
?>