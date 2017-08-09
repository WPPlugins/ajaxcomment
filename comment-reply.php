<?php
/*
Plugin Name: Ajax Comments-Reply
Plugin URI: http://zhiqiang.org/blog/plugin/ajaxcomment
Version: 2.5.4
Description: ajax留言，并可针对性留言。1.0版由懒懒喵开发。
Author: zhiqiang
Author URI: http://zhiqiang.org/blog/
*/

$max_level = 5; // choose the max level 
$comments_per_page = 100; // comments per page

function reply_column_checker() {
	global $wpdb;
	$column_name = 'comment_reply_ID';
	foreach ($wpdb->get_col("DESC $wpdb->comments", 0) as $column) {
		if ($column == $column_name) {
		    return true;
		}
	}
	$q = $wpdb->query("ALTER TABLE $wpdb->comments ADD COLUMN comment_reply_ID INT NOT NULL DEFAULT 0;");
	foreach ($wpdb->get_col("DESC $wpdb->comments", 0) as $column) {
		if  ($column == $column_name) {
			return true;
		}
	}
	return false;
}
function commentreply_load_scripts() {
	echo '<link rel="stylesheet" href="'.get_settings('siteurl').'/wp-content/plugins/ajaxcomment/comment.css" type="text/css" media="screen" />';
}
function add_reply_id_formfield() {
	echo '<input type="hidden" name="comment_reply_ID" id="comment_reply_ID" value="0" />';
}
function add_reply_ID($id) {
	global $wpdb;
	$reply_id = mysql_escape_string($_REQUEST['comment_reply_ID']);
	$q = $wpdb->query("UPDATE $wpdb->comments SET comment_reply_ID='$reply_id' WHERE comment_ID='$id'");
}

// choose your comment comtemplate
function change_comments_template($file) {
	return ABSPATH . "/wp-content/plugins/ajaxcomment/comments.php";
}

add_action('wp_head','reply_column_checker');
add_action('wp_head','commentreply_load_scripts');
// add_action('edit_post', 'add_reply_id', 100);
add_action('comment_post','add_reply_id');
add_filter('comments_template', change_comments_template);


// the function below send email notice when you with specific email as $email_send_comment reply some comments.
// if you want to actitive this function, fill in $email_send_comment with your email and uncomment add_action line.
$email_send_comment="";
// add_action('comment_post', 'email_back');
function email_back($id) {
	global $wpdb, $email_send_comment;
	$reply_id = mysql_escape_string($_REQUEST['comment_reply_ID']);
	$post_id  = mysql_escape_string($_REQUEST['comment_post_ID']);
	if ($reply_id == 0 || $_REQUEST["email"]!=$email_send_comment) return;
	$comment = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_ID='$id' LIMIT 0, 1");

	$reply_comment = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_ID='$reply_id' LIMIT 0, 1");
	$post    = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID='$post_id' LIMIT 0, 1");
	
	$comment = $comment[0];
	$reply_comment = $reply_comment[0];
	$post = $post[0];
	$title = $post->post_title;
	$author = $reply_comment->comment_author;
	$url =	get_permalink($post_id);

	$to = $reply_comment->comment_author_email;
	if ($to == "") return;
	
	$subject = "作者回复了你在[".get_bloginfo()."]'".$title."'的留言";
	$date = mysql2date('Y.m.d H:i', $reply_comment->comment_date);
	$message = "$reply_id;$id;$post_id;
	<div>
		<p>Dear $author:<p>
		<p>{$comment->comment_content}</p>
		<div style='color:grey;'><small>$date, 你在".get_bloginfo()."<a href='$url#comment-$id'>$title</a>留言：</small>
			<blockquote>
				<p>{$reply_comment->comment_content}</p>
			</blockquote>
		</div>
	</div>";

		// strip out some chars that might cause issues, and assemble vars
		$site_name = get_bloginfo();
		$site_email = $email_send_comment;
		$charset = get_settings('blog_charset');

		$headers  = "From: \"{$site_name}\" <{$site_email}>\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=\"{$charset}\"\n";
		
		$email_send_comment = "Email has sent to ".$to." with subject ".$subject;
		return wp_mail($to, $subject, $message, $headers);
}

?>