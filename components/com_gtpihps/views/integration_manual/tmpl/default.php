<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<div id="com_gtpihps" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)): ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>

		
	<div class="row">
		<div class="col-md-3">
			<?php echo $this->loadTemplate('form'); ?>
		</div>
		<div class="col-md-9">
			<?php echo $this->loadTemplate('table'); ?>
		</div>
	</div>
</div>
