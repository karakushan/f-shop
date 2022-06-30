<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Ecoveles
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

use FS\FS_Product;

if ( post_password_required() ) {
	return;
}
// Получаем комментарии
$comments  = get_comments( array(
	'post_id' => get_the_ID(),
	'status'  => 'approve'
) );
$commenter = wp_get_current_commenter();
$product   = new FS_Product();
?>
<div class="fs-comments">
    <div class="fs-comments__meta">
        <div class="fs-comments__total"><?php esc_html_e( 'Всего отзывов:', 'adtools' ); ?>
            <span><?php echo get_comments_number(); ?></span></div>
		<?php fs_product_rating(); ?>
        <div class="fs-comments__avg-rating"><?php esc_html_e( 'Средняя оценка', 'adtools' ); ?> <span>4,5</span></div>
    </div>
    <div class="fs-comments__bts">
        <div class="fs-comments__bts--header"><?php esc_html_e( 'Оставьте свой отзыв об этом товаре', 'adtools' ); ?></div>
        <button class="btn btn-primary" data-remodal-target="fs-comment-form"><?php esc_html_e( 'Оставить отзыв', 'adtools' ); ?></button>
    </div>
    <div class="fs-comments__area">
        <div class="fs-comments__list">
			<?php
			if ( $comments ) {
				wp_list_comments(
					array(
						'style'      => 'div',
						'short_ping' => true,
						'echo'       => true,
						'callback'   => 'fs_comment_single'
					), $comments
				);
			}
			?>
        </div><!-- .fs-comments__list -->

        <div class="remodal xs" data-remodal-id="fs-comment-form"
             data-remodal-options="hashTracking: false, closeOnOutsideClick: false">
            <button data-remodal-action="close"><?php adtools_icon( 'modal-close' ); ?></button>
            <div class="fs-comments__form">
				<?php
				if ( ! comments_open() ) :
					?>
                    <p class="fs-comments__no-comments"><?php esc_html_e( 'Reviews and comments are closed.', 'f-shop' ); ?></p>
				<?php endif; ?>
				<?php
				comment_form( [
					'title_reply'        => __( 'Give feedback', 'f-shop' ),
					'title_reply_before' => '<div id="reply-title" class="comment-reply-title remodal__title">',
					'title_reply_after'  => '</div>',
					'fields'             => array(
						'author' => '<p class="comment-form-author">
		<input id="author" placeholder="' . __( 'Name', 'f-shop' ) . '" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '"/>
	</p>',
						'email'  => '<p class="comment-form-email">
		<input id="email" name="email" placeholder="' . __( 'Email', 'f-shop' ) . '"  value="' . esc_attr( $commenter['comment_author_email'] ) . '" aria-describedby="email-notes" />
	</p>',

					),

					'comment_notes_before' => null,
					'comment_notes_after'  => null,
					'comment_field'        => '<p class="comment-form-comment"><textarea id="comment" placeholder="' . _x( 'Comment', 'noun' ) . '" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
					'label_submit'         => __( 'Give feedback', 'f-shop' ),
					'class_submit'         => 'btn btn-primary'
				] ); ?>
            </div><!-- .fs-comments__form -->
        </div>
    </div>
	<?php the_comments_navigation(); ?>
</div>