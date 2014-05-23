<?php
	class session_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			if (! session_id()) { session_start(); }

			$this->flash_pre_name = 'flash_';
			$this->flash_next_name = 'next_';
			$this->flash_current_name = 'current_';

			$this->remove_old_flash();
			$this->prepare_current_flash();
		}

		public function remove_old_flash()
		{
			foreach ($_SESSION as $key => $val)
			{
				$flash_current_identifier = $this->flash_pre_name . $this->flash_current_name;

				if (substr($key, 0, strlen($flash_current_identifier)) === $flash_current_identifier)
				{
					unset($_SESSION[$key]);
				}
			}
		}

		public function prepare_current_flash()
		{
			foreach ($_SESSION as $key => $val)
			{
				$flash_current_identifier = $this->flash_pre_name . $this->flash_current_name;
				$flash_next_identifier = $this->flash_pre_name . $this->flash_next_name;

				if (substr($key, 0, strlen($flash_next_identifier)) === $flash_next_identifier)
				{
					$flash_key = str_replace($flash_next_identifier, "", $key);
					$flash_new_name = $this->flash_pre_name . $this->flash_current_name . $flash_key;

					$_SESSION[$flash_new_name] = $val;
					unset($_SESSION[$key]);
				}
			}
		}

		public function set_flash($name, $data)
		{
			$session_name = $this->flash_pre_name . $this->flash_next_name . $name;

			if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'Flash set: ' . $session_name); }

			$_SESSION[$session_name] = $data;
		}

		public function get_flash($name)
		{
			$session_name = $this->flash_pre_name . $this->flash_current_name . $name;

			if (isset($_SESSION[$session_name]) && ! empty($_SESSION[$session_name]))
			{
				if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'Flash get: ' . $name); }

				return $_SESSION[$session_name];
			}
			else
			{
				return "";
			}
		}

	}
?>