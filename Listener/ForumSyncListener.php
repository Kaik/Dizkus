<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\DizkusModule\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zikula\DizkusModule\Events\DizkusEvents;

/**
 * Forum Sync Listener
 *
 * @author Kaik
 */
class ForumSyncListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public static function getSubscribedEvents()
    {
        return [
            DizkusEvents::FORUM_SYNC => ['forumSync'],
        ];
    }

    /**
     * @param EntityManager $entityManager EntityManager service instance
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * Sync forum
     * Respond to event DizkusEvents::FORUM_SYNC
     *
     * @param GenericEvent $event
     *
     * @param bollean $recursive
     *
     * @return void
     */
    public function forumSync(GenericEvent $event)
    {
        $recursive = $event->hasArgument('recursive') ? $event->getArgument('recursive') : false;
//        dump($recursive);
    }
}
