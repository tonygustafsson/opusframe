<?php
	class log_module
	{
		public function __construct()
		{	
			$this->opus =& opus::$instance;

			if (! file_exists($this->opus->config->log->path . $this->opus->config->log->file_name))
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

			if ($log_level <= $this->opus->config->log->level)
			{
				$trace = debug_backtrace();
				$topic = $trace[1]['class'];

				$message = date($this->opus->config->log->time_format) . "\t" . $topic . "\t" . $level . "\t" . $message . "\r\n";

				$fp = fopen($this->opus->config->log->path . $this->opus->config->log->file_name, $this->opus->config->log->file_method);
				fwrite($fp, $message);
				fclose($fp);
			}
		}

		private function remove_old_log_files()
		{
			$this->write('info', 'Removing old log files, if any...');

			$log_files = glob($this->opus->config->log->path . '/*', GLOB_NOSORT | GLOB_BRACE);
			$delete_older_than = strtotime("-" . $this->opus->config->log->history_keep . " days");

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