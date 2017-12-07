 <?php
 /**
  * @version        
  * @copyright  Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
  * @license        GNU General Public License version 2 or later; see LICENSE.txt
  */
 
 defined('JPATH_BASE') or die;
 
  /**
   * An example custom profile plugin.
   *
   * @package       Joomla.Plugins
   * @subpackage    user.profile
   * @version       1.6
   */
  class plgUserCustom extends JPlugin
  {
	/**
	 * @param   string  The context for the data
	 * @param   int     The user id
	 * @param   object
	 * @return  boolean
	 * @since   1.6
	 */

	protected function setCometchatStatus($id, $status) {
		$db     = JFactory::getDBO();
		$query  = $db->getQuery(true);

		$query->update($db->quoteName('cometchat_status', 'a'));
		$query->set($db->quoteName('a.status').' = '.$db->quote($status));
		$query->where($db->quoteName('a.userid').' = '.intval($id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		return $db->execute();
	}

	public function onUserLogin($user, $options = array()) {
		$id = (int) JUserHelper::getUserId($user['username']);
		//return self::setCometchatStatus($id, 'available');
	}

	public function onUserLogout($user, $options = array()) {
		//return self::setCometchatStatus($user['id'], 'offline');
	}
}