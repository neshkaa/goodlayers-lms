<?php
/**
 * The template for displaying Comments.
 */

if ( post_password_required() )
	return;
?>

<div id="comments" class="gdlr-comments-area">
<?php if(have_comments()){ ?>
	<h3 class="comments-title">
		<?php 
			if( get_comments_number() <= 1 ){
				echo get_comments_number() . ' ' . pll__('Response', 'gdlr_translate'); 
			}else{
				echo get_comments_number() . ' ' . pll__('Responses', 'gdlr_translate'); 
			}
		?>
	</h3>

	<ol class="commentlist">
		<?php wp_list_comments(array('callback' => 'gdlr_comment_list', 'style' => 'ol')); ?>
	</ol><!-- .commentlist -->

	<?php if (get_comment_pages_count() > 1 && get_option('page_comments')){ ?>
		<nav id="comment-nav-below" class="navigation" role="navigation">
			<h1 class="assistive-text section-heading"><?php echo pll__( 'Comment navigation', 'gdlr_translate' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( pll__( '&larr; Older Comments', 'gdlr_translate' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( pll__( 'Newer Comments &rarr;', 'gdlr_translate' ) ); ?></div>
		</nav>
	<?php } ?>

<?php } ?>

<?php 
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ($req ? " aria-required='true'" : '');
	$consent  = empty($commenter['comment_author_email']) ? '':' checked="checked"';

	$args = array(
		'id_form'           => 'commentform',
		'id_submit'         => 'submit',
		'title_reply'       => pll__('Leave a Reply', 'gdlr_translate'),
		'title_reply_to'    => pll__('Leave a Reply to %s', 'gdlr_translate'),
		'cancel_reply_link' => pll__('Cancel Reply', 'gdlr_translate'),
		'label_submit'      => pll__('Post Comment'),
		'comment_notes_before' => '',
		'comment_notes_after' => '',

		'must_log_in' => '<p class="must-log-in">' .
			sprintf( pll__('You must be <a href="%s">logged in</a> to post a comment.', 'gdlr_translate'),
			wp_login_url(apply_filters( 'the_permalink', get_permalink())) ) . '</p>',
		'logged_in_as' => '<p class="logged-in-as">' .
			sprintf( pll__('Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'gdlr_translate'),
			admin_url('profile.php'), $user_identity, wp_logout_url(apply_filters('the_permalink', get_permalink( ))) ) . '</p>',

		'fields' => apply_filters('comment_form_default_fields', array(
			'author' =>
				'<div class="comment-form-head">' .
				'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
				'" data-default="' . esc_attr(__('Name*', 'gdlr_translate')) . '" size="30"' . $aria_req . ' />',
			'email' => 
				'<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .
				'" data-default="' . esc_attr(__('Email*', 'gdlr_translate')) . '" size="30"' . $aria_req . ' />',
			'url' =>
				'<input id="url" name="url" type="text" value="' . esc_attr($commenter['comment_author_url']) .
				'" data-default="' . esc_attr(__('Website', 'gdlr_translate')) . '" size="30" /><div class="clear"></div></div>',
			'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' />' .
	            '<label for="wp-comment-cookies-consent">' . esc_html__('Save my name, email, and website in this browser for the next time I comment.', 'gdlr_translate') . '</label></p>',
		)),
		'comment_field' =>  '<div class="comment-form-comment">' .
			'<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true">' .
			'</textarea></div>'
		
	);
	comment_form($args); 

?>
</div><!-- gdlr-comment-area -->