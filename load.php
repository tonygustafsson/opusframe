<?php
	class load
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
		}

		public function auto_load()
		{
			foreach ($this->opus->config->autoload_modules as $module)
			{
				$this->opus->$module = $this->module($module);
			}
		}

		public function controller($controller_name)
		{
			//Start output buffering
			ob_start();

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
			}
			else
			{
				//Page does not exist
				load::view('404');
			}
			
			//Save outputted views to $contents
			$contents = ob_get_contents();
			ob_end_clean();
			
			//Output the content to the browser
			echo $contents;
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
			else
				echo 'Could not find module ' . $this_module_path;
		}

		public function view($view, $data = FALSE)
		{
			//Get view path depending on if the caller method is a controller, a module or something else
			$backtrace = debug_backtrace()[0];

			if (isset($backtrace['file']) && strpos($backtrace['file'], '.controller.php') !== FALSE)
				$view_path = dirname($backtrace['file']) . '\\' . $view . '.view.php';
			else if (isset($backtrace['file']) && strpos($backtrace['file'], '.module.php') !== FALSE)
				$view_path = dirname($backtrace['file']) . '\\' . $view . '.view.php';
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
				$class_name = $model . '_model';
				return new $class_name();
			}
			else
				echo 'Could not find model ' . $model_path;
		}

		public function css($css_file)
		{
			echo '<link rel="stylesheet" type="text/css" media="screen" href="' . $this->opus->config->style_path . '/' . $css_file . '">';
		}

		public function js($js_file)
		{
			echo '<script src="' . $this->opus->config->js_path . '/' . $js_file . '"></script>';
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

		public function require_modules($modules)
		{
			foreach ($modules as $module)
			{
				if (! $this->is_module_loaded($module))
				{
					$callers = debug_backtrace();
					echo 'The module "' . $module . '" is required for using "' . $callers[1]['class'] . '".';
					exit;
				}
			}
		}

		public function is_module_loaded($module)
		{
			return (isset($this->opus->$module)) ? TRUE : FALSE;
		}

		public function is_ajax_request()
		{
			return (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		}

	}
?>