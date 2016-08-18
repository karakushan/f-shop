<?php  
function fs_per_page_filter($post_count=array(12,24,36,48,60,100),$echo=true)
{
	$filters=new FS\FS_Filters;
	$page_filter=$filters->posts_per_page_filter($post_count);
	if ($echo===true) {
		echo $page_filter;
	}else{
		return $page_filter;
	}
}