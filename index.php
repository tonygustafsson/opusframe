<?php
	class opus
	{
		public static $instance;

		public function __construct()
		{
			self::$instance =& $this;

			require_once('config.php');
			require_once('load.php');

			$this->prevent_controller_load = FALSE; //An autoloaded module can prevent the loading of the controller
			$this->ending_task = FALSE; //A autoloaded module can set tasks to do in the destruct

			$this->config = new config;
			$this->load = new load();

			$area_name = $this->config->area_name;
			$method_name = $this->config->method_name;

			//Start output buffering so that modules can manipulate data
			ob_start();

			//Load modules automatically
			foreach ($this->config->pre_load_modules as $module)
			{
				$this->$module = $this->load->module($module);
			}

			//Load modules if not already loaded with preloading
			if ($this->config->auto_route_modules &&  ! empty($area_name) && ! isset($this->$area_name)) {
				$this->$area_name = $this->load->module($area_name);
			}

			if ($this->prevent_controller_load === FALSE && $this->load->controller($this->config->path))
			{
				//Run the controller if an autoloaded module does not prevent it through prevent_controller_load
				return;
			}
			else if (
				isset($this->$area_name)
				&& method_exists($this->$area_name, $method_name)
				&& isset($this->$area_name->url_accessible)
				&& in_array($method_name, $this->$area_name->url_accessible)
			)
			{
				//Load the method from the module if it exists and is accessible
				$this->$area_name->$method_name();
			}
			else if ($this->prevent_controller_load === FALSE)
			{
				//Page does not exist (no controller found, no module reroute found)
				header("HTTP/1.0 404 Not Found");
				$this->load->view('404');
			}
		}

		public function __destruct()
		{
			if ($this->ending_task)
			{
				foreach ($this->ending_task as $module => $task)
				{
					$this->$module->$task();
				}
			}

			//Save outputted views to $contents
			$contents = ob_get_contents();
			ob_end_clean();
			
			//Output the content to the browser
			echo $contents;

			if ($this->config->debug)
			{
				echo '<pre id="debug">';
					echo '<h2>Debug info</h2>';
					echo '<h3>$this</h3>';
					print_r($this);
					echo '<h3>$_GET</h3>';
					print_r($_GET);
					echo '<h3>$_POST</h3>';
					print_r($_POST);
					echo '<h3>$_SESSION</h3>';
					print_r($_SESSION);
					echo '<h3>$_SERVER</h3>';
					print_r($_SERVER);
				echo '</pre>';
			}
		}

		public function __get($module)
		{
			//Dynamically load modules with $this->opus->module_name->method_name();

			if ($this->config->auto_load_modules !== TRUE)
				throw new Exception("Module '" . $module . "' is not set to load automatically.");

			$module_path = 'modules/' . $module . '/' . $module . '.module.php';
			$class_name = $module . '_module';

			//Do not load the module twice
			if (class_exists($class_name))
				return $this->module;

			if (file_exists($module_path))
			{
				include_once($module_path);
				$this->$module = new $class_name;
				return $this->$module;
			}
		}

	}

	//Run the framework
	$opus = new opus;
?>