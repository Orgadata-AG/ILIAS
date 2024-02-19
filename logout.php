<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* logout script for ilias
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

/**
 * @see https://www.php.net/manual/en/function.str-starts-with
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strlen($needle) === 0 || strpos($haystack, $needle) === 0;
    }
}

if(!empty($_GET['state']) && str_starts_with($_GET['state'], 'https://' . $_SERVER['SERVER_NAME'] . '/')) {
   header('Location: ' . $_GET['state']);
   exit;
}

require_once("Services/Init/classes/class.ilInitialisation.php");

ilInitialisation::initILIAS();

$ilCtrl->setCmd('doLogout');
$ilCtrl->callBaseClass('ilStartUpGUI');
$ilBench->save();

exit;
