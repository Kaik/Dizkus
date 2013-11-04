<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides the post api functions
 */

namespace Zikula\Module\DizkusModule\Api;

use DateTime;
use DataUtil;
use DateUtil;
use UserUtil;
use LogUtil;
use ModUtil;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Zikula\Module\DizkusModule\Manager\PostManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PostApi extends \Zikula_AbstractApi
{

    /**
     * get_latest_posts
     *
     * @params $args['selorder'] int 1-6, see below
     * @params $args['nohours'] int posting within these hours
     * @params $args['unanswered'] int 0 or 1(= postings with no answers)
     * @params $args['last_visit_unix'] string the users last visit data as unix timestamp
     * @params $args['limit'] int limits the numbers hits read (per list), defaults and limited to 250
     * @returns array (postings, mail2forumpostings, rsspostings, text_to_display)
     */
    public function getLatest($args)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
            ->from('Zikula\Module\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.last_post', 'l')
            ->orderBy('l.post_time', 'DESC');
        // sql part per selected time frame
        switch ($args['selorder']) {
            case '2':
                // today
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new DateTime('today'));
                $text = $this->__('Today');
                break;
            case '3':
                // since yesterday
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new DateTime('yesterday'));
                $text = $this->__('Since yesterday');
                break;
            case '4':
                // lastweek
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new DateTime('-1 week'));
                $text = $this->__('In the last week');
                break;
            default:
                // default is case '1'
                // no break - process as case '1' ...
            case '1':
                // last 24 hours
                $args['nohours'] = 24;
                // no break - process as case 5 ...
            case '5':
                // last x hours
                // maximum two weeks back = 2 * 24 * 7 hours
                if (isset($args['nohours']) && $args['nohours'] > 336) {
                    $args['nohours'] = 336;
                }
                $qb->where('l.post_time > :wheretime')
                    ->setParameter('wheretime', new DateTime('-' . $args['nohours'] . ' hours'));
                $text = DataUtil::formatForDisplay($this->__f('In the last %s hours', $args['nohours']));
                break;
            case '6':
                // last visit
                $lastVisit = DateTime::createFromFormat('U', $args['last_visit_unix']);
                $qb->where('l.post_time > :wheretime')
                    ->setParameter('wheretime', $lastVisit);
                $text = DataUtil::formatForDisplay($this->__f('Since your last visit on %s', DateUtil::formatDatetime($args['last_visit_unix'], 'datetimebrief')));
                break;
            case 'unanswered':
                $qb->where('t.replyCount = 0');
                $text = $this->__('Unanswered');
                break;
            case 'unsolved':
                $qb->where('t.solved = :status')
                    ->setParameter('status', -1);
                $text = $this->__('Unsolved');
                break;
        }
        $qb->setFirstResult(0)->setMaxResults(10);
        $topics = new Paginator($qb);
        $pager = array(
            'numitems' => count($topics),
            'itemsperpage' => 10);

        return array($topics, $text, $pager);
    }

    public function search($args)
    {
        $text = '';
        $uname = '';
        $own = false;
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
            $own = true;
        }
        if (!is_int($args['uid'])) {
            $uname = $args['uid'];
            $args['uid'] = UserUtil::getIdFromName($uname);
        } else {
            $uname = UserUtil::getVar('uname', $args['uid']);
        }
        if (!$own && $args['uid'] == UserUtil::getVar('uid')) {
            $own = true;
        }
        if (empty($args['action'])) {
            $args['action'] = 'posts';
        }
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
            ->from('Zikula\Module\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.last_post', 'l')
            ->leftJoin('t.posts', 'p')
            ->orderBy('l.post_time', 'DESC');
        if ($args['action'] == 'topics') {
            $qb->where('t.poster = :uid');
            if ($own) {
                $text = $this->__('Your topics');
            } else {
                $text = $this->__f('%s\'s topics', array($uname));
            }
        } else {
            $qb->where('p.poster = :uid');
            if ($own) {
                $text = $this->__('Your posts');
            } else {
                $text = $this->__f('%s\'s posts', array($uname));
            }
        }
        $qb->setParameter('uid', $args['uid']);
        $qb->setFirstResult(0)->setMaxResults(10);
        $topics = new Paginator($qb);
        $pager = array(
            'numitems' => count($topics),
            'itemsperpage' => 10);

        return array($topics, $text, $pager);
    }

    /**
     * movepost
     *
     * @params $args['post_id']
     * @params $args['old_topic_id']
     * @params $args['to_topic_id']
     *
     * @returns count of posts in destination topic
     */
    public function move($args)
    {
        $old_topic_id = isset($args['old_topic_id']) ? $args['old_topic_id'] : null;
        $to_topic_id = isset($args['to_topic_id']) ? $args['to_topic_id'] : null;
        $post_id = isset($args['post_id']) ? $args['post_id'] : null;
        if (!isset($old_topic_id) || !isset($to_topic_id) || !isset($post_id)) {
            return LogUtil::registerArgsError();
        }
        $managedOriginTopic = new TopicManager($old_topic_id);
        $managedDestinationTopic = new TopicManager($to_topic_id);
        $managedPost = new PostManager($post_id);
        $managedOriginTopic->get()->getPosts()->removeElement($managedPost->get());
        $managedPost->get()->setTopic($managedDestinationTopic->get());
        $managedDestinationTopic->get()->addPost($managedPost->get());
        $managedOriginTopic->decrementRepliesCount();
        $managedDestinationTopic->incrementRepliesCount();
        $managedPost->get()->updatePost_time();
        $this->entityManager->flush();
        ModUtil::apiFunc($this->name, 'sync', 'topicLastPost', array(
            'topic' => $managedOriginTopic->get(),
            'flush' => false));
        ModUtil::apiFunc($this->name, 'sync', 'topicLastPost', array(
            'topic' => $managedDestinationTopic->get(),
            'flush' => true));

        return $managedDestinationTopic->getPostCount();
    }

    /**
     * Checks if the given message isn't too long.
     *
     * @param $args['message'] The message to check.
     *
     * @return bool False if the message is to long, else true.
     */
    public function checkMessageLength($args)
    {
        if (!isset($args['message'])) {
            return LogUtil::registerArgsError();
        }
        if (strlen($args['message']) + 8 > 65535) {
            return false;
        }

        return true;
    }

}
