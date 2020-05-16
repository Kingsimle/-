<?php
/*
Template Name: 时间轴模板
*/
get_header();?>
<?php
$frontpage_carousels_type = _opt('frontpage_carousels_type');$type = strstr($frontpage_carousels_type, 'full') ? 'single-imageflow-full':'single-imageflow';get_topSlider(array($post->ID),$type);?><div class="container postListsModel"><div class="row"><?php
if (_opt('is_single_post_hide_sidebar')) {$leftClass = 'col-xs-12 no-sidebar';$rightClass = 'hidden';} else {$leftClass = 'col-md-9 col-lg-9_5';$rightClass = 'col-md-3 col-lg-2_5 hidden-xs hidden-sm';}?><div class="<?php echo $leftClass; ?>"><div class="col-xs-12">
<div class="row postLists"><div class="toggle_sidebar" @click="this.single_toggle_sidebar()" data-toggle="tooltip" data-placement="auto top" title="切换边栏"><i class="fas fa-angle-right"></i></div>
<div class="article_wrapper post clearfix page">
	<article class="clearfix">
	<style>
	.archives-title{border-bottom:1px #eee solid;position:relative;padding-bottom:4px;margin-bottom:10px}    
	.archives li a{padding:8px 0;display:block}
	.archives li a:hover .atitle:after{background:#ff5c43}
	.archives li a span{display: inline-block;width:100px;font-size:12px;text-indent:20px;}
	.archives li a .atitle{display: inline-block;padding:0 15px;position:relative}
	.archives li a .atitle:after{position:absolute;left:-6px;background:#ccc;height:8px;width:8px;border-radius:6px;top:8px;content:""}
	.archives li a .atitle:before{position:absolute;left:-8px;background:#fff;height:12px;width:12px;border-radius:6px;top:6px;content:""}
	.archives{position:relative;padding:10px 0}
	.archives:before{height:100%;width:4px;background:#eee;position:absolute;left:100px;content:"";top:0}
	.m-title{position:relative;margin:10px 0;cursor:pointer}    
	.m-title:hover:after{background:#ff5c43}
	.m-title:before{position:absolute;left:93px;background:#fff;height:0px;width:18px;border-radius:6px;top:3px;content:""}
	.m-title:after{position:absolute;left:96px;background:#ccc;height:12px;width:12px;border-radius:6px;top:6px;content:""}
	</style>
	<div class="archives">
			<?php
			$previous_year = $year = 0;
			$previous_month = $month = 0;
			$ul_open = false;
			$myposts = get_posts('numberposts=-1&orderby=post_date&order=DESC');
			
	
    
	

			foreach($myposts as $post) :
				setup_postdata($post);
				$year = mysql2date('Y', $post->post_date);
				$month = mysql2date('n', $post->post_date);
				$day = mysql2date('j', $post->post_date);
				if($year != $previous_year || $month != $previous_month) :
					if($ul_open == true) : 
						echo '</ul>';
					endif;
					echo '<h3 class="m-title">'; echo the_time('Y-m'); echo '</h3>';
										
					echo '<ul class="archives-monthlisting">';
					$ul_open = true;
				endif;
				$previous_year = $year; $previous_month = $month;
			?>

			<?php //初始参数
			$posttype = get_post_type();switch ($posttype) {case 'post':
			$thumbnail = get_the_post_thumbnail_url();$tags = get_the_tags();break;case 'gallery':
			$gallery_images = get_post_meta(get_the_id(), "gallery_images",true);$gallery_images = $gallery_images ? $gallery_images : array();switch (get_option('gallery_thumbnail')) {case 'first':
			$thumbnail = $gallery_images[0];break;case 'last':
			$thumbnail = $gallery_images[count($gallery_images) - 1];break;default:
			$thumbnail = count($gallery_images) > 0 ? $gallery_images[array_rand($gallery_images,1)] : '';break;}$tags = get_the_terms(get_the_ID(),'gallery-tag');break;default:
			$thumbnail = '';break;}
			?>
			
				<div class="wp-block-pandastudio-single" posttype="post">
					<div class="single-wrapper">
						<a class="cover" style="background-image:url(<?php echo $thumbnail; ?>);" href="<?php the_permalink(); ?>"></a><!-- 获取文章图片 地址  -->
						<div class="single-meta">
							<a class="post-title" href="<?php the_permalink(); ?>"><!-- 获取文章地址  -->
								<h4><?php the_title(); ?></h4><!-- 获取文章标题  -->
							</a>
							<div class="summary">
								<span class="date"><i class="far fa-clock"></i> <?php the_time('Y-m-j'); ?></span><!-- 获取时间 -->
								<span class="likes"><i class="fas fa-heart"></i> <?php $like = get_post_meta($post->ID,'bigfa_ding',true) ? get_post_meta($post->ID,'bigfa_ding',true) : "0" ; echo $like;?></span><!-- 获取点赞数量 -->
								<span class="comments"><i class="fas fa-comments"></i> <?php echo $post->comment_count ?></span><!-- 获取评论数量 -->
							</div>
						</div>
					</div>
				</div>
				
			<?php endforeach; ?>
			</ul>
	</div>
	<script>
	$('.archives ul.archives-monthlisting').hide();
	$('.archives ul.archives-monthlisting:first').show();
	$('.archives .m-title').click(function() {
		$(this).next().slideToggle('fast');
		return false;
	});
	</script>
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