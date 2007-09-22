<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

define('_PNFORUM_WELCOMETOINTERACTIVEUPGRADE', 'pnForum Upgrade');
define('_PNFORUM_OLDVERSION', 'alte Version');
define('_PNFORUM_NEWVERSION', 'neue Version');
define('_PNFORUM_NEXTVERSION', 'n�chste Version');

define('_PNFORUM_BACKUPHINT', 'Vor der Durchf�hrung dieses Upgrades<br />bitte eine Sicherung der Datenbank erstellen!');
define('_PNFORUM_UPGRADE_ADDINDEXNOW', 'Indexfelder jetzt anlegen');
define('_PNFORUM_UPGRADE_ADDINDEXLATER', 'Indexfelder manuell in phpmyadmin o.� anlegen');

define('_PNFORUM_TO25_HINT', 'Das Upgrade auf Version 2.5 beinhaltet einige Datenbank�nderungen, u.a. das Hinzuf�gen zweier Indexfelder, um die Volltextsuche zu beschleunigen. Dies k�nnte auf manchem Systemen aufgrund der Laufzeitbegrenzung von PHP-Skripten in Zusammenhang mit einem gro�en Datenbestand zu Problemen f�hren.<br /><br />');
define('_PNFORUM_TO25_FAILED', 'Upgrade auf pnForum 2.5 fehlgeschlagen');

define('_PNFORUM_TO26_HINT', 'Das Upgrade auf Version 2.6 beinhaltet einige Datenbank�nderungen, um pnForum als Kommentarmodul zu verwenden.<br />');
define('_PNFORUM_TO26_FAILED', 'Upgrade auf pnForum 2.6 fehlgeschlagen');

define('_PNFORUM_TO27_HINT', 'Das Upgrade auf Version 2.7 beinhaltet keine �nderung an der Datenbankstruktur.<br />Die wichtigste Neuerung in dieser Version ist die Verwendung von Ajax (<a href="http://de.wikipedia.org/wiki/Ajax_(Programmierung)">mehr dazu</a>).<br /><br />');
define('_PNFORUM_TO27_FAILED', 'Upgrade auf pnForum 2.7 fehlgeschlagen');
