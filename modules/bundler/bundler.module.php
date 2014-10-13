<?php
	class bundler_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->url_accessible = array('bundle_js');
		}

		public function bundle_js()
		{
			$files = array('assets/js/main.js', 'assets/js/custom.js');
			$bundle = "/* Bundled at " . date("c") . " */";

			foreach ($files as $file)
			{
				if (file_exists($file))
				{
					$bundle .= "\r\n\r\n/* File: " . $file . " */\r\n";
					$bundle .= file_get_contents($file);
				}
			}

			echo $bundle;
		}

	}
?>