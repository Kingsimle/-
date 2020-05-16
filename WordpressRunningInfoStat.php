<?php
// 定义小工具的类 WordpressRunningInfoStat
class WordpressRunningInfoStat extends WP_Widget{

	function WordpressRunningInfoStat(){
		// 定义小工具的构造函数
		$widget_ops = array('classname' => 'widget_blogstat', 'description' => '显示博客的统计信息');
		$this->WP_Widget(false, '博客统计', $widget_ops);
	}
	
	function form($instance){
		// 表单函数,控制后台显示
		// $instance 为之前保存过的数据
		// 如果之前没有数据的话,设置默认量
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title' => '博客统计',
				'establish_time' => '2019-02-27' 
			)
		);
		$title = htmlspecialchars($instance['title']);
		$establish_time = htmlspecialchars($instance['establish_time']);
		// establish_time => 建站日期
		
		// 表格布局输出表单
		$output = '<table>';
		$output .= '<tr><td>标题</td><td>';
		$output .= '<input id="'.$this->get_field_id('title') .'" name="'.$this->get_field_name('title').'" type="text" value="'.$instance['title'].'" />';
		$output .= '</td></tr><tr><td>建站日期：</td><td>';   
		$output .= '<input id="'.$this->get_field_id('establish_time') .'" name="'.$this->get_field_name('establish_time').'" type="text" value="'.$instance['establish_time'].'" />';   
		$output .= '</td></tr></table>';  
		echo $output;   
	}
	
	function update($new_instance, $old_instance){
		// 更新数据的函数
		$instance = $old_instance;
		// 数据处理
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['establish_time'] = strip_tags(stripslashes($new_instance['establish_time']));
		return $instance;
	}
	
	function widget($args, $instance){
		extract($args); //展开数组
		$title = apply_filters('widget_title',empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$establish_time = empty($instance['establish_time']) ? '2013-01-27' : $instance['establish_time'];
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div style="margin-top: 15px;"><section class="widget widget_categories wrapper-md clear"><ul class="list-group">';
		$this->efan_get_blogstat($establish_time);
		echo '</ul></section></div>';
		echo $after_widget;
	}
	
	function efan_get_blogstat($establish_time /*, $instance */){
		// 相关数据的获取
		global $wpdb;
		$count_posts = wp_count_posts(); 
		$published_posts = $count_posts->publish; //日志总数
		$draft_posts = $count_posts->draft; //草稿数目
		$comments_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments"); //评论数目
		$time = floor((time()-strtotime($establish_time))/86400); //运行天数
		$count_tags = wp_count_terms('post_tag'); //标签总数
		$count_pages = wp_count_posts('page'); //页面总数
		$page_posts = $count_pages->publish; //页面总数
		$count_categories = wp_count_terms('category');  //分类总数
		$link = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'");  //友链总数
		$users = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users"); //用户总数
		$last = $wpdb->get_results("SELECT MAX(post_modified) AS MAX_m FROM $wpdb->posts WHERE (post_type = 'post' OR post_type = 'page') AND (post_status = 'publish' OR post_status = 'private')"); //最后更新
		$last = date('Y-m-d', strtotime($last[0]->MAX_m)); //最后更新
		$total_views = $wpdb->get_var("SELECT SUM(meta_value+0) FROM $wpdb->postmeta WHERE meta_key = 'views'");  //浏览次数

		$uptime = trim(file_get_contents('/proc/uptime'));
		$uptime = explode(' ', $uptime);
		$uptime = $uptime[0];
		$uptime = round($uptime / 86400, 2);

		$this->statItem('fas fa-file-alt', '文章数量', $published_posts);
		$this->statItem('fas fa-comments', '评论数量', $comments_count);
		$this->statItem('fas fa-book-reader', '浏览次数', $total_views);		
		$this->statItem('fas fa-tags', '标签数量', $count_tags);
		$this->statItem('fas fa-blog', '建站日期', $establish_time);
		//$this->statItem('far fa-clock', '累计安全运行', $time . ' 天');
		$this->statItem('far fa-clock', '本站性感在线', $time . ' 天');
		//$this->statItem('fas fa-power-off', '自上次服务重启后持续运行', $uptime . ' 天');
		$this->statItem('fas fa-sync-alt', '最后更新', $last);
	}

	function statItem($fa, $desc, $data) {
		echo '<li class="list-group-item">';
		echo '<i class="' . $fa . '"></i>';
		echo '<span class="badge pull-right">' . $data . '</span>';
		echo ' ' . $desc. ' ';
		echo '</li>';
	}
}

function WordpressRunningInfoStat(){
	// 注册小工具
	register_widget('WordpressRunningInfoStat');
}

add_action('widgets_init','WordpressRunningInfoStat');

?>
