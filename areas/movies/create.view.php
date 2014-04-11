<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<h2>Create new movie</h2>

<form method="post" action="<?=$this->opus->config->base_url('movies/create_post')?>">
	<?= $form_elements ?>

	<input type="submit" value="Save">
</form>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer.sharedview', $data);
	}
?>