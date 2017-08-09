<?php 
include("../../../wp-config.php");

$comment_content      = trim($_REQUEST['comment']);

$comment_content = apply_filters('pre_comment_content', $comment_content);
$comment_content = stripslashes($comment_content);
$comment_content = apply_filters('get_comment_text', $comment_content);
$comment_content = apply_filters('comment_text', $comment_content);

echo "<div class='meta red'>Notice: This is only a preview of your comment. </div>";

echo "<div style='font-size:1.1em;'>$comment_content</div>";

echo "<div class='meta red'>Press 'Say it!' to submit it! Press 'Edit' or double click the comment content to continue to edit it.";
?>