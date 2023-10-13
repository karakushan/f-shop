<?php
echo apply_filters( 'the_content', get_post_meta( get_the_ID(), '_fs_delivery_description', 1 ) );