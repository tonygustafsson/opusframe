<h2>Edit movie</h2> 

<form method="post" action="<?=$this->opus->url('movies/edit_post')?>">
	<?=$form_elements?>

	<input type="submit" value="Save">
</form>