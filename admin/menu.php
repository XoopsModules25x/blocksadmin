<?php

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

$adminmenu[0]['title'] = _BLOCKS_ADMIN_CUSTOM ;
$adminmenu[0]['link'] = "admin/myblocksadmin.php?mid=0";

$module_handler4menu =& xoops_gethandler('module');
$criteria4menu = new CriteriaCompo(new Criteria('isactive', 1));
//$criteria4menu->add(new Criteria('hasmain', 1));
$criteria4menu->add(new Criteria('mid', '1', '>'));
$modules4menu =& $module_handler4menu->getObjects($criteria4menu, true);
array_unshift( $modules4menu , $module_handler4menu->get(1) ) ;

foreach( $modules4menu as $m4menu ) {
	$adminmenu[] = array(
		'title' => $m4menu->getVar('name') ,
		'link' => "admin/myblocksadmin.php?dirname=".$m4menu->getVar('dirname')
	) ;
}
?>