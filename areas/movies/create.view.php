<h2>Create new movie</h2>

<form method="post" action="<?=$this->opus->url('movies/create_post')?>">
	<?=$form_elements?>

	<input type="submit" value="Save">
</form>