<?php
	class log_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			//Log level 0: Nothing is logged
			//Log level 1: CRITICAL, errors that breaks the page
			//Log level 2: WARNING, errors that impact users experience
			//Log Level 3: INFO, not very important, but nice to know
			//Log Level 4: DEBUG, more than you want to know in a production environement :)
			$this->log_level = 4;

			$this->log_path = $this->opus->config->base_path_absolute . '/logs/';
			$this->log_file_name = date("Ymd") . '.log';
			$this->log_time_format = "H:i:s";
			$this->log_file_method = "a"; //Open for writing only, pointer at the end of the file. Create if not exists.
			$this->log_history_keep = 90; //Keep log files newer than # days

			if (! file_exists($this->log_path . $this->log_file_name))
				$this->remove_old_log_files();
		}

		public function write($level, $message)
		{
			$log_level = 4; //Debug default

			switch (strtolower($level))
			{
				case "critical":
					$log_level = 1;
				case "warning":
					$log_level = 2;
				case "info":
					$log_level = 3;
			}

			if ($log_level <= $this->log_level)
			{
				$trace = debug_backtrace();
				$topic = $trace[1]['class'];

				$message = date($this->log_time_format) . "\t" . $topic . "\t" . $level . "\t" . $message . "\r\n";

				$fp = fopen($this->log_path . $this->log_file_name, $this->log_file_method);
				fwrite($fp, $message);
				fclose($fp);
			}
		}

		private function remove_old_log_files()
		{
			$this->write('info', 'Removing old log files, if any...');

			$log_files = glob($this->log_path . '/*', GLOB_NOSORT | GLOB_BRACE);
			$delete_older_than = strtotime("-" . $this->log_history_keep . " days");

			foreach($log_files as $log_file) {
				if (filectime($log_file) < $delete_older_than)
				{
					$this->write('info', 'Removing old log file ' . $log_file . ', created at ' . date("Ymd H:i:s", filectime($log_file)));
					unlink($log_file);
				}
			}
		}

	}
?>