<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_banners/helpers/banner.php';
$baseurl = JURI::base();
?>
<div class="bannergroup metro <?php echo $moduleclass_sfx ?>">
<?php if ($headerText) : ?>
	<?php echo $headerText; ?>
<?php endif; ?>

<?php foreach ($list as $item) : ?>
	<div class="banneritem">
		<?php //$link = JRoute::_('index.php?option=com_banners&task=click&id='. $item->id);?>
		<?php $link = strpos($item->clickurl, 'http://local') !== FALSE ? str_replace('http://local', JURI::base(true), $item->clickurl) : $item->clickurl ;?>
		
		<?php
		$link = $item->clickurl;
		$target = $params->get('target', 1);
		if(is_numeric(strpos($item->clickurl, 'http://local'))) {
			$link = str_replace('http://local', JURI::base(true), $link);
			$target = 0;
		}
		?>
		<?php if ($item->type == 1) :?>
			<?php // Text based banners ?>
			<?php echo str_replace(array('{CLICKURL}', '{NAME}'), array($link, $item->name), $item->custombannercode);?>
		<?php else:?>
			<?php $imageurl = $item->params->get('imageurl');?>
			<?php $width = $item->params->get('width');?>
			<?php $height = $item->params->get('height');?>
			<?php $title = $item->name;?>
			<?php $description = isset($item->description) ? $item->description : NULL;?>
			<?php if (BannerHelper::isImage($imageurl)) :?>
				<?php // Image based banner ?>
				<?php $alt = $item->params->get('alt');?>
				<?php $alt = $alt ? $alt : $item->name; ?>
				<?php $alt = $alt ? $alt : JText::_('MOD_BANNERS_BANNER'); ?>
				<?php if ($item->clickurl) :?>
					<?php // Wrap the banner in a link?>
					<?php if ($target == 1) :?>
						<?php // Open in a new window?>
						<a class="bannercontainer"
							href="<?php echo $link;?>" target="_blank"
							title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8');?>">
							<div class="bannerimg" alt="<?php echo $alt;?>">
								<img src="<?php echo $baseurl . $imageurl?>" />
							</div>
							<div class="bannermeta">
								<div class="bannertitle"><?php echo $title ?></div>
								
							</div>
							<?php if($description):?><div class="bannerdesc"><?php echo nl2br($description) ?></div><?php endif;?>
						</a>
					<?php elseif ($target == 2):?>
						<?php // open in a popup window?>
						<a class="bannercontainer"
							href="<?php echo $link;?>"  onclick="window.open(this.href, '',
								'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550');
								return false"
							title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8');?>">
							<div class="bannerimg" alt="<?php echo $alt;?>">
								<img src="<?php echo $baseurl . $imageurl?>" />
							</div>
							<div class="bannermeta">
								<div class="bannertitle"><?php echo $title ?></div>
							</div>
							<?php if($description):?><div class="bannerdesc"><?php echo nl2br($description) ?></div><?php endif;?>
						</a>
					<?php else :?>
						<?php // open in parent window?>
						<a class="bannercontainer"
							href="<?php echo $link;?>"
							title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8');?>">
							<div class="bannerimg" alt="<?php echo $alt;?>">
								<img src="<?php echo $baseurl . $imageurl?>" />
							</div>
							<div class="bannermeta">
								<div class="bannertitle"><?php echo $title ?></div>
							</div>
							<?php if($description):?><div class="bannerdesc"><?php echo nl2br($description) ?></div><?php endif;?>
						</a>
					<?php endif;?>
				<?php else :?>
					<?php // Just display the image if no link specified?>
					<div class="bannercontainer">
						<div class="bannerimg" alt="<?php echo $alt;?>">
							<img src="<?php echo $baseurl . $imageurl?>" />
						</div>
						<div class="bannermeta">
							<div class="bannertitle"><?php echo $title ?></div>
						</div>
						<?php if($description):?><div class="bannerdesc"><?php echo nl2br($description) ?></div><?php endif;?>
					</div>
				<?php endif;?>
			<?php elseif (BannerHelper::isFlash($imageurl)) :?>
				<object
					classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
					codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
					<?php if (!empty($width)) echo 'width ="'. $width.'"';?>
					<?php if (!empty($height)) echo 'height ="'. $height.'"';?>
				>
					<param name="movie" value="<?php echo $imageurl;?>" />
					<embed
						src="<?php echo $imageurl;?>"
						loop="false"
						pluginspage="http://www.macromedia.com/go/get/flashplayer"
						type="application/x-shockwave-flash"
						<?php if (!empty($width)) echo 'width ="'. $width.'"';?>
						<?php if (!empty($height)) echo 'height ="'. $height.'"';?>
					/>
				</object>
			<?php endif;?>
		<?php endif;?>
		<div class="clr"></div>
	</div>
<?php endforeach; ?>

<?php if ($footerText) : ?>
	<div class="bannerfooter">
		<?php echo $footerText; ?>
	</div>
<?php endif; ?>
</div>
