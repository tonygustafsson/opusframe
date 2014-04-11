<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<h2>Log in</h2>

<form method="post" action="<?php echo $this->opus->config->base_url('auth/login_post'); ?>">
	<?= $form_elements ?>

	<input type="submit" value="Log in">
</form>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer.sharedview', $data);
	}
?>