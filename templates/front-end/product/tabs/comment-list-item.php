<div id="comment-<?php echo $comment->comment_ID; ?>"
     class="comment byuser comment-author-admin even thread-even depth-<?php echo $depth; ?> parent">
    <article id="div-comment-<?php echo $comment->comment_ID; ?>" class="comment-body">
        <footer class="comment-meta">
            <div class="comment-author vcard">
                <b class="fn">
                    <a rel="external nofollow ugc"
                       class="url" itemprop="name"><?php echo $user->data->display_name; ?></a>
                </b>
            </div><!-- .comment-author -->
            <div class="comment-metadata">
                <a>
                    <time>
                        <?php echo date_i18n( 'd F Y в H:i', strtotime( $comment->comment_date ) ); ?>
                    </time>
                </a>
            </div><!-- .comment-metadata -->
            <?php fs_product_rating( $comment->comment_post_ID ); ?>
        </footer><!-- .comment-meta -->

        <div class="comment-content">
            <div class="comment-text">
                <?php echo apply_filters( 'the_content', $comment->comment_content ); ?>
            </div>
            <div class="comment-buttons">
                <div class="comment-rating">
                    <?php esc_html_e( 'Product evaluation:', 'f-shop' ); ?> <span class="comment-rating__stars">
						<?php fs_product_rating( $comment->comment_post_ID ); ?>
					</span>
                </div>
                <?php do_action( 'fs_product_comment_likes', $comment->comment_ID ); ?>
            </div>
        </div><!-- .comment-content -->
    </article><!-- .comment-body -->