<?php if (! $this->opus->load->is_ajax_request()): ?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<title>
			<?php if (isset($page_title)): ?>
				<?=$page_title?> | <?=$this->opus->config->site_name?>
			<?php else: ?>
				<?= $this->opus->config->site_name?>
			<?php endif; ?>
		</title>

		<?php if (isset($page_description)): ?>
			<meta name="description" content="<?=$page_description?>">
		<?php endif; ?>
		<?php if (isset($page_keywords)): ?>
			<meta name="keywords" content="<?=$page_keywords?>">
		<?php endif; ?>

		<?php if (isset($css)): ?>
			<?php echo $css ?>
		<?php endif; ?>

		<meta charset="utf-8">
	</head>

	<body>
		<?php if (! empty($this->opus->session->get_flash('success'))): ?>
			<div id="success"><?=$this->opus->session->get_flash('success')?></div>
		<?php endif; ?>

		<?php if (! empty($this->opus->session->get_flash('error'))): ?>
			<div id="error"><?=$this->opus->session->get_flash('error')?></div>
		<?php endif; ?>

		<?php if (isset($this->opus->auth->user['logged_in']) && $this->opus->auth->user['logged_in'] === TRUE): ?>
			<p class="right">
				<span class="icon-lock"></span>
				<a href="<?=$this->opus->url('auth/logout')?>">Log out, <?=$this->opus->auth->get_first_name()?></a>
			</p>
		<?php else: ?>
			<p class="right">
				<span class="icon-lock"></span>
				<a href="<?=$this->opus->url('auth/login')?>">Log in</a>
				<a href="<?=$this->opus->url('auth/register')?>">Register</a>
			</p>
		<?php endif; ?>

		<header>
			<h1><a href="<?=$this->opus->url['base']?>"><?= $this->opus->config->site_name ?></a></h1>
		</header>

		<article id="main">
<?php endif; ?>

<?php if (isset($partial)): ?>
	<?=$partial?>
<?php endif; ?>

<?php if (! $this->opus->load->is_ajax_request()): ?>
		</article>

		<footer>
			<p>This is an example site.</p>
		</footer>

		<?php if (isset($js)): ?>
			<?php echo $js ?>
		<?php endif; ?>

	</body>
	</html>
<?php endif; ?>