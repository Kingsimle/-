<?php
/*
Template Name: 文章归档
*/
get_header();

?>


<?php
$frontpage_carousels_type = _opt('frontpage_carousels_type');$type = strstr($frontpage_carousels_type, 'full') ? 'single-imageflow-full':'single-imageflow';get_topSlider(array($post->ID),$type);?><div class="container postListsModel"><div class="row"><?php
if (_opt('is_single_post_hide_sidebar')) {$leftClass = 'col-xs-12 no-sidebar';$rightClass = 'hidden';} else {$leftClass = 'col-md-9 col-lg-9_5';$rightClass = 'col-md-3 col-lg-2_5 hidden-xs hidden-sm';}?><div class="<?php echo $leftClass; ?>"><div class="col-xs-12">


<div class="row postLists"><div class="toggle_sidebar" @click="this.single_toggle_sidebar()" data-toggle="tooltip" data-placement="auto top" title="切换边栏"><i class="fas fa-angle-right"></i></div>

<div class="article_wrapper post clearfix page">



	<article class="clearfix">

	<div class="archives">
			<main class="meowblog">	
<div class="main-container">
<div class="container">
<div class="row">
<div class="col-lg-10 col-md-10 ml-auto mr-auto">


 <?php function zww_archives_list() {
	 
	if( !$output = get_option('zww_db_cache_archives_list') ){
		update_option('zww_db_cache_archives_list', $output);
	}
	echo $output;
}
?>
 
 
 <?php zww_archives_list(); ?>
 
 
 
<script>
    $(function(){
        $(".biji-content").hide();
        //按钮点击事件
        $(".openoff").click(function(){
            var txts = $(this).parents("li");
            if ($(this).text() == "[ 展开 ]"){
                $(this).text("[ 收起 ]");
                txts.find(".biji-tit").hide();
                txts.find(".biji-content").show();
            }else{
                $(this).text("[ 展开 ]");
                txts.find(".biji-tit").show();
                txts.find(".biji-content").hide();
            }
        })
    });
</script>

		
	</div>



	</article>


</div>

<?php comments_template(); ?>
</div></div></div>

	<div class="<?php echo $rightClass; ?>">
		<div class="row">
			<div class="sidebar sidebar-affix">
				<div manual-template="sidebarMenu"></div>
				<div manual-template="sidebar"></div>
			</div>
		</div>
	</div>
</div>
</div>
<?php get_footer(); ?>