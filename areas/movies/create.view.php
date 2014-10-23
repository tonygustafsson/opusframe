<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('header', $data);
	}
?>

<h2>Create new movie</h2>

<form method="post" action="<?=$this->opus->url('movies/create_post')?>">
	<?= $form_elements ?>

	<input type="submit" value="Save">
</form>

<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>