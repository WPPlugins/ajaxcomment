<?php 
include("../../../wp-config.php");

$id 	= (int)$_REQUEST['id'];
$start = (int)$_REQUEST['s'];
$number		= (int)$_REQUEST['n'];

$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$id' AND comment_approved = '1' ORDER BY comment_date");

$comments = array_slice($comments, $start, $number);
	$GLOBALS['comments_reply'] = array();

	function write_comment(&$c, $deep_id = -1, $color = true) {
		global $max_level;
		$comments_reply = $GLOBALS['comments_reply'];
		if ($c->comment_author_email== get_the_author_email())
			$style = ' class="mine"';
		else if ($color==true){$style=' class="borderc1"';$color=!$color;}
		else{$style=' class="borderc2"';$color=!$color;}
?>
		<li id="comment-<?php echo $c->comment_ID ?>" <?php echo $style?>><div class="commenthead">At <?php echo mysql2date('Y.m.d H:i', $c->comment_date);?>, <a name='comment-<?php echo $c->comment_ID ?>'></a><span><?php echo get_comment_author_link();?></span> said: </div>
	<div class="body">
			<?php comment_text();?>
		</div>
		<div class="meta">
			<?php
			global $user_ID, $post;
			get_currentuserinfo();
			if (user_can_edit_post_comments($user_ID, $post->ID) || ($GLOBALS['cmtDepth'] < $max_level))
				echo '[';
				// delete link
				if (user_can_edit_post_comments($user_ID, $post->ID)) {
					$deleteurl = get_bloginfo("siteurl") . '/wp-admin/comment.php?action=deletecomment&amp;p=' . $c->comment_post_ID . '&amp;c=' . $c->comment_ID;
					$deleteurl = wp_nonce_url($deleteurl, 'delete-comment_'.$c->comment_ID);	
					echo "<a href='$deleteurl' onclick='ajaxShowPost(\"$deleteurl\", \"comment-{$c->comment_ID}\", \"\", \"alert(\\\"comment is deleted\\\")\", \"delete\");return false;'>delete</a>|";
					$spamurl = get_bloginfo("siteurl") . '/wp-admin/comment.php?action=deletecomment&amp;dt=spam&amp;p=' . $c->comment_post_ID . '&amp;c=' . $c->comment_ID;
					$spamurl = wp_nonce_url($spamurl, 'delete-comment_'.$c->comment_ID);
					echo "<a href='$spamurl' onclick='ajaxShowPost(\"$spamurl\", \"comment-{$c->comment_ID}\", \"\", \"alert(\\\"comment is spamed\\\")\", \"delete\");return false;'>spam</a>|";
					edit_comment_link('Edit', '',(($GLOBALS['cmtDepth'] < $max_level)?'|': ''));
				}
					if ($GLOBALS['cmtDepth'] < $max_level) {
						if ( get_option("comment_registration") && !$user_ID )
							echo '<a href="'. get_option('siteurl') . '/wp-login.php?redirect_to=' . get_permalink() .'">Log in to Reply</a> ]';
						else
							echo '<a href="javascript:moveForm('.$c->comment_ID.')" title="reply">Reply</a>';
					}
			if (user_can_edit_post_comments($user_ID, $post->ID) || ($GLOBALS['cmtDepth'] < $max_level))
				echo ']</div>';
					if ($comments_reply[$c->comment_ID]) {
						$id = $c->comment_ID;
						if($GLOBALS['cmtDepth'] < $max_level )
							echo '<ul>';
							$first_c = true;
		foreach($comments_reply[$id] as $c) {
							if ($first_c){$first_c=false;continue;}
							$GLOBALS['cmtDepth']++;
							if($GLOBALS['cmtDepth'] == $max_level)
								write_comment($c, $c->comment_ID, $color);
							else
								write_comment($c, $deep_id, $color);
							$GLOBALS['cmtDepth']--;
		}
						if($GLOBALS['cmtDepth'] < $max_level )
							echo '</ul>';
					}
					echo '</li>';
	}
?>

	<?php
		if ($comments) :
			foreach ($comments as $c) {
				$GLOBALS['comments_reply'][$c->comment_ID][] = $c;
				if (isset($GLOBALS['comments_reply'][$c->comment_reply_ID]))
					$GLOBALS['comments_reply'][$c->comment_reply_ID][] = $c;
				else 
					$GLOBALS['comments_reply'][0][] = $c;
			}
			$GLOBALS['cmtDepth'] = 0;$color=true;
			foreach($GLOBALS['comments_reply'][0] as $cmt) {
				$GLOBALS['comment'] = &$cmt;
				write_comment($GLOBALS['comment'], '-1', $color);
				$color=!$color;
			}
		else:
		endif;
	?>