<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('header', $data);
	}
?>

<form id="form-search" method="post" action="<?=$this->opus->config->base_url('movies/search')?>">
	<input type="search" name="search" id="search" autofocus value="<?= ((! empty($this->opus->url->get_parameter('search')) ? $this->opus->url->get_parameter('search') : '')) ?>" placeholder="Search for movie">
	<input type="submit" value="Search">
</form>

<h2>List movies</h2>

<p><?=$movies->total_rows?> movies found.</p>

<?php
	foreach ($filters as $filter)
	{
		echo $filter;
	}
?>

<table id="movie_table">
	<tr>
		<th></th>
		<th><a href="<?=$this->opus->config->base_url('movies' . $this->opus->url->get_url(array('sort' => 'name', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Name</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies' . $this->opus->url->get_url(array('sort' => 'genre', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Genre</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies' . $this->opus->url->get_url(array('sort' => 'rating', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Rating</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies' . $this->opus->url->get_url(array('sort' => 'seen', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Seen</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies' . $this->opus->url->get_url(array('sort' => 'media_type', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Media type</a></th>
		<th><a href="<?=$this->opus->config->base_url('movies' . $this->opus->url->get_url(array('sort' => 'recommended', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Recommended</a></th>
		<th></th>
	</tr>

	<?php
		while ($movie = mysqli_fetch_object($movies))
		{
			echo '<tr>';
				echo '<td class="center"><img src="' . $this->opus->load->image('assets/images/uploads/movies/' . $movie->id . '/' . $movie->id . '_1.jpg') . '"></td>';
				echo '<td><a href="' . $this->opus->config->base_url('movies/edit/' . $movie->id) . '">' . $movie->name . '</a></td>';
				echo '<td>' . $movie->genre . '</td>';
				echo '<td>' . $movie->rating . '</td>';
				echo '<td>' . date('Y-m-d', strtotime($movie->seen)) . '</td>';
				echo '<td>' . $movie->media_type . '</td>';
				echo '<td>' . (($movie->recommended == 1) ? 'Yes' : 'No') . '</td>';
				if ($this->opus->auth->user['logged_in'] === TRUE)
				{
					echo '	<td class="center">
								<a class="no-underline" href="' . $this->opus->config->base_url('movies/edit/' . $movie->id) . '"><span class="icon-pencil"></span></a>
								<a class="no-underline" href="' . $this->opus->config->base_url('movies/remove/' . $movie->id) . '"><span class="icon-remove"></span></a>
							</td>';
					echo '</tr>';
				}
		}
	?>
</table>

<p id="pagination-links"><?=$pagination_links?></p>

<p>
	<a href="<?=$this->opus->config->base_url('movies/create')?>">Create new movie</a>
</p>

<p>
	<a href="javascript:ajaxPage('movies/create')">Create new movie with AJAX</a>
</p>

<?php
	if (! $this->opus->load->is_ajax_request()) {
		$this->opus->load->view('footer', $data);
	}
?>