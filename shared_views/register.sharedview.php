<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<h2>Register</h2>

<form method="post" action="<?php echo $this->opus->config->base_url('auth/register_post'); ?>">
	<?= $form_elements ?>

	<input type="submit" value="Register">
</form>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer.sharedview', $data);
	}
?>