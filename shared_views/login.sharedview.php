<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<h2>Log in</h2>

<form method="post" action="<?php echo $this->opus->config->base_url('auth/login_post'); ?>">
	<?= $form_elements ?>

	<a href="<?=$this->opus->config->base_url('auth/forgot_password')?>">I forgot my password</a>

	<input type="submit" value="Log in">
</form>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer.sharedview', $data);
	}
?>