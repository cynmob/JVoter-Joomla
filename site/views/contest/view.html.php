<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * HTML Contest View class for the JVoter component
 *
 * @since  1.5
 */
class JVoterViewContest extends JViewLegacy
{
    protected $item;
    
    protected $params;
    
    protected $print;
    
    protected $state;
    
    protected $user;
    
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $app        = JFactory::getApplication();
        $user       = JFactory::getUser();
        $dispatcher = JEventDispatcher::getInstance();
        
        $this->item  = $this->get('Item');
        $this->print = $app->input->getBool('print');
        $this->state = $this->get('State');
        $this->user  = $user;
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseWarning(500, implode("\n", $errors));
            
            return false;
        }
        
        // Create a shortcut for $item.
        $item            = $this->item;
              
        // Add router helpers.
        $item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
        $item->catslug     = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
        $item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
        
        // No link for ROOT category
        if ($item->parent_alias === 'root')
        {
            $item->parent_slug = null;
        }
                       
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));
        
        $this->_prepareDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Prepares the document.
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $app     = JFactory::getApplication();
        $menus   = $app->getMenu();
        $pathway = $app->getPathway();
        $title   = null;
        
        /**
         * Because the application sets a default page title,
         * we need to get it from the menu item itself
         */
        $menu = $menus->getActive();
        
        if ($menu)
        {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else
        {
            $this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
        }
        
        $title = $this->params->get('page_title', '');
        
        $id = (int) @$menu->query['id'];
        
        // If the menu item does not concern this article
        if ($menu && ($menu->query['option'] !== 'com_content' || $menu->query['view'] !== 'article' || $id != $this->item->id))
        {
            // If a browser page title is defined, use that, then fall back to the article title if set, then fall back to the page_title option
            $title = $this->item->params->get('article_page_title', $this->item->title ?: $title);
            
            $path     = array(array('title' => $this->item->title, 'link' => ''));
            $category = JCategories::getInstance('Content')->get($this->item->catid);
            
            while ($category && ($menu->query['option'] !== 'com_content' || $menu->query['view'] === 'article' || $id != $category->id) && $category->id > 1)
            {
                $path[]   = array('title' => $category->title, 'link' => ContentHelperRoute::getCategoryRoute($category->id));
                $category = $category->getParent();
            }
            
            $path = array_reverse($path);
            
            foreach ($path as $item)
            {
                $pathway->addItem($item['title'], $item['link']);
            }
        }
        
        // Check for empty title and add site name if param is set
        if (empty($title))
        {
            $title = $app->get('sitename');
        }
        elseif ($app->get('sitename_pagetitles', 0) == 1)
        {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2)
        {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        
        if (empty($title))
        {
            $title = $this->item->title;
        }
        
        $this->document->setTitle($title);
        
        if ($this->item->metadesc)
        {
            $this->document->setDescription($this->item->metadesc);
        }
        elseif ($this->params->get('menu-meta_description'))
        {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }
        
        if ($this->item->metakey)
        {
            $this->document->setMetadata('keywords', $this->item->metakey);
        }
        elseif ($this->params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }
        
        if ($this->params->get('robots'))
        {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
        
        if ($app->get('MetaAuthor') == '1')
        {
            $author = $this->item->created_by_alias ?: $this->item->author;
            $this->document->setMetaData('author', $author);
        }
        
        $mdata = $this->item->metadata->toArray();
        
        foreach ($mdata as $k => $v)
        {
            if ($v)
            {
                $this->document->setMetadata($k, $v);
            }
        }
        
        // If there is a pagebreak heading or title, add it to the page title
        if (!empty($this->item->page_title))
        {
            $this->item->title = $this->item->title . ' - ' . $this->item->page_title;
            $this->document->setTitle(
                $this->item->page_title . ' - ' . JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1)
                );
        }
        
        if ($this->print)
        {
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }
    }
}
