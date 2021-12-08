<?php

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
#   MAGNIFICA WEB SCRIPTS - ANIMA GALLERY 2.6 - HTTP://DG.NO.SAPO.PT   #
#                 LICENSE: FREE FOR NON COMMERCIAL USE                 #
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#

array_walk($_POST, 'reslash_multi');
array_walk($_GET, 'reslash_multi');

ini_set('max_execution_time', '90');
ini_set('memory_limit', '96M');

#############################################################
# INDEX
#############################################################
#COMMON
#makerandom, pfancy, isadmin, per, ip
#
#PARSING
#mktitle, parse, parserate, path, print_err, tdat, tdat2, popt, parse_news, page_list, read_pn, getlang
#
#MODULES
#blog, bbcode, captcha_check, captcha_init, captcha_req, captcha_print,  corr_or, feeds, lupl, parseoptions, rcom_init, rcom_list, rcom_upd,  read_home, read_home_select, rss, smiley, trated_init, trated_list,  trated_upd, ajax, sysinfo
#
#MAIN PAGE
#import_admin_cookie, import_feeds, import_lang_list, checkrequirements,  import_log, import_onload, import_theme_lang, import_theme_list
#
#MAIN FUNCTIONS
#loaddir, adminboard, main_page, preview, search, upl
#
#IMAGE PROCESSING
#deli, movei, parseimage, testi, tn, isimg
#
#DIR/FILE MANIPULATION
#convert_ulog, read_idf, decode_ulog, dir_is_empty, encode_ulog, get_title_desc,  rend, sfv_check, test_d, test_subd, upd_dirs, update_ulog,  updat_ulog_batch, updc, write_data, isblog, update_disabled, titlefromfile, read_attr, upd_attr, compat25
#
#STRING PROCESSING
#reslash, reslash_multi, restrip_multi, restrip_multi_noentities,  trim_bad_chars, wrap_long, char2space
#############################################################

function test_human_spam($data)
{
	$data = html_entity_decode($data);
	if(HUMANSPAM_NOURLS)
	{
		if(preg_match("#(http|ftp|https)://|www\.#i",$data))
			$error = getlang(218);
	}
	if(HUMANSPAM_NOWORDS)
	{
		$words = explode(',',trim(HUMANSPAM_NOWORDS));
		foreach($words as $key => $value)
		{
			if(strstr($data,trim($value)))
			{
				$error = getlang(218);
				break;
			}
		}
	}
	if(HUMANSPAM_IPBAN)
	{
		$words = explode(',',trim(HUMANSPAM_IPBAN));
		$ip = ip();
		foreach($words as $key => $value)
		{		
			if(preg_match('/^'.trim(str_replace('*','',$value)).'/i',$ip))
			{
				$error = getlang(218);
				break;
			}
		}
	}
	return $error;
}

function batch_com_del($sfv,$mode)
{
	if($mode == '0')
	{
		$file_contents = @file_get_contents(DATA_FILES_DIR.$sfv.".df");
		$init = 1;
	}
	else
	{
		$file_contents = @file_get_contents(DATA_FILES_DIR.'blog_'.$sfv.".df");
		$init = 0;
	}

	$count = 0;
	$lines = compat25(explode("\n",$file_contents),$sfv);
	foreach($lines as $key => $value)
	{
		if($key > $init)
		{
			$todel = 0;
			list($v1,$v2,$v3,$v4) = explode('|',$value);
			if($mode == '0')
				$com = base64_decode($v2).' '.base64_decode($v1);
			else
				$com = base64_decode($v1).' '.base64_decode($v2);
			if($_POST['nourls'])
			{
				if(preg_match("#(http|ftp|https)://|www\.#i",trim($com)))
					$todel = 1;
			}
			if($_POST['nowords'] AND !$todel)
			{
				$words = explode(',',trim($_POST['nowords']));
				foreach($words as $key2 => $value2)
				{
					if(strstr(trim($com),trim($value2)))
					{
						$todel = 1;
						break;
					}
				}
			}
			if($todel)
			{
				unset($lines[$key]);
				$count++;
			}
		}
	}

	if($count)
	{
		foreach($lines as $key => $value)
		{
			if($mode == '0' AND $key == '1')
			{
				$fields = explode('-',trim($value));
				$fields[2] = intval($fields[2])-$count;
				$tmp = '';
				foreach($fields as $k => $v)
					$tmp .= $v.'-';
				$value = trim($tmp,'-');
			}
			if($mode == '1' AND $key == '0')
			{
				$fields = explode('-',trim($value));
				$fields[0] = intval($fields[0])-$count;
				$tmp = '';
				foreach($fields as $k => $v)
					$tmp .= $v.'-';
				$value = trim($tmp,'-');
			}
			$towrite .= $value."\n";
		}
		if($mode == '0')
			write_data(DATA_FILES_DIR.$sfv.'.df',trim($towrite),'w');
		else
			write_data(DATA_FILES_DIR.'blog_'.$sfv.'.df',trim($towrite),'w');
	}

	return str_replace('{n}',$count,getlang(359));
}

function parse_news($arr, $mode)
{
	$ct = count($arr);
	foreach($arr as $key => $value)
	{
		list($time,$tmp) = explode(' ',$value);
		$tmp = base64_decode($tmp);

		$out .=
			popt('parse_news.item',
				array('entry' =>
					pfancy(
						popt('parse_news.item.entry',
							array(
								'date' => strtoupper(tdat2("l, M d, Y",$time)),
								'contents' => parse($tmp, '')
							)
						)
					, 1, $mode
					)
				)
			);
	}
	return($out);
}

function char2space($string)
{
	$queue = stripslashes(TITLE_FROM_FILE_CHAR);

	while(strlen($queue) > 0)
	{
		$string = str_replace(substr($queue,0,1),' ',stripslashes($string));
		$queue = substr($queue,1,strlen($queue)-1);
	}
	return($string);
}

function isimg($filename)
{
	if(preg_match("/\.jpg$|\.jpeg$|\.gif$|\.png$/i", $filename))
		return(1);
	else
		return(0);
}

function isblog($filename)
{
	if(preg_match("/^blog_/i", $filename))
		return(1);
	else
		return(0);
}

function update_disabled($cdir,$mode)
{
	$lines = explode("\n", trim(file_get_contents(DATA_FILES_DIR.'disabled.log')));

	foreach($lines as $key => $value)
		$arr[$value] = 1;

	if($mode == '0')
		$arr[$cdir] = 0;
	if($mode == '1')
		$arr[$cdir] = 1;
		
	foreach($arr as $key => $value)
	{
		if($value == '1')
			$out .= $key."\n";
	}

	write_data(DATA_FILES_DIR.'disabled.log',trim($out),'w');

}

function titlefromfile($file)
{
	if(TITLE_FROM_FILE OR $_POST['titlefromfile'])
	{
		$out = char2space(preg_replace("/.jpg$|.jpeg$|.png$|.gif$/i", '', $file));

		if(strlen($out) >= TITLE_MIN_LENGTH AND strlen($out) <= TITLE_MAX_LENGTH)
		{
			if($_POST['titlefromfile'])
				return($out);
			else
				return(base64_encode($out));
		}
		else
			return('');
	}
	else
		return('');
}

function read_attr($source)
{
	if(!is_array($source))
		$lines = explode("\n",file_get_contents($source));
	else
		$lines = $source;

	$attr = explode("-",$lines[1]);

	return($attr);
}


function upd_attr($arr,$source)
{
	if(!is_array($source))
		$lines = explode("\n",file_get_contents($source));
	else
		$lines = $source;

	$attr = explode("-",$lines[1]);

	foreach($arr as $key => $value)
		$attr[$key] = $value;

	$tmp = '';
	foreach($attr as $key => $value)
		$tmp .= $value.'-';
	$tmp = rtrim($tmp,'-');

	$lines[1] = $tmp;
	$tmp = '';
	foreach($lines as $key => $value)
		$tmp .= $value."\n";
	
	return trim($tmp);
}

#converts image's df file to version 2.5 if necessary
function compat25($arr,$sfv)
{
	foreach($arr as $key => $value)
	{
		if((strlen(trim($value)) == 0 OR preg_match('/^\[FILE\]/i',$value)) AND $key != '1')
			unset($arr[$key]);
	}
	$tmp = array_merge(array(), $arr);
	$arr = $tmp;

	$attr = explode('-',$arr[1]);
	if(count($attr) < 5 AND preg_match("/\.jpg$|\.jpeg$|\.gif$|\.png$/i", $arr[0]))
	{
		$title = '';
		$fattr = @getimagesize($arr[0]);
		$fsize = filesize($arr[0]);
		foreach($arr as $key => $value)
		{
			if(strstr($value,'[TITLE]'))
			{
				list($tmp,$title) = explode('[TITLE]', $value);
				break;
			}
		}

		$new_arr = array(0 => $arr[0], 1 => base64_encode($arr[1]).'-'.$arr[2].'-'.$arr[3].'-'.$arr[4].'-'.$fattr[0].'-'.$fattr[1].'-'.$fattr[2].'-'.$fsize.'-'.filemtime($arr[0]).'-'.base64_encode($title).'-');

		if(intval($arr[3]) > 0)
		{
			for($i = 5; $i < 5+$arr[3] ; $i++)
				$new_arr[$i-3] = $arr[$i];
		}

		foreach($new_arr as $key => $value)
			$out .= $value."\n";

		write_data(DATA_FILES_DIR.$sfv.'.df', trim($out), 'w');
		return($new_arr);
	}
	else
		return($arr);
}


function blog()
{
	global $TITLE, $THEME;

	if($_POST['batch_com_del'])
	{
		$_SESSION['onload'] = batch_com_del($_GET['id'],1);
		header("location: ".$_SERVER['REQUEST_URI']);
		exit();
	}

	if($_POST['addcom'])
	{
		$bid = $_POST['bid'];
		$upl_name = $_POST['upl_name'];
		$com = $_POST['com'];
	
		if(!per('blog_com'))
			$upl_err = popt('msg.3', '');
		else
		{
			if(!$upl_name OR !$com)
				$upl_err = popt('msg.4', '');
			else
			{
				if(strlen($upl_name) > 20)
					$upl_err = popt('msg.8', '');
				else
				{
					if(strlen($_POST['com']) > GAL_COM_MAX_LENGTH OR strlen($_POST['com']) < GAL_COM_MIN_LENGTH)
						$upl_err =
							popt('preview.form.err1',
								array(
									'gal_com_min_length' => GAL_COM_MIN_LENGTH,
									'gal_com_max_length' => GAL_COM_MAX_LENGTH,
									'curr' => strlen($_POST['com'])
								)
							);
					else
					{
						if($_SESSION['blog_'.$bid]+(60*ANTI_SPAM_DELAY) > time())
							$upl_err = popt('msg.6', '');
						else
						{
							$upl_err = captcha_check('com');
							
							if(!$upl_err)
							{
								$upl_err = test_human_spam($com.' '.$upl_name);
								if(!$upl_err)
								{
									$time_t = time();
									$tmp = '';
									list($comn,$tmp) = explode("-",file_get_contents(DATA_FILES_DIR.'blog_'.$bid.".df"),2);
									$comn++;
									$tmp = $comn.'-'.$tmp;

									write_data(DATA_FILES_DIR.'blog_'.$bid.'.df', trim($tmp)."\n".base64_encode($com).'-'.base64_encode($upl_name).'-'.ip().'-'.$time_t, 'w');

									if($upl_name != $_COOKIE['username'])
										setcookie('username',$upl_name,time()+31536000,'/');

									$_SESSION['blog_'.$bid] = time();

									rcom_upd(base64_encode($upl_name).'|'.base64_encode($_POST['com']).'|blog_'.$bid.'.df'.'|'.$time_t);

									header("location: ".$_SERVER['REQUEST_URI'].'#g'.$bid);
									$_SESSION['onload'] = popt('msg.10', '');
									exit();
								}
							 }
						}
					}
				}
			}
		}
	}

	$id = $_GET['id'];
	
	$dat = file(DATA_FILES_DIR.'blog_'.$id.'.df');
	list($comn,$title,$name_b,$body,$time_blog) = explode('-',trim($dat[0]));
	$title = base64_decode($title);
	$name_b = base64_decode($name_b);
	$body = base64_decode($body);

	$upl_st = 'closed';

	if($upl_err)
		$upl_st = 'open';

	if(!$upl_name AND $_COOKIE['username'])
		$upl_name = $_COOKIE['username'];

	$edit_bt = '';
	if(per('blog_edit'))
		$edit_bt = popt('blog.item.edit_bt', array('bid' => $time_blog, 'THEME' => str_replace("'",'_QUOTE_',$THEME)));

	$del_bt = '';
	if(per('blog_del'))
		$del_bt = popt('blog.item.del_bt',
					array(
						'bid' => $time_blog,
						'msg' => popt('javascript.confirm.2', ''),
						'THEME' => str_replace("'",'_QUOTE_',$THEME)
					)
				);

	$TITLE = stripslashes($title);
	$tmp = $dat;
	unset($tmp[0]);

	if($comn)
	{
		foreach($tmp as $key => $value)
		{	
			list($com1,$name,$ip,$time_com) = explode('-',trim($value));
			$com1 = base64_decode($com1);
			$name = base64_decode($name);

			$del = '';
			if(per('blog_com_del'))
				$del =
					popt('blog.com.del',
						array(
							'id' => sfv_check(trim($value)),
							'time' => $id,
							'msg' => popt('javascript.confirm.1', ''),
							'THEME' => str_replace("'",'_QUOTE_',$THEME)
						)
					);

			$ipinfo = '';
			if(isadmin())
				$ipinfo = " ({$ip})";

			$comments .=
				popt('preview.comments.entry.box',
					array(
						'comment' =>
							pfancy(
								popt('preview.comments.entry',
									array(
										'id' => sfv_check(trim($value)),
										'fdate' => tdat($time_com),
										'del' => $del,
										'name' => stripslashes($name).$ipinfo,
										'com' => wrap_long(parse($com1, 'com'))
									)
								)
							,1,1)
					)
				);
		}
	}
	else
		$comments = popt('preview.comments.void','');

	$prev_box = '';
	if($_POST['preview'])
	{
		$bid = $_POST['bid'];
		$upl_name = $_POST['upl_name'];
		$com = $_POST['com'];

		$upl_st = 'open';
		$prev_box =
			popt('preview.comments.entry.box',
				array(
					'comment' =>
						pfancy(
							popt('preview.comments.entry',
								array(
									'id' => '',
									'fdate' => tdat(time()),
									'del' => '',
									'name' => stripslashes($upl_name),
									'com' => wrap_long(parse($com, 'com'))
								)
							)
						,1,1)
				)
			);
	}

	$captcha = captcha_init('com');

	$batch_com_removal = '';
	if(isadmin() AND $comn)
		$batch_com_removal = popt('blog.entry.batch_com_removal', '');

	return
		popt('blog.entry',
			array(
				'batch_com_removal' => $batch_com_removal,
				'bid' => $id,
				'form' =>
					popt('blog.item.standalone.comform',
						array
						(
							'smilies' => smiley('com', 0),
							'captcha' => $captcha,
							'bid' => $id,
							'upl_err' => print_err($upl_err),
							'upl_name' => stripslashes($upl_name),
							'upl_st' => $upl_st,
							'com' => stripslashes($com),
							'prev_box' => $prev_box
						)
					),
				'comments' => $comments,
				'entry' =>
					popt('blog.item.standalone',
						array
						(
							'edit_bt' => $edit_bt,
							'del_bt' => $del_bt,
							'date' => strtoupper(tdat2("l, M d, Y",$time_blog)),
							'title' => $title,
							'contents' => parse($body, ''),
							'hour' => tdat2("H:i",$time_blog),
							'user_link' => stripslashes($name_b),
							'comn' => $comn,
							'bid' => $id
						)
					)
			)
		);

}

function rss()
{

	$farr = array();

	$dir_items = glob(DATA_FILES_DIR.'blog_*');
	if($dir_items)
	{
		foreach($dir_items as $filename)
			$farr[] = $filename;
	}

	$n = count($farr);
	rsort($farr);

	if($n)
	{
		foreach($farr as $key => $value)
		{	
			$dat = file($value);
			list($comn,$title_rss,$name,$body,$time) = explode('-',trim($dat[0]));
			$title_rss = base64_decode($title_rss);
			$name = base64_decode($name);
			$body = base64_decode($body);

			$fdate = gmdate("D, d M Y H:i",$time)." GMT";

			$bd = $body;
			if(strlen($bd) > 200)
				$bd = substr($bd,0,200)."...";

			if($i == 0)
				$lbdate = $fdate;

			$out .= 
				popt('rss.item',
					array(
						'title' => $title_rss,
						'serv' => $_SERVER['SERVER_NAME'],
						'id' => $time,
						'tl' => mktitle($title_rss),
						'bd' => strip_tags(parse($bd, '')),
						'fdate' => $fdate,
						'HOME' => str_replace('?load=rss','',$_SERVER['REQUEST_URI'])
					)
				);
	
		}
	}

	return
		popt('rss',
			array(
				'pn' => htmlspecialchars(TITLE),
				'serv' => $_SERVER['SERVER_NAME'],
				'tagd' => htmlspecialchars(META_DESC),
				'items' => $out,
				'lbdate' => $lbdate,
				'home' => str_replace('?load=rss','',$_SERVER['REQUEST_URI'])
			)
		);

}

function per($id)
{
	switch($id)
	{
		case 'upload': $tmp = PER_UPLOAD; break;
		case 'overwrite': $tmp = PER_OVERWRITE; break;
		case 'blog': $tmp = PER_BLOG; break;
		case 'blog_edit': $tmp = PER_BLOG_EDIT; break;
		case 'blog_del': $tmp = PER_BLOG_DEL; break;
		case 'blog_com': $tmp = PER_BLOG_COM; break;
		case 'blog_com_del': $tmp = PER_BLOG_COM_DEL; break;
		case 'com': $tmp = PER_COM; break;
		case 'com_del': $tmp = PER_COM_DEL; break;
		case 'rate': $tmp = PER_RATE; break;
	}

	switch($tmp)
	{
		case '0': return(1); break;
		case '1': if(isadmin()) return(1); else return(0);
	}
}

function isadmin()
{
	if(($_SESSION['des_pass'] != md5(DES_PASS)) OR !$_SESSION['des_pass'] OR !DES_PASS)
		return(0);
	else
		return(1);
}

