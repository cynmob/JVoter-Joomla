<?php
/**
 * @version    v1.0.0
 * @package    jdonate
 * @author     Jdonate Team <support@jdonate.com>
 * @link       http://www.jdonate.com
 * @copyright  Copyright (C) 2018 Jdonate. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Stripe\Util;

/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die('Restricted access');

class AutoPagingIterator implements \Iterator
{
    private $lastId = null;
    private $page = null;
    private $pageOffset = 0;
    private $params = array();

    public function __construct($collection, $params)
    {
        $this->page = $collection;
        $this->params = $params;
    }

    public function rewind()
    {
    }

    public function current()
    {
        $item = current($this->page->data);
        $this->lastId = $item !== false ? $item['id'] : null;

        return $item;
    }

    public function key()
    {
        return key($this->page->data) + $this->pageOffset;
    }

    public function next()
    {
        $item = next($this->page->data);
        if ($item === false) {
            $this->pageOffset += count($this->page->data);
            if ($this->page['has_more']) {
                $this->params = array_merge(
                    $this->params ? $this->params : array(),
                    array('starting_after' => $this->lastId)
                );
                $this->page = $this->page->all($this->params);
            } else {
                return false;
            }
        }
    }

    public function valid()
    {
        $key = key($this->page->data);
        $valid = ($key !== null && $key !== false);
        return $valid;
    }
}
