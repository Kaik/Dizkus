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

// post_id, cat_id, forum_id
function smarty_function_splittopic_button($params, &$smarty) 
{
    extract($params); 
	unset($params);

    if(allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        $image = pnModGetVar('pnForum', 'splittopic_image');
        $img_attr = getimagesize($image);
        $out = "<a href=\"".pnModURL('pnForum', 'user', 'splittopic', array('post'=>$post_id))."\"><img src=\"$image\" alt=\"".pnVarPrepForDisplay(_PNFORUM_SPLITTOPIC_TITLE)."\" ".$img_attr[3]." >".pnVarPrepForDisplay(_PNFORUM_SPLITTOPIC_TITLE)."</a>&nbsp;&nbsp;&nbsp;";
    }
    return $out;
}

?>