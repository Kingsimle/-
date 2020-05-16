<?php
	if ( 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']) )
		die ( 'Please do not load this page directly. Thanks.' );
?>

<?php if ( !(!$comments & 'open' != $post->comment_status) ): //允许评论，且有评论内容 ?>
			<div id="comments">
<?php
	if ( !empty($post->post_password) ) :
		if ( $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password ) :
?>
				<div class="nopassword"><?php _e( 'This post is protected. Enter the password to view any comments.', 'sandbox' ) ?></div>
			</div><!-- .comments -->
<?php
		return;
	endif;
endif;
?>

<div class="post-model"><i class="far fa-comment-alt"></i><?php echo $post->comment_count; ?> 条回应</div>
<!-- 评论输入框 -->
<?php if ( 'open' == $post->comment_status ) { ?>
	<?php
	if (get_option('comment_registration') && !is_user_logged_in()) {
	?>
	<div class="tip">
		<p>必须<a href="<?php echo wp_registration_url(); ?>" style="color:#fff;font-weight: bold;"> 注册 </a>为本站用户，<a href="<?php echo wp_login_url(); ?>" style="color:#fff;font-weight: bold;"> 登录 </a>后才可以发表评论！</p>
	</div>
	<?php } else { ?>
	<div id="respond" class="comment-respond clearfix">
		<?php
		$cmt_face_arr = get_faces_from_dir();
		$cmt_face_imgs = '';
		foreach ($cmt_face_arr as $face) {
			switch ($face['type']) {
				case 'p': $cmt_face_fmt = 'png'; break;
				case 'g': $cmt_face_fmt = 'gif'; break;
			}
			$cmt_face_imgs .= '<img src='.get_stylesheet_directory_uri().'/faces/'.$face['name'].'.'.$cmt_face_fmt.' @click="this.addCommentFace(\'[face '.$face['type'].'='.$face['name'].']\')">';
		}
		?>

		<div class="popover_faces hidden">
			<?php echo $cmt_face_imgs; ?>
		</div>


		<div class="popover_loggedin hidden">
			<div style="text-align: right; width: 100px;">
				<a>取消</a>
				<a href="<?php echo wp_logout_url( get_permalink() ); ?>" class="primary"><i class="fas fa-check"></i> 确定</a>
			</div>
		</div>

		<div class="popover_guests hidden">
			<p>
				<label>
					<i class="fas fa-user"></i>
				</label>
				<input v-model="comment_author" class="comment-input" placeholder="昵称">
			</p>
			<p>
				<label>
					<i class="fas fa-envelope"></i>
				</label>
				<input v-model="comment_email" class="comment-input" placeholder="邮箱">
			</p>
			<p>
				<label>
					<i class="fas fa-globe-americas"></i>
				</label>
				<input v-model="comment_url" class="comment-input" placeholder="网站">
			</p>
		</div>
		
		<form method="post" id="cmt_form" class="comment-form clear-float">
			<div class="comment-form-comment">
				<textarea  id="comment" name="comment" rows="5" aria-required="true"    placeholder="<?php _eopt("comment_area_placeholder",'请回复..'); ?>" v-model="comment_text" class="comment-input"></textarea>
			</div> 
			<script>
				$(function(){
					$("#comment").attr("placeholder","  温馨提示:	\n \t 1.您的回帖是对博主莫大的鼓励和支持,请不要发布纯表情,无意义内容的回复;	\n \t 2.请不要用讽刺,鄙视,不屑的方式沟通,如果您是对方,您也不会舒服;	\n \t 3.如发现口水,请忍耐,不要参与,无视其会更好,同时举报给博主;	\n \t 4.如果您希望别人怎么对待您.也请您先以同样的方式对待其他人;");
					
					$("#comment").blur(function(){
						$("#comment").attr("placeholder","  温馨提示:	\n \t 1.您的回帖是对博主莫大的鼓励和支持,请不要发布纯表情,无意义内容的回复;	\n \t 2.请不要用讽刺,鄙视,不屑的方式沟通,如果您是对方,您也不会舒服;	\n \t 3.如发现口水,请忍耐,不要参与,无视其会更好,同时举报给博主;	\n \t 4.如果您希望别人怎么对待您.也请您先以同样的方式对待其他人;");
					});
					
					
					/**
					$("#comment").focus(function(){
						$("#comment").attr("placeholder","");
					});**/
					
					
					$("#comment_faces_toggle").click(function(){
						$(".popover_faces").show();
					});
				});
			</script>
			

			
			<input name="author" v-model="comment_author" class="hidden">
			<input name="email" v-model="comment_email" class="hidden">
			<input name="url" v-model="comment_url" class="hidden">

			<p class="form-meta">
				<?php
				if (count($cmt_face_arr) > 0) {
					echo '<a id="comment_faces_toggle" tabindex="0"><i class="far fa-smile-wink" data-toggle="tooltip" title="表情"></i></a>
					<a @click="this.insert_code_to_comment_form()"><span data-toggle="tooltip" title="" data-original-title="插入代码"><i class="far fa-file-code"></i></span></a>
					<a @click="this.insert_images_to_comment_form()"><span data-toggle="tooltip" title="" data-original-title="插入图片"><i class="far fa-images"></i></span></a>';
					
				}
				?><a class="comment-meta nick-name guests" tabindex="0" v-show="!this.is_user_loggedin" :html="this.comment_author ? this.comment_author.replace(/ /g, '') != '' ? this.comment_author : '昵称' : '昵称'">
				</a><a class="comment-meta nick-name loggedin" tabindex="0" v-show="this.is_user_loggedin">
					<?php global $current_user;get_currentuserinfo();echo $current_user->user_login; ?>
				</a>
				<span class="big_fa_ding" data-toggle="tooltip" title="同时点赞" data-original-title="同时点赞">
					<input name="big_fa_ding" type="checkbox" v-model="comment_ding" class="jv-switcher" id="tmKlyukmIyoHjCUxTJxlgWVecFdGIEhe">
					<label for="tmKlyukmIyoHjCUxTJxlgWVecFdGIEhe"></label>
				</span>
			</p>
			
			


			<p class="form-submit">
				<?php echo get_cancel_comment_reply_link( '<i class="fas fa-ban"></i> 取消回复' ); ?><button name="submit" class="submit-comment" value="提交评论" class="submit" @click="this.submit_comments(event);"><i class="fas fa-paper-plane"></i> 提交评论</button>
				<?php
				echo get_comment_id_fields( $post->id );
				do_action( 'comment_form', $post->id );
				?>
			</p>
		</form>
	</div>
	<?php } // REFERENCE: 已启用已注册用户评论 ?>
<?php } // REFERENCE: if ( 'open' == $post->comment_status ) ?>

<!-- 评论列表 -->

<?php if ( !(!$comments & 'open' != $post->comment_status) ) : ?>
	<div id="comments-list" class="comments">
		<ol class="commentlist">
			<?php wp_list_comments( 'type=comment&callback=mytheme_comment' ); ?>
			<?php //wp_list_comments(); ?>
		</ol>
<?php
// 如果用户在后台选择要显示评论分页
if (get_option('page_comments')) {
	// 获取评论分页的 HTML
	$comment_pages = paginate_comments_links('echo=0');
	// 如果评论分页的 HTML 不为空, 显示导航式分页
	if ($comment_pages) {
?>
		<div id="commentnavi">
			<?php echo $comment_pages; ?>
		</div>
<?php
	}
}
?>
	</div>
	


<?php endif; ?>
			</div><!-- #comments -->
<?php endif; //允许评论，且有评论内容 ?>