function lupl()
{
	$dest = DATA_FILES_DIR.'upload.log';

	if(file_exists($dest))
	{
		$tmp_ = convert_ulog();
		$log_tmp = explode("\n",$tmp_);
		$log = array_Reverse($log_tmp);

		$ic = count($log);

		if(!$_GET['page'])
			$_GET['page'] = 1;

		$page = $_GET['page']-1;
		$perpage = LAST_UPLOADS_N;
		$init = $page*$perpage;

		$inite = ($page+1)*$perpage;
		if($inite > $ic)
			$inite = $ic;

		$pages = $ic/$perpage;

		if($ic)
		{
			for($i = $init ; $i < $inite ; $i++)
			{
				$value = $log[$i];
				list($filename,$path,$uname,$ip,$sfv,$date) = decode_ulog($value);

				if(file_exists(ALB_DIR.$path.$filename) AND file_exists(DATA_FILES_DIR.$sfv.'.df') AND file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
				{
					$idat = getimagesize(DATA_FILES_DIR.$sfv.'.jpg');

					$st = 'last_web_upl_w';
					if($idat[1] > $idat[0])
						$st = 'last_web_upl_h';

					$out .=
						popt('last_web_uploads.item',
							array(
								'sfv' => $sfv,
								'thumb_link' => DATA_FILES_DIR.$sfv,
								'user' => $uname,
								'class' => $st
							)
						);
				}
				else
				{
					$tmp_ = $inite+1;
					if($tmp_ <= $ic)
						$inite++;
				}
			}
		}
		else
			$out = popt('last_web_uploads.void', '');
	}
	else
		$out = popt('last_web_uploads.void', '');

	return(popt('last_web_uploads', array( 'items' => $out)));
}

function smiley($field,$id)
{

	$s1 =
		array(
			'1' => ':)',
			'3' => ':o',
			'2' => ':(',
			'4' => ':|',
			'5' => ':frust:',
			'6' => ':D',
			'7' => ':p',
			'8' => '-_-',
			'10' => ':E',
			'11' => ':mad:',
			'12' => '^_^',
			'13' => ':cry:',
			'14' => ':inoc:',
			'15' => ':z',
			'16' => ':love:',
			'17' => '@_@',
			'18' => ':sweat:',
			'19' => ':ann:',
			'20' => ':susp:',
			'9' => '>_<'
		);

	foreach($s1 as $key => $value)
		$out .=
			popt('smiley.item',
				array(
					'id' => $id,
					'title' => $value,
					'field' => $field,
					'str_pat' => $value,
					'str_rep' => 'sm'.$key.'.gif'
				)
			);

	return($out);

}


function bbcode ($string, $mode)
{
	$search =
		array(
      		'/\[b\](.*?)\[\/b\]/si',
      		'/\[i\](.*?)\[\/i\]/si',
      		'/\[u\](.*?)\[\/u\]/si',
      		'/\[quote\](.*?)\[\/quote\]/si',
      		'/\[img([|]?)([left|center|right]*)\](.*?)\[\/img\]/si',
      		'/\[url\](.*?)\[\/url\]/si',
      		'/\[url\=(.*?)\](.*?)\[\/url\]/si'
   		);

	$replace =
		array(
      		'<b>\\1</b>',
      		'<i>\\1</i>',
      		'<u>\\1</u>',
			'<fieldset class="quote"><legend>Quote</legend><i>\\1</i></fieldset>',
      		'<img src="\\3" align="\\2" class="mar10">',
      		'<a href="\\1">\\1</a>',
      		'<a href="\\1">\\2</a>'
   		);

	if($mode == 'com')
		$replace =
			array(
      			'\\1',
      			'\\1',
      			'\\1',
				'\\1',
      			'',
      			'\\1',
      			'\\1'
			);

	$s1 =
		array(
			'1' => ':)',
			'3' => ':o',
			'2' => ':(',
			'4' => ':|',
			'5' => ':frust:',
			'6' => ':D',
			'7' => ':p',
			'8' => '-_-',
			'10' => ':E',
			'11' => ':mad:',
			'12' => '^_^',
			'13' => ':cry:',
			'14' => ':inoc:',
			'15' => ':z',
			'16' => ':love:',
			'17' => '@_@',
			'18' => ':sweat:',
			'19' => ':ann:',
			'20' => ':susp:',
			'9' => '>_<'
		);

	if($_GET['load'] != 'rss')
	{
		foreach($s1 as $key => $value)
			$string =
				str_replace(
					$value,
					popt('smiley.item.parsed',
						array(
							'key' => $key
						)
					),
					$string
				);
	}

	preg_match_all('#\[list\](.*?)\[/list\]#si', $string, $matches);

	foreach($matches[1] as $key => $value)
	{
		$data = '';
		$lines = explode("\n",trim($value));
		foreach($lines as $key2 => $value2)
		{
			if(trim($value2))
				$data .= '<li>'.$value2.'</li>';
		}
		$string = str_replace($matches[0][$key], '<ul>'.$data.'</ul>', $string);
	}

	preg_match_all('#\[olist\](.*?)\[/olist\]#si', $string, $matches);

	foreach($matches[1] as $key => $value)
	{
		$data = '';
		$lines = explode("\n",trim($value));
		foreach($lines as $key2 => $value2)
		{
			if(trim($value2))
				$data .= '<li>'.$value2.'</li>';
		}
		$string = str_replace($matches[0][$key], '<ol>'.$data.'</ol>', $string);
	}

  	return preg_replace($search, $replace, $string);
}

function dir_is_empty($dir)
{
	$handle2 = @opendir($dir);
	if ($handle2 == false) return -1;
	while (($file2 = @readdir($handle2)) != false)
	{
		if($file2 != "." AND $file2 != "..")
		{
			@closedir($handle2);
			return(0);
			break;
		}
	}
	@closedir($handle2);
	return(1);
}


function ajax($mode)
{
	global $THEME, $L;

	switch($mode)
	{
		case '1':
			sleep(1);
			$id = $_GET['id'];
			$tmp = file(DATA_FILES_DIR.'blog_'.$id.'.df');
			unset($tmp[0]);
			$n = count($tmp);

			if($n)
			{
				foreach($tmp as $key => $value)
				{	
					list($com,$name,$ip,$time) = explode('-',trim($value));
					$com = base64_decode($com);
					$name = base64_decode($name);

					$del = '';
					if(per('blog_com_del'))
						$del =
							popt('blog.com.del',
								array(
									'id' => sfv_check(trim($value)),
									'time' => $id,
									'msg' => popt('javascript.confirm.1', ''),
									'THEME' => str_replace("'",'_QUOTE_',$THEME)
								)
							);

					$md = '0';
					if($key >= 1 AND $key < $n)
						$md = '4';
					if($key == 1 AND $n == 1)
						$md = '2';
					if($n > 1 AND $key == $n)
						$md = '2';

					$out .=
						popt('preview.comments.entry.box',
							array(
								'comment' =>
									pfancy(
										popt('preview.comments.entry',
											array(
												'id' => sfv_check(trim($value)),
												'fdate' => tdat($time),
												'del' => $del,
												'name' => stripslashes($name),
												'com' => wrap_long(parse($com, 'com'))
											)
										)
									,$md,1)
							)
						);
				}
				echo popt('blog.comments', array('comments' => $out));
			}
			else
				echo popt('blog.comments.void', '');
		break;
		case '2':
			sleep(1);
			$id = $_GET['id'];
			$sfv = $_GET['sfv'];
			$file_contents = file_get_contents(DATA_FILES_DIR.$sfv.".df");
			$lines = explode("\n",$file_contents);
			$attr = read_attr($lines);

			if(per('com_del'))
			{
				$attr[2]--;
				$lines = explode("\n", upd_attr(array('2' => $attr[2]),$lines));
				foreach($lines as $key => $value)
				{
					if(sfv_check($value) != $id)
						$nc .= $value."\n";
				}
				write_data(DATA_FILES_DIR.$sfv.".df",rtrim($nc),'w');
		
				echo popt('msg.1', '');
			}
		break;
		case '3':
			sleep(1);
			$id = $_GET['id'];
			$time = $_GET['time'];
			$file_contents = file_get_contents(DATA_FILES_DIR.'blog_'.$time.".df");
			$lines = explode("\n",$file_contents);

			if(per('blog_com_del'))
			{
				list($comn,$tmp) = explode("-",$lines[0],2);
				$lines[0] = ($comn-1).'-'.$tmp;
				foreach($lines as $key => $value)
				{
					if(sfv_check($value) != $id)
						$nc .= $value."\n";
				}
				write_data(DATA_FILES_DIR.'blog_'.$time.".df",rtrim($nc),'w');
		
				echo popt('msg.1', '');
			}
		break;
		case '4':
			sleep(1);
			$id = $_GET['id'];
			$target = DATA_FILES_DIR.'blog_'.$id.".df";
			$contents = file($target);

			if(per('blog_edit'))
			{
				list($comn,$tmp) = explode("-",$contents[0],2);
				list($title,$name,$entry,$time) = explode("-",trim($tmp));
				$title = base64_decode($title);
				$name = base64_decode($name);
				$entry = base64_decode($entry);

				echo
					popt('blog.form.edit',
						array(
							'bid' => $id,
							'smilies' => smiley('entry_'.$id, $id),
							'bbcode' =>
								popt('bbcode',
									array(
										'id' => 'entry_'.$id,
										'id2' => 'form_'.$id
									)
								),
							'status' => $status,
							'error' => print_err($error),
							'name' => stripslashes($name),
							'title' => stripslashes($title),
							'entry' => htmlentities(stripslashes($entry))
						)
					);
			}
		break;
		case '5':
			sleep(1);
			$id = $_GET['id'];
			$target = DATA_FILES_DIR.'blog_'.$id.".df";

			if(per('blog_del'))
			{
				@unlink($target);
				if(!file_exists($target))
					echo popt('msg.2', '');
			}
		break;
		case '6':
			sleep(1);
			if(isadmin())
			{
				$id = $_GET['id'];
				$lines = explode("\n",file_get_contents(DATA_FILES_DIR.$id.'.df'));
				$source = $lines[0];

				if(file_exists($source))
				{
					if(is_writable($source))
					{
						@unlink($source);
						if(!file_exists($source))
						{
							if(file_exists(DATA_FILES_DIR.$id.'.df'))
								@unlink(DATA_FILES_DIR.$id.'.df');
							if(file_exists(DATA_FILES_DIR.$id.'.jpg'))
								@unlink(DATA_FILES_DIR.$id.'.jpg');
							if(file_exists(DATA_FILES_DIR.'p_'.$id.'.jpg'))
								@unlink(DATA_FILES_DIR.'p_'.$id.'.jpg');
							echo popt('msg.9', '');
						}
					}
				}
			}
		break;
		case '7':
			sleep(1);
			if(isadmin())
			{
				$id = $_GET['id'];
				if(file_exists(ALB_DIR.$id))
				{
					if(!dir_is_empty(ALB_DIR.$id))
						echo popt('msg.11', '');
					else
					{
						if(rmdir(ALB_DIR.$id))
							echo popt('msg.12', '');
						else
							echo popt('msg.13', '');
					} 
				}
				else
					echo popt('msg.14', '');
			}
		break;
		case '8':
			echo popt('javascript.bbcode_info', array('THEME' => str_replace("'",'_QUOTE_',$THEME)));
		break;
		case '9':
			sleep(1);
			echo popt('loaddir.desc.edit.dir_manipulation.album_list', array('options' => read_home_select(ALB_DIR,1,base64_decode($_GET['dir']))));
		break;
		case '10':
			sleep(1);
			$english = $toedit = array();
			$tmp = $L;
			$L = array();
			include('languages/english.php');
			$english = $L;
			$L = array();
			include('languages/'.$_GET['id'].'.php');
			$toedit = $L;
			$L = array();
			$L = $tmp;
			foreach($english as $key => $value)
			{
				$out .= popt(
							'adminboard.editor.form.item',
							array(
								'key' => $key,
								'english_item' => str_replace(
												array('{','}'),
												array('<b>{','}</b>'),
												htmlentities($english[$key])
											),
								'lang_item' => str_replace(
												htmlentities('&#'),
												'&#',
												htmlentities($toedit[$key])
											)
							)
						);
			}
			echo popt('adminboard.editor.form', array('contents' => $out, 'idl' => $_GET['id']));
			break;
		case '11':
			sleep(1);
			$target = 'languages/'.$_GET['id'].'.php';
			if(@unlink($target))
				echo 'OK, Successfully Removed!';
			else
				echo 'Removal Failed!';
			break;
		case '12':
			sleep(1);
			$dir = base64_decode($_GET['id']);
			$cdir = ALB_DIR.trim($dir, '\//').'/';
			if(file_exists($cdir))
			{
				$handle = @opendir($cdir);
				if ($handle == false) return -1;
				while (($file = @readdir($handle)) != false)
				{
					if($file != "." AND $file != "..")
					{
						if(!is_dir($cdir.$file) AND isimg($file))
						{
							if(testi($file,1))
							{
								$ct++;
								$sfv = sfv_check($cdir.$file);
								if(!file_exists(DATA_FILES_DIR.$sfv.".df"))
								{
									$ex++;
									$testimg = @getimagesize($cdir.$file);
									write_data(DATA_FILES_DIR.$sfv.'.df', $cdir.$file."\n-0-0-0-0-{$testimg[0]}-{$testimg[1]}-{$testimg[2]}-".filesize($cdir.$file).'-'.time().'-'.titlefromfile($file).'-', 'w');
									if(file_exists(DATA_FILES_DIR.$sfv.'.df'))
									{
										@tn($cdir.$file,DATA_FILES_DIR.$sfv.'.jpg',THUMBNAIL_MAX_DIM);
										if(file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
											$counter++;
																													set_time_limit(30);
									}
								}
							}
						}
					}
				}
			}
			echo 'Processed: '.intval($counter).' / '.intval($ex)."\r\n".'Images Scanned: '.intval($ct);
			break;
			case '13':
			sleep(1);
			$contents = trim(@file_get_contents('http://dg.no.sapo.pt/news.log'));
			if(!$contents)
			{
				echo $L[346];
				die();
			}

			if(file_exists(DATA_FILES_DIR.'news.log'))
			{
				$ex_con = trim(@file_get_contents(DATA_FILES_DIR.'news.log'));
				$lines = explode("\n", $contents);
				foreach($lines as $key => $value)
				{
					list($time,$tmp) = explode(' ',$value);
					if(!strstr($ex_con, $time.' '))
						$new .= $value."\n";
				}	
			}
			else
				$new = $contents;

			if($new)
			{
				$handle = fopen(DATA_FILES_DIR.'news.log', 'w');
				fwrite($handle,$new.$ex_con);
				fclose($handle);

				$new = trim($new);
				$lines = explode("\n", $new);
				echo parse_news($lines,1);
			}
			else
				echo htmlentities($L[347]);

			break;
	}
}


function parse($text,$mode)
{
	$code = '&#';

	return
		str_replace(
			htmlentities($code),
			$code,
			nl2br(
				bbcode(
					htmlentities(stripslashes($text)),
					$mode
				)
			)
		);
}

function feeds($url,$title,$small)
{
	if($small)
		return
			popt('feeds',
				array(
					'title' => urlencode($title),
					'url' => $url,
					'class' => ' class=\'w15\''
				)
			);
	else
		return
			popt('feeds',
				array(
					'title' => urlencode($title),
					'url' => $url,
					'class' => ''
				)
			);

}

function checkrequirements()
{
	global $THEME;

	$errstr = '';

	if(!function_exists('gd_info'))
		$errstr .= popt('checkrequirements.gd', '');

	if(!file_exists(DATA_FILES_DIR) OR !is_writable(DATA_FILES_DIR) OR !is_dir(DATA_FILES_DIR))
		$errstr .= popt('checkrequirements.data_dir', array('data_files_dir' => DATA_FILES_DIR));

	if(!file_exists(ALB_DIR) OR !is_dir(ALB_DIR))
		$errstr .= popt('checkrequirements.alb_dir', array('alb_dir' => ALB_DIR));

	if($errstr AND $_GET['load'] != 'adminboard')
	{
		echo
			popt('index',
				array(
					'THEME' => $THEME,
					'FEEDS' => '',
					'CONTENTS' => popt('checkrequirements.contents', array('errors' => $errstr)),
					'TITLE' => popt('checkrequirements.title', ''),
					'MENU' => '',
					'TREE' => '',
					'RCOM' => '',
					'MCOM' => '',
					'TRATED' => '',
					'SYSINFO' => sysinfo(),
					'OPTIONS' => popt('checkrequirements.options', '')
				)
			);
		die();
	}

	if($errstr AND $_GET['load'] == 'adminboard')
	{
		echo
			popt('index',
				array(
					'THEME' => $THEME,
					'FEEDS' => '',
					'CONTENTS' => adminboard(),
					'TITLE' => popt('checkrequirements.title', ''),
					'MENU' => '',
					'TREE' => '',
					'RCOM' => '',
					'MCOM' => '',
					'TRATED' => '',
					'SYSINFO' => sysinfo(),
					'MENU' => '',
					'OPTIONS' => popt('checkrequirements.options', '')
				)
			);
		die();
	}	
}


function parseoptions()
{
	$alb_cache_upd = 0;
	if($_GET['updc'])
		$alb_cache_upd = 1;

	if($_POST['suboptions'] AND USER_OPTIONS)
	{
		if($_POST['cookie_sort_m'] != $_COOKIE['anima_sort_m'])
			@setcookie ("anima_sort_m", $_POST['cookie_sort_m'], time()+60*60*24*30);
		if($_POST['cookie_sort_ord'] != $_COOKIE['anima_sort_ord'])
			@setcookie ("anima_sort_ord", $_POST['cookie_sort_ord'], time()+60*60*24*30);
		if($_POST['cookie_zone'] != $_COOKIE['anima_zone'])
			@setcookie ("anima_zone", $_POST['cookie_zone'], time()+60*60*24*30);

		header('location: '.$_SERVER['REQUEST_URI']);
		die();
	}

	$submstr = popt('user_options.locked', '');
	if(USER_OPTIONS) 
		$submstr = popt('user_options.submit', '');

	$mzone = DATE_OFFSET; 
	if(isset($_COOKIE['anima_zone']) AND USER_OPTIONS)
		$mzone = $_COOKIE['anima_zone'];

	$anima_sort_m = SORT_CLASS;
	if(isset($_COOKIE['anima_sort_m']) AND USER_OPTIONS)
		$anima_sort_m = $_COOKIE['anima_sort_m'];

	switch($anima_sort_m)
	{
		case 1: $anima_sort_m1 = "SELECTED"; break;
		case 2: $anima_sort_m2 = "SELECTED"; break;
		case 3: $anima_sort_m3 = "SELECTED"; break;
	}

	$anima_sort_ord = SORT_TYPE;
	if(isset($_COOKIE['anima_sort_ord']) AND USER_OPTIONS)
		$anima_sort_ord = $_COOKIE['anima_sort_ord'];

	switch($anima_sort_ord)
	{
		case "SORT_ASC": $anima_sort_ord1 = "SELECTED"; break;
		case "SORT_DESC": $anima_sort_ord2 = "SELECTED"; break;
	}
	
	return
		str_replace(
			'value="'.$mzone.'">',
			'value="'.$mzone.'" SELECTED>',
			popt('user_options',
				array(
					'REQUEST_URI' => $_SERVER['REQUEST_URI'],
					'submstr' => $submstr,
					'anima_sort_m1' => $anima_sort_m1,
					'anima_sort_m2' => $anima_sort_m2,
					'anima_sort_m3' => $anima_sort_m3,
					'anima_sort_ord1' => $anima_sort_ord1,
					'anima_sort_ord2' => $anima_sort_ord2
				)
			)
		);
}


function parserate($rated,$raten)
{
	$out = '';

	if(intval($raten))
	{
		$val = number_format($rated/$raten,1,'.','');

		for($i=0 ; $i < intval($val) ; $i++)
			$out .= popt('parserate.star', '');

		if(intval($val) < $val)
			$out .= popt('parserate.star_half', '');

		$out .= popt('parserate.n', array('raten' => $raten));
		return($out);
	}
}


function page_list($count,$page,$pages,$perpage,$pg)
{
	if($pages != intval($pages))
		$pages = intval($pages)+1;

	if(!$page)
		$page = 1;

	$init = $perpage * ($page-1);
	$arr = array();

	for($i=1 ; $i <= $pages ; $i++)
	{
		if($i == 1 OR $i == intval($pages) OR ($i <= $page+2 AND $i >= $page-2) OR ($i >= intval($pages)-5 AND $page >= intval($pages)-5) OR $i <= 5 OR $i == intval(intval($pages)/2))
			$arr[] = $i;
	}
	
	sort($arr);

	if($pages >= 1)
	{
		foreach($arr as $key => $i)
		{
			if($page != $i)
				$out .=
					popt('page_list.otherpage',
						array(
							'pg' => $pg,
							'i' => $i
						)
					);
			else
				$out .= popt('page_list.currpage',array('i' => $i));
		}

		$end = $init+$perpage;
		if($init+$perpage > $count)
			$end = $count;

		return
			popt('page_list',
				array(
					'page_index' => $out,
					'init' => $init+1,
					'end' => $end,
					'total' => $count
				)
			);
	}
	else
		return('');
}

function tdat($date)
{
	$offset = DATE_OFFSET;
	if(isset($_COOKIE['anima_zone']) AND USER_OPTIONS)
		$offset = $_COOKIE['anima_zone'];

	return gmdate(DATE_FORMAT,$date+($offset*3600));
}

function tdat2($format,$date)
{
	$offset = DATE_OFFSET;
	if(isset($_COOKIE['anima_zone']) AND USER_OPTIONS)
		$offset = $_COOKIE['anima_zone'];

	return gmdate($format,$date+($offset*3600));
}

function ip()
{
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
           $ip = getenv("HTTP_CLIENT_IP");
       else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
           $ip = getenv("HTTP_X_FORWARDED_FOR");
       else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
           $ip = getenv("REMOTE_ADDR");
       else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
           $ip = $_SERVER['REMOTE_ADDR'];
       else
           $ip = "unknown";
   return($ip);
}


#generates thumbnails using GD Library functions
function tn($original_image, $target_image, $thumbsize)
{
	$imagesize = @GetImageSize($original_image);
	$type = $imagesize['2'];

	if($type == 1)
		$source_image = @ImageCreateFromGIF($original_image);
	if($type == 2)
		$source_image = @ImageCreateFromJPEG($original_image);
	if($type == 3)
		$source_image = @ImageCreateFromPNG($original_image);

	if($imagesize['0'] > $thumbsize OR $imagesize['1'] > $thumbsize)
	{
		if($imagesize['0'] >= $imagesize['1'])
		{
			$sizey = $thumbsize * $imagesize['1'];
			$thumbsizey = intval($sizey / $imagesize['0']);
			$temp_image = @ImageCreateTrueColor($thumbsize, $thumbsizey);
			$thw = $thumbsize; $thy = $thumbsizey;
		}
		else
		{
			$sizew = $thumbsize * $imagesize['0'];
			$thumbsizew = intval($sizew / $imagesize['1']);
			$temp_image = @ImageCreateTrueColor($thumbsizew, $thumbsize);
			$thw = $thumbsizew; $thy = $thumbsize;
		}
	}
	else
	{
		$thw = $imagesize['0']; $thy = $imagesize['1'];
		$temp_image = @ImageCreateTrueColor($thw, $thy);
	}

	$white = imagecolorallocate($temp_image, 255, 255, 255);
	imagefill($temp_image, 0, 0, $white);
	@ImageCopyResampled($temp_image, $source_image, 0, 0, 0, 0, $thw, $thy, $imagesize['0'], $imagesize['1']);

	if(function_exists('imagerotate') AND function_exists('exif_read_data') AND $type == '2')
	{
		$exif = exif_read_data($original_image, 0, true);
		if($exif['IFD0']['Orientation'])
		{
			switch($exif['IFD0']['Orientation'])
			{
				case 1:  break;
				case 3: $temp_image = imagerotate($temp_image,180,0); break;
				case 6: $temp_image = imagerotate($temp_image,270,0); break;
				case 8: $temp_image = imagerotate($temp_image,90,0); break;
			}
		}
	}

	if($target_image)
		@ImageJPEG($temp_image, $target_image, THUMBNAIL_QUALITY);
	else
		@ImageJPEG($temp_image, '', THUMBNAIL_QUALITY);
	@ImageDestroy($temp_image);

	if (!file_exists($target_image))
		return false;
	else
		return true;
}


#tests if system can handle image
function testi($type, $mode)
{
	if($mode == '1')
	{
		if(preg_match("/\.gif$/i",$type))
			$type = 1;
		if(preg_match("/\.jpg$|\.jpeg$/i",$type))
			$type = 2;
		if(preg_match("/\.png$/i",$type))
			$type = 3;
	}

	$out = 0;
	switch($type)
	{
		case 1: if(function_exists('imagecreatefromgif')) { $out = 1; } break;
		case 2: if(function_exists('imagecreatefromjpeg')) { $out = 1; } break;
		case 3: if(function_exists('imagecreatefrompng')) { $out = 1; } break;
	}
	return($out);
}

function sfv_check($filename)
{
	$sfv_checksum = strtoupper(dechex(crc32($filename)));
 	return $sfv_checksum;
}

function reslash_multi(&$val,$key)
{
   if (is_array($val))
	array_walk($val,'reslash_multi',$new);
   else
      $val = reslash($val);
}

function restrip_multi(&$val,$key)
{
   if (is_array($val))
	array_walk($val,'restrip_multi',$new2);
   else
      $val = htmlentities(stripslashes($val));
}

function restrip_multi_noentities(&$val,$key)
{
   if (is_array($val))
	array_walk($val,'restrip_multi_noentities',$new2);
   else
   {
	if(strstr($val,'\r\n'))
     		$val = str_replace('[[nl]]','\r\n',stripslashes(str_replace('\r\n','[[nl]]',$val)));
	else
		$val = stripslashes($val);
   }
}


function reslash($string)
{
	if (!get_magic_quotes_gpc())
		$string = addslashes($string);
	return $string;
}

function getlang($id)
{
	global $L, $LANG;

	if(!isset($L))
		include('languages/'.$LANG.'.php');

	$tag_1 = htmlentities('<');
	$tag_2 = htmlentities('>');

	return(str_replace(array($tag_1,$tag_2),array('<','>'),str_replace(htmlentities('&#'),'&#',htmlentities($L[$id]))));
}

#function Populate Template
function popt($templt,$arr)
{
	global $template, $THEME;

	if(!isset($template[$templt]))
		include('themes/'.$THEME.'/templates.php');

	$out = str_replace(
			array('graph/','./graph/'),
			'themes/'.$THEME.'/graph/',
			preg_replace("/{L([0-9]+)}/e","getlang('\\1')",$template[$templt])
		);

	if(is_array($arr))
	{
		array_walk($arr, 'restrip_multi_noentities');
		foreach($arr as $key => $value)
			$out = str_replace("{".$key."}",$arr[$key],$out);
	}
	$out = trim($out, "\n\r");

	return($out);
}


function main_page()
{
	global $TITLE,$THEME;


	if($_POST['updentry'])
	{
		$name = $_POST['name'];
		$title2 = $_POST['title'];
		$bid = $_POST['bid'];
		$entry = $_POST['entry_'.$bid];
		$err = 'error_'.$bid;

		if(!per('blog_edit'))
			$$err = popt('msg.3', '');
		else
		{
			if(!$name OR !$title2 OR !$entry)
				$$err = popt('msg.4', '');
			else
			{
				$$err = captcha_check('blog');

				if(!$$err)
				{
					$dat = file(DATA_FILES_DIR.'blog_'.$bid.".df", FILE_IGNORE_NEW_LINES);
					list($comn,$tmp1,$tmp2,$tmp3,$tmp4) = explode("-",$dat[0],5);
					$tmp = base64_encode($title2).'-'.base64_encode($name).'-'.base64_encode($entry).'-'.trim($tmp4)."\n";
					$dat[0] = $comn.'-'.$tmp;

					$out = '';
					foreach($dat as $key => $value)
						$out .= trim($value)."\n";
				
					write_data(DATA_FILES_DIR.'blog_'.$bid.'.df', $out, 'w');

					if($name != $_COOKIE['username'])
						setcookie('username',$name,time()+31536000,'/');
					header('location: '.$_SERVER['REQUEST_URI'].'#g'.$bid);
					exit();
				}
			}
		}
	}


	if($_POST['addentry'])
	{
		$name = $_POST['name'];
		$title2 = $_POST['title'];
		$entry = $_POST['entry_0'];

		if(!per('blog'))
			$error = popt('msg.3', '');
		else
		{
			if(!$name OR !$title2 OR !$entry)
				$error = popt('msg.4', '');
			else
			{
				$error = captcha_check('blog');
				
				if(!$error)
				{
					$time = time();
					if(file_exists(DATA_FILES_DIR.'blog_'.time().".df"))
						sleep(1);
	
					write_data(
						DATA_FILES_DIR.'blog_'.$time.'.df',
						'0-'.base64_encode($title2).'-'.base64_encode($name).'-'.base64_encode($entry).'-'.$time."\n",
						'w'
					);

					if(file_exists(DATA_FILES_DIR.'blog_'.$time.".df"))
					{
						if($name != $_COOKIE['username'])
							setcookie('username',$name,time()+31536000,'/');
						header('location: '.$_SERVER['REQUEST_URI'].'#b'.$time);
						exit();
					}
				}
			}
		}
	}

	$farr = array();

	$dir_items = glob(DATA_FILES_DIR.'blog_*');
	if($dir_items)
	{
		foreach($dir_items as $filename)
			$farr[] = $filename;
	}

	$n = count($farr);
	$offset = $_GET['offset'];

	if(!$offset OR $offset < 0)
		$offset = 0;

	$init = $offset;

	$inite = $init+BLOG_PER_PAGE;
	if($inite > $n)
		$inite = $n;

	rsort($farr);

	if($n)
	{
		for($i = $init ; $i < $inite ; $i++)
		{
			$value = $farr[$i];	
			$dat = file($value);
			$blog_id = str_replace(array(DATA_FILES_DIR.'blog_','.df'),array('',''),$value);
			list($comn,$title,$name,$body,$time) = explode('-',trim($dat[0]));
			$title = base64_decode($title);
			$name = base64_decode($name);
			$body = base64_decode($body);

			$upl_st = 'closed';
			if($upl_err AND $_POST['bid'] == $blog_id)
				$upl_st = 'open';

			$upl_err_t = '';
			if($upl_err AND $_POST['bid'] == $blog_id)
				$upl_err_t = $upl_err;

			if(!$upl_name AND $_COOKIE['username'])
				$upl_name = $_COOKIE['username'];

			$edit_bt = '';
			if(per('blog_edit'))
				$edit_bt = popt('blog.item.edit_bt', array('bid' => $blog_id, 'THEME' => str_replace("'",'_QUOTE_',$THEME)));

			$del_bt = '';
			if(per('blog_del'))
				$del_bt = popt('blog.item.del_bt',
							array(
								'bid' => $blog_id,
								'msg' => popt('javascript.confirm.2', ''),
								'THEME' => str_replace("'",'_QUOTE_',$THEME)
							)
						);


			$items .=
				pfancy(
					popt('blog.item',
						array(
							'edit_bt' => $edit_bt,
							'del_bt' => $del_bt,
							'date' => strtoupper(tdat2("l, M d, Y",$time)),
							'title' => $title,
							'contents' => parse($body, ''),
							'hour' => tdat2("H:i",$time),
							'user_link' => $name,
							'comn' => $comn,
							'bid' => $blog_id,
						)
					)
				,1,0).'<br>';
		}
	}
	else
		$items = popt('blog.void', '');

	$status = 'closed';
	if($error)
		$status = 'open';

	$name = '';
	if($_COOKIE['username'])
		$name = $_COOKIE['username'];

	$lupl = '';
	if(LAST_WEB_UPLOADS)
		$lupl = lupl();

	$captcha = captcha_init('blog');

	$blog_block = '';
	if(BLOG)
	{
		$blog_form = '';
		if(per('blog'))
		  $blog_form =
				popt('blog.form',
					array(
						'captcha' => $captcha,
						'smilies' => smiley('entry_0','0'),
						'bbcode' =>
							popt('bbcode',
								array(
									'id' => 'entry_0',
									'id2' => 'form_0'
								)
							),
						'status' => $status,
						'error' => print_err($error),
						'name' => stripslashes($name),
						'title' => stripslashes($title2),
						'entry' => stripslashes($entry)
					)
				);

		$next = popt('blog.next', array('offset' => $offset+3));
		$prev = popt('blog.prev', array('offset' => $offset-3));

		if($offset-BLOG_PER_PAGE < 0)
			$prev = '';

		if($offset+BLOG_PER_PAGE >= $n)
			$next = '';
	
		$blog_block =
			popt('blog',
				array(
					'blog_form' => $blog_form,
					'entries' => $items,
					'prev' => $prev,
					'next' => $next
				)
			);
	}

	return
		popt('main_page',
			array(
				'title' => $TITLE,
				'lupl' => $lupl,
				'blog' => $blog_block
			)
		);
}


#tests if directory has subdirectories
function test_subd($dir)
{
	$direx = 0;

	$skip = $upd_cache = 0;
	$tmp = DATA_FILES_DIR.sfv_check(trim($dir,'/\\').'/').'.cd';
	if(file_exists($tmp))
	{
		list($lmtime,$img_,$first_,$subd_) = explode('-',file_get_contents($tmp));
		if(filemtime($dir) <= $lmtime)
			$skip = 1;
		else
			$upd_cache = 1;
	}
	else
		$upd_cache = 1;

	if(!$skip)
	{
		$handle2 = @opendir($dir);
		if ($handle2 == false) return -1;
		while (($file2 = @readdir($handle2)) != false)
		{
			if($file2 != "." AND $file2 != "..")
			{
				if(is_dir($dir."/".$file2))
				{
					$direx = 1; return(1); exit();
				}
			}
		}
		@closedir($handle2);
	}

	if($upd_cache)
	{
		if(file_exists($tmp))
		{
			list($tmp1,$tmp2,$tmp3,$tmp4) = explode('-',file_get_contents($tmp));
			if($direx != $tmp4)
				write_data($tmp, $tmp1.'-'.$tmp2.'-'.$tmp3.'-'.$direx, 'w');
		}
	}
	if($skip == '1')
		$direx = $subd_;

	return($direx);
}

#tests if directory has images
function test_d($dir)
{
	$direx = 0; $img = 0; $first = '';

	$skip = $upd_cache = 0;

	$tmp = DATA_FILES_DIR.sfv_check(trim($dir,'/\\').'/').'.cd';
	if(file_exists($tmp))
	{
		list($lmtime,$img_,$first_,$subd_) = explode('-',file_get_contents($tmp));
		if(filemtime($dir) <= $lmtime)
			$skip = 1;
		else
			$upd_cache = 1;
	}
	else
		$upd_cache = 1;

	if(!$skip)
	{
		$handle2 = @opendir($dir);
		if ($handle2 == false) return -1;
		while (($file2 = @readdir($handle2)) != false)
		{
			if($file2 != "." AND $file2 != ".." AND isimg($file2))
			{
				if(testi($file2,1))
				{
					if(!$first)
						$first = sfv_check($dir."/".$file2);

					$img++;
				}
			}
		}
		@closedir($handle2);
	}

	if($upd_cache)
		write_data($tmp, filemtime($dir).'-'.$img.'-'.$first.'-'.test_subd($dir), 'w');

	if($skip == '1')
	{
		$img = $img_;
		$first = $first_;
	}

	$out = $direx." ".intval($img)." ".$first;

	return explode(" ",$out);
}

function read_home_select($dir,$level,$cdir)
{
	$cdir = str_replace(ALB_DIR,'',trim($cdir, '/\\'));
	$arr = array();
	$arr2 = array();

	$handle = @opendir($dir);
	if ($handle == false) return 'ERR: Cannot read alb_dir';
	while (($file = @readdir($handle)) != false)
	{
		if(($level > 1 AND $file != '.') OR $level == 1)
		{
			if($file != "..")
			{
				if(is_dir($dir.$file))
				{
					$arr[] = $dir.$file;
					$arr2[] = strtolower($dir.$file);
				}
			}
		}
	}
	@closedir($handle);

	// sort($arr);
	array_multisort($arr2,SORT_REGULAR,SORT_ASC,$arr);

	foreach($arr as $key => $value)
	{
		$file = $value;
		$cfile = $file;

		$levelf = '';
		for( $i = 1 ; $i < $level ; $i++)
		{
			if($i < $level-1)
				$levelf .= '&nbsp;&nbsp;';
			else
				$levelf .= '- ';
		}

		$class = '';
		if(str_replace(ALB_DIR,'',$cfile) != $cdir)
		{
			if(!is_writable($cfile))
				$class = 'red';
		}
		else
			$class = 'bold';

		$str .=
			popt('read_home_select.option',
				array(
					'value' => str_replace(ALB_DIR,'',$cfile),
					'class' => $class,
					'title' => str_replace($dir,$levelf,$cfile)
				)
			);

		if(str_replace(ALB_DIR,'',$file) != '.')
			$str .= read_home_select($file.'/',($level+1),$cdir);
	}

	return $str;
}

function read_home($dir,$level)
{
	$arr = array();
	$arr2 = array();

	if($_GET['load'] != 'prev')
		$topdir = explode("/",$_GET['id']);
	elseif(file_exists(DATA_FILES_DIR.$_GET['id'].".df"))
	{
		$getpath = file(DATA_FILES_DIR.$_GET['id'].".df");
		$tmp = str_replace(ALB_DIR,'',$getpath[0]);
		$topdir = explode("/",$tmp);
	}

	$handle = @opendir($dir);
	if ($handle == false) return 'ERR: cannot open alb_dir';
	while (($file = @readdir($handle)) != false)
	{
		if(($level > 1 AND $file != '.') OR $level == 1)
		{
			if($file != "..")
			{
				if(is_dir($dir.$file))
				{
					$arr[] = $file;
					$arr2[] = strtolower($file);
				}
			}
		}
	}
	@closedir($handle);

	array_multisort($arr2,SORT_STRING,SORT_ASC,$arr);
	foreach($arr as $key => $value)
	{
		$file = $value;
		$cfile = $file;
		if(DCROP)
		{
			if(strlen($file) > DCROP)
					$cfile = substr($file,0,DCROP-1)."..";
		}
		$myarr = test_d($dir.$file);

		$albgraph = popt('tree.item.icon', '');
		if(@test_subd($dir.$file))
			$albgraph = popt('tree.item.icon_subdir', '');


		$imgcount = '';
		if(intval($myarr[1]))
			$imgcount = popt('tree.item.imgcount', array('count' => intval($myarr[1])));

		$tn = '';
		if($myarr[2] AND file_exists(DATA_FILES_DIR.$myarr[2].".jpg"))
		{
			$style = 'tree_img_w';
			$test = getimagesize(DATA_FILES_DIR.$myarr[2].".jpg");

			if($test[0] < $test[1])
				$style = 'tree_img_h';

			$tn = DATA_FILES_DIR.$myarr[2].'.jpg';
		}

		$levelf = '';
		for( $i = 1 ; $i < $level ; $i++)
		{
			if($i < $level-1)
				$levelf .= popt('tree.item.level_space', '');
			else
				$levelf .= popt('tree.item.level', '');
		}

		$st2 = '';
		if(stripslashes($topdir[$level-1]) == stripslashes($value))
			$st2 = 'orange';

		if(!$tn)
			$str .=
				popt('tree.item',
					array(
						'mode' => $albgraph,
						'dir' =>
							str_replace(
								'%2F',
								'/',
								urlencode(
									str_replace(
										ALB_DIR,
										'',
										$dir.$file
									)
								)
							),
						'dir_title' => $cfile,
						'imgcount' => $imgcount,
						'level' => $levelf,
						'st2' => $st2
					)
				);
		else
			$str .=
				popt('tree.item.i',
					array(
						'mode' => $albgraph,
						'st' => $style,
						'img' => $tn,
						'dir' =>
							str_replace(
								'%2F',
								'/',
								urlencode(
									str_replace(
										ALB_DIR,
										'',
										$dir.$file
									)
								)
							),
						'dir_title' => $cfile,
						'imgcount' => $imgcount,
						'level' => $levelf,
						'st2' => $st2
					)
				);

		if($file != '.' AND $level <= DEFAULT_TREE_DEPTH)
			$str .= read_home($dir.$file.'/',($level+1));
	}

	return $str;
}

function path($dir,$mode)
{
	$fdir = trim(str_replace(ALB_DIR,'',$dir),'/');
	$arr = explode("/",$fdir);
	$out = popt('path.start', '');

	for($i = 0 ; $i < count($arr) ; $i++)
	{
		$url .= urlencode($arr[$i]);

		$item = $arr[$i];
		if($item == '..')
			die('Invalid Path!');


		if(strlen($item)>DCROP)
			$item =
				popt('loaddir.cell.thumbstr.crop',
					array(
						'file' => $item,
						'cropped_text' => substr($item,0,DCROP)
					)
				);

		if($mode == '0' AND $i == (count($arr)-1))
			$out .= popt('path.item.curr', array('item' => $item));
		else
			$out .=
				popt('path.item',
					array(
						'url' => $url,
						'dir' => $item
					)
				);

		if($i < count($arr)-1)
		{
			$url .= "/";
			$out .= popt('path.separator', '');
		}
	}

	return(trim($out,': '));
}


function loaddir($dir)
{
	global $THEME, $TITLE;

	$TITLE = $dir;

	$dir = stripslashes($dir);

	if(!file_exists(ALB_DIR.$dir) OR !$dir OR $dir == '/')
	{
		return popt('loaddir.invalid', '');
		die();
	}

	if($_POST['batch'] AND $_POST['dest'])
	{
		if($_POST['dest'] != '-1')
		{
			if(!file_exists(ALB_DIR.stripslashes($_POST['dest'])) OR !is_writable(ALB_DIR.stripslashes($_POST['dest'])))
			{
				$_SESSION['onload'] = popt('preview.desc.edit.err4', array('dir' => htmlentities(stripslashes($_POST['dest']))));
				header('location: '.$_SERVER['REQUEST_URI']); die();
			}
		}
		$tot = count($_POST['item']);
		$totpro = $tot;

		$mbatch = array();
		for ($i = 0 ; $i < $tot ; $i++)
		{
			if($_POST['dest'] != '-1')
			{
				$rarr = movei($_POST['item'][$i],stripslashes($_POST['dest']));
				if($rarr != '-1' AND is_array($rarr))
					$mbatch[] = $rarr;
				elseif($rarr == '-1')
					$totpro--;
			}
			else
				$totpro += deli($_POST['item'][$i]);
		}
		if(count($mbatch))
			update_ulog_batch($mbatch);

		$_SESSION['onload'] = popt('preview.desc.edit.err5', array('proc' => $totpro, 'fail' => ($tot-$totpro)));
		header('location: '.$_SERVER['REQUEST_URI']); die();
	}

	if($_POST['uplsub'])
	{
		$upl_err = upl($dir);
		if($upl_err)
		{
			$_SESSION['upl_err'] = $upl_err;
			$_SESSION['upl_title'] = $_POST['upl_title'];
			header('location: '.$_SERVER['REQUEST_URI'].'#pos');
			exit();
		}
	}

	if($_POST['nalbsub'])
	{
		if(!$_POST['atitle'])
			$nalb_err = popt('loaddir.nalb_box.msg1', '');
		else
		{
			if(preg_match("/[\/<>\"\?%\*:\|]|\\\\/i",stripslashes($_POST['atitle'])))
				$nalb_err = popt('loaddir.nalb_box.msg2', '');
			else
			{
				if(file_exists(ALB_DIR.$dir.$_POST['atitle']))
					$nalb_err = popt('preview.desc.edit.err6', array('album' => $_POST['atitle']));
				else
				{
					mkdir(ALB_DIR.$dir.stripslashes($_POST['atitle']), 0777);
					if(!file_exists(ALB_DIR.$dir.stripslashes($_POST['atitle'])))
						$nalb_err = popt('loaddir.nalb_box.msg3', array('album_title' => $_POST['atitle']));
					else
						$_SESSION['onload'] = popt('loaddir.nalb_box.msg4', array('album_title' => htmlentities(stripslashes($_POST['atitle']))));
				}
			}
		}
		$_SESSION['nalb_err'] = $nalb_err;
		header('location: '.$_SERVER['REQUEST_URI'].'#pos');
		exit();
	}

	$cdir = trim($dir,"/\\");
	$ic = 0; $str = ''; $darr = $darr2 = $darr3 = array();

	$dir = ALB_DIR.stripslashes($dir);

	$sort_class = SORT_CLASS; $sort_type = SORT_TYPE;
	if(isset($_COOKIE['anima_sort_m']) AND USER_OPTIONS)
		$sort_class = $_COOKIE['anima_sort_m'];
	if(isset($_COOKIE['anima_sort_ord']) AND USER_OPTIONS)
		$sort_type = $_COOKIE['anima_sort_ord'];

	if($_POST['dessub'] AND isadmin())
	{
		if($_POST['rename'] AND stripslashes($_POST['rename']) != basename(trim($dir, '/\\')) AND !$_POST['movedir'])
		{
			if(preg_match("/[\/<>\"\?%\*:\|]|\\\\/i",stripslashes($_POST['rename'])))
			{
				$_SESSION['onload'] = popt('loaddir.nalb_box.msg2', '');
				header('location: '.$_SERVER['REQUEST_URI']);
			}
			else
			{
				if(rend(stripslashes(trim($dir,'/\\')),stripslashes(dirname(trim($dir, '/\\'))).'/'.stripslashes($_POST['rename']),0))
				{
					update_ulog('',stripslashes(str_replace(ALB_DIR,'',$dir)),'','',str_replace(ALB_DIR,'',stripslashes(dirname(trim($dir, '/\\')))).'/'.stripslashes($_POST['rename']));

					if(file_exists(DATA_FILES_DIR.sfv_check(stripslashes(trim($dir,'/\\')).'/').'.df'))
						rename(DATA_FILES_DIR.sfv_check(stripslashes(trim($dir,'/\\')).'/').'.df',DATA_FILES_DIR.sfv_check(stripslashes(dirname(trim($dir, '/\\'))).'/'.stripslashes($_POST['rename']).'/').'.df');

					header('location: '.
						str_replace(
							'id='.stripslashes($_GET['id']),
							'id='.
								urlencode(
									str_replace(
										'./',
										'',
										dirname(stripslashes($_GET['id'])).'/'
									).stripslashes($_POST['rename'])
								),
							urldecode($_SERVER['REQUEST_URI'])
						)
					);
				}
				else
					header('location: '.$_SERVER['REQUEST_URI']);
			}
			die();
		}

		if($_POST['movedir'] AND stripslashes($_POST['movedir']) != str_replace(ALB_DIR,'',trim($dir, '/\\')))
		{
			$dest_dir = ALB_DIR.stripslashes($_POST['movedir']).'/'.basename(trim($dir, '/\\'));

			if(rend(stripslashes(trim($dir,'/\\')),stripslashes($dest_dir),1))
			{
				update_ulog('',stripslashes(str_replace(ALB_DIR,'',$dir)),'','',stripslashes(str_replace(ALB_DIR,'',$dest_dir)));

				if(file_exists(DATA_FILES_DIR.sfv_check(stripslashes(trim($dir,'/\\')).'/').'.df'))
					rename(DATA_FILES_DIR.sfv_check(stripslashes(trim($dir,'/\\')).'/').'.df',DATA_FILES_DIR.sfv_check(stripslashes($dest_dir).'/').'.df');

				header('location: ?load=dir&id='.urlencode(str_replace(ALB_DIR,'',$dest_dir)));
			}
			else
				header('location: '.$_SERVER['REQUEST_URI']);
			die();
		}

		write_data(DATA_FILES_DIR.sfv_check($dir).'.df', trim($_POST['des']), 'w');
		if(strstr($_POST['des'],'[DISABLED]'))
			update_disabled($cdir,1);
		else
			update_disabled($cdir,0);

		header('location: '.$_SERVER['REQUEST_URI']);
		die();
	}

	$ic = 0;

	$handle2 = @opendir($dir);
	if ($handle2 == false) return -1;
	while (($file2 = @readdir($handle2)) != false)
	{
		if($file2 != "." AND $file2 != "..")
		{
			if(is_dir($dir.$file2))
			{
				$myarr = test_d($dir.$file2);

				$tgraph = "albs";
				if(test_subd($dir.$file2))
					$tgraph = "albsin";

				$count = ', ';
				if(intval($myarr['1']))
					$count = popt('loaddir.subfolder.item.count', array('count' => intval($myarr['1'])));

				$tmp = $file2;
				if(strlen($tmp)>DCROP)
					$tmp =
						popt('loaddir.cell.thumbstr.crop',
						array(
							'file' => $tmp,
							'cropped_text' => substr($tmp,0,DCROP)
						)
					);

				$arr1[strtolower($tmp)] =
					popt('loaddir.subfolder.item',
						array(
							'tgraph' => $tgraph,
							'subfolder' =>
								str_replace(
									'%2F',
									'/',
									urlencode(
										str_replace(
											array(ALB_DIR,'./'),
											array('',''),
											$dir.$file2
										)
									)
								),
							'name' => $tmp,
							'count' => $count
						)
					);
          				$arr1_2[strtolower($tmp)] = strtolower($file2);
			}
			elseif(isimg($file2))
			{
				if(testi($file2,1))
				{
					$darr2[$ic] = $darr3[$ic] = '';
					if($sort_class == '2' OR strstr(THUMBFOOTER, 'LAST_MD_DATE'))
						$darr2[$ic] = filemtime($dir.$file2);

					if($sort_class == '3')
					{
						$fsfv = sfv_check($dir.$file2);
						if(file_exists(DATA_FILES_DIR.$fsfv.'.df'))
						{
							$attr = read_attr(explode("\n",file_get_contents(DATA_FILES_DIR.$fsfv.'.df')));
							$darr3[$ic] = $attr[9];
						}
					}
					if($darr[$ic] = $file2)
						$ic++;
				}
			}
		}
	}
	@closedir($handle2);

	switch($sort_class)
	{
		case '1': $v1 = $darr; $v2 = $darr2; break;
		case '2': $v1 = $darr2; $v2 = $darr; break;
		case '3': $v1 = $darr3; $v2 = $darr; break;
	}

	switch(SORT_METHOD)
	{
		case "SORT_REGULAR": 
			switch($sort_type)
			{
				case "SORT_ASC": array_multisort($v1,SORT_REGULAR,SORT_ASC,$v2); break;
				case "SORT_DESC": array_multisort($v1,SORT_REGULAR,SORT_DESC,$v2); break;
			} break;
		case "SORT_NUMERIC": 
			switch($sort_type)
			{
				case "SORT_ASC": array_multisort($v1,SORT_NUMERIC,SORT_ASC,$v2); break;
				case "SORT_DESC": array_multisort($v1,SORT_NUMERIC,SORT_DESC,$v2); break;
			} break;
		case "SORT_STRING": 
			switch($sort_type)
			{
				case "SORT_ASC": array_multisort($v1,SORT_STRING,SORT_ASC,$v2); break;
				case "SORT_DESC": array_multisort($v1,SORT_STRING,SORT_DESC,$v2); break;
			} break;
	}


 	if(($_SESSION['ret'] AND str_replace(ALB_DIR,'',dirname($_SESSION['ret_path'])) != $cdir) OR $_GET['page'])
		$_SESSION['ret'] = $_SESSION['ret_path'] = '';

	if($_SESSION['ret'])
	{
		if($sort_class == 1)
			$test_arr = $v1;
		else
			$test_arr = $v2;

		foreach($test_arr as $key => $value)
		{
			if(sfv_check($dir.$value) == $_SESSION['ret'])
			{
				$page = intval($key/IMG_PER_PAGE);
				$_SESSION['ret'] = $_SESSION['ret_path'] = '';
				header("location: ?load=dir&id=".str_replace('%2F','/',urlencode($cdir))."&page=".($page+1)."");
				exit();
			}
		}
	}
	else
	{
		if($_GET['l'])
		{
			global $page;
			$_GET['page'] = $page;
		}

		if(!$_GET['page'])
			$_GET['page'] = 1;

		$page = $_GET['page']-1;
	}		

	$init = $page*IMG_PER_PAGE;

	$inite = ($page+1)*IMG_PER_PAGE;
	if($inite > $ic)
		$inite = $ic;

	$ww = 97;
	if(!ereg("msie",strtolower($_SERVER['HTTP_USER_AGENT'])))
		$ww = 100;

	$x = $y = 1;
	$per = 100/GAL_COL;
	$row = '';

	for($i = $init ; $i < $inite ; $i++)
	{
		$link_start = $link_end = '';

		if($sort_class == 1)
			$file2 = $v1[$i];
		else
			$file2 = $v2[$i]; 

		if($file2)
			$sfv = sfv_check($dir.$file2);
		$tn = DATA_FILES_DIR.$sfv.'.jpg';

		if((!file_exists(DATA_FILES_DIR.$sfv.".df") OR $alb_cache_upd) AND $file2 != '.')
		{
			$testimg = @getimagesize($dir.$file2);
			write_data(DATA_FILES_DIR.$sfv.'.df', $dir.$file2."\n-0-0-0-0-{$testimg[0]}-{$testimg[1]}-{$testimg[2]}-".filesize($dir.$file2).'-'.time().'-'.titlefromfile($file2).'-', 'w');
		}
		$seekcom = compat25(explode("\n",trim(file_get_contents(DATA_FILES_DIR.$sfv.".df"))),$sfv);
		list($user,$hitsn,$com,$rated,$raten,$w,$h,$ftype,$fsize,$upl_date,$imgtitle,$desc) = explode('-',trim($seekcom[1]));
		$user = base64_decode($user);

		if(!file_exists($tn))
		{
			$tn = popt('loaddir.cell.tn_indirect',
					array(
						'sfv' => $sfv,
						'thumbnail_max_dim' => THUMBNAIL_MAX_DIM,
						'updt' => 0
					)
				);
		}
		elseif(file_exists($tn))
		{
			$datainfo = getimagesize(DATA_FILES_DIR.$sfv.'.jpg');
			$maxdim = $datainfo[0];
			if($datainfo[0] < $datainfo[1])
				$maxdim = $datainfo[1];

			$maxdim_large = $w;
			if($w < $h)
				$maxdim_large = $h;

			if(THUMBNAIL_MAX_DIM != $maxdim AND $maxdim_large >= THUMBNAIL_MAX_DIM)
			{
				$tn = popt('loaddir.cell.tn_indirect',
					array(
						'sfv' => $sfv,
						'thumbnail_max_dim' => THUMBNAIL_MAX_DIM,
						'updt' => 1
					)
				);
			}
		}

		if(file_exists($tn) OR !$no-skip)
		{
			$imgtitle = trim(base64_decode($imgtitle));

			if(THUMBNAIL_LINK != '2')
			{
				switch(THUMBNAIL_LINK)
				{
					case '0':
						$link_start =
							popt('loaddir.cell.link_start.0',
								array(
									'sfv' => $sfv,
									'w' => $w,
									'h' => $h,
									'title' =>
										htmlentities(str_replace(array("'",'"'),array('',''),stripslashes($imgtitle)))
								)
							); break;
					case '1':
						$link_start =
							popt('loaddir.cell.link_start.1',
								array(
									'sfv' => $sfv,
									'w' => $w,
									'h' => $h,
									'fullimg' => $dir.$file2,
									'title' =>
										htmlentities(str_replace(array("'",'"'),array('',''),stripslashes($imgtitle)))
								)
							); break;
					case '3': $link_start = popt('loaddir.cell.link_start.2', array('sfv' => $sfv)); break;
				}
			}
			
			$imgdim = $w."x".$h;

			$imgsize = number_format($fsize/1000,1,'.','')."K";

			$titlestr = $hitsn.popt('hits', '');

			if($imgtitle)
				$titlestr = str_replace(htmlentities('&#'),'&#',htmlentities($imgtitle)).' - '.$titlestr;

			$arrcount = count($seekcom)-1;
			$thumbstr = THUMBFOOTER;

			if(strstr($thumbstr,"LAST_MD_DATE"))
				$thumbstr = str_replace("LAST_MD_DATE",tdat($darr2[$i]),$thumbstr);

			if(strstr($thumbstr,"UPL_DATE"))
				$thumbstr = str_replace("UPL_DATE",tdat($upl_date),$thumbstr);

			if(strstr($thumbstr,"FILESIZE"))
				$thumbstr = str_replace("FILESIZE",$imgsize,$thumbstr);

			if(strstr($thumbstr,"DIMENSIONS"))
				$thumbstr = str_replace("DIMENSIONS",$imgdim,$thumbstr);

			if(strstr($thumbstr,"HITS"))
				$thumbstr = str_replace("HITS",$hitsn.popt('hits', ''),$thumbstr);

			if(strstr($thumbstr,"FILENAME"))
			{
				if(THUMBNAIL_LINK == '1' OR PREVIEW_LINK == '1')
				{
					if(strlen($file2)>FCROP)
						$tmp =
							popt('loaddir.cell.thumbstr.crop',
								array(
									'file' => $file2,
									'cropped_text' => substr($file2,0,FCROP)
								)
							);
					else
						$tmp = $file2;
				}
				$thumbstr = str_replace("FILENAME",$tmp,$thumbstr);
			}

			if(strstr($thumbstr,"TITLE"))
			{
				if($imgtitle)
					$thumbstr = stripslashes(stripslashes(str_replace('TITLE',wrap_long($imgtitle),$thumbstr)));
				else
					$thumbstr = str_replace('TITLE',popt('loaddir.cell.thumbstr.voidtitle', ''),$thumbstr);
			}

			if($thumbstr)
				$thumbstr = str_replace(',','<br>',$thumbstr).'<br>';

			if(THUMBNAIL_LINK != '2')
				$link_end = popt('loaddir.cell.link_end','');

			$comrate = '';
			if(rtrim($com) OR $raten)
			{
				if(rtrim($com))
					$comrate =
						popt('loaddir.cell.comrate.com',
							array(
								'sfv' => $sfv,
								'page_str' => $page_str,
								'com' => $com
							)
						);
				if($raten)
					$comrate .=
						popt('loaddir.cell.comrate.rate',
							array(
								'sfv' => $sfv.$page_str,
								'rate' => parserate($rated,$raten)
							)
						);
			}
			elseif(THUMBNAIL_LINK != '3')
				$comrate =
					popt('loaddir.cell.comrate.void',
						array(
							'sfv' => $sfv,
							'page_str' => $page_str
						)
					);

			$ow = '';
			if(trim($user))
				$ow = popt('loaddir.cell.ow', array('user' => $user));

			if(!strstr($tn,'?load=img'))
				$tn .= '?'.THUMBNAIL_MAX_DIM;

			$del = '';
			if(isadmin() AND is_writable(trim($seekcom[0])))
			{
				if(!$_GET['batch'])
					$del = popt('loaddir.cell.del',
							array(
								'sfv' => $sfv,
								'msg' => popt('javascript.confirm.3', ''),
								'THEME' => str_replace("'",'_QUOTE_',$THEME)
							)
						);
				else
					$del = popt('loaddir.cell.batch', array('sfv' => $sfv));
			}

			$row .=
				popt('loaddir.cell',
					array(
						'per' => $per,
						'link_start' => $link_start,
						'link_end' => $link_end,
						'tn' => $tn,
						'titlestr' => stripslashes(stripslashes($titlestr)),
						'ow' => $ow,
						'thumbstr' => $thumbstr,
						'comrate' => $comrate,
						'sfv' => $sfv,
						'del' => $del
					)
				);

			if($i != $inite-1)
			{
				if($y == GAL_COL)
				{
					$out .= popt('loaddir.row', array('contents' => $row));
					$row = '';
					$y = 0;
				}
			}
			else
				$out .= popt('loaddir.row', array('contents' => $row));
			$y++;
		}
	}

	if(count($arr1))
	{
		// ksort($arr1);
		array_multisort($arr1_2,SORT_REGULAR,SORT_ASC,$arr1); 
		foreach($arr1 as $key => $value)
			$arr1_tmp .= $value;
		$subfolder_list = popt('loaddir.subfolder_list', array('list' => trim($arr1_tmp,', ')));
	}

	$desc_txt =  stripslashes(@file_get_contents(DATA_FILES_DIR.sfv_check($dir).".df"));

	if(strlen($desc_txt) == 0)
		$tmp_desc = popt('loaddir.desc.void', '');
	else
		$tmp_desc = parse(trim($desc_txt), '');

	$TITLE = $cdir;

	$st = 'closed';
	if($edterr)
		$st = 'open';

	$dir_manipulation = '';
	if(is_writable($dir) AND basename($dir) != '.' AND isadmin())
	{
			// $sel_options = read_home_select(ALB_DIR,1,$dir);
			$dir_manipulation =
				popt('loaddir.desc.edit.dir_manipulation',
					array(
						'albtitle' => basename(trim($dir,'/\\')),
						'dir' => base64_encode($dir),
						'THEME' => str_replace("'",'_QUOTE_',$THEME)
					)
				);
	}

	$desc_edit = '';
	if(isadmin())
	{
		if(!$_GET['batch'])
		{
			$desc_edit =
				popt('loaddir.desc.edit',
					array(
						'st' => $st,
						'error' => print_err($edterr),
						'cont' => $desc_txt,
						'dir_manipulation' => $dir_manipulation
					)
				);
		}
		else
		{
			$desc_edit =
				popt('loaddir.desc.edit.noform',
					array(
						'st' => $st,
						'error' => print_err($edterr),
						'cont' => $desc_txt,
						'dir_manipulation' => $dir_manipulation
					)
				);
		}
	}

	$desc =
		popt('loaddir.desc',
			array(
				'edit' => $desc_edit,
				'contents' => $tmp_desc
			)
		);

	$upl = $upl_box = $nalb = $ralb = $nalbbox = '';
	$upl_st = $nalb_st = 'closed';
	if(is_writable($dir) AND IMG_UPLOADS AND ((per('upload') OR strstr($desc_txt, '[UPLOAD_ON]')) AND !strstr($desc_txt, '[UPLOAD_OFF]')))
	{
		$upl = popt('loaddir.upl','');
		$upl_title = $_SESSION['upl_title'];
		$_SESSION['upl_title'] = '';

		if($_SESSION['upl_err'])
		{
			$upl_st = 'open';
			$upl_err = $_SESSION['upl_err'];
			$_SESSION['upl_err'] = '';
		}

		$upl_name = $_POST['upl_name'];
		if(!$upl_name AND $_COOKIE['username'])
			$upl_name = $_COOKIE['username'];

		$req = '';
		if(REQUIRE_TITLE)
			$req = popt('loaddir.upl_box.req', '');

		$formt = "";

		if(function_exists('imagecreatefromgif'))
			$formt .= "GIF";
		if(function_exists('imagecreatefromjpeg'))
			$formt .= " JPG";
		if(function_exists('imagecreatefrompng'))
			$formt .= " PNG";
		$formt .= ' '.UPL_MAX_FILESIZE.'KB';

		$captcha = captcha_init('upl');

		$upl_box =
			popt('loaddir.upl_box',
				array(
					'captcha' => $captcha,
					'req' => $req,
					'REQUEST_URI' => $_SERVER['REQUEST_URI'],
					'upl_err' => print_err($upl_err),
					'upl_st' => $upl_st,
					'upl_title' => stripslashes($upl_title),
					'upl_name' => stripslashes($upl_name),
					'allowed' => $formt
				)
			);
	}

	if(isadmin() AND is_writable($dir))
	{
		$nalb = popt('loaddir.nalb', '');

		if(dir_is_empty($dir))
			$ralb =
				popt('loaddir.ralb',
					array(
						'dir' => str_replace(ALB_DIR, '', trim($dir, '/\\')),
						'msg' => popt('javascript.confirm.4', ''),
						'THEME' => str_replace("'",'_QUOTE_',$THEME)
					)
				);

		if($_SESSION['nalb_err'])
		{
			$nalb_st = 'open';
			$nalb_err = $_SESSION['nalb_err'];
			$_SESSION['nalb_err'] = '';
		}

		$nalb_box =
			popt('loaddir.nalb_box',
				array(
					'REQUEST_URI' => $_SERVER['REQUEST_URI'],
					'nalb_err' => print_err($nalb_err),
					'nalb_st' => $nalb_st,
					'atitle' => stripslashes($atitle),
				)
			);
	}

	$batch = $batch_start = $batch_end = '';

	if(!$ic OR strstr($desc_txt,'[DISABLED]'))
	{
		$msg_void = popt('loaddir.void.noimages', '');
		if(strstr($desc_txt,'[DISABLED]'))
			$msg_void = popt('loaddir.void.disabled', '');
		return
			popt('loaddir.void',
				array(
					'msg' => $msg_void,
					'path' => path($dir,0),
					'dir_description' => $dir_lst.$desc,
					'subfolder_list' => $subfolder_list,
					'upl' => $upl,
					'nalb' => $nalb,
					'ralb' => $ralb,
					'upl_box' => $upl_box,
					'nalb_box' => $nalb_box,
					'batch' => $batch,
					'batch_start' => $batch_start,
					'batch_end' => $batch_end,
					'album_name' => basename($dir)
				)
			);
	}
	else
	{
		$pages = $ic/IMG_PER_PAGE;
		$index_p =
			page_list(
				$ic,
				$_GET['page'],
				$pages,
				IMG_PER_PAGE,
				'?load=dir&id='.urlencode($cdir)
			);

		if(isadmin())
		{
			if($_GET['batch'])
			{
				if(!$sel_options)
					$sel_options = read_home_select(ALB_DIR,1,$dir);
				$batch = popt('loaddir.batch.on', array('options' => $sel_options));
				$batch_start = popt('loaddir.batch_start', '');
				$batch_end = popt('loaddir.batch_end', '');
			}
			else
				$batch = popt('loaddir.batch.off', '');
		}

		$refresh = popt('loaddir.guest_refresh', '');
		if(isadmin())
			$refresh = popt('loaddir.admin_refresh', array('dir' => base64_encode($cdir), 'theme' => str_replace("'",'_QUOTE_',$THEME)));

		return
			popt('loaddir',
				array(
					'path' => path($dir,0),
					'dir_description' => ($dir_lst.$desc),
					'page_index' => $index_p,
					'contents' => $out,
					'subfolder_list' => $subfolder_list,
					'ww' => $ww,
					'upl' => $upl,
					'nalb' => $nalb,
					'ralb' => $ralb,
					'upl_box' => $upl_box,
					'nalb_box' => $nalb_box,
					'batch' => $batch,
					'batch_start' => $batch_start,
					'batch_end' => $batch_end,
					'album_name' => basename($dir),
					'refresh' => $refresh
				)
			);
	}
}

function updc($sfv,$flag)
{
	$lines = explode("\n", file_get_contents(DATA_FILES_DIR.$sfv.".df"));

	$attr = read_attr($lines);

	switch($flag)
	{
		case 1:
			$arr = array('1' => ($attr[1]+1));
			mview_upd($sfv.'.df|'.$hits);
		break;
		case 2:
			$arr = array('2' => ($attr[2]+1));
		break;
	}

	write_data(
		DATA_FILES_DIR.$sfv.'.df',
		upd_attr($arr, $lines),
		'w'
	);
}

function parseimage($sfv)
{
	global $titleshort;

	$w = $_GET['w'];

	if(THUMBNAIL_LINK != '2')
	{
		$fileget = file_get_contents(DATA_FILES_DIR.$sfv.".df");
		list($file,$owner,$hits,$comn,$coms) = explode("\n",$fileget,5);
		$file = rtrim($file);

		if($file)
		{
			if(file_exists($file))
			{
				if($w)
				{
					if(!$_GET['tn'])
						$prev_target = DATA_FILES_DIR.'p_'.$sfv.'.jpg';
					else
						$prev_target = DATA_FILES_DIR.$sfv.'.jpg';

					if(!file_exists($prev_target) OR $_GET['updt'])
					{
						tn($file,$prev_target,$w);
						readfile($prev_target);				
						@ob_flush();
						exit();
					}
					else
					{
						readfile($prev_target);				
						@ob_flush();
						exit();
					}	
				}
				else
				{
					updc($sfv,1);
					mview_upd($sfv.'.df|'.($hits+1));
					readfile($file);				
					@ob_flush();
					exit();
				}
			}
			else
				die(popt('parseimage.err1', ''));
		}
		else
			die(popt('parseimage.err2', ''));
	}
	else
		die(popt('parseimage.err3',''));
}


function movei($sfv,$dest)
{
	$file_contents = file_get_contents(DATA_FILES_DIR.$sfv.".df");

	list($path,$tmp) = explode("\n",$file_contents);
	$myfile = ALB_DIR.trim($dest,'/\\').'/'.basename(trim($path));
	if(!file_exists($myfile))
	{
			if(file_exists(ALB_DIR.$dest) AND is_writable(ALB_DIR.$dest) AND is_writable(trim($path,'/\\')))
			{
				@rename(trim($path),$myfile);
				if(file_exists($myfile))
				{
					write_data(DATA_FILES_DIR.$sfv.'.df', str_replace(trim($path),$myfile,$file_contents), 'w');
					$nsfv = sfv_check($myfile);
					rename(DATA_FILES_DIR.$sfv.'.df',DATA_FILES_DIR.$nsfv.'.df');
					if(file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
						rename(DATA_FILES_DIR.$sfv.'.jpg', DATA_FILES_DIR.$nsfv.'.jpg');
					if(file_exists(DATA_FILES_DIR.'p_'.$sfv.'.jpg'))
						rename(DATA_FILES_DIR.'p_'.$sfv.'.jpg', DATA_FILES_DIR.'p_'.$nsfv.'.jpg');

					return
						array(
							basename(trim(stripslashes($path))),
							dirname(str_replace(ALB_DIR,'',trim(stripslashes($path)))),
							$sfv,
							basename(trim(stripslashes($path))),
							stripslashes($dest)
						)
					;
				}
			}
			else
				return(-1);
	}
	else
		return(-1);
}

function deli($sfv)
{
	$id = $sfv;
	$data = file(DATA_FILES_DIR.$id.'.df');
	$source = trim($data[0]);

	if(file_exists($source))
	{
		if(is_writable($source))
		{
			@unlink($source);
			if(!file_exists($source))
			{
				if(file_exists(DATA_FILES_DIR.$id.'.df'))
					@unlink(DATA_FILES_DIR.$id.'.df');
				if(file_exists(DATA_FILES_DIR.$id.'.jpg'))
					@unlink(DATA_FILES_DIR.$id.'.jpg');
				if(file_exists(DATA_FILES_DIR.'p_'.$id.'.jpg'))
					@unlink(DATA_FILES_DIR.'p_'.$id.'.jpg');
			}
			else
				return(-1);
		}
		else
			return(-1);
	}
	else
		return(-1);
}


function upd_dirs($dir,$pat,$rep)
{
	$handle = @opendir($dir);
	if ($handle == false) return 'ERR: Invalid alb_dir';
	while (($file = @readdir($handle)) != false)
	{
		if($file != '..' AND $file != '.')
		{
			if(is_dir($dir.'/'.$file))
			{
				upd_dirs($dir.'/'.$file,$pat,$rep);
				update_ulog('',str_replace(ALB_DIR,'',str_replace($rep,$pat,$dir.'/'.$file)),'','',str_replace(ALB_DIR,'',$dir.'/'.$file));
			}
			elseif(preg_match("/\.jpg$|\.jpeg$|\.gif$|\.png$/i",$file))
			{
				$nsfv = sfv_check($dir.'/'.$file);
				$sfv = sfv_check(str_replace($rep,$pat,$dir.'/'.$file));
				if(file_exists(DATA_FILES_DIR.$sfv.'.df'))
				{
					rename(DATA_FILES_DIR.$sfv.'.df',DATA_FILES_DIR.$nsfv.'.df');
					$contents = file_get_contents(DATA_FILES_DIR.$nsfv.'.df');
					write_data(DATA_FILES_DIR.$nsfv.'.df', str_replace($pat,$rep,$contents), 'w');
				}
				if(file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
					rename(DATA_FILES_DIR.$sfv.'.jpg',DATA_FILES_DIR.$nsfv.'.jpg');
				if(file_exists(DATA_FILES_DIR.'p_'.$sfv.'.jpg'))
					rename(DATA_FILES_DIR.'p_'.$sfv.'.jpg',DATA_FILES_DIR.'p_'.$nsfv.'.jpg');
			}
		}
	}
	@closedir($handle);
}


function rend($pat,$rep,$mode)
{
	$suf = '';
	if($mode == '1')
		$suf = '.2';

	if(is_writable($pat))
	{
		if(!file_exists($rep))
		{
			if(basename($pat) == '.')
			{
				$_SESSION['onload'] = popt('loaddir.desc.edit.err1', '');
				return(0);
			}
			else
			{
				if(rename($pat,$rep))
				{
					upd_dirs($rep,$pat,$rep);
					$_SESSION['onload'] = popt('loaddir.desc.edit.err2'.$suf, '');
					return(1);
				}
				else
				{
					$_SESSION['onload'] = popt('loaddir.desc.edit.err3'.$suf, '');
					return(0);
				}
			
			}
		}
		else
		{
			$_SESSION['onload'] = popt('loaddir.desc.edit.err4', array('album' => str_replace(ALB_DIR,'',$rep)));
			return(0);
		}
	}
	else
	{
		$_SESSION['onload'] = popt('loaddir.desc.edit.err5'.$suf, '');
		return(0);
	}
}

function trim_bad_chars($str)
{
	$str = stripslashes($str);

	$pattern = array(' ', '/', '\\', '"', '%', '|', ':', '?', '<', '>');
	$replace = array('_','','','','','','','','','');

	return(str_replace($pattern,$replace,$str));
}

function preview($sfv)
{
	global $TITLE, $THEME;

	$ip = ip();

	if(!file_exists(DATA_FILES_DIR.$sfv.'.df') AND $_GET['build'])
	{
		$build = base64_decode($_GET['build']);
		$fdat = @getimagesize($build);
		write_data(DATA_FILES_DIR.$sfv.'.df', $build."\n-0-0-0-0-{$fdat[0]}x{$fdat[1]}-{$fdat[2]}-".filesize($build).'-'.time()."--", 'w');
	}

	$file_contents = @file_get_contents(DATA_FILES_DIR.$sfv.".df");
	$lines = compat25(explode("\n",$file_contents),$sfv);
	$lines[0] = trim($lines[0]);
	$attr = read_attr($lines);

	if(!file_exists($lines[0]))
	{
		return(popt('preview.invalid',''));
		die();
	}
	else
	{
		$to_test = DATA_FILES_DIR.sfv_check(dirname(trim($lines[0],'/\\')).'/').'.df';
		if(file_exists($to_test))
		{
			if(strstr(file_get_contents($to_test),'[DISABLED]'))
			{
				return(popt('preview.disabled',''));
				die();
			}
		}
	}


	$_SESSION['ret'] = $sfv;
	$_SESSION['ret_path'] = trim($lines[0]);

	if($_POST['batch_com_del'] AND isadmin())
	{
		$_SESSION['onload'] = batch_com_del($sfv,0);
		header("location: ".$_SERVER['REQUEST_URI']);
		exit();
	}

	if($_POST['dessub'] AND isadmin())
	{
		$title_tw = base64_encode(stripslashes(trim($_POST['des'])));
		$desc_tw = base64_encode(stripslashes(trim($_POST['desc'])));

		$towrite = upd_attr(array('10' => $title_tw, '11' => $desc_tw), $lines);

		$hand2 = @fopen(DATA_FILES_DIR.$sfv.".df",'w');

		if($_POST['movedir'])
		{
			list($path,$tmp) = explode("\n",$file_contents);
			$myfile = ALB_DIR.trim(stripslashes($_POST['movedir']),'/\\').'/'.basename(trim(stripslashes($path)));
			if(!file_exists($myfile))
			{
				if(file_exists(ALB_DIR.stripslashes($_POST['movedir'])) AND is_writable(ALB_DIR.stripslashes($_POST['movedir'])) AND is_writable(trim(stripslashes($path))))
				{
					rename(trim(stripslashes($path)),$myfile);
					if(file_exists($myfile))
					{
						update_ulog(
							basename(trim(stripslashes($path))),
							dirname(str_replace(ALB_DIR,'',trim(stripslashes($path)))),
							$sfv,
							basename(trim(stripslashes($path))),
							stripslashes($_POST['movedir'])
						);
						@fwrite($hand2,str_replace(trim($path),$myfile,$towrite));
						@fclose($hand2);
						$nsfv = sfv_check($myfile);
						rename(DATA_FILES_DIR.$sfv.'.df',DATA_FILES_DIR.$nsfv.'.df');
						if(file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
							rename(DATA_FILES_DIR.$sfv.'.jpg', DATA_FILES_DIR.$nsfv.'.jpg');
						if(file_exists(DATA_FILES_DIR.'p_'.$sfv.'.jpg'))
							rename(DATA_FILES_DIR.'p_'.$sfv.'.jpg', DATA_FILES_DIR.'p_'.$nsfv.'.jpg');
						header('location: ?load=dir&id='.urlencode(stripslashes($_POST['movedir']))); die();
					}
				}
				else
					$_SESSION['onload'] = popt('preview.desc.edit.err4', array('dir' => $_POST['movedir']));
			}
			else
				$_SESSION['onload'] = popt('preview.desc.edit.err7', '');
		}
		@fwrite($hand2,$towrite);
		@fclose($hand2);

		if($_FILES['newimage']['name'] AND !$_POST['movedir'] AND isadmin())
		{
			$data = @getimagesize($_FILES['newimage']['tmp_name']);
			if(!testi($data['2'],0))
				$upd_error = popt('loaddir.upl.err5', '');
			else
			{
				$fsize = @filesize($_FILES['newimage']['tmp_name']);
				if($fsize > (UPL_MAX_FILESIZE*1024))
					$upd_error = popt('loaddir.upl.err6', array('upl_max_filesize' => UPL_MAX_FILESIZE));
				else
				{
					$ex_path = $lines[0];
					$cname = trim_bad_chars($_FILES['newimage']['name']);

					if(!$_POST['rename'])
						$dest = $ex_path;
					else
						$dest = dirname($ex_path).'/'.$cname;

					if(move_uploaded_file($_FILES['newimage']['tmp_name'],$dest))
					{
						if($_POST['rename'])
						{
							$sfv_n = sfv_check($dest);
							rename(DATA_FILES_DIR.$sfv.'.df',DATA_FILES_DIR.$sfv_n.'.df');
							@unlink(DATA_FILES_DIR.'p_'.$sfv.'.jpg');

							@tn($dest,DATA_FILES_DIR.$sfv_n.'.jpg',THUMBNAIL_MAX_DIM);

							$arr = array('5' => $data[0], '6' => $data[1], '7' => $data[2], '8' => $fsize, '9' => $t5);
							$towrite = str_replace(trim($ex_path),$dest,upd_attr($arr, DATA_FILES_DIR.$sfv_n.'.df'));
							write_data(DATA_FILES_DIR.$sfv_n.'.df', $towrite, 'w');

							$_SESSION['onload'] = popt('preview.desc.edit.err1', '');
							header('location: ?load=prev&id='.$sfv_n);
							die();
						}
						else
						{
							@unlink(DATA_FILES_DIR.'p_'.$sfv.'.jpg');
							@tn($dest,DATA_FILES_DIR.$sfv.'.jpg',THUMBNAIL_MAX_DIM);
							$_SESSION['onload'] = popt('preview.desc.edit.err2', '');

							$arr = array('5' => $data[0], '6' => $data[1], '7' => $data[2], '8' => $fsize, '9' => $t5);
							$towrite = upd_attr($arr, $lines);			
							write_data(DATA_FILES_DIR.$sfv.'.df', $towrite, 'w');
						}
					}
					else
						$upd_error = popt('preview.desc.edit.err3', '');
				}
			}
			if($upd_error)
				$_SESSION['onload'] = $upd_error;
		}

		header('location: '.$_SERVER['REQUEST_URI']);
		die();
	}

	if($_POST['comsub'] AND GAL_COMMENTS)
	{
		if(!per('com'))
			$error = popt('msg.3', '');
		else
		{
			if(!$_POST['name'] OR !$_POST['com'])
				$error = popt('msg.4', '');
			else
			{
				if(strlen($_POST['name']) > 20)
					$error = popt('msg.8', '');
				else
				{
					if(strlen($_POST['com']) > GAL_COM_MAX_LENGTH OR strlen($_POST['com']) < GAL_COM_MIN_LENGTH)
						$error =
							popt('preview.form.err1',
								array(
									'gal_com_min_length' => GAL_COM_MIN_LENGTH,
									'gal_com_max_length' => GAL_COM_MAX_LENGTH,
									'curr' => strlen($_POST['com'])
								)
							);
					else
					{
						if($_SESSION['im_'.$sfv]+(60*ANTI_SPAM_DELAY) > time())
							$error = popt('msg.6', '');
						else
						{
							$error = captcha_check('com');

							if(!$error)
							{
								$error = test_human_spam($_POST['com'].' '.$_POST['name']);
								if(!$error)
								{
									$time = time();
									$mcom = "\n".str_replace('|','_',base64_encode($_POST['name']))."|".base64_encode($_POST['com'])."|".$time."|".$ip;

									write_data(DATA_FILES_DIR.$sfv.'.df', $mcom, 'a');

									$_SESSION['im_'.$sfv] = time();
									$name = $_POST['name'];

									if($name != $_COOKIE['username'])
										setcookie('username',$name,time()+31536000,'/');
									updc($sfv,2);

									rcom_upd(base64_encode($name).'|'.base64_encode($_POST['com']).'|'.$sfv.'.df'.'|'.$time);
									mcom_upd($sfv.'.df|'.($attr[2]+1));
									header('location: '.$_SERVER['REQUEST_URI'].'#'.sfv_check(trim($mcom))); die();
								}
							}
						}
					}
				}
			}
		}
	}

	if($_POST['ratesub'] AND GAL_RATINGS)
	{
		if(!per('rate'))
			$error = popt('msg.3', '');
		else
		{
			if($_COOKIE[$sfv] == 1)
				$error = popt('preview.form.rate.err1', '');
			else
			{
				$attr[3] += $_POST['rate'];
				$attr[4]++;

				$arr = array('3' => $attr[3], '4' => $attr[4]);
				write_data(DATA_FILES_DIR.$sfv.'.df', upd_attr($arr,$lines), 'w');

				trated_upd($sfv.'.df'.'|'.number_format($attr[3]/$attr[4],1,'.','').'|'.$attr[4]);

				@setcookie ($sfv, 1, time()+60*60*24*30);
			}
		}
	}

	$ldir = rtrim(str_replace(ALB_DIR,'',$lines[0]));
	$fl = explode("/",$ldir);

	for($i = 0 ; $i < count($fl)-1 ; $i++)
		$mdir .= $fl[$i]."/";
	$mdir = rtrim($mdir,"/");

	$rt = $attr[3];
	$rtn = $attr[4];

	if($rtn)
		$frate = "| ".parserate($rt,$rtn);

	if(GAL_COMMENTS)
	{
		$fname = stripslashes($_POST['name']);
		$fcom = stripslashes($_POST['com']);

		if(!$fname AND $_COOKIE['username'])
			$fname = stripslashes($_COOKIE['username']);

		$st = 'closed';
		if($error)
			$st = 'open';

		$prev_box = '';
		if($_POST['preview'])
		{
			$st = 'open';
			$prev_box =
				popt('preview.comments.entry.box',
					array(
						'comment' =>
							pfancy(
								popt('preview.comments.entry',
									array(
										'id' => '',
										'fdate' => tdat(time()),
										'del' => '',
										'name' => stripslashes($fname),
										'com' => wrap_long(parse($fcom, 'com'))
									)
								)
							,1,1)
					)
				);
		}

		$captcha = captcha_init('com');

		$comt =
			popt('preview.form',
				array(
					'error' => print_err($error),
					'captcha' => $captcha,
					'smilies' => smiley('com', 0),
					'sfv' => $sfv,
					'page' => $_GET['page'],
					'name' => $fname,
					'com' => $fcom,
					'prev_box' => $prev_box,
					'st' => $st
				)
			);
	}
	else
		$comt = popt('preview.form.disabled', '');

	$tmp = explode(' ',popt('preview.form.rate.desc', ''));
	foreach($tmp as $key => $value)
	{
		list($tmp1,$tmp2) = explode('-',$value);
		$rating_desc[$tmp1] = $tmp2; 
	}

	for($opn = GAL_RATE_MIN ; $opn < GAL_RATE_MAX+1 ; $opn++)
		$opd .= popt('preview.form.rate.option', array('opn' => $opn.' - '.$rating_desc[$opn]));

	if(GAL_RATINGS)
		$ratet =
			popt('preview.form.rate',
				array(
					'frate' => $frate,
					'sfv' => $sfv,
					'opd' => $opd
				)
			);

	$ww = 97;
	if(!ereg("msie",strtolower($_SERVER['HTTP_USER_AGENT'])))
		$ww = 100;

	list($user,$hitsn,$com,$rated,$raten,$w,$h,$ftype,$fsize,$upl_date,$imgtitle,$descstr) = explode('-',trim($lines[1]));

	$imgtitle = base64_decode($imgtitle);
	$descstr = base64_decode($descstr);

	if(PREVIEW_LINK != '2')
	{
		$imgtitle =	stripslashes(stripslashes($imgtitle));

		switch(PREVIEW_LINK)
		{
			case '0': $mlnk =
					popt('preview.link_start.0',
						array(
							'sfv' => $sfv,
							'w' => $w,
							'h' => $h,
							'title' => str_replace(array('"',"'"),array('',''),$imgtitle)
						)
					); break;
			case '1': $mlnk .=
					popt('preview.link_start.1',
						array(
							'sfv' => $sfv,
							'w' => $w,
							'h' => $h,
							'title' => str_replace(array('"',"'"),array('',''),$imgtitle),
							'image_link' => rtrim(str_replace('&#',urlencode('&#'),$lines[0]))
						)
					); break;
		}
	}
	else
		$mlnke = '';

	$mlnke = '';
	if(PREVIEW_LINK != '2')
		$mlnke = popt('preview.link_end', '');

	if(strlen($imgtitle) == 0)
	{
		$tmp_desc = popt('preview.desc.void', '');
		
		if(PREVIEW_LINK != '2')
			$TITLE = preg_replace("/.jpg|.jpeg|.png|.gif/i", '', basename($lines[0]));
		else
			$TITLE = $sfv;
	}
	else
	{
		$TITLE = $imgtitle;
		$tmp_desc = parse(trim($imgtitle), '');
	}

	$st = 'closed';
	if($edterr)
		$st = 'open';

	$file_manipulation = '';
	if(is_writable(trim($file_contents[0])) AND isadmin())
		$file_manipulation =
			popt('preview.desc.edit.file_manipulation',
				array(
					'move' => $move,
					'dir' => base64_encode(dirname($file_contents[0])),
					'THEME' => str_replace("'",'_QUOTE_',$THEME)
				)
			);

	$desc_edit = '';
	if(isadmin())
		$desc_edit =
			popt('preview.desc.edit',
				array(
					'pass' => $pass,
					'st' => $st,
					'error' => print_err($edterr),
					'cont' => str_replace('"',htmlentities('"'),$imgtitle),
					'desc' => $descstr,
					'file_manipulation' => $file_manipulation
				)
			);

	$img_description =
		popt('preview.desc',
			array(
				'edit' => $desc_edit,
				'contents' => $tmp_desc
			)
		);

	$encdir = urlencode($mdir);

	$farr = explode("/",$lines[0]);
	if(PREVIEW_LINK != '2' OR THUMBNAIL_LINK != '3')
	{
		$imgt = $farr[count($farr)-1];

		if(strlen($imgt)>FCROP)
			$imgt =
				popt('loaddir.cell.thumbstr.crop',
					array(
						'file' => $imgt,
						'cropped_text' => substr($imgt,0,FCROP)
					)
				);

	}
	else
		$imgt = $sfv;

	$title =
		str_replace(
			array(
				'.jpg',
				'.jpeg',
				'.gif',
				'.png'
			),
			array('','','',''),
			strtolower($imgt)
		);

	$ow = '-';
	if(base64_decode($attr[0]))
		$ow = base64_decode($attr[0]);

	if(!file_exists(DATA_FILES_DIR.'p_'.$sfv.'.jpg'))
		$img_link =
			popt('preview.img.indirect',
				array(
					'sfv' => $sfv,
					'preview_size' => PREVIEW_SIZE,
					'updt' => 0
				)
			);
	elseif(file_exists(DATA_FILES_DIR.'p_'.$sfv.'.jpg'))
	{
		$datainfo = getimagesize(DATA_FILES_DIR.'p_'.$sfv.'.jpg');
		$maxdim = $datainfo[0];
		if($datainfo[0] < $datainfo[1])
			$maxdim = $datainfo[1];

		$maxdim_large = $w;
		if($w < $h)
			$maxdim_large = $h;

		if(PREVIEW_SIZE == $maxdim OR $maxdim_large < PREVIEW_SIZE)
			$img_link =
				popt('preview.img.direct',
					array(
						'sfv' => $sfv,
						'data_files_dir' => DATA_FILES_DIR,
						'preview_size' => PREVIEW_SIZE
					)
				);
		else
		{
			$img_link =
				popt('preview.img.indirect',
					array(
						'sfv' => $sfv,
						'preview_size' => PREVIEW_SIZE,
						'updt' => 1
					)
				);
		}
	}

	if(intval($attr[2]) > 0)
	{
		for($i = 2 ; $i < count($lines) ; $i++)
		{
			if(strstr($lines[$i],"|") AND !strstr($lines[$i], '[TITLE]'))
			{
				list($name,$com,$date,$ip) = explode("|",stripslashes($lines[$i]));
				$fdate = tdat($date);

				$del = '';
				if(per('com_del'))
					$del =
						popt('preview.comments.del',
							array(
								'id' => sfv_check($lines[$i]),
								'sfv' => $sfv,
								'msg' => popt('javascript.confirm.1', ''),
								'THEME' => str_replace("'",'_QUOTE_',$THEME)
							)
						);

				$ipinfo = '';
				if(isadmin())
					$ipinfo = " ({$ip})";

				$comments .=
					popt('preview.comments.entry.box',
						array(
							'comment' =>
								pfancy(
									popt('preview.comments.entry',
										array(
											'id' => sfv_check($lines[$i]),
											'del' => $del,
											'name' => stripslashes(base64_decode($name)).$ipinfo,
											'com' => wrap_long(parse(trim(stripslashes(base64_decode($com))), 'com')),
											'fdate' => $fdate
										)
									)
								,1,1)
						)
					);
			}
		}
	}
	else
		$comments = popt('preview.comments.void','');


	if(function_exists('exif_read_data') AND $ftype == '2')
	{
		$exif = exif_read_data($lines[0], 0, true);

		if($exif['IFD0']['Make'] AND $exif['IFD0']['Model'])
			$exif_t .= $exif['IFD0']['Make'].' '.$exif['IFD0']['Model'].' ';

		if($exif['COMPUTED']['ApertureFNumber'] AND $exif['EXIF']['FocalLength'])
		{
			list($tmp1,$tmp2) = explode("/",$exif['EXIF']['FocalLength']);
			$exif_t .= 'Ap.'.$exif['COMPUTED']['ApertureFNumber'].' FLen.'.$tmp1/$tmp2.'mm ';
		}
		if($exif['IFD0']['Software'])
			$exif_t .= $exif['IFD0']['Software'].' ';

		if($exif_t AND $exif['IFD0']['DateTime'])
			$exif_t .= '- ';

		if($exif['IFD0']['DateTime'])
		{
			$arr1_ = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$arr2_ = array(1,2,3,4,5,6,7,8,9,10,11,12);
			list($tmp1,$tmp2,$tmp3,$tmp4,$tmp5,$tmp6,$tmp7) = explode(":",str_replace(' ',':',str_replace($arr1_,$arr2_,$exif['IFD0']['DateTime'])));
      		$exif_t .= tdat(mktime($tmp4,$tmp5,$tmp6,$tmp2,$tmp3,$tmp7));
		}
	}

	list($prev_id,$next_id,$cur,$tot) = read_pn(
								dirname($lines[0]),
								basename($lines[0])
							);

	if($prev_id)
	{
		$prev_sfv = sfv_check(dirname($lines[0]).'/'.$prev_id);

		if(!file_exists(DATA_FILES_DIR.$prev_sfv.'.df'))
			$prev =
				popt('preview.prev',
					array(
						'id' => $prev_sfv,
						'build' => base64_encode(dirname($lines[0]).'/'.$prev_id)
					)
				);
		else
			$prev = str_replace('&build={build}','',popt('preview.prev',array('id' => $prev_sfv)));
	}
	else
		$prev = popt('preview.prev.off', '');

	if($next_id)
	{
		$next_sfv = sfv_check(dirname($lines[0]).'/'.$next_id);

		if(!file_exists(DATA_FILES_DIR.$next_sfv.'.df'))
			$next =
				popt('preview.next',
					array(
						'id' => $next_sfv,
						'build' => base64_encode(dirname($lines[0]).'/'.$next_id)
					)
				);
		else
			$next = str_replace('&build={build}','',popt('preview.next',array('id' => $next_sfv)));
	}
	else
		$next = popt('preview.next.off', '');

	$corr_exif_or = '';
	if($exif['IFD0']['Orientation'] != '1' AND $exif['IFD0']['Orientation'] AND PREVIEW_LINK != '2' AND $ftype == '2')
	{
		$test = getimagesize(DATA_FILES_DIR.$sfv.'.jpg');

		if(($test[0] > $test[1] AND $w < $h) OR ($test[0] < $test[1] AND $w > $h))
		{
			$w2 = $h; $h2 = $w;
		}
		else
		{
			$w2 = $w; $h2 = $h;
		}

		$corr_exif_or =
			popt('preview.corr_exif_or',
				array(
					'sfv' => $sfv,
					'w' => $w,
					'h' => $h,
					'w2' => $w2,
					'h2' => $h2,
					'title' => htmlentities(str_replace("'",'',trim(stripslashes($title))))
				)
			);

	}

	$img_description_long = '';
	if($descstr)
	{
		$len = strlen(trim($descstr));

		if($len > 150)
			$img_description_long =
				popt('preview.img_description_long.cropped',
					array(
						'desc' => parse(substr(trim($descstr),0,150),''),
						'desc_full' => parse(trim($descstr),'')
					)
				);
		else
			$img_description_long = popt('preview.img_description_long', array('desc' => parse(trim($descstr),'')));
	}

	$batch_com_removal = '';
	if(isadmin() AND intval($attr[2]))
		$batch_com_removal = popt('preview.batch_com_removal', '');

	$str =
		popt('preview',
			array(
				'corr_exif_or' => $corr_exif_or,
				'batch_com_removal' => $batch_com_removal,
				'prev' => $prev,
				'next' => $next,
				'cur' => $cur,
				'tot' => $tot,
				'img_link' => $img_link,
				'encdir' => $encdir,
				'page' => $_GET['page'],
				'mdir' =>
					popt('preview.path',
						array(
							'path' => path($mdir,1),
							'filename' => $imgt
						)
					),
				'title' => $title,
				'ww' => $ww,
				'mlnk' => $mlnk,
				'sfv' => $sfv,
				'ct' => $attr[1],
				'error' => print_err($error),
				'comt' => $comt,
				'mlnke' => $mlnke,
				'ratet' => $ratet,
				'preview_size' => PREVIEW_SIZE,
				'img_description' => $img_description,
				'img_description_long' => $img_description_long,
				'1' => $ow,
				'2' => number_format($fsize/1024,1,'.',''),
				'3' => $w,
				'4' => $h,
				'5' => $attr[2],
				'6' => $attr[4],
				'7' => tdat($upl_date).'<br>'.$exif_t,
				'comments' => $comments
			)
		);

	return($str);
}


function update_file($dir,$mode)
{
	if($_POST['dessub'] AND isadmin())
	{
		write_data(DATA_FILES_DIR.sfv_check($dir).'.df', stripslashes($_POST['des']), 'w');
		$error = popt('msg.5', '');
	}

	$cont =
		str_replace(
			"\r",
			"",
			stripslashes(
				file_get_contents(DATA_FILES_DIR.sfv_check($dir).".df")
			)
		);
	$encdir = urlencode($dir);

	return
		popt('update_file',
			array(
				'error' => $error,
				'encdir' => $encdir,
				'cont' => $cont
			)
		);
}

function print_err($error)
{
	global $L;

	if($error)
	{
		$style = 'red'; $img = '';
		if(preg_match("/".$L[327]."/i",$error))
		{
			$style = 'green';
			$img = popt('print_err.icon', '');
		}
					
		return
			popt('print_err',
				array(
					'style' => $style,
					'img' => $img,
					'error' => $error
				)
			);
	}
}

function search()
{
	function test_match($filecon,$key)
	{
		$key = trim($key);

		if($_POST['search_mode'] == "3")
			$test = stristr($filecon,$key);
		elseif($_POST['search_mode'] == "2")
		{
			$myk = explode(" ",$key);
			$test = 0;

			for($kc = 0 ; $kc < count($myk) ; $kc++)
			{
				if(stristr($filecon,trim($myk[$kc])) AND strlen(trim($myk[$kc])) >= SEARCH_KEY_LENGTH_MIN)
				$test = 1;
			}
		}
		else
		{
			$myk = explode(" ",$key);
			$mtest = 0;

			for($kc = 0 ; $kc < count($myk) ; $kc++)
			{
				if(trim($myk[$kc]))
				{
					if(stristr($filecon,trim($myk[$kc])) AND strlen(trim($myk[$kc])) >= SEARCH_KEY_LENGTH_MIN)
						$mtest++;
				}
			}
		

			$test = 0;
			if($mtest == count($myk))
				$test = 1;
		}
		return($test);
	}

	$imgn = 0; $darr = $darr2 = array(); $contents = '';

	if(SEARCH_ENABLED)
	{
		if(!$_GET['l'])
			$key = base64_decode($_GET['key']);
		else
		{
			global $key;
			$_GET['key'] = $key;
			$key = base64_decode($key);
		}

		if($_GET['t'])
			$_POST['search_target'] = $_GET['t'];

		if(!$key)
			$key = stripslashes($_POST['strkey']);

		if(strlen($key) >= SEARCH_KEY_LENGTH_MIN)
		{
			$keystr = popt('search.keystr.exact_phrase', array('key' => $key));
			if($_POST['search_mode'] != "3")
				$keystr = popt('search.keystr.normal', array('key' => $key));
		}

		$x = $y = 1;
		$per = 100/GAL_COL;

		if(file_exists(DATA_FILES_DIR.'disabled.log'))
			$disabled = explode("\n",trim(file_get_contents(DATA_FILES_DIR.'disabled.log')));
		else
			$disabled = array();

		$row = '';
		if($_POST['search'] OR $_GET['key'])
		{ 
			if($key)
			{
				if(strlen($key) < SEARCH_KEY_LENGTH_MIN)
					$contents = popt('search.err1', array('search_key_length_min' => SEARCH_KEY_LENGTH_MIN));
				else
				{
					$handle2 = @opendir(DATA_FILES_DIR);
					if ($handle2 == false) return -1;
					while (($filename = @readdir($handle2)) != false)
					{
						if($filename != "." AND $filename != ".." AND strstr($filename,".df") AND !preg_match("/^blog/i",$filename))
						{
							$sfv = str_replace(".df","",$filename);
							$con = file_get_contents(DATA_FILES_DIR.$filename);
							$filecon = explode("\n",$con);

							if(!in_array(str_replace(ALB_DIR,'',dirname($filecon[0])),$disabled))
							{
							list($owner_,$hitsn,$com,$rated,$raten,$w,$h,$type,$fsize,$upl_date,$title_,$desc_) = explode("-",trim($filecon[1]));
							$name_ = basename(trim($filecon[0]));
							$title_ = base64_decode($title_);
							$owner_ = base64_decode($owner_);

							switch($_POST['search_target'])
							{
								case '': break;
								case '1': $totest = $name_; break;
								case '2': $totest = $owner_; break;
								case '3': $totest = $title_; break;
								case '4': $totest = $w.'x'.$h; break;
							}

							if(test_match($totest,$key))
							{
								if(file_exists(trim($filecon[0])))
								{	
									$imgdim = $w."x".$h;
									$imgsize = number_format($fsize/1000,1,'.','')."K";
									$titlestr = $imgsize." ".$imgdim." ".trim($hitsn).popt('hits', '');
									if(THUMBNAIL_LINK != '2')
									{
										switch(THUMBNAIL_LINK)
										{
											case '0':
												$link_start =
													popt('loaddir.cell.link_start.0',
														array(
															'sfv' => $sfv,
															'w' => $w,
															'h' => $h
														)
													); break;
											case '1':
												$link_start =
													popt('loaddir.cell.link_start.1',
														array(
															'sfv' => $sfv,
															'w' => $w,
															'h' => $h,
															'fullimg' => $fl
														)
													); break;
											case '3':
												$link_start =
													popt('loaddir.cell.link_start.2',
														array(
															'sfv' => $sfv
														)
													); break;
										}
										$link_end = popt('loaddir.cell.link_end', ''); 
									}
									else
										$link_start = $link_end = '';

$ow = '';
									$darr[$imgn] =
										popt('search.cell.contents',
											array(
												'link_start' => $link_start,
												'data_files_dir' => DATA_FILES_DIR,
												'filename' => $sfv,
												'titlestr' => $titlestr,
												'link_end' => $link_end
											)
										);
									if(!file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
										$darr[$imgn] =
											str_replace(
												DATA_FILES_DIR.$sfv.'.jpg',
												popt('loaddir.cell.tn_indirect',
													array(
														'sfv' => $sfv,
														'thumbnail_max_dim' => THUMBNAIL_MAX_DIM,
														'updt' => 0
													)
												),
												$darr[$imgn]
											);

									$darr2[$imgn] =
										array(
											$sfv,
											$com,
											$rated.'-'.$raten,
											$owner_
										);
									$imgn++;
								}
							}}
						}
						if($imgn >= SEARCH_LIMIT)
							break;
					}
					@closedir($handle2);
				}
			}
			elseif(!$contents)
				$contents = popt('search.err2', '');
		}
		else
			$contents = popt('search.err3', '');

		if(!$imgn AND !$contents)
			$contents = popt('search.err4', '');

		if($_GET['l'])
		{
			global $page;
			$_GET['page'] = $page;
		}

		if(!$_GET['page'])
			$_GET['page'] = 1;
		$page = $_GET['page']-1;
		$init = $page*SEARCH_ITEMS_PPAGE;

		$inite = ($page+1)*SEARCH_ITEMS_PPAGE;
		if($inite > $imgn)
			$inite = $imgn;

		$pages = $imgn/SEARCH_ITEMS_PPAGE;
		$mkey = base64_encode($key);

		if($imgn)
		{
			$y = 1;
			$row = $out = '';

			for($i = $init ; $i < $inite ; $i++)
			{
				$comrate = '';
				$sfv = $darr2[$i][0];
				$com = $darr2[$i][1];
				list($rated,$raten) = explode("-",trim($darr2[$i][2]));
		
				if(rtrim($com) OR $raten)
				{
					if(rtrim($com))
						$comrate =
							popt('loaddir.cell.comrate.com',
								array(
									'sfv' => $sfv,
									'page_str' => $page_str,
									'com' => $com
								)
							);
					if($raten)
						$comrate .=
							popt('loaddir.cell.comrate.rate',
								array(
									'sfv' => $sfv.$page_str,
									'rate' => parserate($rated,$raten)
								)
							);
				}
				elseif(THUMBNAIL_LINK != '3')
					$comrate =
						popt('loaddir.cell.comrate.void',
							array(
								'sfv' => $sfv,
								'page_str' => $page_str
							)
						);

				$ow = '';
				if(trim($darr2[$i][3]))
					$ow = popt('loaddir.cell.ow', array('user' => $darr2[$i][3]));

				$row .=
					popt('search.cell',
						array(
							'ow' => $ow,
							'per' => $per,
							'link' => $darr[$i],
							'comrate' => $comrate
						)
					);

				if($i != $inite-1)
				{
					if($y == GAL_COL)
					{
						$out .= popt('loaddir.row', array('contents' => $row));
						$row = '';
						$y = 0;
					}
				}
				else
					$out .= popt('loaddir.row', array('contents' => $row));
				$y++;
			}
			$contents = popt('search.contents', array('contents' => $out));
		}

		$index_p =
			page_list(
				$imgn,
				$_GET['page'],
				$pages,
				SEARCH_ITEMS_PPAGE,
				"?load=search&key={$mkey}&t=".intval($_POST['search_target'])
			);

		switch($_POST['search_mode'])
		{
			case '1': $op1 = "SELECTED"; break;
			case '2': $op2 = "SELECTED"; break;
			case '3': $op3 = "SELECTED"; break;
		}

		switch($_POST['search_target'])
		{
			case '1': $op11 = "SELECTED"; break;
			case '2': $op21 = "SELECTED"; break;
			case '3': $op31 = "SELECTED"; break;
			case '4': $op41 = "SELECTED"; break;
		}

		$limit_msg = '';
		if(SEARCH_LIMIT)
			$limit_msg = popt('search.limit_msg', array('n' => SEARCH_LIMIT));

		$str =
			popt('search',
				array(
					'op1' => $op1,
					'op2' => $op2,
					'op3' => $op3,
					'op11' => $op11,
					'op21' => $op21,
					'op31' => $op31,
					'op41' => $op41,
					'index_p' => $index_p,
					'contents' => $contents,
					'keystr' => $keystr,
					'imgn' => $imgn,
					'limit_msg' => $limit_msg
				)
			);
	}
	else
		$str = popt('search.disabled', '');

	return $str;
}

function upl($dir)
{
	$mdir = ALB_DIR.$dir;

	$data_file = @file_get_contents(DATA_FILES_DIR.sfv_check(trim($mdir, '/\\').'/').'.df');

	if(is_writable($mdir))
	{
		if($_POST['uplsub'] AND IMG_UPLOADS)
		{
			if((!per('upload') AND !strstr($data_file, '[UPLOAD_ON]')) OR strstr($data_file, '[UPLOAD_OFF]'))
				$error = popt('msg.3', '');
			else
			{
				$user_name = $_POST['upl_name'];

				if(!$_FILES['userfile']['name'])
					$error = popt('loaddir.upl.err1', '');
				else
				{
					if((!$_POST['upl_title'] AND REQUIRE_TITLE) OR !$user_name)
					{
						if((!$_POST['upl_title'] AND REQUIRE_TITLE))
							$error = popt('loaddir.upl.err2', '');
						if($error)
							$error .= '<br>';
						if(!$user_name)
							$error .= popt('loaddir.upl.err3', '');
					}
					else
					{
						if((strlen($_POST['upl_title']) >= TITLE_MAX_LENGTH OR strlen($_POST['upl_title']) <= TITLE_MIN_LENGTH) AND $_POST['upl_title'])
							$error =
								popt('loaddir.upl.err4',
									array(
										'title_min_length' => TITLE_MIN_LENGTH,
										'title_max_length' => TITLE_MAX_LENGTH,
										'curr' => strlen($_POST['upl_title'])
									)
								);
						else
						{
							$fdet = @getimagesize($_FILES['userfile']['tmp_name']);

							if(!testi($fdet['2'],0))
								$error = popt('loaddir.upl.err5', '');
							else
							{
								$fsize = @filesize($_FILES['userfile']['tmp_name']);
								if($fsize > (UPL_MAX_FILESIZE*1024))
									$error = popt('loaddir.upl.err6', array('upl_max_filesize' => UPL_MAX_FILESIZE));
								else
								{
									$cname = trim_bad_chars($_FILES['userfile']['name']);
									$dest = $mdir.$cname;

									if(file_exists($dest) AND (!per('overwrite') OR !ALLOW_OVERWRITE))
										$error = popt('loaddir.upl.err7', '');
									else
									{
										$error = captcha_check('upl');

										if(!$error)
										{
											$over = 0;
											if(@file_exists($dest))
												$over = 1;

											if(move_uploaded_file($_FILES['userfile']['tmp_name'],$dest))
											{
												$sfv = sfv_check($dest);

												$error = popt('loaddir.upl.err8', array('cname' => $cname));
												if($over)
													$error .= popt('loaddir.upl.err9', '');
											
												if(!$over)
													write_data(DATA_FILES_DIR.'upload.log', encode_ulog($cname,$dir,$user_name,ip(),$sfv,time()), 'a');

												$tn = DATA_FILES_DIR.$sfv.'.jpg';
												$tn_p = DATA_FILES_DIR.'p_'.$sfv.'.jpg';
												@tn($dest,$tn,THUMBNAIL_MAX_DIM);

												if($over AND file_exists($tn_p))
													@unlink($tn_p);

												$upl_title = $_POST['upl_title'];
												if($_POST['titlefromfile'])
													$upl_title = titlefromfile($_FILES['userfile']['name']);
					
												if(!$over)
												{
													if($upl_title)
														write_data(DATA_FILES_DIR.$sfv.'.df', $dest."\n".base64_encode($user_name)."-0-0-0-0-{$fdet[0]}-{$fdet[1]}-{$fdet[2]}-{$fsize}-".time()."-".base64_encode(stripslashes($upl_title)).'-', 'w');
													else
														write_data(DATA_FILES_DIR.$sfv.'.df', $dest."\n".base64_encode($user_name)."-0-0-0-0-{$fdet[0]}-{$fdet[1]}-{$fdet[2]}-{$fsize}-".time()."--", 'w');
												}
												if($user_name != $_COOKIE['username'])
													setcookie('username',$user_name,time()+31536000,'/');
											}
											else
												$error = popt('loaddir.upl.err10', '');
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if(IMG_UPLOADS)
		{
			$enc_dir = urlencode($dir);
			$tlv = stripslashes($_POST['title']);
		}
		else
			$error = popt('loaddir.upl.err11', '');
	}
	else
		$error = popt('loaddir.upl.err12', '');

	return($error);
}


function adminboard()
{
	global $TITLE;
	
	function ablogin()
	{
		if($_POST['login'])
		{
			$pass = $_POST['pass'];
			if($pass != DES_PASS OR !$pass OR !DES_PASS)
				$error = popt('adminboard.login.err1', '');
			else
			{
				$error = captcha_check('log');
				if(!$error)
				{
					list($basedir,$rest) = explode('?',$_SERVER['REQUEST_URI']);
					$_SESSION['des_pass'] = md5(DES_PASS);

					if($_POST['remember'])
						setcookie('des_pass',md5(DES_PASS),time()+31536000,$basedir);

					header('location: ?load=adminboard&mode=1');
					die();
				}
			}
		}

		$des_pass_err = '';
		if(!DES_PASS)
			$des_pass_err = popt('adminboard.login.des_pass_err', '');

		return
			popt('adminboard.login',
				array(
					'error' => print_err($error),
					'des_pass_err' => $des_pass_err,
					'captcha' => captcha_init('log')
				)
			);
	}

	function ablogout()
	{
		if($_POST['logout'])
		{
				list($basedir,$rest) = explode('?',$_SERVER['REQUEST_URI']);
				$_SESSION['des_pass'] = '';

				if(isset($_COOKIE['des_pass']))
					setcookie('des_pass','',time()-3600,$basedir);

				header('location: ?load=adminboard&mode=0');
				die();
		}
		return(popt('adminboard.logout',''));
	}

	function settings()
	{
		if($_POST['updset'])
		{
			if(!is_writable('settings.inc.php'))
			{
				$_SESSION['onload'] = popt('adminboard.settings.err', '');
				header('location: '.$_SERVER['REQUEST_URI']);
				die();
			}

			if(!$_POST['des_pass'])
				$_POST['des_pass'] = DES_PASS;

			$file = file_get_contents('settings.inc.php');
			$arr1 = array(
						'\'TITLE\', \''.TITLE.'\');',
						'\'SUFIXTITLE\', \''.SUFIXTITLE.'\');',
						'\'THUMBNAIL_LINK\', '.THUMBNAIL_LINK.');',
						'\'PREVIEW_LINK\', '.PREVIEW_LINK.');',
						'\'DEFAULT_TREE_DEPTH\', '.DEFAULT_TREE_DEPTH.');',
						'\'DCROP\', '.DCROP.');',
						'\'FCROP\', '.FCROP.');',
						'\'WCROP\', '.WCROP.');',
						'\'THUMBNAIL_MAX_DIM\', '.THUMBNAIL_MAX_DIM.');',
						'\'PREVIEW_SIZE\', '.PREVIEW_SIZE.');',
						'\'THUMBNAIL_QUALITY\', '.THUMBNAIL_QUALITY.');',
						'\'SORT_CLASS\', '.SORT_CLASS.');',
						'\'SORT_METHOD\', \''.SORT_METHOD.'\');',
						'\'SORT_TYPE\', \''.SORT_TYPE.'\');',
						'\'SEARCH_ENABLED\', '.SEARCH_ENABLED.');',
						'\'SEARCH_ITEMS_PPAGE\', '.SEARCH_ITEMS_PPAGE.');',
						'\'SEARCH_KEY_LENGTH_MIN\', '.SEARCH_KEY_LENGTH_MIN.');',
						'\'THUMBFOOTER\', \''.THUMBFOOTER.'\');',
						'\'IMG_PER_PAGE\', '.IMG_PER_PAGE.');',
						'\'GAL_COL\', '.GAL_COL.');',
						'\'GAL_COMMENTS\', '.GAL_COMMENTS.');',
						'\'GAL_RATINGS\', '.GAL_RATINGS.');',
						'\'GAL_COM_MIN_LENGTH\', '.GAL_COM_MIN_LENGTH.');',
						'\'GAL_COM_MAX_LENGTH\', '.GAL_COM_MAX_LENGTH.');',
						'\'GAL_COM_LOG_DSP\', '.GAL_COM_LOG_DSP.');',
						'\'GAL_RATE_MIN\', '.GAL_RATE_MIN.');',
						'\'GAL_RATE_MAX\', '.GAL_RATE_MAX.');',
						'\'DATE_FORMAT\', \''.DATE_FORMAT.'\');',
						'\'DATE_OFFSET\', '.DATE_OFFSET.');',
						'\'IMG_UPLOADS\', '.IMG_UPLOADS.');',
						'\'UPL_MAX_FILESIZE\', '.UPL_MAX_FILESIZE.');',
						'\'ALLOW_OVERWRITE\', '.ALLOW_OVERWRITE.');',
						'\'REQUIRE_TITLE\', '.REQUIRE_TITLE.');',
						'\'TITLE_MIN_LENGTH\', '.TITLE_MIN_LENGTH.');',
						'\'TITLE_MAX_LENGTH\', '.TITLE_MAX_LENGTH.');',
						'\'TITLE_FROM_FILE\', '.TITLE_FROM_FILE.');',
						'\'TITLE_FROM_FILE_CHAR\', \''.TITLE_FROM_FILE_CHAR.'\');',
						'\'USER_OPTIONS\', '.USER_OPTIONS.');',
						'\'SOCIAL_BOOKMARKS\', '.SOCIAL_BOOKMARKS.');',
						'\'BLOG\', '.BLOG.');',
						'\'BLOG_PER_PAGE\', '.BLOG_PER_PAGE.');',
						'\'LAST_UPLOADS_N\', '.LAST_UPLOADS_N.');',
						'\'RECENT_COM_N\', '.RECENT_COM_N.');',
						'\'MOST_COM_N\', '.MOST_COM_N.');',
						'\'MOST_VIEW_N\', '.MOST_VIEW_N.');',
						'\'TOP_RATED_N\', '.TOP_RATED_N.');',
						'\'LAST_WEB_UPLOADS\', '.LAST_WEB_UPLOADS.');',
						'\'RECENT_COM\', '.RECENT_COM.');',
						'\'MOST_COM\', '.MOST_COM.');',
						'\'MOST_VIEW\', '.MOST_VIEW.');',
						'\'TOP_RATED\', '.TOP_RATED.');',
						'\'DES_PASS\', \''.DES_PASS.'\');',
						'\'DATA_FILES_DIR\', \''.DATA_FILES_DIR.'\');',
						'\'PER_UPLOAD\', '.PER_UPLOAD.');',
						'\'PER_OVERWRITE\', '.PER_OVERWRITE.');',
						'\'PER_BLOG\', '.PER_BLOG.');',
						'\'PER_BLOG_EDIT\', '.PER_BLOG_EDIT.');',
						'\'PER_BLOG_DEL\', '.PER_BLOG_DEL.');',
						'\'PER_BLOG_COM\', '.PER_BLOG_COM.');',
						'\'PER_BLOG_COM_DEL\', '.PER_BLOG_COM_DEL.');',
						'\'PER_COM\', '.PER_COM.');',
						'\'PER_COM_DEL\', '.PER_COM.');',
						'\'PER_RATE\', '.PER_RATE.');',
						'\'ANTI_SPAM_DELAY\', '.ANTI_SPAM_DELAY.');',
						'\'ALB_DIR\', \''.ALB_DIR.'\');',
						'\'USE_MYSQL\', '.USE_MYSQL.');',
						'\'SEARCH_LIMIT\', '.SEARCH_LIMIT.');',
						'\'CAPTCHA_BLOG\', '.CAPTCHA_BLOG.');',
						'\'CAPTCHA_COM\', '.CAPTCHA_COM.');',
						'\'CAPTCHA_UPL\', '.CAPTCHA_UPL.');',
						'\'CAPTCHA_LOG\', '.CAPTCHA_LOG.');',
						'\'RECAPTCHA_PUBK\', \''.RECAPTCHA_PUBK.'\');',
						'\'RECAPTCHA_PRIVK\', \''.RECAPTCHA_PRIVK.'\');',
						'\'HUMANSPAM_NOURLS\', \''.HUMANSPAM_NOURLS.'\');',
						'\'HUMANSPAM_NOWORDS\', \''.HUMANSPAM_NOWORDS.'\');',
						'\'HUMANSPAM_IPBAN\', \''.HUMANSPAM_IPBAN.'\');',
						'\'DEFAULT_THEME\', \''.DEFAULT_THEME.'\');',
						'\'DEFAULT_LANG\', \''.DEFAULT_LANG.'\');',
						'\'THEME_LOCKED\', '.THEME_LOCKED.');',
						'\'LANG_LOCKED\', '.LANG_LOCKED.');',
						'\'MYSQL_HOST\', \''.MYSQL_HOST.'\');',
						'\'MYSQL_USERNAME\', \''.MYSQL_USERNAME.'\');',
						'\'MYSQL_PASSWORD\', \''.MYSQL_PASSWORD.'\');',
						'\'MYSQL_TABLE\', \''.MYSQL_TABLE.'\');'
			);
			$arr2 = array(
						'\'TITLE\', \''.$_POST['title'].'\');',
						'\'SUFIXTITLE\', \''.$_POST['sufixtitle'].'\');',
						'\'THUMBNAIL_LINK\', '.$_POST['thumbnail_link'].');',
						'\'PREVIEW_LINK\', '.$_POST['preview_link'].');',
						'\'DEFAULT_TREE_DEPTH\', '.intval($_POST['default_tree_depth']).');',
						'\'DCROP\', '.$_POST['dcrop'].');',
						'\'FCROP\', '.$_POST['fcrop'].');',
						'\'WCROP\', '.$_POST['wcrop'].');',
						'\'THUMBNAIL_MAX_DIM\', '.$_POST['thumbnail_max_dim'].');',
						'\'PREVIEW_SIZE\', '.$_POST['preview_size'].');',
						'\'THUMBNAIL_QUALITY\', '.$_POST['thumbnail_quality'].');',
						'\'SORT_CLASS\', '.$_POST['sort_class'].');',
						'\'SORT_METHOD\', \''.$_POST['sort_method'].'\');',
						'\'SORT_TYPE\', \''.$_POST['sort_type'].'\');',
						'\'SEARCH_ENABLED\', '.intval($_POST['search_enabled']).');',
						'\'SEARCH_ITEMS_PPAGE\', '.$_POST['search_items_ppage'].');',
						'\'SEARCH_KEY_LENGTH_MIN\', '.intval($_POST['search_key_length_min']).');',
						'\'THUMBFOOTER\', \''.$_POST['thumbfooter'].'\');',
						'\'IMG_PER_PAGE\', '.$_POST['img_per_page'].');',
						'\'GAL_COL\', '.intval($_POST['gal_col']).');',
						'\'GAL_COMMENTS\', '.intval($_POST['gal_comments']).');',
						'\'GAL_RATINGS\', '.intval($_POST['gal_ratings']).');',
						'\'GAL_COM_MIN_LENGTH\', '.intval($_POST['gal_com_min_length']).');',
						'\'GAL_COM_MAX_LENGTH\', '.intval($_POST['gal_com_max_length']).');',
						'\'GAL_COM_LOG_DSP\', '.intval($_POST['gal_com_log_dsp']).');',
						'\'GAL_RATE_MIN\', '.intval($_POST['gal_rate_min']).');',
						'\'GAL_RATE_MAX\', '.intval($_POST['gal_rate_max']).');',
						'\'DATE_FORMAT\', \''.$_POST['date_format'].'\');',
						'\'DATE_OFFSET\', '.$_POST['date_offset'].');',
						'\'IMG_UPLOADS\', '.intval($_POST['img_uploads']).');',
						'\'UPL_MAX_FILESIZE\', '.$_POST['upl_max_filesize'].');',
						'\'ALLOW_OVERWRITE\', '.intval($_POST['allow_overwrite']).');',
						'\'REQUIRE_TITLE\', '.intval($_POST['require_title']).');',
						'\'TITLE_MIN_LENGTH\', '.intval($_POST['title_min_length']).');',
						'\'TITLE_MAX_LENGTH\', '.intval($_POST['title_max_length']).');',
						'\'TITLE_FROM_FILE\', '.intval($_POST['ftptitlefromfile']).');',
						'\'TITLE_FROM_FILE_CHAR\', \''.$_POST['title_from_file_char'].'\');',
						'\'USER_OPTIONS\', '.intval($_POST['user_options']).');',
						'\'SOCIAL_BOOKMARKS\', '.intval($_POST['social_bookmarks']).');',
						'\'BLOG\', '.intval($_POST['blog']).');',
						'\'BLOG_PER_PAGE\', '.intval($_POST['blog_per_page']).');',
						'\'LAST_UPLOADS_N\', '.intval($_POST['last_uploads_n']).');',
						'\'RECENT_COM_N\', '.intval($_POST['recent_com_n']).');',
						'\'MOST_COM_N\', '.intval($_POST['most_com_n']).');',
						'\'MOST_VIEW_N\', '.intval($_POST['most_view_n']).');',
						'\'TOP_RATED_N\', '.intval($_POST['top_rated_n']).');',
						'\'LAST_WEB_UPLOADS\', '.intval($_POST['last_web_uploads']).');',
						'\'RECENT_COM\', '.intval($_POST['recent_com']).');',
						'\'MOST_COM\', '.intval($_POST['most_com']).');',
						'\'MOST_VIEW\', '.intval($_POST['most_view']).');',
						'\'TOP_RATED\', '.intval($_POST['top_rated']).');',
						'\'DES_PASS\', \''.$_POST['des_pass'].'\');',
						'\'DATA_FILES_DIR\', \''.trim($_POST['data_files_dir'],'/\\').'/\');',
						'\'PER_UPLOAD\', '.intval($_POST['per_upload']).');',
						'\'PER_OVERWRITE\', '.intval($_POST['per_overwrite']).');',
						'\'PER_BLOG\', '.intval($_POST['per_blog']).');',
						'\'PER_BLOG_EDIT\', '.intval($_POST['per_blog_edit']).');',
						'\'PER_BLOG_DEL\', '.intval($_POST['per_blog_del']).');',
						'\'PER_BLOG_COM\', '.intval($_POST['per_blog_com']).');',
						'\'PER_BLOG_COM_DEL\', '.intval($_POST['per_blog_com_del']).');',
						'\'PER_COM\', '.intval($_POST['per_com']).');',
						'\'PER_COM_DEL\', '.intval($_POST['per_com_del']).');',
						'\'PER_RATE\', '.intval($_POST['per_rate']).');',
						'\'ANTI_SPAM_DELAY\', '.intval($_POST['anti_spam_delay']).');',
						'\'ALB_DIR\', \''.trim($_POST['alb_dir'],'/\\').'/\');',
						'\'USE_MYSQL\', '.intval($_POST['use_mysql']).');',
						'\'SEARCH_LIMIT\', '.intval($_POST['search_limit']).');',
						'\'CAPTCHA_BLOG\', '.intval($_POST['captcha_blog']).');',
						'\'CAPTCHA_COM\', '.intval($_POST['captcha_com']).');',
						'\'CAPTCHA_UPL\', '.intval($_POST['captcha_upl']).');',
						'\'CAPTCHA_LOG\', '.intval($_POST['captcha_log']).');',
						'\'RECAPTCHA_PUBK\', \''.$_POST['recaptcha_pubk'].'\');',
						'\'RECAPTCHA_PRIVK\', \''.$_POST['recaptcha_privk'].'\');',
						'\'HUMANSPAM_NOURLS\', \''.$_POST['humanspam_nourls'].'\');',
						'\'HUMANSPAM_NOWORDS\', \''.$_POST['humanspam_nowords'].'\');',
						'\'HUMANSPAM_IPBAN\', \''.$_POST['humanspam_ipban'].'\');',
						'\'DEFAULT_THEME\', \''.$_POST['default_theme'].'\');',
						'\'DEFAULT_LANG\', \''.$_POST['default_lang'].'\');',
						'\'THEME_LOCKED\', '.intval($_POST['theme_locked']).');',
						'\'LANG_LOCKED\', '.intval($_POST['lang_locked']).');',
						'\'MYSQL_HOST\', \''.$_POST['mysql_host'].'\');',
						'\'MYSQL_USERNAME\', \''.$_POST['mysql_username'].'\');',
						'\'MYSQL_PASSWORD\', \''.$_POST['mysql_password'].'\');',
						'\'MYSQL_TABLE\', \''.$_POST['mysql_table'].'\');'
			);
			$fn = str_replace($arr1,$arr2,$file);
			write_data('settings.inc.php', $fn, 'w');

			$_SESSION['onload'] = popt('adminboard.settings.msg', '');

			header('location: ?load=adminboard&mode=1');
			die();

		}
		include('settings.inc.php');

		for($i = 1 ; $i < 100 ; $i++)
			$v{$i} = '';
	
		switch(THUMBNAIL_LINK)
		{
			case '0': $v1 = ' SELECTED'; break;
			case '1': $v2 = ' SELECTED'; break;
			case '2': $v3 = ' SELECTED'; break;
			case '3': $v4 = ' SELECTED'; break;
		}

		switch(PREVIEW_LINK)
		{
			case '0': $v5 = ' SELECTED'; break;
			case '1': $v6 = ' SELECTED'; break;
			case '2': $v7 = ' SELECTED'; break;
		}
		switch(SORT_CLASS)
		{
			case '1': $v11 = ' SELECTED'; break;
			case '2': $v12 = ' SELECTED'; break;
			case '3': $v64 = ' SELECTED'; break;
		}
		switch(SORT_METHOD)
		{
			case 'SORT_REGULAR': $v13 = ' SELECTED'; break;
			case 'SORT_NUMERIC': $v14 = ' SELECTED'; break;
			case 'SORT_STRING': $v15 = ' SELECTED'; break;
		}
		switch(SORT_TYPE)
		{
			case 'SORT_ASC': $v16 = ' SELECTED'; break;
			case 'SORT_DESC': $v17 = ' SELECTED'; break;
		}
		if(SEARCH_ENABLED)
			$v18 = ' CHECKED';
		if(GAL_COMMENTS)
			$v19 = ' CHECKED';
		if(GAL_RATINGS)
			$v20 = ' CHECKED';
		if(IMG_UPLOADS)
			$v21 = ' CHECKED';
		if(ALLOW_OVERWRITE)
			$v23 = ' CHECKED';
		if(REQUIRE_TITLE)
			$v24 = ' CHECKED';
		if(USER_OPTIONS)
			$v25 = ' CHECKED';
		if(SOCIAL_BOOKMARKS)
			$v26 = ' CHECKED';
		if(BLOG)
			$v50 = ' CHECKED';
		if(LAST_WEB_UPLOADS)
			$v51 = ' CHECKED';
		if(RECENT_COM)
			$v52 = ' CHECKED';
		if(MOST_COM)
			$v58 = ' CHECKED';
		if(MOST_VIEW)
			$v59 = ' CHECKED';
		if(TOP_RATED)
			$v53 = ' CHECKED';
		if(USE_MYSQL)
			$v54 = ' CHECKED';
		if(CAPTCHA_BLOG)
			$v55 = ' CHECKED';
		if(CAPTCHA_COM)
			$v56 = ' CHECKED';
		if(CAPTCHA_UPL)
			$v57 = ' CHECKED';
		if(CAPTCHA_LOG)
			$v63 = ' CHECKED';
		if(THEME_LOCKED)
			$v60 = ' CHECKED';
		if(LANG_LOCKED)
			$v61 = ' CHECKED';
		if(TITLE_FROM_FILE)
			$v62 = ' CHECKED';
		if(HUMANSPAM_NOURLS)
			$v65 = ' CHECKED';

		for($i = 0 ; $i < 20 ; $i++)
			$p{$i} = '';

		switch(PER_UPLOAD)
		{
			case '0': $p0 = ' SELECTED'; break;
			case '1': $p1 = ' SELECTED'; break;
		}

		switch(PER_OVERWRITE)
		{
			case '0': $p2 = ' SELECTED'; break;
			case '1': $p3 = ' SELECTED'; break;
		}

		switch(PER_BLOG)
		{
			case '0': $p4 = ' SELECTED'; break;
			case '1': $p5 = ' SELECTED'; break;
		}
		switch(PER_BLOG_EDIT)
		{
			case '0': $p12 = ' SELECTED'; break;
			case '1': $p13 = ' SELECTED'; break;
		}
		switch(PER_BLOG_DEL)
		{
			case '0': $p14 = ' SELECTED'; break;
			case '1': $p15 = ' SELECTED'; break;
		}

		switch(PER_BLOG_COM)
		{
			case '0': $p6 = ' SELECTED'; break;
			case '1': $p7 = ' SELECTED'; break;
		}
		switch(PER_BLOG_COM_DEL)
		{
			case '0': $p16 = ' SELECTED'; break;
			case '1': $p17 = ' SELECTED'; break;
		}

		switch(PER_COM)
		{
			case '0': $p8 = ' SELECTED'; break;
			case '1': $p9 = ' SELECTED'; break;
		}
		switch(PER_COM_DEL)
		{
			case '0': $p18 = ' SELECTED'; break;
			case '1': $p19 = ' SELECTED'; break;
		}

		switch(PER_RATE)
		{
			case '0': $p10 = ' SELECTED'; break;
			case '1': $p11 = ' SELECTED'; break;
		}

		for($i = -12 ; $i <= 12 ; $i++)
		{
			$tmp .= popt('adminboard.settings.option', array('value' => $i, 'title' => $i));
			if($i == -3 OR $i == 3 OR $i == 4 OR $i == 5 OR $i == 9)
				$tmp .= popt('adminboard.settings.option', array('value' => $i.'.5', 'title' => $i.'.5'));
		}
		$tmp_offset = str_replace('value="'.DATE_OFFSET.'"','value="'.DATE_OFFSET.'" SELECTED',$tmp);

		for($i = 0 ; $i < 6 ; $i++)
		{
			$tmp = 'v3'.$i;
			$$tmp = '';
		}
		$tmp = 'v3'.DEFAULT_TREE_DEPTH;
		$$tmp = ' SELECTED';

		$lang_list = str_replace('value="'.DEFAULT_LANG.'"','value="'.DEFAULT_LANG.'" SELECTED',str_replace('SELECTED', '', import_lang_list(1)));
		$theme_list = str_replace('value="'.DEFAULT_THEME.'"','value="'.DEFAULT_THEME.'" SELECTED',str_replace('SELECTED', '', import_theme_list(1)));

		return
			popt('adminboard.settings',
				array(
					'title' => TITLE,
					'sufixtitle' => SUFIXTITLE,
					'meta_desc' => META_DESC,
					'meta_keys' => META_KEYS,
					'fcrop' => FCROP,
					'dcrop' => DCROP,
					'wcrop' => WCROP,
					'thumbnail_max_dim' => THUMBNAIL_MAX_DIM,
					'preview_size' => PREVIEW_SIZE,
					'thumbnail_quality' => THUMBNAIL_QUALITY, 
					'search_items_ppage' => SEARCH_ITEMS_PPAGE, 
					'search_key_length_min' => SEARCH_KEY_LENGTH_MIN,
					'thumbfooter' => THUMBFOOTER,
					'img_per_page' => IMG_PER_PAGE,
					'gal_col' => GAL_COL,
					'gal_com_min_length' => GAL_COM_MIN_LENGTH,
					'gal_com_max_length' => GAL_COM_MAX_LENGTH,
					'gal_com_log_dsp' => GAL_COM_LOG_DSP,
					'gal_rate_min' => GAL_RATE_MIN,
					'gal_rate_max' => GAL_RATE_MAX,
					'date_format' => DATE_FORMAT,
					'server_offset' => $tmp_offset,
					'upl_max_filesize' => UPL_MAX_FILESIZE,
					'data_files_dir' => DATA_FILES_DIR,
					'alb_dir' => ALB_DIR,
					'title_max_length' => TITLE_MAX_LENGTH, 
					'title_min_length' => TITLE_MIN_LENGTH,
					'title_from_file_char' => stripslashes(TITLE_FROM_FILE_CHAR),
					'blog_per_page' => BLOG_PER_PAGE,
					'last_uploads_n' => LAST_UPLOADS_N,
					'recent_com_n' => RECENT_COM_N,
					'most_com_n' => MOST_COM_N,
					'most_view_n' => MOST_VIEW_N,
					'top_rated_n' => TOP_RATED_N,
					'anti_spam_delay' => ANTI_SPAM_DELAY,
					'search_limit' => SEARCH_LIMIT,
					'recaptcha_pubk' => RECAPTCHA_PUBK,
					'recaptcha_privk' => RECAPTCHA_PRIVK,
					'humanspam_nourls' => HUMANSPAM_NOURLS,
					'humanspam_nowords' => HUMANSPAM_NOWORDS,
					'humanspam_ipban' => HUMANSPAM_IPBAN,
					'mysql_host' => MYSQL_HOST,
					'mysql_username' => MYSQL_USERNAME,
					'mysql_password' => MYSQL_PASSWORD,
					'mysql_table' => MYSQL_TABLE,
					'theme_list' => $theme_list,
					'lang_list' => $lang_list,
					'v1' => $v1,
					'v2' => $v2,
					'v3' => $v3,
					'v4' => $v4,
					'v5' => $v5,
					'v6' => $v6,
					'v7' => $v7,
					'v8' => $v8,
					'v9' => $v9,
					'v10' => $v10,
					'v11' => $v11,
					'v12' => $v12,
					'v13' => $v13,
					'v14' => $v14,
					'v15' => $v15,
					'v16' => $v16,
					'v17' => $v17,
					'v18' => $v18,
					'v19' => $v19,
					'v20' => $v20,
					'v21' => $v21,
					'v22' => $v22,
					'v23' => $v23,
					'v24' => $v24,
					'v25' => $v25,
					'v26' => $v26,
					'v30' => $v30,
					'v31' => $v31,
					'v32' => $v32,
					'v33' => $v33,
					'v34' => $v34,
					'v35' => $v35,
					'v50' => $v50,
					'v51' => $v51,
					'v52' => $v52,
					'v53' => $v53,
					'v54' => $v54,
					'v55' => $v55,
					'v56' => $v56,
					'v57' => $v57,
					'v58' => $v58,
					'v59' => $v59,
					'v60' => $v60,
					'v61' => $v61,
					'v62' => $v62,
					'v63' => $v63,
					'v64' => $v64,
					'v65' => $v65,
					'p0' => $p0,
					'p1' => $p1,
					'p2' => $p2,
					'p3' => $p3,
					'p4' => $p4,
					'p5' => $p5,
					'p6' => $p6,
					'p7' => $p7,
					'p8' => $p8,
					'p9' => $p9,
					'p10' => $p10,
					'p11' => $p11,
					'p12' => $p12,
					'p13' => $p13,
					'p14' => $p14,
					'p15' => $p15,
					'p16' => $p16,
					'p17' => $p17,
					'p18' => $p18,
					'p19' => $p19,
					'REQUEST_URI' => $_SERVER['REQUEST_URI']
				)
			);
	}

	function httplog()
	{
		$dest = DATA_FILES_DIR.'upload.log';

		if(file_exists($dest))
		{
			$contents = convert_ulog();
			$log_tmp = explode("\n",trim($contents));
			$log = array_Reverse($log_tmp);
			$ic = count($log);

			if(!$_GET['page'])
				$_GET['page'] = 1;

			$page = $_GET['page']-1;
			$perpage = 15;
			$init = $page*$perpage;

			$inite = ($page+1)*$perpage;
			if($inite > $ic)
				$inite = $ic;

			$pages = $ic/$perpage;

			for( $i = $init ; $i < $inite ; $i++)
			{
				$value = $log[$i];
				list($filename,$path,$uname,$ip,$sfv,$date) = decode_ulog($value);

				$tmp = $path.$filename;
				if(strlen($tmp) > 20)
					$tmp = '<span title="'.$path.$filename.'">'.substr($tmp,0,20).'..</span>';

				if(file_exists(DATA_FILES_DIR.$sfv.'.jpg'))
					$testi = getimagesize(DATA_FILES_DIR.$sfv.'.jpg');

				$st = 'last_web_upl_w';
				if($testi[1] > $testi[0])
					$st = 'last_web_upl_h';

				if(file_exists(DATA_FILES_DIR.$sfv.'.jpg') AND file_exists(ALB_DIR.$path.$filename))
					$source = DATA_FILES_DIR.$sfv.'.jpg';
				else
					$source = popt('adminboard.httplog.invalid_thumb', '');

				$out .=
					popt('adminboard.httplog.item',
						array(
							'st' => $st,
							'sfv' => $sfv,
							'source' => $source,
							'location' => $tmp,
							'uname' => $uname,
							'ip' => $ip,
							'date' => tdat($date)
						)
					);
			}
		}
		return
			popt('adminboard.httplog',
				array(
					'items' => $out,
					'pagelist' => page_list($ic, $_GET['page'], $pages, $perpage, '?load=adminboard&mode=2')
				)
			);
	}


	function news()
	{
		global $THEME;
		$con = @file_get_contents(DATA_FILES_DIR.'news.log');
		if($con)
		{
			$lines = explode("\n",$con);
			$ex_news = parse_news($lines,0);
		}
		return
			popt('adminboard.news',
				array('THEME' => str_replace("'",'_QUOTE_',$THEME),
					'ex_news' => $ex_news
				)
			);
	}


	function editor()
	{
		global $THEME;		

		if($_POST['nlang'] AND $_POST['ltitle'])
		{
			$towrite = '';
			for($i=1 ; $i< 335 ; $i++)
				$towrite .= '$L['.$i.'] = \'\';'."\r\n";

			$towrite = '<?php

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
#  MAGNIFICA WEB SCRIPTS - ANIMA GALLERY 2.5.0 - HTTP://DG.NO.SAPO.PT  #
#			LICENSE: FREE FOR NON COMMERCIAL USE                 #
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#

'.$towrite.'

?>';

			write_data('languages/'.$_POST['ltitle'].'.php', $towrite, 'w');			
		}

		if($_POST['ulang'])
		{
			$target = $_POST['target'];
			include('languages/english.php');
			$total = count($L);
			for($i = 1 ; $i <= $total ; $i++)
				$towrite .= '$L['.$i.'] = \''.str_replace("'","\'",stripslashes($_POST[$i])).'\';'."\r\n";

			$towrite = '<?php

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
#  MAGNIFICA WEB SCRIPTS - ANIMA GALLERY 2.5.0 - HTTP://DG.NO.SAPO.PT  #
#			LICENSE: FREE FOR NON COMMERCIAL USE                 #
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#

'.$towrite.'

?>';
			write_data('languages/'.$target.'.php', $towrite, 'w');
		}

		$lang_list = array();
		foreach(glob('languages/*') as $item)
		{
			$item = basename($item);
			if($item != '.' AND $item != '..')
				$lang_list[strtolower($item)] = $item;
		}
		ksort($lang_list);

		foreach($lang_list as $key => $value)
		{
			$del = $edit = '';
			if(!preg_match("/^english/i", $value))
			{
				$edit = popt('adminboard.editor.edit', array('id' => $value, 'theme' => str_replace("'",'_QUOTE_',$THEME)));
				$del = popt('adminboard.editor.del', array('id' => $value, 'theme' => str_replace("'",'_QUOTE_',$THEME)));
			}
			$out .= popt('adminboard.editor.item', array('id' => $value, 'edit' => $edit, 'del' => $del));
		}

		return
			popt('adminboard.editor', array('list' => str_replace('.php','',$out)))
		;
	}

	$curr0 = $curr1 = $curr2 = $curr3 = $curr4 = '';
	global $L;

	if(!isadmin())
	{
		$curr0 = ' id="current"';
		$TITLE = $L[95];
		$out = ablogin();
	}
	else
	{
		switch($_GET['mode'])
		{
			case '0':
				$curr0 = ' id="current"';
				$TITLE = $L[276];
				$out = ablogout();
			break;
			case '1':
				$curr1 = ' id="current"';
				$TITLE = $L[162];
				$out = settings();
			break;
			case '2':
				$curr2 = ' id="current"';
				$TITLE = $L[163];
				$out = httplog();
			break;
			case '3':
				$curr3 = ' id="current"';
				$TITLE = $L[334];
				$out = editor();
			break;
			case '4':
				$curr4 = ' id="current"';
				$TITLE = $L[344];
				$out = news();
			break;
			default:
				$curr1 = ' id="current"';
				$TITLE = $L[162];
				$out = settings();
			break;
		}
	}

	$mode = $L[95];
	if($_SESSION['des_pass'])
		$mode = $L[276];

	return
		popt('adminboard',
			array(
				'curr0' => $curr0,
				'curr1' => $curr1,
				'curr2' => $curr2,
				'curr3' => $curr3,
				'curr4' => $curr4,
				'contents' => $out,
				'mode' => $mode
			)
		);
}

function pfancy($text,$type,$is_comment)
{
	$ic = '';
	if($is_comment)
		$ic = 'y';

	$tl = "tl"; $tr = "tr";
	$bl = "bl"; $br = "br";

	switch($type)
	{
		case "1": break; 
		case "2": $tl = "tls"; $tr = "trs"; break;
		case "3": $bl = "bls"; $br = "brs"; break;
		case "4": $tl = "tls"; $tr = "trs"; $bl = "bls"; $br = "brs"; break;
	}

	return
		popt('pfancy',
			array(
				'ic' => $ic,
				'bl' => $bl,
				'br' => $br,
				'tl' => $tl,
				'tr' => $tr,
				'contents' => $text
			)
		);

}

function mktitle($str)
{
	$badchars = array('-', '_', '(', ')', '&', '/', '\\', "'", '%', ';', ',', '?', '!', ':', '*', '$', '\"');
	$rep = array(' ', ' ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
	$str = str_replace($badchars,$rep,$str);

	$keyw = '';

	$list = explode(" ",$str);
	for($i = 0 ; $i < count($list) ; $i++)
	{
		if(strlen($list[$i]) >= 1 OR intval($list[$i]))
		{
			$keyw .= $list[$i];
			if($i<count($list)-1)
				$keyw .= '-';
		}
	}

	return(urlencode(rtrim($keyw,'-')));

}

function read_pn($dir,$file)
{
	$dir = $dir.'/';
	$cdi_sfv = sfv_check($dir);
	$darr = $darr2 = $darr3 = array();

	$sc = SORT_CLASS;
	if(isset($_COOKIE['anima_sort_m']) AND USER_OPTIONS)
		$sc = $_COOKIE['anima_sort_m'];

	$st = SORT_TYPE;
	if(isset($_COOKIE['anima_sort_ord']) AND USER_OPTIONS)
		$st = $_COOKIE['anima_sort_ord'];

	$ic = 0;
	$handle2 = @opendir($dir);
	if ($handle2 == false) return -1;
	while (($file2 = @readdir($handle2)) != false)
	{
		if($file2 != "." AND $file2 != "..")
		{
			if(!is_dir($dir.$file2))
			{ 
				if(isimg($file2))
				{
					if(testi($file2,1))
					{
						$darr2[$ic] = $darr3[$ic] = '';
						if($sc == '2')
							$darr2[$ic] = filemtime($dir.$file2);

						if($sc == '3')
						{
							$fsfv = sfv_check($dir.$file2);
							if(file_exists(DATA_FILES_DIR.$fsfv.'.df'))
							{
								$attr = read_attr(explode("\n",file_get_contents(DATA_FILES_DIR.$fsfv.'.df')));
								$darr3[$ic] = $attr[9];
							}
						}
						if($darr[$ic] = $file2)
							$ic++;
					}
				}
			}
		}
	}
	@closedir($handle2);

	switch($sc)
	{
		case '1': $v1 = $darr; $v2 = $darr2; break;
		case '2': $v1 = $darr2; $v2 = $darr; break;
		case '3': $v1 = $darr3; $v2 = $darr; break;
	}
	
	switch(SORT_METHOD)
	{
		case "SORT_REGULAR": 
			switch($st)
			{
				case "SORT_ASC": array_multisort($v1,SORT_REGULAR,SORT_ASC,$v2); break;
				case "SORT_DESC": array_multisort($v1,SORT_REGULAR,SORT_DESC,$v2); break;
			} break;
		case "SORT_NUMERIC": 
			switch($st)
			{
				case "SORT_ASC": array_multisort($v1,SORT_NUMERIC,SORT_ASC,$v2); break;
				case "SORT_DESC": array_multisort($v1,SORT_NUMERIC,SORT_DESC,$v2); break;
			} break;
		case "SORT_STRING": 
			switch($st)
			{
				case "SORT_ASC": array_multisort($v1,SORT_STRING,SORT_ASC,$v2); break;
				case "SORT_DESC": array_multisort($v1,SORT_STRING,SORT_DESC,$v2); break;
			} break;
	}

	if($sc == '1')
	{
		$curr = array_search($file,$v1);
		return
			array(
				$v1[$curr-1],
				$v1[$curr+1],
				$curr+1,
				count($v1)
			)
		;
	}
	else
	{
		$curr = array_search($file,$v2);
		return
			array(
				$v2[$curr-1],
				$v2[$curr+1],
				$curr+1,
				count($v2)
			)
		;
	}
}

function makeRandom()
{
	$salt = "abchefghjkmnpqrstuvwxyz0123456789";
	srand((double)microtime()*1000000);
	$i = 0;
      while ($i <= 3)
	{
		$num = rand() % 33;
		$tmp = substr($salt, $num, 1);
		$pass = $pass . $tmp;
		$i++;
	}
	return $pass;
}

function captcha_init($mode)
{
	$captcha = '';
	if(captcha_req($mode))
	{
		if(!trim(RECAPTCHA_PUBK) OR !trim(RECAPTCHA_PRIVK))
			$captcha = popt('captcha', '');
		else
		{
			require_once('recaptchalib.php');
  			$captcha = recaptcha_get_html(RECAPTCHA_PUBK);
		}
	}

	return $captcha;
}

function captcha_req($mode)
{
	$inivar = 0;
	switch($mode)
	{
		case 'blog': if(CAPTCHA_BLOG) $inivar = 1; break;
		case 'com': if(CAPTCHA_COM) $inivar = 1; break;
		case 'upl': if(CAPTCHA_UPL) $inivar = 1; break;
		case 'log': if(CAPTCHA_LOG) $inivar = 1; break;
	}

	$status = 1;
	if(isadmin() OR !function_exists('imageline') OR !function_exists('imagettftext') OR !$inivar)
		$status = 0;

	return($status);
}

function captcha_print()
{
	header('Content-type: image/png');

	$im = imagecreatetruecolor(50, 25);
	$font = 'verdana';
	$white = imagecolorallocate($im, 237, 239, 245);
	$black = imagecolorallocate($im, 0, 0, 0);
	$noise_color = imagecolorallocate($im, 100, 120, 180);
	imagefill($im, 0, 0, $white);

	$width = 50; $height = 25;
	for( $i=0 ; $i<($width*$height)/150 ; $i++ )
	{
         imageline($im, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
      }

	$code = makerandom();
	$_SESSION['captcha'] = $code;



	imagettftext($im, 12, 5, 5, 20, $black, $font, $code);
	imagepng($im);
	imagedestroy($im);

}

function captcha_check($mode)
{
	$captcha_req = captcha_req($mode);

	if(!RECAPTCHA_PUBK OR !RECAPTCHA_PRIVK)
	{
		if($captcha_req AND ($_POST['captcha'] != $_SESSION['captcha'] OR !$_POST['captcha'] OR !$_SESSION['captcha']))
		{
			$_SESSION['captcha'] = '';
			return popt('msg.7', '');
		}
		else
		{
			if($captcha_req)
				$_SESSION['captcha'] = '';
			return '';
		}
	}
	elseif($captcha_req)
	{
		require_once('recaptchalib.php');
 		$resp =
			recaptcha_check_answer (
				RECAPTCHA_PRIVK,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);

  		if (!$resp->is_valid)
    			return popt('msg.7', '');
		else
    			return '';
	}
}

function corr_or()
{
	if(PREVIEW_LINK != '2')
	{
		header('Content-type: image/jpeg');

		$sfv = $_GET['sfv'];
		$contents = file(DATA_FILES_DIR.$sfv.'.df');
		$source = trim($contents[0]);

		$w = $_GET['w'];
		$h = $_GET['h'];

		if($w > $h)
			$maxd = $h;
		else
			$maxd = $w;

		tn($source,'',$maxd);
	}

}

function rcom_init()
{
	if(!isadmin())
	{
		header('location: ?load=adminboard');
		exit();
	}

	$comments = array();

	if(file_exists(DATA_FILES_DIR.'disabled.log'))
		$disabled = explode("\n",trim(file_get_contents(DATA_FILES_DIR.'disabled.log')));
	else
		$disabled = array();

	$dir_items = glob(DATA_FILES_DIR.'*.df');
	if(!$dir_items)
		$dir_items = array();
	foreach($dir_items as $filename)
	{
		$lines = explode("\n", file_get_contents($filename));
		if(isimg($lines[0]))
		{
			if(!in_array(str_replace(ALB_DIR,'',dirname($lines[0])),$disabled))
			{
				$attr = read_attr($lines);
				for($i=2; $i < intval($attr[2])+2; $i++)
				{
					if(trim($lines[$i]))
					{
						list($name,$comment,$time,$ip) = explode('|', $lines[$i]);
						$comments[$time] =
							array(
								$name,
								$comment,
								$time,
								basename($filename)
							);
					}
				}
			}
		}
		elseif(isblog(basename($filename)))
		{
			list($count,$tmp) = explode('-',$lines[0],2);
			if($count)
			{
				for($i=1;$i<count($lines);$i++)
				{
					if(trim($lines[$i]))
					{
						list($comment,$name,$ip,$time) = explode('-',trim($lines[$i]));
						$comments[$time] =
							array(
								$name,
								$comment,
								$time,
								basename($filename)
							);
					}
				}
			}
		}
	}
	krsort($comments);

	if(count($comments))
	{
		$i = 0;
		foreach($comments as $key => $value)
		{
			$i++;
			$data .= $comments[$key][0].'|'.$comments[$key][1].'|'.$comments[$key][3].'|'.$comments[$key][2]."\n";
			if($i >= RECENT_COM_N)
				break;
		}
	}
	write_data(DATA_FILES_DIR.'rcom.log', $data, 'w');

	header('location: '.$_SERVER['HTTP_REFERER']);
	exit;

}


function wrap_long($text)
{
	$words = explode(" ",str_replace("\n",' ',$text));

	$arr = array();
	foreach($words as $key => $value)
	{
		if(strlen($value) > WCROP AND WCROP AND !preg_match("/src=|href=/i", $value))
		{
			$text = str_replace($value,'[[_*'.$key.'*_]]',$text);
			$arr[$key] = '<span title="'.$value.'">'.substr($value,0,25).'</span>.. ';
		}
	}

	foreach($arr as $key => $value)
	{
		$text = str_replace('[[_*'.$key.'*_]]', $value, $text);
	}

	return($text);

}

function rcom_list()
{	
	$data = file(DATA_FILES_DIR.'rcom.log');
	
	if(count($data))
	{
		$i = 0;
		foreach($data as $key => $value)
		{
			list($name,$com,$file,$time) = explode('|',trim($value));

			if(file_exists(DATA_FILES_DIR.$file))
			{
				$i++;
				if(strstr($file,'blog_'))
				{
					$link1 = '?load=blog&id='.str_replace(array('blog_','.df'),array('',''),$file);
					$link2 = popt('rcom.blog_thumb', '');
				}
				else
				{
					$link1 = '?load=prev&id='.str_replace('.df','',$file);
					$link2 = DATA_FILES_DIR.str_replace('.df','',$file).'.jpg';

					if(!file_exists($link2))
						$link2 = popt('rcom.invalid_thumb', '');
				}

				$style = '';

				if(file_exists($link2))
					$dat = getimagesize($link2);
				else
					$dat = array('50', '50');

				if($dat[0] >= $dat[1])
					$style = 'rcom_w';
				else
					$style = 'rcom_h';

				$border = '';
				if($key < count($data)-1 AND $key < RECENT_COM_N-1)
					$border = 'bdb';

				$user = '';
				if($name)
					$user = popt('rcom.item.user', array('user' => base64_decode($name)));

				$com = nl2br(base64_decode($com));
				if(strlen($com) > GAL_COM_LOG_DSP AND GAL_COM_LOG_DSP)
				{
					
					$com = popt('rcom.item.cropped', array('key' => $key, 'cropped' => wrap_long(substr($com,0,GAL_COM_LOG_DSP)), 'full' => trim($com)));
				}
				else
					$com = wrap_long(stripslashes($com));

				$out .=
					popt('rcom.item',
						array(
							'border' => $border,
							'link1' => $link1,
							'link2' => $link2,
							'com' => nl2br($com),
							'user' => $user,
							'date' => tdat($time),
							'style' => $style
						)
					);

				if($i >= RECENT_COM_N)
					break;
			}
		}
		if(!$i)
			$out = popt('rcom.void', '');
	}
	else
		$out = popt('rcom.void', '');

	return($out);
}

function rcom_upd($data)
{
	$contents = file(DATA_FILES_DIR.'rcom.log');

	$i = 0;
	foreach($contents as $key => $value)
	{
		$i++;
		$out .= trim($value)."\n";
		if($i >= RECENT_COM_N-1)
			break;
	}
	write_data(DATA_FILES_DIR.'rcom.log', $data."\n".trim($out), 'w');
}

function mcom_init()
{
	if(!isadmin())
	{
		header('location: ?load=adminboard');
		exit();
	}

	$mcom = $mcom2 = array();

	if(file_exists(DATA_FILES_DIR.'disabled.log'))
		$disabled = explode("\n",trim(file_get_contents(DATA_FILES_DIR.'disabled.log')));
	else
		$disabled = array();

	$dir_items = glob(DATA_FILES_DIR.'*.df');
	if(!$dir_items)
		$dir_items = array();
	foreach($dir_items as $filename)
	{
		if(!isblog(basename($filename)))
		{
			$lines = explode("\n",file_get_contents($filename));
			if(!in_array(str_replace(ALB_DIR,'',dirname($lines[0])),$disabled) AND isimg($lines[0]))
			{
				$attr = read_attr($lines);
				if(intval($attr[2]) != '0')
				{
					$mcom[] =
						str_replace(
							DATA_FILES_DIR,
							'',
							$filename
						).'|'.$attr[2]."\n";
					$mcom2[] = $attr[2];
				}
			}
		}
	}
	array_multisort($mcom2,SORT_NUMERIC,SORT_DESC,$mcom);

	if(count($mcom))
	{
		$i=0;
		foreach($mcom as $key => $value)
		{
			$i++;
			$data .= $mcom[$key];
			if($i >= MOST_COM_N)
				break;
		}
	}
	write_data(DATA_FILES_DIR.'mcom.log', $data, 'w');

	header('location: '.$_SERVER['HTTP_REFERER']);
	exit;

}

function mcom_list()
{	
	$data = file(DATA_FILES_DIR.'mcom.log');
	
	if(count($data))
	{
		$i = 0;
		foreach($data as $key => $value)
		{
			list($file,$cn) = explode('|',trim($value));

			if(file_exists(DATA_FILES_DIR.$file))
			{
				$i++;
				$link1 = '?load=prev&id='.str_replace('.df','',$file);
				$link2 = DATA_FILES_DIR.str_replace('.df','',$file).'.jpg';

				if(!file_exists($link2))
					$link2 = popt('mcom.invalid_thumb', '');

				$style = '';

				if(file_exists($link2))
					$dat = getimagesize($link2);
				else
					$dat = array('50', '50');

				if($dat[0] >= $dat[1])
					$style = 'mcom_w';
				else
					$style = 'mcom_h';

				$border = '';
				if($key < count($data)-1 AND $key < MOST_COM_N-1)
					$border = 'bdt';

				$out .=
					popt('mcom.item',
						array(
							'border' => '',
							'link1' => $link1,
							'link2' => $link2,
							'style' => $style,
							'cn' => $cn
						)
					);

				if($i >= MOST_COM_N)
					break;
			}
		}
		if(!$i)
			$out = popt('mcom.void', '');
	}
	else
		$out = popt('mcom.void', '');

	return($out);
}

function mcom_upd($data)
{
	list($file,$cn) = explode('|',$data);

	$contents = file(DATA_FILES_DIR.'mcom.log');

	list($file2,$cn2) = explode('|',trim($contents[count($contents)-1]));

	if($cn > $cn2 OR count($contents) < MOST_COM_N)
	{
		$match = -1;
		foreach($contents as $key => $value)
		{
			list($file3,$cn3) = explode('|',$value);
			if(str_replace('.df','',$file) == str_replace('.df','',$file3))
				$match = $key;
		}

		if($match == -1)
			array_push($contents,$data);
		else
			$contents[$match] = $data;
	}

	foreach($contents as $key => $value)
	{
		list($file,$cn) = explode('|',$value);
		$mcom[$key] = $cn;
	}

	array_multisort($mcom,SORT_NUMERIC,SORT_DESC,$contents);

	$i = 0; $out = '';
	foreach($contents as $key => $value)
	{
		$i++;
		$out .= trim($value)."\n";
		if($i >= MOST_COM_N)
			break;
	}
	write_data(DATA_FILES_DIR.'mcom.log', $out, 'w');

}

function mview_init()
{
	if(!isadmin())
	{
		header('location: ?load=adminboard');
		exit();
	}

	$mview = $mview2 = array();

	if(file_exists(DATA_FILES_DIR.'disabled.log'))
		$disabled = explode("\n",trim(file_get_contents(DATA_FILES_DIR.'disabled.log')));
	else
		$disabled = array();

	$dir_items = glob(DATA_FILES_DIR.'*.df');
	if(!$dir_items)
		$dir_items = array();
	foreach($dir_items as $filename)
	{
		if(!isblog(basename($filename)))
		{
			$lines = explode("\n",file_get_contents($filename));
			if(!in_array(str_replace(ALB_DIR,'',dirname($lines[0])),$disabled) AND isimg($lines[0]))
			{
				$attr = read_attr($lines);
				if(intval($attr[1]) != '0')
				{
					$mview[] =
						str_replace(
							DATA_FILES_DIR,
							'',
							$filename
						).'|'.$attr[1]."\n";
					$mview2[] = $attr[1];
				}
			}
		}
	}
	array_multisort($mview2,SORT_NUMERIC,SORT_DESC,$mview);

	if(count($mview))
	{
		$i=0;
		foreach($mview as $key => $value)
		{
			$i++;
			$data .= $mview[$key];
			if($i >= MOST_VIEW_N)
				break;
		}
	}
	write_data(DATA_FILES_DIR.'mview.log', $data, 'w');

	header('location: '.$_SERVER['HTTP_REFERER']);
	exit;

}

function mview_list()
{	
	$data = file(DATA_FILES_DIR.'mview.log');
	
	if(count($data))
	{
		$i = 0;
		foreach($data as $key => $value)
		{
			list($file,$cn) = explode('|',trim($value));

			if(file_exists(DATA_FILES_DIR.$file))
			{
				$i++;
				$link1 = '?load=prev&id='.str_replace('.df','',$file);
				$link2 = DATA_FILES_DIR.str_replace('.df','',$file).'.jpg';

				if(!file_exists($link2))
					$link2 = popt('mview.invalid_thumb', '');

				$style = '';

				if(file_exists($link2))
					$dat = getimagesize($link2);
				else
					$dat = array('50', '50');

				if($dat[0] >= $dat[1])
					$style = 'mview_w';
				else
					$style = 'mview_h';

				$border = '';
				if($key < count($data)-1 AND $key < MOST_VIEW_N-1)
					$border = 'bdt';

				$out .=
					popt('mview.item',
						array(
							'border' => '',
							'link1' => $link1,
							'link2' => $link2,
							'style' => $style,
							'cn' => $cn
						)
					);

				if($i >= MOST_VIEW_N)
					break;
			}
		}
		if(!$i)
			$out = popt('mview.void', '');
	}
	else
		$out = popt('mview.void', '');

	return($out);
}

function mview_upd($data)
{
	list($file,$cn) = explode('|',$data);

	$contents = file(DATA_FILES_DIR.'mview.log');

	list($file2,$cn2) = explode('|',trim($contents[count($contents)-1]));

	if($cn > $cn2 OR count($contents) < MOST_VIEW_N)
	{
		$match = -1;
		foreach($contents as $key => $value)
		{
			list($file3,$cn3) = explode('|',$value);
			if(str_replace('.df','',$file) == str_replace('.df','',$file3))
				$match = $key;
		}

		if($match == -1)
			array_push($contents,$data);
		else
			$contents[$match] = $data;
	}

	foreach($contents as $key => $value)
	{
		list($file,$cn) = explode('|',$value);
		$mview[$key] = $cn;
	}

	array_multisort($mview,SORT_NUMERIC,SORT_DESC,$contents);

	$i = 0; $out = '';
	foreach($contents as $key => $value)
	{
		$i++;
		$out .= trim($value)."\n";
		if($i >= MOST_VIEW_N)
			break;
	}
	write_data(DATA_FILES_DIR.'mview.log', $out, 'w');

}

function trated_init()
{
	if(!isadmin())
	{
		header('location: ?load=adminboard');
		exit();
	}

	$ratings = $ratings2 = array();

	if(file_exists(DATA_FILES_DIR.'disabled.log'))
		$disabled = explode("\n",trim(file_get_contents(DATA_FILES_DIR.'disabled.log')));
	else
		$disabled = array();

	$dir_items = glob(DATA_FILES_DIR.'*.df');
	if(!$dir_items)
		$dir_items = array();
	foreach($dir_items as $filename)
	{
		if(!isblog(basename($filename)))
		{
			$lines = explode("\n",file_get_contents($filename));
			if(!in_array(str_replace(ALB_DIR,'',dirname($lines[0])),$disabled) AND isimg($lines[0]))
			{
				$attr = read_attr($lines);
				$rated = $attr[3];
				$raten = $attr[4];
			
				if(intval($raten) != '0')
				{
					$val = number_format($rated/$raten,1,'.','');

					$rid = ($val*10000)+$raten;
					$ratings[] =
						str_replace(
							DATA_FILES_DIR,
							'',
							$filename
						).'|'.$val.'|'.$raten."\n";
					$ratings2[] = $rid;
				}
			}
		}
	}
	array_multisort($ratings2,SORT_NUMERIC,SORT_DESC,$ratings);

	if(count($ratings))
	{
		$i=0;
		foreach($ratings as $key => $value)
		{
			$i++;
			$data .= $ratings[$key];
			if($i >= TOP_RATED_N)
				break;
		}
	}
	write_data(DATA_FILES_DIR.'trated.log', $data, 'w');

	header('location: '.$_SERVER['HTTP_REFERER']);
	exit;

}

function trated_list()
{	
	$data = file(DATA_FILES_DIR.'trated.log');
	
	if(count($data))
	{
		$i2 = 0;
		foreach($data as $key => $value)
		{
			list($file,$val,$raten) = explode('|',trim($value));

			if(file_exists(DATA_FILES_DIR.$file))
			{
				$i2++;
				$border = '';
				if($key < count($data)-1 AND $key < TOP_RATED_N-1)
					$border = 'bdb';

				$link1 = '?load=prev&id='.str_replace('.df','',$file);
				$link2 = DATA_FILES_DIR.str_replace('.df','',$file).'.jpg';

				if(!file_exists($link2))
					$link2 = './graph/sig_info.png';

				$style = '';

				if(file_exists($link2))
					$dat = getimagesize($link2);
				else
					$data = array('50','50');

				if($dat[0] >= $dat[1])
					$style = 'trated_w';
				else
					$style = 'trated_h';

				$stars = '';
				for($i=0 ; $i < intval($val) ; $i++)
					$stars .= popt('parserate.star', '');

				if(intval($val) < $val)
					$stars .= popt('parserate.star_half', '');

				$out .=
					popt('trated.item',
						array(
							'border' => $border,
							'link1' => $link1,
							'link2' => $link2,
							'rate' => str_replace('.0','',$val),
							'raten' => $raten,
							'stars' => $stars,
							'style' => $style
						)
					);

				if($i2 >= TOP_RATED_N)
					break;
			}
		}
		if(!$i2)
			$out = popt('trated.void', '');
	}
	else
		$out = popt('trated.void', '');

	return($out);
}

function trated_upd($data)
{
	list($file,$rate,$raten) = explode('|',$data);

	$contents = file(DATA_FILES_DIR.'trated.log');

	list($file2,$rate2,$raten2) = explode('|',$contents[count($contents)-1]);

	if(($rate*10000+$raten) > ($rate2*10000+$raten2) OR count($contents) < TOP_RATED_N)
	{
		$match = -1;
		foreach($contents as $key => $value)
		{
			list($file3,$rate3,$raten3) = explode('|',$value);
			if($file == $file3)
				$match = $key;
		}

		if($match == -1)
			array_push($contents,$data);
		else
			$contents[$match] = $data;
	}

	foreach($contents as $key => $value)
	{
		list($file,$rate,$raten) = explode('|',$value);
		$ratings2[$key] = ($rate*10000)+$raten;
	}

	array_multisort($ratings2,SORT_NUMERIC,SORT_DESC,$contents);

	$i = 0; $out = '';
	foreach($contents as $key => $value)
	{
		$i++;
		$out .= trim($value)."\n";
		if($i >= TOP_RATED_N)
			break;
	}
	write_data(DATA_FILES_DIR.'trated.log', $out, 'w');

}

function sysinfo()
{
	if(isadmin())
	{
		$gd = '-';
		if(function_exists('gd_info'))
		{
			$tmp = gd_info();
			$gd = $tmp['GD Version'];
		}

		$formats = '-';
		if(function_exists('imagecreatefromgif'))
			$formt .= 'GIF';
		if(function_exists('imagecreatefromjpeg'))
			$formt .= ' JPG';
		if(function_exists('imagejpeg'))
			$formt .= ' JPGx';
		if(function_exists('imagecreatefrompng'))
			$formt .= ' PNG';

		$exif = '-';
		if(function_exists('exif_read_data'))
		{
			$exif = 'Info';
			if(function_exists('imagerotate'))
				$exif .= '; Rotate';
		}
		$captcha = 'OK';
		if(!function_exists('imageline') OR !function_exists('imagettftext'))
			$captcha = '-';

		if($formt)
			$formats = $formt;

		$upl_lim = ini_get('upload_max_filesize');
		if(!$upl_lim)
			$upl_lim = '-';
		else
			$upl_lim .= 'B';

		return
			popt('sysinfo',
				array(
					'formats' => $formats,
					'gd' => $gd,
					'php_ver' => 'v'.phpversion(),
					'exif' => $exif,
					'captcha' => $captcha,
					'upl_lim' => $upl_lim
				)
			);
	}
}


function convert_ulog()
{
	if(file_exists(DATA_FILES_DIR.'upload.log'))
	{
		$contents = file_get_contents(DATA_FILES_DIR.'upload.log');

		if(!strstr($contents, '|'))
		{
			$data = '';
			$lines = explode("\n", $contents);
			foreach($lines as $key => $value)
			{
				if(trim($value))
				{
					if(!strstr($value, '|'))
					{
						list($cname,$dir,$user_name,$ip,$sfv,$time) = explode('|', stripslashes(base64_decode(trim($value))));
						$data .= 'n-'.base64_encode($cname).'|d-'.base64_encode($dir).'|u-'.base64_encode($user_name).'|i-'.base64_encode($ip).'|s-'.base64_encode($sfv).'|t-'.base64_encode($time)."\n";
					}
					else
						$data .= trim($value)."\n";
				}
			}
			if($data)
				write_data(DATA_FILES_DIR.'upload.log', $data, 'w');
		}
		return($contents);
	}
}

function decode_ulog($str)
{
	list($filename,$path,$uname,$ip,$sfv,$date) = explode("|",str_replace(array('n-','d-','u-','i-','s-','t-'),array('','','','','',''),trim($str,"|")));

	return
		array(
			base64_decode($filename),
			base64_decode($path),
			base64_decode($uname),
			base64_decode($ip),
			base64_decode($sfv),
			base64_decode($date)
		);
}

function encode_ulog($filename,$path,$uname,$ip,$sfv,$date)
{
	return 'n-'.base64_encode($filename).'|'.'d-'.base64_encode($path).'|'.'u-'.base64_encode($uname).'|'.'i-'.base64_encode($ip).'|'.'s-'.base64_encode($sfv).'|'.'t-'.base64_encode($date)."\n";
}

function update_ulog($fname,$dir,$sfv,$nfname,$ndir)
{
	if(file_exists(DATA_FILES_DIR.'upload.log'))
	{
		if($dir)
			$dir = trim(stripslashes($dir),'/\\').'/';
		if($ndir)
			$ndir = trim(stripslashes($ndir),'/\\').'/';

		$contents = convert_ulog();

		$arr1 = $arr2 = '';
		if($dir)
		{
			$arr1[] = 'd-'.base64_encode($dir);
			$arr2[] = 'd-'.base64_encode($ndir);
		}
		if($fname AND $fname != $nfname)
		{
			$arr1[] = 'n-'.base64_encode($fname);
			$arr2[] = 'n-'.base64_encode($nfname);
		}
		if(!$fname)
		{
			$lines = explode("\n", $contents);
			foreach($lines as $key => $value)
			{
				if(strstr($value, 'd-'.base64_encode($dir)))
				{
					list($tmp1,$tmp2,$tmp3,$tmp4,$tmp5,$tmp6) = explode('|', trim($value));
					$arr1[] = $tmp5;
					$arr2[] = 's-'.base64_encode(sfv_check(ALB_DIR.$ndir.base64_decode(str_replace('n-','',$tmp1))));
				}
			}
		}
		else
		{
			$arr1[] = 's-'.base64_encode($sfv);
			$arr2[] = 's-'.base64_encode(sfv_check(ALB_DIR.$ndir.$nfname));
		}

		write_data(DATA_FILES_DIR.'upload.log', str_replace($arr1,$arr2,$contents), 'w');
	}
}


function update_ulog_batch($arr)
{
	if(file_exists(DATA_FILES_DIR.'upload.log'))
	{
		$contents = convert_ulog();

		foreach($arr as $key => $value)
		{
			$fname = $value[0];
			$dir = $value[1];
			$sfv = $value[2];
			$nfname = $value[3];
			$ndir = $value[4];

			if($dir)
				$dir = trim(stripslashes($dir),'/\\').'/';
			if($ndir)
				$ndir = trim(stripslashes($ndir),'/\\').'/';

			$arr1 = $arr2 = '';
			if($dir)
			{
				$arr1[] = 'd-'.base64_encode($dir);
				$arr2[] = 'd-'.base64_encode($ndir);
			}
			if($fname AND $fname != $nfname)
			{
				$arr1[] = 'n-'.base64_encode($fname);
				$arr2[] = 'n-'.base64_encode($nfname);
			}
			if(!$fname)
			{
				$lines = explode("\n", $contents);
				foreach($lines as $key => $value)
				{
					if(strstr($value, 'd-'.base64_encode($dir)))
					{
						list($tmp1,$tmp2,$tmp3,$tmp4,$tmp5,$tmp6) = explode('|', trim($value));
						$arr1[] = $tmp5;
						$arr2[] = 's-'.base64_encode(sfv_check(ALB_DIR.$ndir.base64_decode(str_replace('n-','',$tmp1))));
					}
				}
			}
			else
			{
				$arr1[] = 's-'.base64_encode($sfv);
				$arr2[] = 's-'.base64_encode(sfv_check(ALB_DIR.$ndir.$nfname));
			}

			$contents = str_replace($arr1,$arr2,$contents);
		}
		write_data(DATA_FILES_DIR.'upload.log', $contents, 'w');
	}
}

function get_title_desc($str)
{
	$lines = explode("\n",$str);
	$attr = explode('-',$lines[1]);

	return(array(base64_decode($attr[10]),base64_decode($attr[11])));
}

function write_data($target,$data,$mode)
{
	if(!file_exists($target) AND $mode == 'a')
		touch($target);
	$handle = @fopen($target,$mode);
	@fwrite($handle,$data);
	@fclose($handle);
}

function import_theme_lang()
{
	$THEME = DEFAULT_THEME;
	if(isset($_COOKIE['theme']) AND !THEME_LOCKED)
		$THEME = $_COOKIE['theme'];

	$LANG = DEFAULT_LANG;
	if(isset($_COOKIE['lang']) AND @file_exists('languages/'.$_COOKIE['lang'].'.php') AND !LANG_LOCKED)
		$LANG = $_COOKIE['lang'];

	return(array($THEME, $LANG));
}

function import_log($mode)
{
	switch($mode)
	{
		case 'rcom':
			$RCOM = '';
			if(RECENT_COM)
			{
				if(!file_exists(DATA_FILES_DIR.'rcom.log'))
					$tmp = popt('rcom.init', '');
				else
					$tmp = rcom_list();

				$rcom_refresh = '';
				if(isadmin())
					$rcom_refresh = popt('rcom.refresh', '');

				$RCOM = popt('rcom', array('contents' => $tmp, 'refresh' => $rcom_refresh));
			}
			return($RCOM);
		break;
		case 'mcom':
			$MCOM = '';
			if(MOST_COM)
			{
				if(!file_exists(DATA_FILES_DIR.'mcom.log'))
					$tmp = popt('mcom.init', '');
				else
					$tmp = mcom_list();

				$mcom_refresh = '';
				if(isadmin())
					$mcom_refresh = popt('mcom.refresh', '');

				$MCOM = popt('mcom', array('contents' => $tmp, 'refresh' => $mcom_refresh));
			}
			return($MCOM);
		break;
		case 'mview':
			$MVIEW = '';
			if(MOST_VIEW)
			{
				if(!file_exists(DATA_FILES_DIR.'mview.log'))
					$tmp = popt('mview.init', '');
				else
					$tmp = mview_list();

				$mview_refresh = '';
				if(isadmin())
					$mview_refresh = popt('mview.refresh', '');

				$MVIEW = popt('mview', array('contents' => $tmp, 'refresh' => $mview_refresh));
			}
			return($MVIEW);
		break;
		case 'trated':
			$TRATED = '';
			if(TOP_RATED)
			{
				if(!file_exists(DATA_FILES_DIR.'trated.log'))
					$tmp = popt('trated.init', '');
				else
					$tmp = trated_list();

				$trated_refresh = '';
				if(isadmin())
					$trated_refresh = popt('trated.refresh', '');

				$TRATED = popt('trated', array('contents' => $tmp, 'refresh' => $trated_refresh));
			}
		return($TRATED);
		break;
	}
}

function import_theme_list($ex)
{
	global $THEME;

	if(!THEME_LOCKED OR $ex)
	{
		$THEME_LIST = '';
		$theme_list = array();
		foreach(glob('themes/*') as $item_)
		{
			$item_ = basename($item_);
			if($item_ != '.' AND $item_ != '..')
				$theme_list[strtolower($item_)] = $item_;
		}

		ksort($theme_list);
		foreach($theme_list as $key_ => $value_)
			$THEME_LIST .= popt('read_home_select.option', array('value' => $value_, 'title' => ucfirst($value_), 'class' => ''));

		$THEME_LIST = str_replace('value="'.$THEME.'"', 'value="'.$THEME.'" SELECTED', $THEME_LIST);

		return($THEME_LIST);
	}
	else
		return(popt('read_home_select.option', array('value' => DEFAULT_THEME, 'title' => ucfirst(DEFAULT_THEME), 'class' => '')));
}

function import_lang_list($ex)
{
	global $LANG;

	if(!LANG_LOCKED OR $ex)
	{
		$LANG_LIST = '';
		$lang_list = array();
		foreach(glob('languages/*') as $item_)
		{
			$item_ = basename($item_);
			if($item_ != '.' AND $item_ != '..')
				$lang_list[strtolower($item_)] = $item_;
		}

		ksort($lang_list);
		foreach($lang_list as $key_ => $value_)
		{
			$value_ = str_replace('.php','',$value_);
			$LANG_LIST .= popt('read_home_select.option', array('value' => $value_, 'title' => ucfirst($value_), 'class' => ''));
		}

		$LANG_LIST = str_replace('value="'.$LANG.'"', 'value="'.$LANG.'" SELECTED', $LANG_LIST);

		return($LANG_LIST);
	}
	else
		return(popt('read_home_select.option', array('value' => DEFAULT_LANG, 'title' => ucfirst(DEFAULT_LANG), 'class' => '')));
}

function import_admin_cookie()
{
	if(isset($_COOKIE['des_pass']) AND !$_SESSION['des_pass'])
	{
 		if(md5(DES_PASS) == $_COOKIE['des_pass'] AND DES_PASS)
			$_SESSION['des_pass'] = $_COOKIE['des_pass'];
	}
}

function import_feeds()
{
	global $TITLE;

	$feeds = '';
	if(SOCIAL_BOOKMARKS)
	{
		list($tmp1,$tmp2) = explode('?load=',$_SERVER['REQUEST_URI']);
		$feeds = feeds('http://'.$_SERVER['SERVER_NAME'].$tmp1.'?l='.base64_encode($tmp2),$TITLE,1);
	}
	return($feeds);
}

function import_onload()
{
	$onload = '';
	if($_SESSION['onload'])
	{
		$onload = "onload=\"alert('{$_SESSION['onload']}')\"";
		$_SESSION['onload'] = '';
	}
	return($onload);
}

?>