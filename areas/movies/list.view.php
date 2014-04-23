<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<form id="form-search" method="post" action="<?=$this->opus->config->base_url('movies/search')?>">
	<input type="search" name="search" id="search" placeholder="Search for movie">
	<input type="submit" value="Search">
</form>

<h2>List movies</h2>

<p><?=$this->opus->database->db->affected_rows?> movies found.</p>

<table>
	<tr>
		<th><a href="<?=$this->opus->config->base_url('movies/sort/index/name/' . $sort_order_link)?>">Name</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies/sort/index/genre/' . $sort_order_link)?>">Genre</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies/sort/index/rating/' . $sort_order_link)?>">Rating</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies/sort/index/seen/' . $sort_order_link)?>">Seen</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies/sort/index/media_type/' . $sort_order_link)?>">Media type</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies/sort/index/recommended/' . $sort_order_link)?>">Recommended</a></th>
		<th></th>
	</tr>

	<?php
		while ($movie = mysqli_fetch_object($movies))
		{
			echo '<tr>';
				echo '<td>' . $movie->name . '</td>';
				echo '<td>' . $movie->genre . '</td>';
				echo '<td>' . $movie->rating . '</td>';
				echo '<td>' . date('Y-m-d', strtotime($movie->seen)) . '</td>';
				echo '<td>' . $movie->media_type . '</td>';
				echo '<td>' . (($movie->recommended == 1) ? 'Yes' : 'No') . '</td>';
				if ($this->opus->auth->is_logged_in)
				{
					echo '	<td>
								<a href="' . $this->opus->config->base_url('movies/edit/' . $movie->id) . '">Edit</a>
								<a href="' . $this->opus->config->base_url('movies/remove/' . $movie->id) . '">Remove</a>
							</td>';
					echo '</tr>';
				}
		}
	?>
</table>

<p><?=$pagination_links?></p>

<p>
	<a href="<?=$this->opus->config->base_url('movies/create')?>">Create new movie</a>
</p>

<p>
	<a href="javascript:ajax('movies/create')">Create new movie with AJAX</a>
</p>

<?php
	if (! load::is_ajax_request()) {
		load::view('footer.sharedview', $data);
	}
?>