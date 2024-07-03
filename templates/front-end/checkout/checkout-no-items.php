<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 07.03.2019
 * Time: 11:31
 */ ?>
<p class="fs-info-block"><i class="fas fa-info-circle"></i>
	<?php printf( __( 'Your basket is empty. <a href="%s">To catalog</a>', 'f-shop' ), esc_url( fs_get_catalog_link() ) ) ?>
</p>
