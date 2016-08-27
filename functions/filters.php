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


/**
 * Добавляет возможность фильтрации по определёному атрибуту
 * @param string $group             название группы (slug)
 * @param string $type              тип фильтра 'option' (список опций в теге "select",по умолчанию) или обычный список "ul"
 * @param string $option_default    первая опция (текст) если выбран 2 параметр "option"
 */
function fs_attr_group_filter($group, $type='option', $option_default='Выберите значение')
{
    $fs_filter=new FS\FS_Filters;
    echo $fs_filter->attr_group_filter($group,$type,$option_default);
}
