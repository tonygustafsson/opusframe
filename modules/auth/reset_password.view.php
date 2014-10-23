<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('header', $data);
	}
?>

<h2>Reset password</h2>

<p>Please set a new password below.</p>

<?php
	if (isset($form_elements))
	{
		echo '<form method="post" action="' . $this->opus->url('auth/reset_password_post') . '">';
		echo $form_elements;
		echo '<input type="submit" value="Reset password">';
		echo '</form>';
	}
?>


<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>