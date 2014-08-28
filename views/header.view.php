<html>
<head>
	<title><?= $this->opus->config->site_name ?></title>
	<?php $this->opus->load->css('base.css') ?>
	<?php $this->opus->load->css('custom.css') ?>
</head>

<body>
	<?php
		if (! empty($this->opus->session->get_flash('success')))
		{
			echo '<div id="success">' . $this->opus->session->get_flash('success') . '</div>';
		}
		if (! empty($this->opus->session->get_flash('error')))
		{
			echo '<div id="error">' . $this->opus->session->get_flash('error') . '</div>';
		}
	?>

	<?php
		if (isset($this->opus->auth->user['logged_in']) && $this->opus->auth->user['logged_in'] === TRUE)
		{
			echo '<p class="right"><span class="icon-lock"> <a href="' . $this->opus->config->base_url('auth/logout') . '">Log out, ' . $this->opus->auth->get_first_name() . '</a></p>';
		}
		else
		{
			echo '<p class="right"><span class="icon-lock"> <a href="' . $this->opus->config->base_url('auth/login') . '">Log in</a>';
			echo ' <a href="' . $this->opus->config->base_url('auth/register') . '">Register</a></p>';
		}
	?>

	<header>
		<h1><a href="<?= $this->opus->config->base_path ?>"><?= $this->opus->config->site_name ?></a></h1>
	</header>

	<article id="main">
