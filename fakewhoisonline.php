<?php
/* Make by Generator */

if(!defined('IN_MYBB')){die();}

$plugins->add_hook("index_end", "fake_statistics");//
$plugins->add_hook("admin_config_settings_change_commit", "genlistuid");

$tb = "fakewhoisonline";
function fakewhoisonline_info(){
	return array(
		"name" => "Fake Who's Online Statistics",
		"description" => "This plugin allow you to fake \" Who's Online \"  statistics.",
		"website" => "http://mybbvietnam.com",
		"author" => "YuuKT",
		"authorsite" => "http://megares.net",
		"version" => "1.0",
		"guid" => "eee4e72883a26559b5d96be525845c92"
	);
}


function fakewhoisonline_install(){
	global $mybb, $db, $lang, $tb;
		
	$lang->load('fakewhoisonline', false, true);
	$query = $db->simple_select('settinggroups', '*', 'name="'.$tb.'"', 1);
	$r = $db->num_rows($query);
	$query = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query, "rows");
	if($r!=0) $db->delete_query('settinggroups','name="'.$tb.'"');
	
	$settinggroup = array(
		'name' 			=> $tb,
		'title'			=> 'Fake Who is Online',
		'description'	=> $lang->fko_setting_desc,
		'disporder'		=> $rows + 1,
		'isdefault'		=> 0
	);
	$gid = $db->insert_query("settinggroups", $settinggroup);
	
	$dorder = 0;
	$setting[] = array(
		'name' 			=> $tb.'_fakeusertype',
		'title'			=> $lang->fko_fakeusertype,		
		'optionscode'	=> 'radio\nfixed=Fixed\nrandom=Random',
		'description'	=> $lang->fko_fakeusertype_desc,
		'value'			=> 'fixed',
		'disporder'		=> $dorder++,
		'gid'			=> intval($gid)	
	);
	
	$setting[] = array(
		'name' 			=> $tb.'_numofuser',
		'title'			=> $lang->fko_numofuser,		
		'optionscode'	=> 'text',
		'description'	=> $lang->fko_numofuser_desc,
		'value'			=> 20,
		'disporder'		=> $dorder++,
		'gid'			=> intval($gid)	
	);
	
	$setting[] = array(
		'name' 			=> $tb.'_fakeguesttype',
		'title'			=> $lang->fko_fakeguesttype,		
		'optionscode'	=> 'radio\nfixed=Fixed\nrandom=Random',
		'description'	=> $lang->fko_fakeguesttype_desc,
		'value'			=> 'random',
		'disporder'		=> $dorder++,
		'gid'			=> intval($gid)	
	);
	
	$setting[] = array(
		'name' 			=> $tb.'_numofguest',
		'title'			=> $lang->fko_numofguest,		
		'optionscode'	=> 'text',		
		'value'			=> 20,
		'disporder'		=> $dorder++,
		'gid'			=> intval($gid)
	);
	
	foreach($setting as $s){
			$db->insert_query("settings", $s);
	}
	
	rebuild_settings();
    
}
function fakewhoisonline_is_installed(){
	global $db,$tb;	
	$query = $db->simple_select('settinggroups', '*', 'name="'.$tb.'"', 1);
	$r = $db->num_rows($query);
	if($r==1) return false;
	return true;		
}


function fakewhoisonline_uninstall(){
	global $db,$tb;
	$db->delete_query("settinggroups","name = '$tb'");
	$db->delete_query("settings","name = '$tb"."_fakeusertype'");
	$db->delete_query("settings","name = '$tb"."_numofuser'");
	$db->delete_query("settings","name = '$tb"."_fakeguesttype'");
	$db->delete_query("settings","name = '$tb"."_numofguest'");
	rebuild_settings();
}

function fakewhoisonline_activate(){

}

function fakewhoisonline_deactivate(){

}

