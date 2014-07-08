<?php
	class opus
	{
		public static $instance;

		public function __construct()
		{
			self::$instance =& $this;

			require_once('config.php');
			require_once('load.php');

			$this->prevent_controller_load = FALSE; //A module can prevent the loading of the controller
			$this->ending_task = FALSE; //A module can set tasks to do in the destruct

			$this->config = new config;
			$this->load = new load();
			$this->load->auto_load();

			$area_name = $this->config->area_name;
			$method_name = $this->config->method_name;

			//Load the method from the module if it exists and is accessible, if not - load controller
			if (
				isset($this->$area_name)
				&& method_exists($this->$area_name, $method_name)
				&& isset($this->$area_name->url_accessible)
				&& in_array($method_name, $this->$area_name->url_accessible)
			)
			{
				$this->$area_name->$method_name();
			}
			else if ($this->prevent_controller_load === FALSE)
			{
				$this->load->controller($this->config->path);
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

	}

	$opus = new opus;
?>