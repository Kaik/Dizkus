<?php
// $Id$
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

/**
 * forumpager plugin
 * creates a forum pager
 *
 *@param $params['total'] int total number of topics in this forum
 *@param $params['forum_id'] int forum id
 *@param $params['start'] int start value
 *
 */
function smarty_function_forumpager($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(empty($total) || empty($forum_id) || empty($start)) {
		$smarty->trigger_error(pnVarPrepForDisplay(_MODARGSERROR), e_error);
	} 

	if(!pnModAPILoad('pnForum', 'admin')) {
		$smarty->trigger_error("loading adminapi failed", e_error);
	} 
        
    $topics_per_page  = pnModGetVar('pnForum', 'topics_per_page');

    $count = 1;
    $next = $start + $topics_per_page;
    $previous = $start - $topics_per_page;
    $pager = "";
    if($total > $topics_per_page) {
        // more topcs than we want to see
        $pager = "<div>" . pnVarPrepForDisplay(_PNFORUM_GOTOPAGE) . "&nbsp;:&nbsp;";
        for($x = 0; $x < $total; $x++) {
            if(($previous >= 0) and ($count == 1)) {
                $pager .=  "<a href=\"". pnModURL('pnForum', 'user', 'viewforum', array( 'forum'=>$forum_id, 'start' => $previous))."\">".pnVarPrepForDisplay(_PNFORUM_PREVPAGE).'</a>';
                //$pager .= " | ";
            }
            if(!($x % $topics_per_page)) {
                if($x > 0) {
                    //$pager .= " | ";
                }
                if($x == $start) {
                    $pager .=  "| $count\n";
    
                } else {
                    if ( (($count%10)==0) // link if page is multiple of 10 
                    || ($count==1) // link first page 
                    || (($x > ($start-6*$topics_per_page)) //link -5 and +5 pages 
                    &&($x < ($start+6*$topics_per_page))) ) {
                        $pager .=  " | <a href=\"".pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forum_id,'start'=>$x))."\">$count</a>\n";
                    }
                }
                $count++;
            }
        }
        if($next < $total) {
            $pager .=  " | <a href=\"".pnModURL('pnForum', 'user', 'viewforum', array('forum'=>$forum_id,'start'=>$next))."\">".pnVarPrepForDisplay(_PNFORUM_NEXTPAGE)."</a>";
        }
    
        $pager .= "</div>";
        $l_phpbb_showGotopage = 1;
    }
    return $pager;
}

?>