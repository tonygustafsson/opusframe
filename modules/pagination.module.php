<?php
	class pagination_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->items_per_page = 5;
		}

		public function make_links($total_items, $items_per_page = FALSE)
		{
			$items_per_page = ($items_per_page !== FALSE) ? $items_per_page : $this->items_per_page;
			$pages = ceil($total_items / $items_per_page);

			$html = '';

			for ($x = 1; $x < $pages; $x++)
			{
				$html .= ' <a href="' . $this->opus->config->base_url('movies' . $this->opus->url->get_url(array('page' => $x))) . '">' . $x . '</a>';
			}

			return $html;
		}

	}
?>