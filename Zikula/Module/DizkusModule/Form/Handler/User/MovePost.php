<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\User;

use Zikula\Module\DizkusModule\Manager\PostManager;
use ModUtil;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use Zikula\Core\ModUrl;
use Zlanguage;

/**
 * This class provides a handler to move a post.
 */
class MovePost extends \Zikula_Form_AbstractHandler
{

    /**
     * post id
     *
     * @var integer
     */
    private $post_id;

    /**
     * old post id
     *
     * @var integer
     */
    private $old_topic_id;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw new AccessDeniedException();
        }

        // get the input
        $id = (int) $this->request->query->get('post');

        $this->post_id = $id;

        $managedPost = new PostManager($id);

        $this->old_topic_id = $managedPost->getTopicId();

        if ($managedPost->get()->isFirst()) {
            $this->request->getSession()->getFlashBag()->add('error', 'You can not move the first post of a topic!');
            $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $managedPost->getTopicId()));
            return $view->redirect($url);
        }

        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Arguments.
     *
     * @return bool|void
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $this->old_topic_id, 'start' => 1), 'pid' . $this->post_id);
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        $data['old_topic_id'] = $this->old_topic_id;
        $data['post_id'] = $this->post_id;

        $newTopicPostCount = ModUtil::apiFunc($this->name, 'post', 'move', $data);
        $start = $newTopicPostCount - $newTopicPostCount % ModUtil::getVar($this->name, 'posts_per_page', 15);

        $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $data['to_topic_id'], 'start' => $start), 'pid' . $this->post_id);
        return $view->redirect($url);
    }

}
