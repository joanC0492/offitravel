<?php if (post_password_required()) return; ?>
        

<div id="comments" class="comments">

    <?php if(have_comments()): ?>
        
        <h3 class="title-comments">
            <?php
                $comments_number = get_comments_number();
                if ( '1' === $comments_number ) {
                    printf( esc_html_x( 'One Comment', 'comments title', 'tripgo' ) );
                } else {
                    printf(
                        esc_html( /* translators: 1: number of comments */
                            _nx(
                                '%1$s Comment',
                                '%1$s Comments',
                                $comments_number,
                                'comments title',
                                'tripgo'
                            )
                        ),
                        esc_html( number_format_i18n( $comments_number ) )
                    );
                }
                ?>
        </h3>

        <?php the_comments_navigation(); ?>

            <ul class="comment-lists">
                <?php 
                    wp_list_comments(
                        array(
                            'style'       => 'ul',
                            'short_ping'  => true,
                            'avatar_size' => 42,
                            'callback'   => function($comment, $args, $depth){
                                include get_theme_file_path('template-parts/parts/comment.php');
                            },
                        )
                    );

                 ?>
            </ul>

        <?php the_comments_navigation(); ?>
       
        
    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="no-comments">
            <?php esc_html_e( 'Comments are closed.', 'tripgo' ); ?>
        </p>
    <?php endif; ?>

   <?php 
       comment_form(
            array(
                'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
                'title_reply_after'  => '</h3>',
            )
        );
    ?>


</div><!-- end comments -->
