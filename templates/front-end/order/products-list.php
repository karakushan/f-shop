<tr style="background-color:white">
<td style="text-align:center;border-width:1px;border-style:solid;border-color:rgb(212,223,230);padding:3px 5px"><?php echo $id ?></td>
	<td style="text-align:center;border-width:1px;border-style:solid;border-color:rgb(212,223,230);padding:3px 5px"><?php echo fs_product_code($id) ?></td>
	<td style="border-width:1px;border-style:solid;border-color:rgb(212,223,230);padding:3px 5px"><a href="<?php echo get_permalink($id) ?>" style="color:rgb(0,153,255)" target="_blank" data-saferedirecturl="https://www.google.com/url?hl=uk&q=<?php echo get_permalink($id) ?>&source=gmail&ust=1481615972810000&usg=AFQjCNHcicJw0rhYpLSnLJduK-9aKidVlw"><?php echo get_the_title($id) ?></a></td>
	<td style="text-align:center;border-width:1px;border-style:solid;border-color:rgb(212,223,230);padding:3px 5px"><?php echo $product['count'] ?> шт.</td>
	<td style="text-align:center;border-width:1px;border-style:solid;border-color:rgb(212,223,230);padding:3px 5px"><?php fs_the_wholesale_price($id) ?></td>
	<td style="text-align:center;border-width:1px;border-style:solid;border-color:rgb(212,223,230);padding:3px 5px"><?php fs_the_price($id) ?></td>
</tr>