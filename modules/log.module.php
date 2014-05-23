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
		}

		public function write($level, $message)
		{
			switch (strtolower($level))
			{
				case "critical":
					$log_level = 1;
				case "warning":
					$log_level = 2;
				case "info":
					$log_level = 3;
				default:
					$log_level = 4;
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

	}
?>