<?php
	class form_module {

		public function __construct()
		{
			$this->opus =& opus::$instance;
		}

		function make($data_model, $settings)
		{
			$wanted_fields = $settings['wanted_fields'] ?: FALSE;
			$values = (isset($settings['values']) && count($settings['values']) > 0) ? $settings['values'] : FALSE;
			$validation_errors = (isset($settings['validation_errors']) && ! empty($settings['validation_errors'])) ? $settings['validation_errors'] : FALSE;

			$html = "";

			foreach ($wanted_fields as $wanted_field)
			{
				if (array_key_exists($wanted_field, $data_model))
				{
					$field = $data_model[$wanted_field];
					$value = ($values !== FALSE && isset($values[$wanted_field])) ? $values[$wanted_field] : "";

					if ($field['type'] == 'id')
					{
						$html .= '
							<input type="hidden" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'string')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="text" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'int')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="number" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'email')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
								<input type="email" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'date')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="date" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . ((! empty($value)) ? $this->to_date_format($value) : date('Y-m-d')) . '">
						';
					}

					if ($field['type'] == 'range')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="range" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" min="' . $field['min_value'] . '" max="' . $field['max_value'] . '" value="' . (! empty($value) ? $value : $field['default_value']) . '">
							<div id="' . $field['form_name'] . '_helper">' . (! empty($value) ? $value : $field['default_value']) . '</div>
						';
					}

					if ($field['type'] == 'password')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="password" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '">
						';
					}

					if ($validation_errors !== FALSE && isset($validation_errors[$field['form_name']]))
					{
						foreach ($validation_errors[$field['form_name']] as $error)
						{
							$html .= '<div class="form-error">* ' . $error . '</div>';
						}
					}
				}
			}

			return $html;
		}

		function to_date_format($input)
		{
			$timestamp = strtotime($input);
			return date('Y-m-d', $timestamp);
		}

		function validate($fields)
		{
			foreach ($fields as $field)
			{
				if (isset($_POST[$field['form_name']]))
				{
					$this_input = $_POST[$field['form_name']];
				
					if (($field['type'] == 'int' || $field['type'] == 'id') && ! is_numeric($this_input))
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' must be a numeric value.';
					}

					if (isset($field['min_length']) && strlen($this_input) < $field['min_length'])
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' must be more than ' . $field['min_length'] . ' characters!';
					}
					
					if (isset($field['max_length']) && strlen($this_input) >  $field['max_length'])
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' must be less than ' .  $field['max_length'] . ' characters!';
					}
					
					if ($field['type'] == 'email' && filter_var($this_input, FILTER_VALIDATE_EMAIL) === false)
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted email address.';
					}

					if ($field['type'] == 'url' && filter_var($this_input, FILTER_VALIDATE_URL) === false)
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted URL.';
					}

					if ($field['type'] == 'ip' && filter_var($this_input, FILTER_VALIDATE_IP) === false)
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted IP address.';
					}
					
					if ($field['type'] == 'date' && ! strtotime($this_input))
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted date.';
					}
					
					if ($field['type'] == 'password')
					{
						if (! preg_match("#.*^(?=.{8,50})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $this_input))
						{
							$error[$field['form_name']][] = $field['friendly_name'] . ' is not a strong password. It needs to be at least 8 characters long, and include 1) One uppercase character, 2) One lowercase character and 3) A number.';
						}
					}
					
					if (isset($field['exact_match']))
					{
						foreach ($field['exact_match'] as $match)
						{
							$matched = FALSE;

							if ($this_input === $match)
							{
								$matched = TRUE;
								break;
							}
						}
						
						if (! $matched)
						{
							$error[$field['form_name']][] = $field['friendly_name'] . ' did not match.';
						}
					}
					
					if (isset($field['in_range']))
					{
						if (! in_array($this_input, $field['in_range']))
						{
							$error[$field['form_name']][] = $field['friendly_name'] . ' must be any of the following values: ' . implode(', ', $field['in_range']) . '.';
						}
					}
				}
			}

			return (isset($error)) ? $error : FALSE;
		}

	}

?>