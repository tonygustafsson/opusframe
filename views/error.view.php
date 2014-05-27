<?php
	if (! load::is_ajax_request()) {
		load::view('header', $data);
	}
?>

<h2>Error</h2>

<?php
	if (isset($errors))
	{
		foreach ($errors as $field)
		{
			foreach ($field as $error)
			{
				echo '<p>' . $error . '</p>';
			}
		}
	}
?>

<a href="javascript:history.go(-1)">Try again</a>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer', $data);
	}
?>