<div style="padding:10px;">
	<h1 style="margin-bottom:10px;text-align:center;"><?php echo $notice['title']; ?></h1>
	<p style="margin-bottom:20px;text-align:center;">发布时间<?php echo $notice['date_added']; ?></p>
	<?php echo htmlspecialchars_decode($notice['content']); ?>
</div>