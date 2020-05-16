<?php

//decode by http://www.yunlu99.com/
function nirvana_block_editor_styles()
{	//本地服务器引入  内部调取的连接访问（本地服务器可能无法访问该引入的连接）存在文本提示语功能-Format格式工具样式缺失 
	//wp_enqueue_style('nirvana-block-editor-styles', 'https://anslp.oss-cn-beijing.aliyuncs.com/%E6%A0%B8%E5%BF%83%E6%96%87%E4%BB%B6/style-editor-bondle.css', false, false, 'all');
	wp_enqueue_style('nirvana-block-editor-styles', get_theme_file_uri('/pandastudio_plugins/block_editor_styles/css/style-editor-bondle.css'),false, false, 'all');
	wp_enqueue_style('nirvana-fontawesome-font-styles', get_theme_file_uri('/pandastudio_plugins/block_editor_styles/css/fontawesome.css'), false, false, 'all');
}
add_action('enqueue_block_editor_assets', 'nirvana_block_editor_styles');
function pandastudio_block_category($categories, $post)
{
	return array_merge($categories, array(array('slug' => 'pandastudio-block-category', 'title' => '•̀.̫•́✧  UI 样式', 'icon' => 'dashicons-admin-appearance')));
}
if (function_exists('register_block_type')) {
	add_theme_support('align-wide');
	add_filter('block_categories', 'pandastudio_block_category', 10, 2);
}