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
					'type' => 'select',
					'form_name' => 'genre',
					'min_length' => 3,
					'max_length' => 50,
					'default_value' => 'Romance',
					'fixed_values' => array('Action' => 'Action', 'Romance' => 'Romance', 'SciFi' => 'SciFi', 'Adventure' => 'Adventure', 'Comedy' => 'Comedy')
				),
				'media_type' => array(
					'friendly_name' => 'Media type',
					'type' => 'radio',
					'form_name' => 'media_type',
					'default_value' => 'DVD',
					'fixed_values' => array('VHS' => 'VHS', 'DVD' => 'DVD', 'BluRay' => 'BluRay')
				),
				'rating' => array(
					'friendly_name' => 'Rating',
					'type' => 'range',
					'form_name' => 'rating',
					'min_value' => 1,
					'max_value' => 5,
					'default_value' => 3,
					'fixed_values' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5')
				),
				'seen' => array(
					'friendly_name' => 'Seen',
					'type' => 'date',
					'form_name' => 'seen',
					'min_date' => '2000-01-01',
					'max_date' => '2019-12-31'
				),
				'recommended' => array(
					'friendly_name' => 'Recommended',
					'type' => 'bool',
					'form_name' => 'recommended'
				)
			);

		}

	}
	
?>