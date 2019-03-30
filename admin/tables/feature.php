<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Feature Table class
 *
 * @since 1.6
 */
class JVoterTableFeature extends JTable
{

    /**
     * Constructor
     *
     * @param
     *            JDatabase &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jvoter_features', 'id', $db);
        
        // Set the alias since the column is called state
        $this->setColumnAlias('published', 'state');
    }   
}
