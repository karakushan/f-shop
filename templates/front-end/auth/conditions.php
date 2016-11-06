<?php 
$page=get_posts(array('p'=>4886,'post_type'=>'page'));
if ($page) {
	foreach ($page as $key => $p) {
		echo "<h2>{$p->post_title}</h2>";
		echo $p->post_content;
	}
}