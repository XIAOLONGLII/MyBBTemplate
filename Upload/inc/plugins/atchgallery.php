<?php
if(!defined("IN_MYBB"))
{
    die("This file cannot be accessed directly.");
} 

$plugins->add_hook('global_end', 'atchgallery', 1000);
$plugins->add_hook("pre_output_page", "atchgallery_removetags", 1000);

// The information that shows up on the plugin manager
function atchgallery_info()
{
	return array(
		"name"			=> "Latest Attachment Gallery",
		"description"	=> "This plugin displays the latest x number of image attachments from a certain forum.",
		"website"		=> "http://jeffchan.org",
		"author"		=> "Jeff Chan",
		"authorsite"	=> "http://jeffchan.org",
		"version"		=> "1.4",
		"guid"			=> "60d97f8754e7d40a27efc235b7c66cd8",
		'compatibility' => "14"
	);
}

// This function runs when the plugin is installed.
function atchgallery_install()
{
	global $db;
	
	$atchgallery_group = array(
		'name'			=> 'atchgallery',
		'title'			=> 'Latest Attachment Gallery Settings',
		'description'	=> 'Settings for the header Gallery plugin.',
		'disporder'		=> 3,
		'isdefault'		=> 'no',
	);
	
	$db->insert_query('settinggroups', $atchgallery_group);
	$gid = $db->insert_id();
	
	$atchgallery_setting = array(
		'name'			=> 'atchgalleryforum',
		'title'			=> 'Latest Attachment Gallery Forum',
		'description'	=> 'Enter the forum ID which you would like to display image attachments from. Enter -1 if you would like it to display from all forums.',
		'optionscode'	=> 'text',
		'value'			=> '-1',
		'disporder'		=> 1,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgallerycache',
		'title'			=> 'Latest Attachment Gallery Cache Expire Time',
		'description'	=> 'The gallery is cached for performance. Specify the time in seconds you would like the cache to be rebuilt. For example, if set at 60, the cache will be regenerated every 60 seconds at the fastest. Append ?atchgallery_flush=1 to any page where the gallery is supposed to show up to manually flush the cache.',
		'optionscode'	=> 'text',
		'value'			=> '60',
		'disporder'		=> 2,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgalleryname',
		'title'			=> 'Latest Attachment Gallery Name',
		'description'	=> 'Enter the name which you want the header gallery box to be called.',
		'optionscode'	=> 'text',
		'disporder'		=> 3,
		'value'			=> '★ Latest Post[photos]',
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgallerymax',
		'title'			=> 'Maximum Thumbnails to Display',
		'description'	=> 'The maximum amount of latest image attachments you would like to display.',
		'optionscode'	=> 'text',
		'value'			=> '5',
		'disporder'		=> 4,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgallerymaxperrow',
		'title'			=> 'Maximum Thumbnails per Row',
		'description'	=> 'The maximum amount of latest image attachments you would like to display per row.',
		'optionscode'	=> 'text',
		'value'			=> '5',
		'disporder'		=> 5,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgallerylink',
		'title'			=> 'Thumbnails Link',
		'description'	=> 'If you would like it to link to the post where the attachment is located, choose yes. If you would like the thumbnails to link to the larger image, choose no.',
		'optionscode'	=> 'yesno',
		'value'			=> '1',
		'disporder'		=> 6,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgallerycollapse',
		'title'			=> 'Enable Collapse?',
		'description'	=> 'If you would like to make the gallery collapsable, select on.',
		'optionscode'	=> 'onoff',
		'value'			=> '1',
		'disporder'		=> 7,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_setting = array(
		'name'			=> 'atchgalleryglobal',
		'title'			=> 'Show globally, only index, or only forum?',
		'description'	=> 'If you would like to make the gallery show globally, input 0. If you would like to limit it to the forum you pulled from, input -1. Or if you would like to make it show only on the forum index, input 1.',
		'optionscode'	=> 'text',
		'value'			=> '1',
		'disporder'		=> 8,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $atchgallery_setting);
	
	$atchgallery_template = array(
		"title"		=> 'atchgallery',
		"template"	=> "<br class=\"clear\" />
<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tborder\">
	<thead>
		<tr>
			<td class=\"thead\" colspan=\"{\$maxperrow}\">
				{\$collapse}
				<div><strong>{\$name}</strong></div>
			</td>
		</tr>
	</thead>
	<tbody style=\"{\$collapsed[\$cid]}\" id=\"atchgallery_id_e\">
		<tr style=\"height: {\$height}px;\">
			{\$thumbnails}
		</tr>
	</tbody>
</table>",
		"sid"		=> -1,
		"version"	=> 120,
		"status"	=> '',
		"dateline"	=> 1134703642,
	);
	$db->insert_query('templates', $atchgallery_template);
	
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("header", '#<navigation>#', "<navigation>\n\t\t\t{atchgallery}");
	
	rebuild_settings();
}

// This function is called to establish whether the plugin is installed or not
function atchgallery_is_installed()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "`gid`", "`name` = 'atchgallery'");
	$g = $db->fetch_array($query);
	if($g)
	{
		return true;
	}
	return false;
}

