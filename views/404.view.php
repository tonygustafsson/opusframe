<?php
	if (! load::is_ajax_request()) {
		load::view('header', $data);
	}
?>

<h2>Page could not be found</h2>

<p>I'm sorry but I cannot find the page you are looking for!</p>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer', $data);
	}
?>