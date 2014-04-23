<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<h2>Edit movie</h2> 

<form method="post" action="<?=$this->opus->config->base_url('movies/edit_post')?>">
	<input type="hidden" name="id" id="id" value="<?=$movie['id']?>">

	<?= $form_elements ?>

	<input type="submit" value="Save">
</form>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer.sharedview', $data);
	}
?>