// This function runs when the plugin is deactivated.
function atchgallery_uninstall()
{
    global $db;
	
	// Remove Code from header template
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("header", '#(\n?)(\t?)(\t?)(\t?){atchgallery}#', '', 0);
	
	//Delete Latest Attachment Gallery template
	$db->delete_query("templates", "`title` = 'atchgallery'");
	
	//Get Setting Group ID
	$query = $db->simple_select("settinggroups", "`gid`", "`name` = 'atchgallery'");
	$g = $db->fetch_array($query);
	
	//Delete Setting Group and Settings
	$db->delete_query("settinggroups", "`gid` = '" . $g['gid'] . "'");
	$db->delete_query("settings", "`gid` = '" . $g['gid'] . "'");
	
	//Rebuild settings.php
	rebuild_settings();
}

// This is the main function
function atchgallery()
{
	global $cache, $header, $mybb;
	
	$max = $mybb->settings['atchgallerymax'];
	$maxperrow = $mybb->settings['atchgallerymaxperrow'];
	$forum = $mybb->settings['atchgalleryforum'];
	$visible = atchgallery_checkvisibility();
	
	// If the gallery is not visible or the settings are incorrect, quit.
	if(!is_numeric($forum) || !is_numeric($max) || $visible == false)
	{
		return false;
	}
	
	// Reads the cache
	$gallery_cache = $cache->read('atchgallery');
	if(!$gallery_cache || $gallery_cache['last_updated'] <= time()-$mybb->settings['atchgallerycache'] || $mybb->input['atchgallery_flush'] == 1)
	{
		// No cache, cache expired, or manual flush. Either way, regenerate the cache.
		$atchgallery = atchgallery_generate();
		$cache->update('atchgallery', array('content' => $atchgallery, 'last_updated' => time()));
	}
	else
	{
		// Wee!!! We found our cache. Use it.
		$atchgallery = $gallery_cache['content'];
	}
	$header = str_replace('{atchgallery}', $atchgallery, $header);
}