function fake_statistics(){
	global $cache,$db,$mybb,$theme,$templates,$whosonline,$lang,$boardstats,$onlinemembers,$forumstats,$birthdays, $membercount,$botcount, $guestcount;
	
	
	$fkl = $cache->read('fake_list');
	if($fkl==null){
		genlistuid();
		$fkl = $cache->read('fake_list');
	}

		if($mybb->settings['fakewhoisonline_fakeguesttype']=="fixed"){
			$fguest = $mybb->settings['fakewhoisonline_numofguest'];
		}
		else{
			$fguest = rand(0,$mybb->settings['fakewhoisonline_numofguest']);
		}
		
	
	
	$query = $db->simple_select('users', '*', 'uid IN'.$fkl, 1);
	$onlinemembers.=$lang->comma;
	$count=0;
	while($user = $db->fetch_array($query)){
		$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
		$user['profilelink'] = build_profile_link($user['username'], $user['uid']);
		eval("\$onlinemembers .= \"".$templates->get("index_whosonline_memberbit", 1, 0)."\";");					
		$comma = $lang->comma;
		$count++;
	}
	
	
	
	
	//
	$membercount = $membercount + $count;
	$guestcount = $guestcount + $fguest;
	$onlinecount = $membercount + $guestcount + $botcount;
	
	
	
	if($onlinecount != 1)
	{
		$onlinebit = $lang->online_online_plural;
	}
	else
	{
		$onlinebit = $lang->online_online_singular;
	}
	if($membercount != 1)
	{
		$memberbit = $lang->online_member_plural;
	}
	else
	{
		$memberbit = $lang->online_member_singular;
	}
	if($anoncount != 1)
	{
		$anonbit = $lang->online_anon_plural;
	}
	else
	{
		$anonbit = $lang->online_anon_singular;
	}
	if($guestcount != 1)
	{
		$guestbit = $lang->online_guest_plural;
	}
	else
	{
		$guestbit = $lang->online_guest_singular;
	}
	
	$lang->online_note = $lang->sprintf($lang->online_note, my_number_format($onlinecount), $onlinebit, $mybb->settings['wolcutoffmins'], my_number_format($membercount), $memberbit, my_number_format($anoncount), $anonbit, my_number_format($guestcount), $guestbit, my_number_format($onlinecount),$onlinebit, my_number_format($membercount), $memberbit, my_number_format($guestcount), $guestbit);
	
	$mostonline = $cache->read("mostonline");	
	if($onlinecount > $mostonline['numusers'])
	{		
		$mostonline['numusers'] = $onlinecount;
		$mostonline['time'] = TIME_NOW;
		$cache->update("mostonline", $mostonline);
	}
	eval("\$whosonline = \"".$templates->get("index_whosonline")."\";");	
	eval("\$boardstats = \"".$templates->get("index_boardstats")."\";");
	
}

function genlistuid(){
	global $db,$mybb,$cache;
	$uid_arr = array();
	$uid_ord = array();
	if($mybb->settings['fakewhoisonline_fakeusertype']=="fixed"){
		$nou = $mybb->settings['fakewhoisonline_numofuser'];
	}else{
		$nou = rand(1,$mybb->settings['fakewhoisonline_numofuser']);
	}
	
		$query = $db->query("SELECT COUNT(uid) as count FROM  ".TABLE_PREFIX."users");
		$count = $db->fetch_field($query,"count");
		$nou = $count<$nou ? $count : $nou;			
		while(count($uid_ord)< $nou){			
			$rand =rand(0,$nou-1);
			if(!in_array($rand,$uid_ord)){
				array_push($uid_ord,$rand);
			}		
		}
		foreach($uid_ord as $ord){
			$query = $db->query("SELECT * FROM  ".TABLE_PREFIX."users LIMIT $ord,1");
			$uid = $db->fetch_field($query,"uid");
			if($mybb->user[uid]!=$uid){
				array_push($uid_arr,$uid);
			}
			
		}
		$string='';
		foreach($uid_arr as $uid){
			$string.=$uid.',';
		}
		$string = '('.substr($string,0,-1).')';
		 
		$cache->update('fake_list',$string);	
}



?>