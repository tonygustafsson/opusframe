<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('header', $data);
	}
?>

<h2>Forgot password</h2>

<form method="post" action="<?php echo $this->opus->url('auth/forgot_password_post'); ?>">
	<?= $form_elements ?>

	<input type="submit" value="E-mail my password">
</form>

<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>