<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews" class="woocommerce-Reviews">
	<div id="comments">
		<h2 class="woocommerce-Reviews-title"><?php
			if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' && ( $count = $product->get_review_count() ) ) {
				/* translators: 1: reviews count 2: product name */
				printf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'motors' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
			} else {
				_e( 'Reviews', 'motors' );
			}
		?></h2>

		<?php if ( have_comments() ) : ?>

			<ol class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links( apply_filters( 'woocommerce_comment_pagination_args', array(
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
					'type'      => 'list',
				) ) );
				echo '</nav>';
			endif; ?>

		<?php else : ?>

			<p class="woocommerce-noreviews"><?php _e( 'There are no reviews yet.', 'motors' ); ?></p>

		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

		<div id="review_form_wrapper">
			<div id="review_form">
				<?php
					$commenter = wp_get_current_commenter();

					$comment_form = array(
						'title_reply'          => have_comments() ? __( 'Add a review', 'motors' ) : __( 'Be the first to review', 'motors' ) . ' &ldquo;' . get_the_title() . '&rdquo;',
						'title_reply_to'       => __( 'Leave a Reply to %s', 'motors' ),
						'title_reply_before'   => '<span id="reply-title" class="comment-reply-title">',
						'title_reply_after'    => '</span>',
						'comment_notes_after'  => '',
						'fields'               => array(
							'author' => '<div class="row">' .
										'<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' .
										'<p class="comment-form-author">' .
							            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" placeholder="'. __( 'Name', 'motors' ) .' *" /></p>' .
										'</div>',
							'email'  => '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' .
										'<p class="comment-form-email">' .
							            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" placeholder="' . __( 'Email', 'motors' ) . ' *" /></p>' .
										'</div>' .
										'</div>',
						),
						'label_submit'  => __( 'Submit', 'motors' ),
						'logged_in_as'  => '',
						'comment_field' => ''
					);

					if ( $account_page_url = wc_get_page_permalink( 'myaccount' ) ) {
						$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a review.', 'woocommerce' ), esc_url( $account_page_url ) ) . '</p>';
					}

					if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
						$comment_form['comment_field'] = '<p class="comment-form-rating"><select name="rating" id="rating">
									<option value="">' . __( 'Rate&hellip;', 'motors' ) . '</option>
									<option value="5">' . __( 'Perfect', 'motors' ) . '</option>
									<option value="4">' . __( 'Good', 'motors' ) . '</option>
									<option value="3">' . __( 'Average', 'motors' ) . '</option>
									<option value="2">' . __( 'Not that bad', 'motors' ) . '</option>
									<option value="1">' . __( 'Very Poor', 'motors' ) . '</option>
								</select></p>';
					}

					$comment_form['comment_field'] .= '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="' . __( 'Your Review', 'motors' ) . '"></textarea></p>';


					comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>

	<?php else : ?>

		<p class="woocommerce-verification-required"><?php _e( 'Only logged in customers who have purchased this product may leave a review.', 'motors' ); ?></p>

	<?php endif; ?>

	<div class="clear"></div>
</div>
