<?php

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
#   MAGNIFICA WEB SCRIPTS - ANIMA GALLERY 2.5 - HTTP://DG.NO.SAPO.PT   #
#                 LICENSE: FREE FOR NON COMMERCIAL USE                 #
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
error_reporting(0);

// INCLUDES
include('settings.inc.php');
include('func.php');

// GLOBALS
list($THEME,$LANG) = import_theme_lang();
$THEME = stripslashes($THEME);
$THEME_LIST = import_theme_list(0);
$LANG_LIST = import_lang_list(0);
$TITLE = TITLE;

// THEME & LANGUAGE INCLUDES
include('themes/'.$THEME.'/templates.php');
include('languages/'.$LANG.'.php');

// LOADER ID's
$load = $_GET['load'];
$id = $_GET['id'];
if($_GET['l'])
	parse_str('load='.base64_decode($_GET['l']));

session_start();

import_admin_cookie();
checkrequirements();

if(!$load)
{
	if(!$CONTENTS)
		$CONTENTS = main_page();

	$DESC = $meta_desc;
	$KEY = $meta_keys;
}
else
{
	switch($load)
	{
		case 'dir': $CONTENTS = loaddir(trim($id,'/\\').'/'); break;
		case 'search': $CONTENTS = search(); break;
		case 'prev': $CONTENTS = preview($id); break;
		case 'editimage': $CONTENTS = editimage($id); break;
		case 'editdir': $CONTENTS = editdir($id); break;
		case 'img': $CONTENTS = parseimage($id); break;
		case 'search': $CONTENTS = search(); break;
		case 'adminboard': $CONTENTS = adminboard(); break;
		case 'upd': $CONTENTS = updc($id,1); break;
		case 'ajax': $CONTENTS = ajax($_GET['mode']); break;
		case 'rss': $CONTENTS = rss(); break;
		case 'blog': $CONTENTS = blog(); break;
		case 'captcha': $CONTENTS = captcha_print(); break;
		case 'corr_or': $CONTENTS = corr_or(); break;
		case 'rcom_init': $CONTENTS = rcom_init(); break;
		case 'mcom_init': $CONTENTS = mcom_init(); break;
		case 'mview_init': $CONTENTS = mview_init(); break;
		case 'trated_init': $CONTENTS = trated_init(); break; 
	}
}

if(SUFIXTITLE)
	$TITLE .= ' - '.SUFIXTITLE;

$VERSION = file_get_contents('version');

$replacement = array(
				'THEME' => $THEME,
				'THEME_LIST' => $THEME_LIST,
				'LANG_LIST' => $LANG_LIST,
				'CONTENTS' => $CONTENTS,
				'TITLE' => $TITLE,
				'RSS_TITLE' => TITLE,
				'MTITLE' => $MTITLE,
				'REQUEST_URI' => str_replace(
								array('"',"'",'<','>'),
								array('','','',''),
								$_SERVER['REQUEST_URI']
							),
				'KEY' => $KEY,
				'DESC' => $DESC,
				'TREE' => read_home(ALB_DIR,1),
				'OPTIONS' => parseoptions(),
				'FEEDS' => import_feeds(),
				'ONLOAD' => import_onload(),
				'CURR' => date("Y", time()),
				'RCOM' => import_log('rcom'),
				'MCOM' => import_log('mcom'),
				'MVIEW' => import_log('mview'),
				'TRATED' => import_log('trated'),
				'SYSINFO' => sysinfo(),
				'VERSION' => $VERSION
				);

ob_start();

if($load != 'img' AND $load != 'upd' AND $load != 'ajax' AND $load != 'rss' AND $load != 'captcha' AND $load != 'corr_or')
	echo popt('index', $replacement);
else
	echo $CONTENTS;

ob_end_flush();

?>
