<?php
	class auth_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			if (! session_id()) { session_start(); }

			/* protect_all or protect_none, sets the default, can also be changed per controller method */
			$this->method = "protect_all";

			$this->db_table = "users";
			$this->db_id_column = "id";
			$this->db_username_column = "username";
			$this->db_password_column = "password";

			$this->session_id_field = 'user_session_id';
			$this->session_ip_field = 'ip';
			$this->session_username_field = 'username';
			$this->session_prev_url_field = 'previous_url';

			$this->data_model = array(
				'id' => array(
					'friendly_name' => 'ID',
					'type' => 'string',
					'form_name' => 'id',
					'max_length' => 30
				),
				'username' => array(
					'friendly_name' => 'Username',
					'type' => 'string',
					'form_name' => 'username',
					'min_length' => 3,
					'max_length' => 50
				),
				'password' => array(
					'friendly_name' => 'Password',
					'type' => 'password',
					'form_name' => 'password',
				),
				'verify_password' => array(
					'friendly_name' => 'Verify password',
					'type' => 'password',
					'form_name' => 'verify_password'
				)
			);

			//Set a unique ID that don't changes ever 5 minutes as session_id does.
			if (! isset($_SESSION[$this->session_id_field]))
				$_SESSION[$this->session_id_field] = uniqid();

			if (isset($_POST['password']) && isset($_POST['verify_password']))
			{
				$this->data_model['verify_password']['exact_match'] = array($_POST['password']);
			}

			$this->module_path = 'auth';

			$this->is_logged_in = (isset($_SESSION[$this->session_ip_field]) && $_SESSION[$this->session_ip_field] == $_SERVER['REMOTE_ADDR']) ? TRUE : FALSE;

			$this->restricted['movies/index'] = FALSE;
			$this->restricted['movies/sort'] = FALSE;
			$this->restricted['movies/search'] = FALSE;

			$is_restricted = $this->is_restricted($this->opus->config->area_name, $this->opus->config->method_name);
			$this->opus->prevent_controller_load = $is_restricted;

			if ($this->opus->config->area_name == $this->module_path)
			{
				//Redirect routing to this module
				$method_name = $this->opus->config->method_name;
				if (method_exists($this, $method_name))
				{
					$this->opus->prevent_controller_load = TRUE;
					$this->$method_name();
				}	
			}
			else if ($is_restricted)
			{
				//If it's not a part of the auth module and it's restricted, redirect to login
				if ($this->opus->config->area_name != $this->module_path)
				{
					$_SESSION[$this->session_prev_url_field] = $this->opus->config->path;
				}

				$make_settings['wanted_fields'] =  array('username', 'password');
				$data['form_elements'] = $this->opus->form->make($this->data_model, $make_settings);

				load::view('login.sharedview', $data);
			}
		}

		public function is_restricted($controller, $method_name)
		{
			$restricted_array_key = $controller . '/' . $method_name;

			if ($this->method == 'protect_none')
			{
				//Default permit everything
				$is_restricted =  array_key_exists($restricted_array_key, $this->restricted) ? $this->restricted[$restricted_array_key] : FALSE;
			}
			else
			{
				//Default permit nothing
				$is_restricted = array_key_exists($restricted_array_key, $this->restricted) ? $this->restricted[$restricted_array_key] : TRUE;
			}

			if ($is_restricted && (! isset($_SESSION[$this->session_ip_field]) || $_SESSION[$this->session_ip_field] != $_SERVER['REMOTE_ADDR']))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}

		}

		public function login()
		{
			$make_settings['wanted_fields'] = array('username', 'password');
			$data['form_elements'] = $this->opus->form->make($this->data_model, $make_settings);

			load::view('login.sharedview', $data);
		}

		public function login_post()
		{
			$user_id = $this->check_login($_POST['username'], $_POST['password']);

			if ($user_id !== FALSE)
			{
				$_SESSION[$this->session_id_field] = $user_id;
				$_SESSION[$this->session_ip_field] = $_SERVER['REMOTE_ADDR'];
				$_SESSION[$this->session_username_field] = $_POST['username'];

				$url = (isset($_SESSION[$this->session_prev_url_field])) ? $_SESSION[$this->session_prev_url_field] : '/';
				unset($_SESSION[$this->session_prev_url_field]);
				
				$this->opus->load->url($url);
			}
			else
			{
				$this->opus->session->set_flash('error', 'Wrong username or password!');
				$this->opus->load->url('auth/login');
			}
		}

		public function register()
		{
			$make_settings['wanted_fields'] =  array('username', 'password', 'verify_password');
			$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
			$make_settings['values'] = $this->opus->session->get_flash('form_values');

			$data['form_elements'] = $this->opus->form->make($this->data_model, $make_settings);

			load::view('register.sharedview', $data);
		}

		public function register_post()
		{
			$_POST[$this->db_id_column] = $_SESSION[$this->session_id_field];
			$_POST[$this->db_password_column] = password_hash($_POST[$this->db_password_column], PASSWORD_DEFAULT);
			
			if ($this->is_registered($_POST[$this->db_username_column]))
			{
				//User already exists
				$form_validation = array('username' => array(0 => 'This username is already taken.'));
			}
			else
			{
				$insert_settings['table_name'] = $this->db_table;
				$insert_settings['data_model'] = $this->data_model;
				$insert_settings['fields'] = array('id', 'username', 'password');
				$insert_output = $this->opus->database->insert($insert_settings);
			}

			//Merge duplicate user check error with the form validation if there is any.
			if (isset($insert_output->form_errors) && isset($form_validation))
				$insert_output->form_errors = array_merge($insert_output->form_errors, $form_validation);
			elseif (! isset($insert_output->form_errors) && isset($form_validation))
				$insert_output->form_errors = $form_validation;

			$form_validation = (isset($insert_output->form_errors)) ? array_merge($insert_output->form_errors, $form_validation) : $insert_output->form_errors;

			if (! isset($insert_output->form_errors))
			{
				$_SESSION[$this->session_ip_field] = $_SERVER['REMOTE_ADDR'];
				$_SESSION[$this->session_username_field] = $_POST['username'];

				$this->opus->load->url('/');
			}
			else
			{
				$this->opus->session->set_flash('form_validation', $insert_output->form_errors);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url($this->module_path . '/register');
			}
		}

		public function logout()
		{
			session_destroy();
			$this->opus->load->url('/');
		}

		public function is_registered($username)
		{
			$get_settings['table_name'] = $this->db_table;
			$get_settings['select'] = array($this->db_username_column);
			$get_settings['where'][$this->db_username_column] = $username;
			$db_user = $this->opus->database->get_row($get_settings);

			return (count($db_user) > 0) ? TRUE : FALSE;
		}

		public function check_login($username, $password)
		{
			$get_settings['table_name'] = $this->db_table;
			$get_settings['select'] = array($this->db_id_column, $this->db_password_column);
			$get_settings['where'][$this->db_username_column] = $username;
			$db_user = $this->opus->database->get_row($get_settings);

			if (password_verify($password, $db_user[$this->db_password_column]))
				return $db_user[$this->db_id_column];
			else
				return FALSE;
		}

	}
?>