<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('header', $data);
	}
?>

<h2>Register</h2>

<form method="post" action="<?php echo $this->opus->url('auth/register_post'); ?>">
	<?= $form_elements ?>

	<input type="submit" value="Register">
</form>

<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>