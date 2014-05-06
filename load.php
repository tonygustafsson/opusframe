<?php
	class load
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
		}

		public static function walla()
		{
			echo 'walla';
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
				load::view('404.sharedview');
			}
			
			//Save outputted views to $contents
			$contents = ob_get_contents();
			ob_end_clean();
			
			//Output the content to the browser
			echo $contents;
		}

		public function module($module)
		{
			$this_module_path = 'modules/' . $module . '.module.php';
			
			if (file_exists($this_module_path))
			{
				require_once($this_module_path);
				$class_name = $module . '_module';
				return new $class_name();
			}
			else
			{
				echo 'Could not find module ' . $this_module_path;
			}
		}

		public function view($view, $data = false)
		{
			$shared_view = (substr_compare($view, '.sharedview', -strlen('.sharedview'), strlen('.sharedview')) === 0) ? TRUE : FALSE;

			if ($shared_view)
			{
				$this_view_path = $this->opus->config->view_path_shared . '/' . $view . '.php';
			}
			else
			{
				$this_view_path = $this->opus->config->view_path . '/' . $view . '.view.php';
			}

			if (file_exists($this_view_path))
			{
				if ($data)
				{
					extract($data);
				}
				
				include($this_view_path);
			}
			else
			{
				echo 'View ' . $this_view_path . ' was not found.';
			}
		}

		public function model($model)
		{
			if (substr($model, 0, 7) == "shared_")
			{
				$this_model_path = $this->opus->config->base_path . $this->opus->config->model_path_shared . '/' . $model . '.model.php';
			}
			else
			{
				$this_model_path = 'areas/' . $this->opus->config->area_name . '/' . $model . '.model.php';
			}
			
			$thisModelPath = $this->opus->config->modelPath . $model . '.php';
		
			if (file_exists($this_model_path))
			{
				include($this_model_path);
				$class_name = $model . '_model';
				return new $class_name();
			}
			else
			{
				echo 'Could not find model ' . $this_model_path;
			}
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