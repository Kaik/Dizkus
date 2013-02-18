<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Api_Favorites extends Zikula_AbstractApi
{

    private $_displayOnlyFavorites = array();

    /**
     * display of user favorite forums only?
     *
     * read the flag from the users table that indicates the users last choice: show all forum (0) or favorites only (1)
     * @params $args['user_id'] int the users id
     * @returns boolean
     *
     */
    public function getStatus()
    {
        $uid = UserUtil::getVar('uid');
        // caching
        if (isset($this->_displayOnlyFavorites[$uid])) {
            return $this->_displayOnlyFavorites[$uid];
        }

        $forumUser = $this->entityManager->find('Dizkus_Entity_ForumUser', $uid);
        if (!$forumUser) {
            return false;
        }
        $this->_displayOnlyFavorites[$uid] = $forumUser->getFavoriteDisplayChoice();

        return $this->_displayOnlyFavorites[$uid];
    }

    /**
     * Get forum subscription status
     *
     * @params $args['forum'] Dizkus_Entity_Forum
     * @params $args['user_id'] int the users uid (optional)
     * @return boolean - true if the forum is user favorite or false if not
     */
    public function isFavorite($args)
    {
        if (empty($args['forum'])) {
            return LogUtil::registerArgsError();
        }
        if (empty($args['user_id'])) {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        $managedForumUser = new Dizkus_Manager_ForumUser($args['user_id']);
        return $managedForumUser->get()->isFavoriteForum($args['forum']);
    }

}
