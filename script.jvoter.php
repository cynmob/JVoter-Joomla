<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_JVoterInstallerScript
{

	/** @var array The list of extra modules and plugins to install */
	private $installation_queue = array(
			// modules => { (folder) => { (module) => { (position), (published)
			// } }* }*
			// plugins => { (folder) => { (element) => (published) }* }*
			'plugins' => array(
					'otbpayment' => array(
							'paypal' => 1,
							'stripe' => 1
					)
			)
	);

	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	public function preflight ($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// Only allow to install on Joomla! 2.5.0 or later
		// return version_compare(JVERSION, '2.5.0', 'ge');
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param string $type
	 *        	install, update or discover_update
	 * @param JInstaller $parent
	 */
	public function postflight ($type, $parent)
	{
		// Add Uncategorised __categories in #__categories table
		$this->addUncategorisedCat();
		
		$status = $this->installSubExtensions($parent);
		$this->renderPostInstallation($status);
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public function install ($parent)
	{
		// $parent is the class calling this method
		$this->setDefault();
		$this->setDefaultParams();
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	public function uninstall ($parent)
	{
		// $parent is the class calling this method
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	public function update ($parent)
	{
		// Create core tables
		$this->runSQL($parent, 'install.sql');
		
		// Added this for tag
		$this->setDefault();
		
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();
		$configdb = $config->get('db');
		
		// Get dbprefix
		$dbprefix = $config->get('dbprefix');
	}

	/**
	 * method to setDefault
	 *
	 * @return void
	 */
	public function setDefault ()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		// Check if tag exists
		$sql = $db->getQuery(true)
			->select($db->qn('type_id'))
			->from($db->qn('#__content_types'))
			->where($db->qn('type_title') . ' = ' . $db->q('JVoter Category'))
			->where($db->qn('type_alias') . ' = ' . $db->q('com_jvoter.category'));
		$db->setQuery($sql);
		$type_id = $db->loadResult();
		
		// Create tag
		$db = JFactory::getDBO();
		$tagobject = new stdclass();
		$tagobject->type_id = '';
		$tagobject->type_title = 'JVoter Category';
		$tagobject->type_alias = 'com_jvoter.category';
		$tagobject->table = '{"special":{"dbtable":"#__categories","key":"id","type":"Category",' .
				'"prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';
		$tagobject->rules = '';
		
		$field_mappings_arr = array(
				'common' => array(
						"core_content_item_id" => "id",
						"core_title" => "title",
						"core_state" => "state",
						"core_alias" => "alias",
						"core_created_time" => "created",
						"core_modified_time" => "modified",
						"core_body" => "description",
						"core_hits" => "null",
						"core_publish_up" => "start_date",
						"core_publish_down" => "end_date",
						"core_access" => "access",
						"core_params" => "params",
						"core_featured" => "featured",
						"core_metadata" => "null",
						"core_language" => "null",
						"core_images" => "image",
						"core_urls" => "null",
						"core_version" => "null",
						"core_ordering" => "ordering",
						"core_metakey" => "metakey",
						"core_metadesc" => "metadesc",
						"core_catid" => "cat_id",
						"core_xreference" => "null",
						"asset_id" => "asset_id"
				),
				'special' => array(
						"parent_id" => "parent_id",
						"lft" => "lft",
						"rgt" => "rgt",
						"level" => "level",
						"path" => "path",
						"path" => "path",
						"extension" => "extension",
						"extension" => "extension",
						"note" => "note"
				)
		);
		
		$tagobject->field_mappings = json_encode($field_mappings_arr);
		$tagobject->router = 'ContentHelperRoute::getCategoryRoute';
		
		$content_history_options_arr = '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml","hideFields":["asset_id","checked_out","checked_out_time",' .
				'"version","lft","rgt","level","path","extension"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out","checked_out_time", "version", ' .
				'"hits", "path"],"convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users", ' .
				'"targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id",' .
				'"targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';
		
		$tagobject->content_history_options = $content_history_options_arr;
		
		if (! $type_id)
		{
			if (! $db->insertObject('#__content_types', $tagobject, 'type_id'))
			{
				echo $db->stderr();
				return false;
			}
		}
		else
		{
			$tagobject->type_id = $type_id;
			
			if (! $db->updateObject('#__content_types', $tagobject, 'type_id'))
			{
				echo $db->stderr();
				return false;
			}
		}
		
		/** @var JTableContentType $table */
		$table = JTable::getInstance('contenttype');
		
		if ($table)
		{
			$table->load(array(
					'type_alias' => 'com_jvoter.category'
			));
			
			if (! $table->type_id)
			{
				$data = array(
						'type_title' => 'JVoter Category',
						'type_alias' => 'com_jvoter.category',
						'table' => '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},' .
						'"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}',
						'rules' => '',
						'field_mappings' => '
					{"common":{
					"core_content_item_id":"id",
					"core_title":"title",
					"core_state":"published",
					"core_alias":"alias",
					"core_created_time":"created_time",
					"core_modified_time":"modified_time",
					"core_body":"description",
					"core_hits":"hits",
					"core_publish_up":"null",
					"core_publish_down":"null",
					"core_access":"access",
					"core_params":"params", "core_featured":"null",
					"core_metadata":"metadata", "core_language":"language",
					"core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey",
					"core_metadesc":"metadesc", "core_catid":"parent_id",
					"core_xreference":"null", "asset_id":"asset_id"},
					"special": {
					"parent_id":"parent_id",
					"lft":"lft",
					"rgt":"rgt",
					"level":"level",
					"path":"path",
					"extension":"extension",
					"note":"note"
					}
					}',
						'content_history_options' => '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml",
					"hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"],

					"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],

					"convertToInt":["publish_up", "publish_down"],
	"displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
					{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},
					{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
					{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
				);
				
				$table->bind($data);
				
				if ($table->check())
				{
					$table->store();
				}
			}
		}
		
		// Create default category on installation if not exists
		$sql = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_jvoter'));
		
		$db->setQuery($sql);
		$cat_id = $db->loadResult();
		
		if (empty($cat_id))
		{
			$catObj = new stdClass();
			$catObj->title = 'Uncategorised';
			$catObj->alias = 'uncategorised';
			
			$catObj->extension = "com_jvoter";
			$catObj->path = "uncategorised";
			$catObj->parent_id = 1;
			$catObj->level = 1;
			$catObj->created_user_id = $user->id;
			$catObj->language = "*";
			$catObj->description = '<p>This is a default JVoter category</p>';
			
			$catObj->published = 1;
			$catObj->access = 1;
			
			if (! $db->insertObject('#__categories', $catObj, 'id'))
			{
				echo $db->stderr();
				return false;
			}
		}
	}

	public function runSQL ($parent, $sqlfile)
	{
		$db = \JFactory::getDBO();
		
		// Obviously you may have to change the path and name if your
		// installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/sql/' . $sqlfile;
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/' . $sqlfile;
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		
		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			
			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					
					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);
						
						if (! $db->execute())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							
							return false;
						}
					}
				}
			}
		}
	}

	// end run sql
	
	/**
	 * Function Add Uncategorised __categories in #__categories table
	 *
	 * @return void
	 */
	function addUncategorisedCat ()
	{
		$db = \JFactory::getDBO();
		$query = 'SELECT `id` FROM `#__categories` WHERE `extension` = \'com_jvoter\' AND `title`=\'Uncategorised\'';
		$db->setQuery($query);
		$result = $db->loadResult();
		
		if (empty($result))
		{
			$catObj = new stdClass();
			$catObj->title = 'Uncategorised';
			$catObj->alias = 'uncategorised';
			$catObj->extension = "com_jvoter";
			$catObj->path = " uncategorised";
			$catObj->parent_id = 1;
			$catObj->level = 1;
			
			$paramdata = array();
			$paramdata['category_layout'] = '';
			$paramdata['image'] = '';
			$catObj->params = json_encode($paramdata);
			
			$catObj->created_user_id = \JFactory::getUser()->id;
			$catObj->language = "*";
			
			$catObj->published = 1;
			$catObj->access = 1;
			
			if (! $db->insertObject('#__categories', $catObj, 'id'))
			{
				echo $db->stderr();
				return false;
			}
		}
	}

	function setDefaultParams ()
	{
		$db = \JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'));
		$defaults = '{"upload_extensions":"gif,jpg,jpeg,png,GIF,JPG,JPEG,PNG","upload_maxsize":"10","image_extensions":"bmp,gif,jpg,png",' .
				'"ignore_extensions":"","upload_mime":"image/jpeg,image/gif,image/png","currency":"USD","currency_symbol":"$",' .
				'"currency_format":"[SYMBOL][AMOUNT]","thousands_sep":",","sef_advanced":"0","sef_ids":"0","gateways":["paypal"]}'; // JSON				                                                                                                                                                                                                                                                                                                                                                    // parameters
		
		$query->set($db->quoteName('params') . ' = ' . $db->quote($defaults));
		$query->where($db->quoteName('name') . ' = ' . $db->quote('com_jvoter'));
		$db->setQuery($query);
		
		if (! $db->execute())
		{
			JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
			return false;
		}
		return true;
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 * @return JObject The subextension installation status
	 */
	private function installSubExtensions ($parent)
	{
		$src = $parent->getParent()->getPath('source');
		$db = \JFactory::getDbo();
		$status = new stdClass();
		$status->plugins = array();
		
		// Plugins installation
		if (count($this->installation_queue['plugins']))
		{
			foreach ($this->installation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
					foreach ($plugins as $plugin => $published)
					{
						$path = "$src/plugins/$folder/$plugin";
						if (! is_dir($path))
						{
							$path = "$src/plugins/$folder/plg_$plugin";
						}
						if (! is_dir($path))
						{
							$path = "$src/plugins/$plugin";
						}
						if (! is_dir($path))
						{
							$path = "$src/plugins/plg_$plugin";
						}
						if (! is_dir($path))
							continue;
						// Was the plugin already installed?
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$count = $db->loadResult();
						$installer = new JInstaller();
						$result = $installer->install($path);
						
						$status->plugins[] = array(
								// 'name' => 'Jdonate Payment - ' .
								// ucfirst($plugin),
								'name' => 'plg_jdpayment_' . $plugin,
								'group' => $folder,
								'result' => $result
						);
						if ($published && ! $count)
						{
							$query = $db->getQuery(true)
								->update($db->qn('#__extensions'))
								->set($db->qn('enabled') . ' = ' . $db->q('1'))
								->where($db->qn('element') . ' = ' . $db->q($plugin))
								->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);
							$db->execute();
						}
					}
			}
		}
		
		return $status;
	}

	/**
	 * Renders the post-installation message
	 */
	private function renderPostInstallation ($status)
	{
		$rows = 1;
		?>
		<table class="table">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>	
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2">JVoter component</td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>						
				<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo JText::_($plugin['name']); ?></td>
					<td class="key"><?php echo $plugin['group']; ?></td>
					<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>	
			</tbody>
		</table>
<?php
	}
}
