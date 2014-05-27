<?php
	if (! load::is_ajax_request()) {
		load::view('header', $data);
	}
?>

<h2>Forgot password</h2>

<form method="post" action="<?php echo $this->opus->config->base_url('auth/forgot_password_post'); ?>">
	<?= $form_elements ?>

	<input type="submit" value="E-mail my password">
</form>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer', $data);
	}
?>