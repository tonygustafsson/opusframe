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
			$get_settings['data_model'] = $this->model->data_model;
			$get_settings['order_by'] = 'name ASC';
			$get_settings['select'] = array('id', 'name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
			$get_settings['get_total_rows'] = TRUE;

			if (! empty($this->opus->urlargs->get_parameter('sort')) && ! empty($this->opus->urlargs->get_parameter('sort')))
				$get_settings['order_by'] = $this->opus->urlargs->get_parameter('sort') . ' ' . $this->opus->urlargs->get_parameter('order');

			if (! empty($this->opus->urlargs->get_parameter('search')))
				$get_settings['where_like']['name'] = $this->opus->urlargs->get_parameter('search');

			$filter_fields = array('genre' => 'checkboxes');
			$data['filters'][] = $this->opus->form->make_filters($this->model->data_model, $filter_fields);

			$pagination_page = (! empty($this->opus->urlargs->get_parameter('page'))) ? $this->opus->urlargs->get_parameter('page') : 1;
			$get_settings['limit_count'] = 5;
			$get_settings['limit_offset'] = ($pagination_page - 1) * $get_settings['limit_count'];

			$data['movies'] = $this->opus->database->get_result($get_settings);
			$data['sort_order_link'] = ($this->opus->urlargs->get_parameter('order') == 'ASC') ? 'DESC' : 'ASC';

			$this->opus->pagination = $this->opus->load->module('pagination');
			$data['pagination_links'] = $this->opus->pagination->make_links($data['movies']->total_rows);

			$this->opus->load->view('list', $data);
		}

		public function create()
		{
			$make_settings['wanted_fields'] = array('name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
			$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
			$make_settings['values'] = $this->opus->session->get_flash('form_values');

			$data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

			$this->opus->load->view('create', $data);
		}
		
		public function create_post()
		{
			$insert_settings['data_model'] = $this->model->data_model;
			$insert_settings['fields'] = array('name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
			$insert_output = $this->opus->database->insert($insert_settings);

			$movie_id = $insert_output->insert_id;

			if (! isset($insert_output->form_errors))
			{
				//Move temporary uploads to the item ID folder
				$this->opus->form->save_temp_images($this->model->data_model['db_table'], $insert_output->insert_id);

				$this->opus->load->url('movies');
			}
			else
			{
				$this->opus->session->set_flash('form_validation', $insert_output->form_errors);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url('movies/create');
			}
		}

		public function edit()
		{
			$id = $this->opus->urlargs->get_parameter('id');

			if ($id)
			{
				$get_settings['data_model'] = $this->model->data_model;
				$get_settings['select'] = array('id', 'name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
				$get_settings['where']['id'] = $id;
				$movie_info = $this->opus->database->get_row($get_settings);

				$make_settings['wanted_fields'] = array('id', 'name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
				$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');

				if (! empty($this->opus->session->get_flash('form_values')))
				{
					//Show new values upon errors instead of the original values
					$make_settings['values'] = $this->opus->session->get_flash('form_values');
				}
				else
					$make_settings['values'] = $movie_info;

				$data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

				$this->opus->load->view('edit', $data);
			}
		}

		public function edit_post()
		{
			$update_settings['data_model'] = $this->model->data_model;
			$update_settings['fields'] = array('name', 'genre', 'rating', 'seen', 'media_type', 'recommended');
			$update_settings['where']['id'] = $_POST['id'];
			$update_output = $this->opus->database->update($update_settings);

			if (! isset($update_output->form_errors))
				$this->opus->load->url('movies');
			else
			{
				$this->opus->session->set_flash('form_validation', $update_output->form_errors);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url('movies/edit/id=' . $_POST['id']);	
			}
		}

		public function remove()
		{
			$id = $this->opus->urlargs->get_parameter('id');

			if ($id)
			{
				$delete_settings['data_model'] = $this->model->data_model;
				$delete_settings['where']['id'] = $id;

				//Remove the movie
				$remove_output = $this->opus->database->delete($delete_settings);

				//Remove the images
				$this->opus->form->image_remove($id);

				$this->opus->load->url('movies');
			}
		}

		public function image_upload()
		{
			if ($this->opus->load->is_ajax_request())
			{
				$item_id = $_SERVER['HTTP_X_ITEM_ID'];
				$image_id = $_SERVER['HTTP_X_IMAGE_ID'];
				$file_path = $this->opus->config->path->image_upload . 'movies/' . $item_id . '/' . $item_id . '_' . $image_id;

				echo $this->opus->form->image_upload($file_path, 'php://input');
			}
		}

		public function image_remove()
		{
			if ($this->opus->load->is_ajax_request())
			{
				$item_id = $this->opus->urlargs->get_parameter('item_id');
				$image_id = $this->opus->urlargs->get_parameter('image_id');

				echo $this->opus->form->image_remove($item_id, $image_id);
			}
		}

		public function search()
		{
			$filter = $_POST['search'];

			$this->opus->load->url('movies/search=' . $filter);
		}

	}
?>