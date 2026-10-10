<p><b>News : Berita ANTARA</b></p>
<?php if (! empty($title) && is_array($title)): ?>
<table width = 640>
 <?php for ($x = 0; $x < count($title); $x++) { ?>
 <tr>
 <td>
 <p><b> <?php echo$title[$x]; ?></b><br>
 <?php echo '<a href="'.$link[$x].'" target = "blank">'
.$link[$x].'</a>'; ?> <br>
 <?php echo $pubDate[$x]; ?> </p>
 <p><?php echo $description[$x]; ?>
 </p><br>
 </td>
 </tr>
 <?php } ?>
</table>
<?php else: ?>
<h3>No Data</h3>
<p>Unable to find any data for you.</p>
<?php endif ?>
