<div class="col-md-12 cart-page">
<div class="row p-listing">
<!-- <div class="delete-product" title="удалить все товары" onclick="if (confirm('Вы действительно хотите удалить все товары из корзины?') ) { document.location.href='/cart/?cart=delete'}">
<i class="fa fa-times"></i> Удалить все товары</div> -->
<h3>в корзине <?php fs_product_count(true) ?> товара</h3>
<table class="table cart-view">
<tr>
	<th colspan="3"><span>Товар </span></th>
	<th ><span>артикул</span></th>
	<th><span>цена</span></th>
	<th><span>количество </span></th>
	<th><span>стоимость</span></th>
</tr>
