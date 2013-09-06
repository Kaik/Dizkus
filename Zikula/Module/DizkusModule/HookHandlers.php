<?php

/**
 * Copyright 2013 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Hooks Handlers.
 */

namespace Zikula\Module\DizkusModule;

use ServiceUtil;
use SecurityUtil;
use ModUtil;
use PageUtil;
use HookUtil;
use LogUtil;
use System;
use ZLanguage;
use Zikula_View;
use Zikula_DisplayHook;
use Zikula_ProcessHook;
use Zikula_Response_DisplayHook;
use Zikula_ValidationHook;
use Zikula_Exception_Forbidden;
use Zikula_Event;
use Zikula\Core\Event\GenericEvent;
use Zikula\Module\DizkusModule\DizkusModuleVersion;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\Module\DizkusModule\Entity\TopicEntity;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Zikula\Module\DizkusModule\AbstractHookedTopicMeta;
use Zikula\Module\DizkusModule\HookedTopicMeta\Generic;

class HookHandlers extends \Zikula_Hook_AbstractHandler
{

    /**
     * Zikula_View instance
     * @var Zikula_View
     */
    private $view;

    /**
     * Zikula entity manager instance
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Module name
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

    /**
     * Post constructor hook.
     *
     * @return void
     */
    public function setup()
    {
        $this->view = Zikula_View::getInstance(self::MODULENAME, false);
        // set caching off
        $this->_em = ServiceUtil::getService('doctrine.entitymanager');
        $this->domain = ZLanguage::getModuleDomain(self::MODULENAME);
    }

