<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('header', $data);
	}
?>

<h2>Page could not be found</h2>

<p>I'm sorry but I cannot find the page you are looking for!</p>

<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>