<?php

	class movies_model
	{

		public function __construct()
		{

			$this->data_model = array(
				'id' => array(
					'friendly_name' => 'ID',
					'type' => 'id',
					'form_name' => 'id',
					'max_length' => 11
				),
				'name' => array(
					'friendly_name' => 'Name',
					'type' => 'string',
					'form_name' => 'name',
					'min_length' => 3,
					'max_length' => 50
				),
				'genre' => array(
					'friendly_name' => 'Genre',
					'type' => 'string',
					'form_name' => 'genre',
					'min_length' => 3,
					'max_length' => 50
				),
				'rating' => array(
					'friendly_name' => 'Rating',
					'type' => 'range',
					'form_name' => 'rating',
					'min_value' => 1,
					'max_value' => 5,
					'default_value' => 3,
					'max_length' => 1,
					'in_range' => array(1, 2, 3, 4, 5)
				),
				'seen' => array(
					'friendly_name' => 'Seen',
					'type' => 'date',
					'form_name' => 'seen',
					'min_date' => '2000-01-01',
					'max_date' => '2019-12-31'
				)
			);

		}

	}
	
?>