    /**
     * Display hook for view.
     *
     * @param Zikula_DisplayHook $hook The hook.
     *
     * @return string
     */
    public function uiView(Zikula_DisplayHook $hook)
    {
        // first check if the user is allowed to do any comments for this module/objectid
        if (!SecurityUtil::checkPermission("{$hook->getCaller()}", '::', ACCESS_COMMENT)) {
            return;
        }
        $request = ServiceUtil::getService('request');
        $start = (int)$request->query->get('start', 1);
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            $managedTopic = new TopicManager(null, $topic);
        } else {
            return;
        }
        // attempt to retrieve return url from hook or create if not available
        $url = $hook->getUrl();
        if (isset($url)) {
            $urlParameters = $url->toArray();
        } else {
            $urlParameters = $request->query->all();
        }
        $returnurlparams = htmlspecialchars(serialize($urlParameters));
        $this->view->assign('returnurl', $returnurlparams);
        list($rankimages, $ranks) = ModUtil::apiFunc(self::MODULENAME, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);
        $this->view->assign('start', $start);
        $this->view->assign('topic', $managedTopic->get()->toArray());
        $this->view->assign('posts', $managedTopic->getPosts(--$start));
        $this->view->assign('pager', $managedTopic->getPager());
        $this->view->assign('permissions', $managedTopic->getPermissions());
        $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
        $this->view->assign('isSubscribed', $managedTopic->isSubscribed());
        $this->view->assign('nextTopic', $managedTopic->getNext());
        $this->view->assign('previousTopic', $managedTopic->getPrevious());
        //$this->view->assign('last_visit', $last_visit);
        //$this->view->assign('last_visit_unix', $last_visit_unix);
        $managedTopic->incrementViewsCount();
        PageUtil::addVar('stylesheet', 'modules/Dizkus/style/style.css');
        $hook->setResponse(new Zikula_Response_DisplayHook(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'user/hook/topicview.tpl'));
    }

    /**
     * Display hook for edit.
     *
     * @param Zikula_DisplayHook $hook The hook.
     *
     * @return string
     */
    public function uiEdit(Zikula_DisplayHook $hook)
    {
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig');
        $forumId = $hookconfig[$hook->getAreaId()]['forum'];
        if (!isset($forumId)) {
            // admin didn't choose a forum, so create one and set as choice
            $managedForum = new ForumManager();
            $data = array(
                'name' => __f('Discussion for %s', $hook->getCaller(), $this->domain),
                'status' => ForumEntity::STATUS_LOCKED,
                'parent' => $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->findOneBy(array(
                    'name' => ForumEntity::ROOTNAME)));
            $managedForum->store($data);
            // cannot notify hooks in non-controller
            $hookconfig[$hook->getAreaId()]['forum'] = $managedForum->getId();
            ModUtil::setVar($hook->getCaller(), 'dizkushookconfig', $hookconfig);
            $forumId = $managedForum->getId();
        }
        $forum = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->find($forumId);
        // add this response to the event stack
        $this->view->assign('forum', $forum->getName());
        $hook->setResponse(new Zikula_Response_DisplayHook(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'user/hook/edit.tpl'));
    }

    /**
     * Display hook for delete.
     *
     * @param Zikula_DisplayHook $hook The hook.
     *
     * @return string
     */
    public function uiDelete(Zikula_DisplayHook $hook)
    {
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            $this->view->assign('forum', $topic->getForum()->getName());
            $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
            // lock or remove
            $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
            $this->view->assign('actionWord', $actionWord);
            $hook->setResponse(new Zikula_Response_DisplayHook(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'user/hook/delete.tpl'));
        }
    }

    /**
     * Validate hook for edit.
     *
     * @param Zikula_ValidationHook $hook The hook.
     *
     * @return void (unused)
     */
    public function validateEdit(Zikula_ValidationHook $hook)
    {
        return;
    }

    /**
     * Validate hook for delete.
     *
     * @param Zikula_ValidationHook $hook The hook.
     *
     * @return void (unused)
     */
    public function validateDelete(Zikula_ValidationHook $hook)
    {
        return;
    }

    /**
     * Process hook for edit.
     *
     * @param Zikula_ProcessHook $hook The hook.
     *
     * @return boolean
     */
    public function processEdit(Zikula_ProcessHook $hook)
    {
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig');
        // create new topic in selected forum
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (!isset($topic)) {
            $topic = new TopicEntity();
        }
        // use Meta class to create topic data
        $topicMetaInstance = $this->getClassInstance($hook);
        // format data for topic creation
        $data = array(
            'forum_id' => $hookconfig[$hook->getAreaId()]['forum'],
            'title' => $topicMetaInstance->getTitle(),
            'message' => $topicMetaInstance->getContent(),
            'subscribe_topic' => false,
            'attachSignature' => false);
        // create the new topic
        $newManagedTopic = new TopicManager(null, $topic);
        // inject new topic into manager
        $newManagedTopic->prepare($data);
        // add hook data to topic
        $newManagedTopic->setHookData($hook);
        // store new topic
        $newManagedTopic->create();
        // cannot notify hooks in non-controller
        // notify topic & forum subscribers
        ModUtil::apiFunc(self::MODULENAME, 'notify', 'emailSubscribers', array(
            'post' => $newManagedTopic->getFirstPost()));
        LogUtil::registerStatus($this->__('Dizkus: Hooked discussion topic created.', $this->domain));

        return true;
    }

    /**
     * Process hook for delete.
     *
     * @param Zikula_ProcessHook $hook The hook.
     *
     * @return boolean
     */
    public function processDelete(Zikula_ProcessHook $hook)
    {
        $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
        // lock or remove
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            switch ($deleteHookAction) {
                case 'remove':
                    ModUtil::apiFunc(self::MODULENAME, 'Topic', 'delete', array('topic' => $topic));
                    break;
                case 'lock':
                default:
                    $topic->lock();
                    $this->_em->flush();
                    break;
            }
        }
        $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
        LogUtil::registerStatus($this->__f('Dizkus: Hooked discussion topic %s.', $actionWord, $this->domain));

        return true;
    }

    /**
     * add hook config options to hooked module's module config
     *
     * @param GenericEvent $z_event
     */
    public static function dizkushookconfig(GenericEvent $z_event)
    {
        // check if this is for this handler
        $subject = $z_event->getSubject();
        if (!($z_event['method'] == 'dizkushookconfig' && strrpos(get_class($subject), '_Controller_Admin') || strrpos(get_class($subject), '\\AdminController'))) {
            return;
        }
        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        $view = Zikula_View::getInstance(self::MODULENAME, false);
        $hookconfig = ModUtil::getVar($moduleName, 'dizkushookconfig');
        $classname = $moduleName . '_Version';
        $moduleVersionObj = new $classname();
        $_em = ServiceUtil::getService('doctrine.entitymanager');
        $bindingsBetweenOwners = HookUtil::getBindingsBetweenOwners($moduleName, self::MODULENAME);
        foreach ($bindingsBetweenOwners as $k => $binding) {
            $areaname = $_em->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->find($binding['sareaid'])->getAreaname();
            $bindingsBetweenOwners[$k]['areaname'] = $areaname;
            $bindingsBetweenOwners[$k]['areatitle'] = $view->__($moduleVersionObj->getHookSubscriberBundle($areaname)->getTitle());
            $hookconfig[$binding['sareaid']]['admincatselected'] = isset($hookconfig[$binding['sareaid']]['admincatselected']) ? $hookconfig[$binding['sareaid']]['admincatselected'] : 0;
            $hookconfig[$binding['sareaid']]['optoverride'] = isset($hookconfig[$binding['sareaid']]['optoverride']) ? $hookconfig[$binding['sareaid']]['optoverride'] : false;
        }
        $view->assign('areas', $bindingsBetweenOwners);
        $view->assign('dizkushookconfig', $hookconfig);
        $view->assign('ActiveModule', $moduleName);
        $view->assign('forums', ModUtil::apiFunc(self::MODULENAME, 'Forum', 'getParents', array('includeLocked' => false)));
        $z_event->setData($view->fetch('hook/modifyconfig.tpl'));
        $z_event->stop();
    }

    /**
     * process results of dizkushookconfig
     *
     * @param GenericEvent $z_event
     */
    public static function dizkushookconfigprocess(GenericEvent $z_event)
    {
        // check if this is for this handler
        $subject = $z_event->getSubject();
        if (!($z_event['method'] == 'dizkushookconfigprocess' && strrpos(get_class($subject), '_Controller_Admin') || strrpos(get_class($subject), '\\AdminController'))) {
            return;
        }
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);
        $request = ServiceUtil::getService('request');
        $hookdata = $request->request->get('dizkus', array());
        $token = isset($hookdata['dizkus_csrftoken']) ? $hookdata['dizkus_csrftoken'] : null;
        if (!SecurityUtil::validateCsrfToken($token)) {
            throw new Zikula_Exception_Forbidden(__('Security token validation failed', $dom));
        }
        unset($hookdata['dizkus_csrftoken']);
        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        foreach ($hookdata as $area => $data) {
            if (!isset($data['forum']) || empty($data['forum'])) {
                LogUtil::registerError(__f('Error: No forum selected for area \'%s\'', $area, $dom));
                $hookdata[$area]['forum'] = null;
            }
        }
        ModUtil::setVar($moduleName, 'dizkushookconfig', $hookdata);
        // ModVar: dizkushookconfig => array('areaid' => array('forum' => value))
        LogUtil::registerStatus(__('Dizkus: Hook option settings updated.', $dom));
        $z_event->setData(true);
        $z_event->stop();

        return System::redirect(ModUtil::url($moduleName, 'admin', 'main'));
    }

    /**
     * Handle module uninstall event "installer.module.uninstalled".
     * Receives $modinfo as $args
     *
     * @param Zikula_Event $z_event
     *
     * @return void
     */
    public static function moduleDelete(Zikula_Event $z_event)
    {
        $module = $z_event['name'];
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);
        $_em = ServiceUtil::getService('doctrine.entitymanager');
        $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
        // lock or remove
        $topics = $_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->findBy(array('hookedModule' => $module));
        $count = 0;
        foreach ($topics as $topic) {
            switch ($deleteHookAction) {
                case 'remove':
                    ModUtil::apiFunc(self::MODULENAME, 'Topic', 'delete', array('topic' => $topic));
                    break;
                case 'lock':
                default:
                    $topic->lock();
                    $count++;
                    if ($count > 20) {
                        $_em->flush();
                        $count = 0;
                    }
                    break;
            }
        }
        // clear last remaining batch
        $_em->flush();
        $actionWord = $deleteHookAction == 'lock' ? __('locked', $dom) : __('deleted', $dom);
        LogUtil::registerStatus(__f('Dizkus: All hooked discussion topics %s.', $actionWord, $dom));
    }

    /**
     * populate Services menu with hook option link
     *
     * @param GenericEvent $event
     */
    public static function servicelinks(GenericEvent $event)
    {
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);
        $module = ModUtil::getName();
        $bindingCount = count(HookUtil::getBindingsBetweenOwners($module, self::MODULENAME));
        if ($bindingCount > 0 && $module != self::MODULENAME && (empty($event->data) || is_array($event->data) && !in_array(array(
                    'url' => ModUtil::url($module, 'admin', 'dizkushookconfig'),
                    'text' => __('Dizkus Hook Settings', $dom)), $event->data))) {
            $event->data[] = array(
                'url' => ModUtil::url($module, 'admin', 'dizkushookconfig'),
                'text' => __('Dizkus Hook Settings', $dom));
        }
    }

    /**
     * Find Meta Class and instantiate
     *
     * @param  Zikula_ProcessHook $hook
     * @return instantiated       object of found class
     */
    private function getClassInstance(Zikula_ProcessHook $hook)
    {
        if (empty($hook)) {
            return false;
        }
        $module = $hook->getCaller();
        $locations = array($module, self::MODULENAME);
        // locations to search for the class
        foreach ($locations as $location) {
            $classnames = array(
                $location . '_HookedTopicMeta_' . $module,
                "$location\\HookedTopicMeta\\$module");
            foreach ($classnames as $classname) {
                if (class_exists($classname)) {
                    $instance = new $classname($hook);
                    if ($instance instanceof AbstractHookedTopicMeta) {
                        return $instance;
                    }
                }
            }
        }

        return new Generic($hook);
    }

}