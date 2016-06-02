<?php
$module_handler =& xoops_gethandler('module');
$GLOBALS['xrestModule'] = $module_handler->getByDirname('xrest');

global $adminmenu;
$adminmenu=array();
$adminmenu[0]['title'] = _XREST_MI_ADMINMENU_0;
$adminmenu[0]['link'] = "admin/index.php?op=dashboard";
$adminmenu[0]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('icons32').'/home.png';
$adminmenu[0]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('icons32').'/home.png';
$adminmenu[1]['title'] = _XREST_MI_ADMINMENU_1;
$adminmenu[1]['link'] = "admin/index.php?op=tables";
$adminmenu[1]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.tables.png';
$adminmenu[1]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.tables.png';
$adminmenu[2]['title'] = _XREST_MI_ADMINMENU_2;
$adminmenu[2]['link'] = "admin/index.php?op=fields";
$adminmenu[2]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.fields.png';
$adminmenu[2]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.fields.png';
$adminmenu[3]['title'] = _XREST_MI_ADMINMENU_3;
$adminmenu[3]['link'] = "admin/index.php?op=views";
$adminmenu[3]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.views.png';
$adminmenu[3]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.views.png';
$adminmenu[4]['title'] = _XREST_MI_ADMINMENU_4;
$adminmenu[4]['link'] = "admin/index.php?op=plugins";
$adminmenu[4]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.plugins.png';
$adminmenu[4]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.plugins.png';
$adminmenu[5]['title'] = _XREST_MI_ADMINMENU_5;
$adminmenu[5]['link'] = "admin/permissions.php";
$adminmenu[5]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.permissions.png';
$adminmenu[5]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('modicons32').'/xrest.permissions.png';
$adminmenu[6]['title'] = _XREST_MI_ADMINMENU_6;
$adminmenu[6]['link'] = "admin/index.php?op=about";
$adminmenu[6]['icon'] = '../../'.$GLOBALS['xrestModule']->getInfo('icons32').'/about.png';
$adminmenu[6]['image'] = '../../'.$GLOBALS['xrestModule']->getInfo('icons32').'/about.png';

?>