<?php
	class database_module {

		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->db = new mysqli( $this->opus->config->database['host'],
									$this->opus->config->database['username'],
									$this->opus->config->database['password'],
									$this->opus->config->database['database']
								);

			if ($this->db->connect_errno > 0)
			{
			    die('Unable to connect to database [' . $this->db->connect_error . ']');
			}
		}

		public function get_result($table, $options)
		{
			$select = $this->xss_clean($options['select']);
			$where = (isset($options['where']) && $options['where'] !== FALSE) ? $this->xss_clean($options['where']) : FALSE;
			$where_like = (isset($options['where_like']) && $options['where_like'] !== FALSE) ? $this->xss_clean($options['where_like']) : FALSE;
			$order_by = (isset($options['order_by']) && $options['order_by'] !== FALSE) ? $options['order_by'] : FALSE;

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

			$sql .= ' FROM ' . $table;

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

			$result = $this->db->query($sql);

			if (! $result)
			{
			    die('Database error: '. $this->db->error);
			}
			else
			{
				return $result;
			}
		}

		public function get_row($table, $select, $where)
		{
			$select = $this->xss_clean($select);
			$where = $this->xss_clean($where);

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

			$sql .= ' FROM ' . $table;
			$sql .= ' WHERE ' . key($where) . ' = ';
			$sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';
			$sql .= " LIMIT 1";

			$result = $this->db->query($sql);

			if (! $result)
			{
			    die('Database error: '. $this->db->error);
			}
			else
			{
				return $result->fetch_assoc();
			}
		}

		public function query($query)
		{
			if (! $result = $this->db->query($query))
			{
			    die('Database error: '. $this->db->error);
			}
		}

		public function insert($table, $insert)
		{
			$insert = $this->xss_clean($insert);

			echo '<pre>'; print_r($_POST);

			$keys = "";
			$values = "";
			$x = 0;

			foreach ($insert as $column)
			{
				if (isset($_POST[$column]))
				{
					$keys .= $column;
					$values .= (is_numeric($_POST[$column])) ? $_POST[$column] : '"' . $_POST[$column] . '"';

					if ($x < count($insert) - 1)
					{
						$keys .= ", ";
						$values .= ", ";
					}
				}

				$x++;
			}

			$sql = 'INSERT INTO ' . $table . ' (' . $keys . ') VALUES (' . $values . ');';
			$result = $this->db->query($sql);

			if (! $result)
			{
			    die('Database error: '. $this->db->error);
			}
			else
			{
				return $this->db;
			}
		}

		public function update($table, $update, $where)
		{
			$update = $this->xss_clean($update);
			$where = $this->xss_clean($where);

			$changes = "";
			$x = 0;

			foreach ($update as $column)
			{
				if (isset($_POST[$column]))
				{
					$changes .= $column . ' = ';
					$changes .= (is_numeric($_POST[$column])) ? $_POST[$column] : '"' . $_POST[$column] . '"';

					if ($x < count($update) - 1)
					{
						$changes .= ", ";
					}
				}

				$x++;
			}

			$sql = 'UPDATE ' . $table . ' SET ' . $changes;
			$sql .= ' WHERE ' . key($where) . ' = ';
			$sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';

			$result = $this->db->query($sql);

			if (! $result)
			{
			    die('Database error: '. $this->db->error);
			}
			else
			{
				return $this->db;
			}
		}

		public function delete($table, $where)
		{
			$where = $this->xss_clean($where);
			
			$sql = "DELETE FROM " . $table;
			$sql .= ' WHERE ' . key($where) . ' = ';
			$sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';

			$result = $this->db->query($sql);

			if (! $result)
			{
			    die('Database error: '. $this->db->error);
			}
			else
			{
				return $this->db;
			}
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