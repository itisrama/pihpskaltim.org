<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// get params
$document		= JFactory::getDocument();
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage	= $logotype == 'image' ? $this->params->get('logoimage') : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}


$headright = $this->countModules('head-search or languageswitcherload');

$uriFavicon = JUri::root(true).'/templates/'.$this->template.'/favicon/';

$this->addHeadLink($uriFavicon.'apple-touch-icon-57x57.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '57x57'));
$this->addHeadLink($uriFavicon.'apple-touch-icon-114x114.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '114x114'));
$this->addHeadLink($uriFavicon.'apple-touch-icon-144x144.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '144x144'));
$this->addHeadLink($uriFavicon.'apple-touch-icon-60x60.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '60x60'));
$this->addHeadLink($uriFavicon.'apple-touch-icon-120x120.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '120x120'));
$this->addHeadLink($uriFavicon.'apple-touch-icon-76x76.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '76x76'));
$this->addHeadLink($uriFavicon.'apple-touch-icon-152x152.png', 'apple-touch-icon-precomposed', 'rel', array('size' => '152x152'));
$this->addHeadLink($uriFavicon.'favicon-196x196.png', 'icon', 'rel', array('type' => 'image/png', 'size' => '196x196'));
$this->addHeadLink($uriFavicon.'favicon-96x96.png', 'icon', 'rel', array('type' => 'image/png', 'size' => '96x96'));
$this->addHeadLink($uriFavicon.'favicon-32x32.png', 'icon', 'rel', array('type' => 'image/png', 'size' => '32x32'));
$this->addHeadLink($uriFavicon.'favicon-16x16.png', 'icon', 'rel', array('type' => 'image/png', 'size' => '16x16'));
$this->addHeadLink($uriFavicon.'favicon-128.png', 'icon', 'rel', array('type' => 'image/png', 'size' => '128x128'));
$document->setMetaData('application-name', '&nbsp;');
$document->setMetaData('msapplication-TileColor', '#FFFFFF');
$document->setMetaData('msapplication-TileImage', $uriFavicon.'mstile-144x144.png');
$document->setMetaData('msapplication-square70x70logo', $uriFavicon.'mstile-70x70.png');
$document->setMetaData('msapplication-square150x150logo', $uriFavicon.'mstile-150x150.png');
$document->setMetaData('msapplication-wide310x150logo', $uriFavicon.'mstile-310x150.png');
$document->setMetaData('msapplication-square310x310logo', $uriFavicon.'mstile-310x310.png');

$logoimageurl	= ($logotype == 'image' && $logoimage) ? JURI::base(false) . '/' . $logoimage : null;

if($logotype == 'image' && $logoimage) {
	$document->addStyleDeclaration(sprintf("
		.logo-image h1 {
			background-image: url(%s);
		}
	", $logoimageurl));
}
?>

<!-- MAIN NAVIGATION -->
<nav id="t3-mainnav" class="wrap navbar navbar-default t3-mainnav">
	<div class="container"><div class="row">
		<!-- LOGO -->
		<div class="logo pull-left">
			<div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
				<a href="<?php echo JURI::base(true) ?>" title="<?php echo strip_tags($sitename) ?>">
					<h1><?php echo $sitename ?></h1>
				</a>
				<small class="site-slogan"><?php echo $slogan ?></small>
			</div>
		</div>
		<!-- //LOGO -->

		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header pull-left">
		
			<?php if ($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) : ?>
				<?php $this->addScript(T3_URL.'/js/nav-collapse.js'); ?>
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
					<i class="fa fa-bars"></i>
				</button>
			<?php endif ?>

			<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
				<?php $this->loadBlock ('off-canvas') ?>
			<?php endif ?>

		</div>

		<?php if ($this->getParam('navigation_collapse_enable')) : ?>
			<div class="t3-navbar-collapse navbar-collapse collapse"></div>
		<?php endif ?>

		<div class="t3-navbar navbar-collapse collapse">
			<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
			<div class="pull-right"><jdoc:include type="modules" name="<?php $this->_p('navright') ?>" style="raw" /></div>
		</div>
	</div></div>
</nav>
<!-- //MAIN NAVIGATION -->
