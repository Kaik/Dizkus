<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Block;

use SecurityUtil;
use ModUtil;
use BlockUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This class provides the center block.
 */
class CenterBlock extends \Zikula_Controller_AbstractBlock
{

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Dizkus_Centerblock::', 'Block ID::');
    }

    /**
     * info
     *
     * @return array
     */
    public function info()
    {
        return array(
            'module' => $this->name,
            'text_type' => $this->__('Dizkus recent'),
            'text_type_long' => $this->__('Dizkus recent posts'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true);
    }

    /**
     * Display the center block
     *
     * @param array $blockinfo Blockinfo array.
     *
     * @return array|boolean
     */
    public function display($blockinfo)
    {
        if (!ModUtil::available($this->name)) {
            return false;
        }
        // check for Permission
        if (!SecurityUtil::checkPermission('Dizkus::Centerblock', $blockinfo['bid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        // check if forum is turned off
        if ($this->getVar('forum_enabled') == 'no') {
            $blockinfo['content'] = $this->getVar('forum_disabled_info');

            return BlockUtil::themesideblock($blockinfo);
        }
        // return immediately if no post exist
        if (ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'all')) == 0) {
            return false;
        }
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        // check if cb_template is set, if not, use the default centerblock template
        if (empty($vars['cb_template'])) {
            $vars['cb_template'] = 'centerblock.tpl';
        }
        if (empty($vars['cb_parameters'])) {
            $vars['cb_parameters'] = 'maxposts=5';
        }
        $paramarray = array();
        $params = explode(',', $vars['cb_parameters']);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param) {
                $paramdata = explode('=', $param);
                $paramarray[trim($paramdata[0])] = trim($paramdata[1]);
            }
        }
        $this->view->assign('params', $paramarray);
        $blockinfo['content'] = $this->view->fetch('Block/' . trim($vars['cb_template']));

        return BlockUtil::themesideblock($blockinfo);
    }

    /**
     * Update the block
     *
     * @param array $blockinfo Blockinfo array.
     *
     * @return boolean|array
     */
    public function update($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Dizkus::Centerblock', $blockinfo['bid'] . '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $cb_template = $this->request->request->get('cb_template', 'centerblock.tpl');
        $cb_parameters = $this->request->request->get('cb_parameters', 'maxposts=5');
        $blockinfo['content'] = BlockUtil::varsToContent(compact('cb_template', 'cb_parameters'));

        return $blockinfo;
    }

    /**
     * Modify the block
     *
     * @param array $blockinfo Blockinfo array.
     *
     * @return string|boolean
     */
    public function modify($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Dizkus::Centerblock', $blockinfo['bid'] . '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        if (!isset($vars['cb_parameters']) || empty($vars['cb_parameters'])) {
            $vars['cb_parameters'] = 'maxposts=5';
        }
        if (!isset($vars['cb_template']) || empty($vars['cb_template'])) {
            $vars['cb_template'] = 'centerblock.tpl';
        }

        return $this->view->assign('vars', $vars)
            ->fetch('Block/centerblock_modify.tpl');
    }

}
