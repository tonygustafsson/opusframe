<?php
	class database_module {

		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->opus->load->require_modules(array('form'));

			$this->db = new mysqli( $this->opus->config->database['host'],
									$this->opus->config->database['username'],
									$this->opus->config->database['password'],
									$this->opus->config->database['database']
								);

			if ($this->db->connect_errno > 0)
			    die('Unable to connect to database [' . $this->db->connect_error . ']');
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

			$sql .= ' FROM ' . $get_settings['table_name'];

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

			$result = $this->db->query($sql);

			if (! $result)
			{
			    die('Database error: '. $this->db->error);
			}
			else
			{
				if (isset($get_settings['get_total_rows']) && $get_settings['get_total_rows'] === TRUE)
				{
					//Also get number of rows for the table, for pagination mostly. Fastest way seems to be a separate query.
					$total_rows_sql = 'SELECT COUNT(*) AS total_rows FROM ' . $get_settings['table_name'];

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

			$sql .= ' FROM ' . $get_settings['table_name'];
			$sql .= ' WHERE ' . key($get_settings['where']) . ' = ';
			$sql .= is_numeric(current($get_settings['where'])) ? current($get_settings['where']) : '"' . current($get_settings['where']) . '"';
			$sql .= " LIMIT 1";

			$result = $this->db->query($sql);

			if (! $result)
			    die('Database error: '. $this->db->error);

			return $result->fetch_assoc();
		}

		public function query($query)
		{
			if (! $result = $this->db->query($query))
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

				foreach ($insert_settings['data_model'] as $column_name => $column_settings)
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

				if (isset($insert_settings['table_name']) && ! empty($keys) && ! empty($values))
				{
					$sql = 'INSERT INTO ' . $insert_settings['table_name'] . ' (' . $keys . ') VALUES (' . $values . ');';
					$result = $this->db->query($sql);

					if (! $result)
					    die('Database error: '. $this->db->error);
				}
			}

			return $this->db;
		}

		public function update($update_settings)
		{
			//Clean the input from dangerous data
			$_POST = $this->xss_clean($_POST);

			if ($this->opus->load->is_module_loaded('form'))
			{
				//Validate the input and add errors to list
				$this->db->form_errors = $this->opus->form->validate($update_settings['data_model']);
			}

			if (! isset($this->db->form_errors))
			{
				$changes = "";
				$x = 0;

				foreach ($update_settings['fields'] as $column)
				{
					if (isset($_POST[$column]))
					{
						$changes .= $column . ' = ';
						$changes .= (is_numeric($_POST[$column])) ? $_POST[$column] : '"' . $_POST[$column] . '"';

						if ($x < count($update_settings['fields']) - 1)
						{
							$changes .= ", ";
						}
					}

					$x++;
				}

				if (isset($update_settings['table_name']) && ! empty($changes))
				{
					$sql = 'UPDATE ' . $update_settings['table_name'] . ' SET ' . $changes;
					$sql .= ' WHERE ' . key($update_settings['where']) . ' = ';
					$sql .= is_numeric(current($update_settings['where'])) ? current($update_settings['where']) : '"' . current($update_settings['where']) . '"';

					$result = $this->db->query($sql);

					if (! $result)
					    die('Database error: '. $this->db->error);

				}
			}

			return $this->db;
		}

		public function delete($delete_settings)
		{
			//Clean the input from dangerous data
			$_POST = $this->xss_clean($_POST);
			
			$sql = "DELETE FROM " . $delete_settings['table_name'];
			$sql .= ' WHERE ' . key($delete_settings['where']) . ' = ';
			$sql .= is_numeric(current($delete_settings['where'])) ? current($delete_settings['where']) : '"' . current($delete_settings['where']) . '"';

			$result = $this->db->query($sql);

			if (! $result)
				die('Database error: '. $this->db->error);
				
			return $this->db;
		}

		private function xss_clean($input)
		{
			$output = array();

			foreach ($input as $key => $val)
			{
				$val = mysql_real_escape_string($val);
				$val = htmlspecialchars($val);

				$output[$key] = $val;
			}

			return $output;
		}
		
	}

?>