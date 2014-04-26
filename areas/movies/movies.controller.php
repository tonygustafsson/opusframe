<?php
	class movies_controller
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->model = $this->opus->load->model('movies');
		}

		public function index()
		{
			$options['select'] = array('id', 'name', 'genre', 'rating', 'seen', 'media_type', 'recommended');

			if (! empty($this->opus->url->get_parameter('sort')) && ! empty($this->opus->url->get_parameter('sort')))
				$options['order_by'] = $this->opus->url->get_parameter('sort') . ' ' . $this->opus->url->get_parameter('order');

			if (! empty($this->opus->url->get_parameter('search')))
				$options['where_like']['name'] = $this->opus->url->get_parameter('search');

			$data['movies'] = $this->opus->database->get_result('movies', $options);
			$data['sort_order_link'] = ($this->opus->url->get_parameter('order') == 'ASC') ? 'DESC' : 'ASC';

			$this->opus->pagination = $this->opus->load->module('pagination');
			$data['pagination_links'] = $this->opus->pagination->make_links($this->opus->database->db->affected_rows);

			load::view('list', $data);
		}

		public function create()
		{
			$make_settings['wanted_fields'] = array('name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
			$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
			$make_settings['values'] = $this->opus->session->get_flash('form_values');

			$data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

			load::view('create', $data);
		}
		
		public function create_post()
		{
			$data['errors'] = $this->opus->form->validate($this->model->data_model);

			if ($data['errors'] === FALSE)
			{
				$insert = array('name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
				$this->opus->database->insert('movies', $insert);

				$this->opus->load->url('movies');
			}
			else
			{
				$this->opus->session->set_flash('form_validation', $data['errors']);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url('movies/create');
			}
		}

		public function edit()
		{
			$id = $this->opus->config->url_args[0];

			if (! empty($id))
			{
				$select = array('id', 'name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
				$where['id'] = $id;
				$movie_info = $this->opus->database->get_row('movies', $select, $where);

				$make_settings['wanted_fields'] = array('id', 'name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
				$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');

				if (! empty ($this->opus->session->get_flash('form_values')))
				{
					$make_settings['values'] = $this->opus->session->get_flash('form_values');
				}
				else
				{
					$make_settings['values'] = $movie_info;
				}

				$data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

				load::view('edit', $data);
			}
		}

		public function edit_post()
		{
			$data['errors'] = $this->opus->form->validate($this->model->data_model);

			if ($data['errors'] === FALSE)
			{
				$update = array('name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
				$where['id'] = $_POST['id'];
				$this->opus->database->update('movies', $update, $where);

				$this->opus->load->url('movies');
			}
			else
			{
				$this->opus->session->set_flash('form_validation', $data['errors']);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url('movies/edit/' . $_POST['id']);
			}
		}

		public function remove()
		{
			$id = $this->opus->config->url_args[0];

			if (! empty($id))
			{
				$where['id'] = $id;
				$this->opus->database->delete('movies', $where);

				$this->opus->load->url('movies');
			}
		}

		public function search()
		{
			$filter = $_POST['search'];

			$this->opus->load->url('movies/search:' . $filter);
		}

	}
?>