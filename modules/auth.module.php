<?php
	class auth_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->opus->load->require_modules(array('session', 'form', 'database'));

			if (! session_id()) { session_start(); }

			/* protect_all or protect_none, sets the default, can also be changed per controller method */
			$this->method = "protect_all";
			$this->require_user_activation = TRUE; //If true, the user has to activate his user via email

			$this->db_table = "users";
			$this->db_id_column = "id";
			$this->db_username_column = "username";
			$this->db_password_column = "password";
			$this->db_real_name_column = "real_name";

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
					'friendly_name' => 'Email address',
					'type' => 'string',
					'form_name' => 'username',
					'min_length' => 3,
					'max_length' => 150
				),
				'real_name' => array(
					'friendly_name' => 'Name',
					'type' => 'string',
					'form_name' => 'real_name',
					'min_length' => 3,
					'max_length' => 150
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

			$this->user = (isset($_SESSION[$this->session_username_field])) ? $this->get_user($_SESSION[$this->session_username_field]) : NULL;

			if (isset($_SESSION[$this->session_ip_field]) && $_SESSION[$this->session_ip_field] !== $_SERVER['REMOTE_ADDR'])
				$this->logout(); //If the user has changed IP, log out the user

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
			else if ($is_restricted && $this->user['logged_in'] !== TRUE)
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

		public function login()
		{
			$make_settings['wanted_fields'] = array('username', 'password');
			$data['form_elements'] = $this->opus->form->make($this->data_model, $make_settings);

			load::view('login.sharedview', $data);
		}

		public function login_post()
		{
			$user = $this->get_user($_POST['username'], $by_pass_session_controll = TRUE);

			if (! isset($user) || ! password_verify($_POST['password'], $user[$this->db_password_column]))
			{
				$this->opus->session->set_flash('error', 'Wrong username or password!');
				$this->opus->load->url('auth/login');
			}

			//The user ID field says that the user is logged in
			$_SESSION[$this->session_username_field] = $user['username'];
			$_SESSION[$this->session_ip_field] = $_SERVER['REMOTE_ADDR'];

			//Remember the URL the person came from, and redirect him here instead of the startpage
			$url = (isset($_SESSION[$this->session_prev_url_field])) ? $_SESSION[$this->session_prev_url_field] : '/';
			unset($_SESSION[$this->session_prev_url_field]);

			$this->opus->load->url($url);
		}

		public function register()
		{
			$make_settings['wanted_fields'] =  array('real_name', 'username', 'password', 'verify_password');
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
				$insert_settings['fields'] = array('id', 'real_name', 'username', 'password');
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

		public function forgot_password()
		{
			$make_settings['wanted_fields'] =  array('username', 'password', 'verify_password');
			$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
			$make_settings['values'] = $this->opus->session->get_flash('form_values');

			$data['form_elements'] = $this->opus->form->make($this->data_model, $make_settings);

			load::view('register.sharedview', $data);
		}

		public function logout()
		{
			session_destroy();
			$this->opus->load->url('/');
		}

		public function get_user($username, $by_pass_session_controll = FALSE)
		{
			$get_settings['table_name'] = $this->db_table;
			$get_settings['select'] = array($this->db_id_column, $this->db_username_column, $this->db_real_name_column, $this->db_password_column);
			$get_settings['where'][$this->db_username_column] = $username;
			$db_user = $this->opus->database->get_row($get_settings);

			//If user does not exists in database
			if (! isset($db_user))
				return NULL;

			//If username does not match the session username (set by login_post)
			if ($by_pass_session_controll !== TRUE && (! isset($_SESSION[$this->session_username_field]) || $_SESSION[$this->session_username_field] != $db_user['username']))
				return NULL;

			$db_user['logged_in'] = TRUE;
			return $db_user;
		}

		public function get_first_name()
		{
			$names = explode(" ", $this->user[$this->db_real_name_column]);
			return $names[0];
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

		public function is_registered($username)
		{
			$get_settings['table_name'] = $this->db_table;
			$get_settings['select'] = array($this->db_username_column);
			$get_settings['where'][$this->db_username_column] = $username;
			$db_user = $this->opus->database->get_row($get_settings);

			return (count($db_user) > 0) ? TRUE : FALSE;
		}

	}
?>