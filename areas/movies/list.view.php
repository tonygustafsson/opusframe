<?php
	if (! load::is_ajax_request()) {
		load::view('header.sharedview', $data);
	}
?>

<h2>List movies</h2>

<table>
	<tr>
		<th>Name</th>
		<th>Genre</th>
		<th>Rating</th>
		<th>Seen</th>
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