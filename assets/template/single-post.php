<?php
//文章模板
/*
single: post
*/
get_header();
$headImg = get_post_meta($post->ID, "日志头图", true);
$type = $headImg ? _opt('single_title_width_headImg_type', 'single-coverflow') : _opt('single_title_widthout_headImg_type', 'single-coverflow');
get_topSlider(array($post->ID), $type);
?><div class="container postListsModel"><div class="row"><?php 
if (_opt('is_single_post_hide_sidebar')) {
	$leftClass = 'col-xs-12 no-sidebar';
	$rightClass = 'hidden';
} else {
	$leftClass = 'col-md-9 col-lg-9_5';
	$rightClass = 'col-md-3 col-lg-2_5 hidden-xs hidden-sm';
}
?><div class="<?php 
echo $leftClass;
?>"><div class="col-xs-12"><div class="row postLists"><div class="toggle_sidebar" @click="this.single_toggle_sidebar()" data-toggle="tooltip" data-placement="auto top" title="切换边栏"><i class="fas fa-angle-right"></i></div><div class="article_wrapper post clearfix"><div class="meta">
<span class="inline-block"><i class="fas fa-user"></i><?php 
echo get_the_author();
?> · <?php 
the_time('n月j日 · Y年');
?>

</span><?php 
if (get_the_tag_list()) {
	?><span class="inline-block"><i class="fas fa-tag"></i><?php 
	the_tags('', ' · ', '');
	?></span><?php 
}
?><?php echo '<i class="fas fa-clock"></i>' .count_words_read_time(); ?><i class="fas fa-book-reader"></i><?php get_post_views($post -> ID); ?></div><article class="clearfix">

<?php 
the_content();
?>

</article><?php 
include 'share-modal.php';
?></div><?php 
if (_opt('show_relate_posts')) {
	include_once 'relate_posts.php';
}
comments_template();
?></div></div></div><div class="<?php 
echo $rightClass;
?>"><div class="row"><div class="sidebar sidebar-affix"><div manual-template="sidebarMenu"></div><div manual-template="sidebar"></div></div></div></div></div></div>

<?php 
get_footer();