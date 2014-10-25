<form id="form-search" method="post" action="<?=$this->opus->url('movies/search')?>">
	<input type="search" name="search" id="search" autofocus value="<?= ((! empty($this->opus->urlargs->get_parameter('search')) ? $this->opus->urlargs->get_parameter('search') : '')) ?>" placeholder="Search for movie">
	<input type="submit" value="Search">
</form>

<h2>List movies</h2>

<p><?=$movies->total_rows?> movies found.</p>

<?php foreach ($filters as $filter): ?>
	<?=$filter?>
<?php endforeach; ?>

<table id="movie_table">
	<tr>
		<th></th>
		<th><a href="<?=$this->opus->url('movies' . $this->opus->urlargs->get_url(array('sort' => 'name', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Name</a></th>
		<th><a href="<?=$this->opus->url('movies' . $this->opus->urlargs->get_url(array('sort' => 'genre', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Genre</a></th>
		<th><a href="<?=$this->opus->url('movies' . $this->opus->urlargs->get_url(array('sort' => 'rating', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Rating</a></th>
		<th><a href="<?=$this->opus->url('movies' . $this->opus->urlargs->get_url(array('sort' => 'seen', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Seen</a></th>
		<th><a href="<?=$this->opus->url('movies' . $this->opus->urlargs->get_url(array('sort' => 'media_type', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Media type</a></th>
		<th><a href="<?=$this->opus->url('movies' . $this->opus->urlargs->get_url(array('sort' => 'recommended', 'order' => $sort_order_link)))?>"><span class="icon-menu"></span> Recommended</a></th>
		<?php if ($this->opus->auth->user['logged_in'] === TRUE): ?>
			<th></th>
		<?php endif; ?>
	</tr>

	<?php while ($movie = mysqli_fetch_object($movies)): ?>
		<tr>
			<td class="center"><img src="<?=$this->opus->load->image('assets/images/uploads/movies/' . $movie->id . '/' . $movie->id . '_1.jpg')?>" alt="Image of <?=$movie->name?>"></td>
			<td><a href="<?=$this->opus->url('movies/edit/id=' . $movie->id)?>"><?=$movie->name?></a></td>
			<td><?=$movie->genre?></td>
			<td><?=$movie->rating?></td>
			<td><?=date('Y-m-d', strtotime($movie->seen))?></td>
			<td><?=$movie->media_type?></td>
			<td><?=(($movie->recommended == 1) ? 'Yes' : 'No')?></td>

			<?php if ($this->opus->auth->user['logged_in'] === TRUE): ?>
				<td class="center">
					<a class="no-underline" href="<?=$this->opus->url('movies/edit/id=' . $movie->id)?>"><span class="icon-pencil"></span></a>
					<a class="no-underline" href="<?=$this->opus->url('movies/remove/id=' . $movie->id)?>"><span class="icon-remove"></span></a>
				</td>
			<?php endif; ?>
		</tr>
	<?php endwhile; ?>
</table>

<p id="pagination-links"><?=$pagination_links?></p>

<p>
	<a href="<?=$this->opus->url('movies/create')?>">Create new movie</a>
</p>

<p>
	<a href="javascript:ajaxPage('movies/create')">Create new movie with AJAX</a>
</p>