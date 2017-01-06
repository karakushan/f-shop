<tr>
	<td style="  text-align: center;  border: 1px solid #12353b;
	padding: 7px;text-align: center;"><?php echo get_the_title($id) ?></td>
	<td style="    border: 1px solid #12353b;
	padding: 7px;text-align: center;"><?php echo fs_product_code($id) ?></td>

	<td style="    border: 1px solid #12353b;
	padding: 7px;text-align: center;">-</td>
	<td style="    border: 1px solid #12353b;
	padding: 7px;text-align: center;"><?php echo $product['count'] ?></td>
	<td style="    border: 1px solid #12353b;
	padding: 7px;text-align: center;"><?php echo fs_the_price($id) ?></td>
	<td style="    border: 1px solid #12353b;
	padding: 7px;text-align: center;"><?php echo fs_row_price($id,$product['count'],true,'%s <span>%s</span>',false) ?></td>
</tr>