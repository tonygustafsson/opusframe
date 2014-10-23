<?php
	if (! $this->opus->load->is_ajax_request()) {
		$data['page_title'] = 'Edit movie';
		$this->opus->load->view('header', $data);
	}
?>

<h2>Edit movie</h2> 

<form method="post" action="<?=$this->opus->url('movies/edit_post')?>">
	<?= $form_elements ?>

	<input type="submit" value="Save">
</form>

<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>