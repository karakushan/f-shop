<?php

/**
 * @param array $post_count
 * @param bool $echo
 * @return bool|string
 */
function fs_per_page_filter($post_count=array(), $echo=true)
{
	$filters=new FS\FS_Filters;
    if (count($post_count)==0 ){
        $post_count=array(12,24,36,48,60,100);
    }
	$page_filter=$filters->posts_per_page_filter($post_count);
	if (true === $echo){
		echo $page_filter;
	}else{
		return $page_filter;
	}
}
