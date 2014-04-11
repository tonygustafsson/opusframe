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

			if ($this->config->debug)
			{
				echo '<pre>';
					print_r($this);
					print_r($_GET);
					print_r($_POST);
					print_r($_SESSION);
				echo '</pre>';
			}

			if ($this->prevent_controller_load === FALSE)
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
		}

	}

	$opus = new opus;
?>