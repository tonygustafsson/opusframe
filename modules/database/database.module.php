<?php
	class database_module {

		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->opus->load->require_modules(array('form'));

			$this->xss_encoding = "ISO8859-1";

			$this->db = new mysqli( $this->opus->config->database['host'],
									$this->opus->config->database['username'],
									$this->opus->config->database['password'],
									$this->opus->config->database['database']
								);

			if ($this->db->connect_errno > 0)
			    exit('Unable to connect to database [' . $this->db->connect_error . ']');
		}

		public function get_result($get_settings)
		{
			$select = $this->xss_clean($get_settings['select']);
			$where = (isset($get_settings['where']) && $get_settings['where'] !== FALSE) ? $this->xss_clean($get_settings['where']) : FALSE;
			$where_like = (isset($get_settings['where_like']) && $get_settings['where_like'] !== FALSE) ? $this->xss_clean($get_settings['where_like']) : FALSE;
			$order_by = (isset($get_settings['order_by']) && $get_settings['order_by'] !== FALSE) ? $get_settings['order_by'] : FALSE;
			$limit = (isset($get_settings['limit_offset']) && isset($get_settings['limit_count'])) ? $get_settings['limit_offset'] . ', ' . $get_settings['limit_count'] : FALSE;

			$sql = 'SELECT ';
			$x = 0;

			foreach ($select as $column)
			{
				$sql .= $column;

				if ($x < count($select) - 1)
				{
					$sql .= ', ';
				}

				$x++;
			}

			$sql .= ' FROM ' . $get_settings['data_model']['db_table'];

			if ($where !== FALSE)
			{
				$sql .= ' WHERE ' . key($where) . ' = ';
				$sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';
			}

			if ($where_like !== FALSE)
			{
				$sql .= ' WHERE ' . key($where_like) . ' LIKE ';
				$sql .= '"%' . current($where_like) . '%"';
			}

			if ($order_by !== FALSE)
			{
				$sql .= ' ORDER BY ' . $order_by;
			}

			if ($limit !== FALSE)
			{
				$sql .= ' LIMIT ' . $limit;
			}

			if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'SQL Query: ' . $sql); }
			$result = $this->db->query($sql);

			if (! $result)
			    die('Database error: '. $this->db->error);
			else
			{
				if (isset($get_settings['get_total_rows']) && $get_settings['get_total_rows'] === TRUE)
				{
					//Also get number of rows for the table, for pagination mostly. Fastest way seems to be a separate query.
					$total_rows_sql = 'SELECT COUNT(*) AS total_rows FROM ' . $get_settings['data_model']['db_table'];

					if ($where !== FALSE)
					{
						$total_rows_sql .= ' WHERE ' . key($where) . ' = ';
						$total_rows_sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';
					}

					if ($where_like !== FALSE)
					{
						$total_rows_sql .= ' WHERE ' . key($where_like) . ' LIKE ';
						$total_rows_sql .= '"%' . current($where_like) . '%"';
					}

					$total_rows_result = $this->db->query($total_rows_sql);
					$total_rows_result = mysqli_fetch_object($total_rows_result);

					$result->total_rows = $total_rows_result->total_rows;
				}

				return $result;
			}
		}

		public function get_row($get_settings)
		{
			$get_settings['select'] = $this->xss_clean($get_settings['select']);
			$get_settings['where'] = $this->xss_clean($get_settings['where']);

			$sql = 'SELECT ';
			$x = 0;

			foreach ($get_settings['select'] as $column)
			{
				$sql .= $column;

				if ($x < count($get_settings['select']) - 1)
				{
					$sql .= ', ';
				}

				$x++;
			}

			$sql .= ' FROM ' . $get_settings['data_model']['db_table'];
			$sql .= ' WHERE ' . key($get_settings['where']) . ' = ';
			$sql .= is_numeric(current($get_settings['where'])) ? current($get_settings['where']) : '"' . current($get_settings['where']) . '"';
			$sql .= " LIMIT 1";

			if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'SQL Query: ' . $sql); }
			$result = $this->db->query($sql);

			if (! $result)
			    die('Database error: '. $this->db->error);

			return $result->fetch_assoc();
		}

		public function query($sql)
		{
			if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'SQL Query: ' . $sql); }

			if (! $result = $this->db->query($sql))
			{
			    die('Database error: '. $this->db->error);
			}
		}

		public function insert($insert_settings)
		{
			//Clean the input from dangerous data
			$_POST = $this->xss_clean($_POST);

			if ($this->opus->load->is_module_loaded('form'))
			{
				//Validate the input and add errors to list
				$this->db->form_errors = $this->opus->form->validate($insert_settings['data_model']);
			}

			if (! isset($this->db->form_errors))
			{
				$keys = "";
				$values = "";
				$x = 1;

				foreach ($insert_settings['data_model']['fields'] as $column_name => $column_settings)
				{
					if (in_array($column_settings['form_name'], $insert_settings['fields']) && isset($_POST[$column_settings['form_name']]))
					{
						$keys .= $column_name;
						$values .= (is_numeric($_POST[$column_name])) ? $_POST[$column_name] : '"' . $_POST[$column_name] . '"';

						if ($x < count($insert_settings['fields']))
						{
							$keys .= ", ";
							$values .= ", ";
						}

						$x++;
					}


				}

				if (isset($insert_settings['data_model']['db_table']) && ! empty($keys) && ! empty($values))
				{
					$sql = 'INSERT INTO ' . $insert_settings['data_model']['db_table'] . ' (' . $keys . ') VALUES (' . $values . ');';
					if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'SQL Query: ' . $sql); }

					$result = $this->db->query($sql);

					if (! $result)
					    die('Database error: '. $this->db->error);
				}
			}

			return $this->db;
		}

		public function update($update_settings)
		{
			if ($this->opus->load->is_module_loaded('form'))
			{
				//Validate the input and add errors to list
				$this->db->form_errors = $this->opus->form->validate($update_settings['data_model']);
			}

			if (! isset($this->db->form_errors))
			{
				$changes = "";
				$val_types = "";
				$value_bindings = array();
				$x = 0;

				foreach ($update_settings['fields'] as $column)
				{
					if (isset($_POST[$column]))
					{
						$changes .= $column . ' = ?';
						$value_bindings[] = $_POST[$column];
						$val_types .= (is_numeric($_POST[$column])) ? "i" : "s";

						if ($x < count($update_settings['fields']) - 1)
						{
							$changes .= ", ";
						}
					}

					$x++;
				}

				if (isset($update_settings['data_model']['db_table']) && ! empty($changes))
				{
					$sql = 'UPDATE ' . $update_settings['data_model']['db_table'] . ' SET ' . $changes;
					$sql .= ' WHERE ' . key($update_settings['where']) . ' = ?';
					$val_types .= is_numeric(current($update_settings['where'])) ? "i" : "s";

					if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'SQL Query: ' . $sql); }

					$value_bindings[] = array_values($update_settings['where'])[0];
					array_unshift($value_bindings, $val_types);

					if (! $statement = $this->db->prepare($sql))
						exit($this->db->error);

					if (! call_user_func_array(array($statement, 'bind_param'), $this->ref_values($value_bindings)))
						exit($statement->error);

					if (! $statement->execute())
						exit($statement->error);

					$statement->close();

				}
			}

			return $this->db;
		}

		public function delete($delete_settings)
		{
			$sql = "DELETE FROM " . $delete_settings['data_model']['db_table'] . " WHERE " . key($delete_settings['where']) . " = ?";
			$val_type = is_numeric($delete_settings['where']) ? 'i' : 's';

			if (! $statement = $this->db->prepare($sql))
				exit($this->db->error);

			if (! $statement->bind_param($val_type, current($delete_settings['where'])))
				exit($statement->error);

			if (! $statement->execute())
				exit($statement->error);

			$statement->close();

			if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('debug', 'SQL Query: ' . $sql); }

			return $this->db;
		}

		public function ref_values($arr){
			$refs = array();

			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];

			return $refs;
		}

		private function get_bind_params($parameters)
		{
			$parameters;
			$counter = 0;
			$bind_params = "";

			foreach ($parameters as $parameter)
			{
				if (isset($_POST[$parameter]))
				{
					$bind_params .= $_POST[$parameter];

					if ($counter < count($parameters) - 1)
						$bind_params .= ", ";
				}

				$counter++;
			}

			return $bind_params;
		}

		private function xss_clean($input)
		{
			$output = array();

			foreach ($input as $key => $val)
			{
				$val = mysql_real_escape_string($val);
				$val = htmlspecialchars($val, ENT_SUBSTITUTE, $this->xss_encoding);

				$output[$key] = $val;
			}

			return $output;
		}
		
	}

?>