// This function generates the HTML for the gallery
function atchgallery_generate()
{
	global $mybb, $templates, $db, $collapsed, $collapsedimg, $theme, $header;
	
	$max = $mybb->settings['atchgallerymax'];
	$maxperrow = $mybb->settings['atchgallerymaxperrow'];
	$forum = $mybb->settings['atchgalleryforum'];
	
	$i = 0;
	$query = $db->simple_select("attachments", "*", "`thumbnail` != ''", array('order_by' => 'aid', 'order_dir' => 'desc'));
	while($i < $max && $rows = $db->fetch_array($query))
	{
		// Display attachemts from all forums
		if($forum == -1)
		{
			$i++;
			$array[$i] = array(
				'aid' 	=> $rows['aid'],
				'pid'	=> $rows['pid'],
				'thumbnail' => $rows['thumbnail']
			);
		}
		else
		// Display attachments from a specific forum
		{
			$checkquery = $db->simple_select("posts", "fid", "`pid` = '" . $rows['pid'] . "' AND `fid` = '" . $forum . "'");
			$c = $db->fetch_field($checkquery, "fid");
			if($c)
			{
				$i++;
				$array[$i] = array(
					'aid' 	=> $rows['aid'],
					'pid'	=> $rows['pid'],
					'thumbnail' => $rows['thumbnail']
				);
			}
		}
	}
	
	if(is_array($array) && $i > 0)
	{
		$count = 0;
		foreach($array as $thumbs)
		{
			$height = $mybb->settings['attachthumbh']+10;
			$count++;
			$i--;
			if($mybb->settings['atchgallerylink'] == 0)
			{
				$link = "attachment.php?aid=" . $thumbs['aid'];
			}
			else if($mybb->settings['atchgallerylink'] == 1)
			{
				$post = get_post($thumbs[pid]);
				$link = get_post_link($thumbs['pid'], $post['tid']) . "#pid" . $thumbs['pid'];
			}
			
			//if there is a thumbnail, that means its a big attachment!
			$image = ($thumbs[thumbnail]=='SMALL') ? 'aid' : 'thumbnail';

			$rowwidth = 100/$maxperrow;
			$thumbnails .= "<td class=\"trow1\" style=\"text-align: center; width: " . $rowwidth . "%;\">\n";
			$thumbnails .= "\t<a href=\"" . $link . "\" target=\"_blank\"><img src=\"attachment.php?" . $image . "=" . $thumbs[aid] . "\" class=\"attachment\" alt=\"\"></a>\n";
			$thumbnails .= "</td>\n";
			
			if(($count == $maxperrow) && ($i != 0))
			{
				$thumbnails .= "\n</tr>\n<tr style=\"height: " . $height . "px;\">";
				$count = 0;
			}
		}
		
		$name = $mybb->settings['atchgalleryname'];
		//if the collapse button is enabled
		if($mybb->settings['atchgallerycollapse'] == 1)
		{
			$collapse = "
			<div class=\"expcolimage\">
				<img src=\"".$theme['imgdir']."/collapse".$collapsedimg[$cid].".gif\" id=\"atchgallery_id_img\" class=\"expander\" alt=\"[-]\" />
			</div>";
		}
		eval("\$atchgallery = \"".$templates->get('atchgallery')."\";");
		return $atchgallery;
	}
}

// This function checks if the gallery is supposed to be displayed or not.
function atchgallery_checkvisibility()
{
	global $mybb;
	
	$base_file = basename($_SERVER["PHP_SELF"]);
	$forum = $mybb->settings['atchgalleryforum'];
	
	//only show on forum index
	if($mybb->settings['atchgalleryglobal'] == '1')
	{
		return ($base_file == "index.php" || $base_file == "portal.php" || $base_file == "forums.html" || $base_file == "forums.php");
	}
	//only show on THE forum specified to pull attachments from
	else if($mybb->settings['atchgalleryglobal'] == '-1')
	{
		if(($base_file == "forumdisplay.php" && $mybb->input['fid'] == $forum) || $forum == '-1')
		{
			return true;
		}
		elseif($base_file == "showthread.php")
		{
			$thread = get_thread($mybb->input['tid']);
			if($forum == $thread['fid'])
			{
				return true;
			}
		}
	}
	else
	// show globally
	{
		return true;
	}
}

function atchgallery_removetags($page)
{
	//remove template tag if it isn't parsed for whatever reason
	$page = str_replace('{atchgallery}', '', $page);
	return $page;
}
?>