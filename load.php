<?php
	class load
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
		}

		public function controller($controller_name)
		{
			if (file_exists($this->opus->config->controller_path))
			{
				//Specific controller, incl default
				require_once($this->opus->config->controller_path);
				$class_name = $this->opus->config->area_name . '_controller';
				$controller = new $class_name();
				$method_name = $this->opus->config->method_name;
				
				if (method_exists($controller, $method_name))
				{
					$controller->$method_name();
				}
				else
				{
					//Page does not exist, get index instead
					$controller->index();
				}

				return true;
			}
			else
			{
				return false;
			}
		}

		public function module($module)
		{
			$this_module_path = 'modules/' . $module . '/' . $module . '.module.php';

			if (file_exists($this_module_path))
			{
				require_once($this_module_path);
				$class_name = $module . '_module';
				return new $class_name();
			}
		}

		public function view($view, $data = FALSE)
		{
			//Get view path depending on if the caller method is a controller, a module or something else
			$backtrace = debug_backtrace()[0];

			if (isset($backtrace['file']) && strpos($backtrace['file'], '.controller.php') !== FALSE)
				$view_path = dirname($backtrace['file']) . '/' . $view . '.view.php';
			else if (isset($backtrace['file']) && strpos($backtrace['file'], '.module.php') !== FALSE)
				$view_path = dirname($backtrace['file']) . '/' . $view . '.view.php';
			else
				$view_path = $this->opus->config->base_path_absolute . '/views/' . $view . '.view.php';

			//Load the view
			if (file_exists($view_path))
			{
				if ($data)
					extract($data);
				
				include($view_path);
			}
			else
				echo 'View ' . $view_path . ' was not found.';
		}

		public function model($model)
		{
			$class_name = $model . '_model';

			//If the class already exists, do not load it again
			if (class_exists($class_name))
				return $class_name;

			//Get model path depending on if the caller method is a controller, a module or something else
			$backtrace = debug_backtrace()[0];

			if (isset($backtrace['file']) && strpos($backtrace['file'], '.controller.php') !== FALSE)
				$model_path = dirname($backtrace['file']) . '\\' . $model . '.model.php';
			else if (isset($backtrace['file']) && strpos($backtrace['file'], '.module.php') !== FALSE)
				$model_path = dirname($backtrace['file']) . '\\' . $model . '.model.php';
			else
				$model_path = $this->opus->config->base_path_absolute . '/models/' . $model . '.model.php';
					
			if (file_exists($model_path))
			{
				include($model_path);
				return new $class_name();
			}
			else
				echo 'Could not find model ' . $model_path;
		}

		public function css($css_file, $media = "all")
		{
			echo '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $this->opus->config->style_path . $css_file . '">';
		}

		public function js($js_file)
		{
			echo '<script type="text/javascript" src="' . $this->opus->config->js_path . $js_file . '"></script>';
		}

		public function url($url)
		{
			$url = ($url == "/") ? "" : $url;
			$url = $this->opus->config->base_url($url);
			header('Location: ' . $url);
			exit;
		}

		public function image($path)
		{
			//Checks if the image exists and delivers it, if not, deliver a default image
			$path = $this->opus->config->base_path_absolute . '/' . $path;

			if (file_exists($path))
				return $this->opus->config->path_to_url($path);
			else
				return $this->opus->config->path_to_url($this->opus->config->image_missing);
		}

		public function is_ajax_request()
		{
			return (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		}

	}
?>