<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<!-- FOOTER -->
<footer id="t3-footer" class="wrap t3-footer">

	<?php if ($this->checkSpotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6')) : ?>
		<!-- FOOT NAVIGATION -->
		<div class="container">
			<?php $this->spotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6') ?>
		</div>
		<!-- //FOOT NAVIGATION -->
	<?php endif ?>

	<section class="t3-copyright">
		<div class="container">
			<div class="copyright <?php $this->_c('footer') ?>">
				<jdoc:include type="modules" name="<?php $this->_p('footer') ?>" style="raw" />
				<?php if ($this->getParam('t3-rmvlogo', 1)): ?>
					<a class="poweredby" href="http://gt.web.id" target="_blank">
						<img alt="Powered by gtWeb" title="Powered by gtWeb" src="<?php echo JURI::base(). '/images/gtweb.png'; ?>" />
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

</footer>
<!-- //FOOTER -->