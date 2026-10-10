<b>Foodmart</b> <p>Data Foodmart</p>
<?php if (! empty($foodmart) && is_array($foodmart)): ?>
<table>
<tr><th>Tanggal</th><th>Customer</th><th>Produk</th></tr>
<?php foreach ($foodmart as $foodmart_item): ?>
<tr>
<td> <?= esc($foodmart_item['the_day']) ?>,
<?= esc($foodmart_item['day_of_month']) ?>
<?= esc($foodmart_item['the_month']) ?>
<?= esc($foodmart_item['the_year']) ?> </td>
<td> (<?= esc($foodmart_item['account_num']) ?>)
<?= esc($foodmart_item['fullname']) ?> </td>
<td> <?= esc($foodmart_item['product_name']) ?>
(<?= esc($foodmart_item['brand_name']) ?>) </td>
</tr>
<?php endforeach ?> </table>
<?php else: ?>
<h3>No Data</h3>
<p>Unable to find any data for you.</p>
<?php endif ?>
