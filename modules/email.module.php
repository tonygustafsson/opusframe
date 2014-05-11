<?php
	class email_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->smtp_host		= "mail.localhost";
			$this->smtp_user		= "";
			$this->smtp_pass		= "";
			$this->smtp_port		= 25;
			$this->smtp_timeout		= 5; //Seconds
			$this->command_retry_timeout = 25; //Milliseconds
			$this->command_retries	= 5; //command_retry_timeout times this number makes the total timeout for commands
			$this->charset			= "utf-8";
			$this->required_args	= array('to_email', 'to_name', 'subject', 'body', 'from_name', 'from_email', 'subject', 'body');

			$this->smtp_codes['service_ready'] = 220;
			$this->smtp_codes['command_successfull'] = 250;
			$this->smtp_codes['unknown_command'] = 500;
			$this->smtp_codes['data_ok'] = 354;
			$this->smtp_codes['connection_closed'] = 221;

			@$this->mail = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

			if (! $this->mail) {
					echo "ERROR: Failed to connect to SMTP server.";
					exit;
			}
		}

		public function __destruct()
		{
			@fclose($this->mail);
		}

		public function send($args)
		{
			//Check if required arguments are available
			foreach ($this->required_args as $required_arg)
			{
				if (! array_key_exists($required_arg, $args))
					return FALSE;
			}

			$this->send_command("HELO " . $_SERVER['SERVER_NAME'], $this->smtp_codes['service_ready']);

			if (isset($args['from_name']) && isset($args['from_email']))
				$this->send_command("MAIL FROM: <" . $args['from_email'] . ">", $this->smtp_codes['command_successfull']);

			if (isset($args['to_name']) && isset($args['to_email']))
				$this->send_command("RCPT TO: <" . $args['to_email'] . ">", $this->smtp_codes['command_successfull']);

			$this->send_command("DATA", $this->smtp_codes['command_successfull']);

			if (isset($args['subject']))
				$this->send_command("Subject: " . $args['subject'], $this->smtp_codes['data_ok']);

			$this->send_command("X-Mailer: PHP/" . phpversion());
			$this->send_command("Date: " . date("r"));

			if (isset($args['from_name']) && isset($args['from_email']))
				$this->send_command("From: " . $args['from_name'] . " <" . $args['from_email'] . ">");

			if (isset($args['to_name']) && isset($args['to_email']))
				$this->send_command("To: " . $args['to_name'] . " <" . $args['to_email'] . ">");

			if (isset($args['bbc_name']) && isset($args['bbc_email']))
				$this->send_command("Bcc: " . $args['from_name'] . " <" . $args['from_email'] . ">");

			if (isset($args['reply_to_name']) && isset($args['reply_to_email']))
				$this->send_command("Reply-To: " . $args['from_name'] . " <" . $args['from_email'] . ">");

			if (isset($args['html_format']) && $args['html_format'] === TRUE)
			{
				$this->send_command("MIME-Version: 1.0");
				$this->send_command("Content-type: text/plain; charset=" . $this->charset);
			}

			$this->send_command($args['body']);
			$this->send_command("."); //Queue the mail for delivery
			$this->send_command("quit", $this->smtp_codes['command_successfull']);
		}

		private function send_command($command, $wanted_response_code = FALSE)
		{
			//Send a command to the SMTP server, and first wait for a certain error code

			if ($wanted_response_code === FALSE)
			{
				//No need to wait for the right answer before sending.
				fwrite($this->mail, $command . "\r\n");
				echo '<pre>' . htmlentities($command) . '</pre>';
			}
			else
			{
				for ($x = 0; $x < $this->command_retries; $x++)
				{
					usleep($this->command_retry_timeout);
					$smtp_response = $this->read_command();
					echo $smtp_response . '<p>';
					$smtp_code = substr($smtp_response, 0, 3); //Get three first chars, like "250"

					if (substr($smtp_code, 0, 1) == "5")
						die('Recieved SMTP error: ' . $smtp_response);

					if ($smtp_code == $wanted_response_code)
					{
						fwrite($this->mail, $command . "\r\n");
						echo '<pre>' . htmlentities($command) . '</pre>';
						break;
					}
					else
						echo '<pre>Waiting for response code ' . $smtp_code . '</pre>';
				}
			}
		}

		private function read_command()
		{
			$data = "";

			while ($str = fread($this->mail, 512))
			{
				echo $str;
				$data .= $str;

				if (substr($str, 3, 1) == " ")
					break;
			}

			return $data;
		}

	}
?>