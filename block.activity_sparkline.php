<div id="module-activity" class="module recent activity">
	<h3><span class="legend">
		<a href="<?php URL::out( 'display_page', array( 'slug' => 'comments' ) ); ?>" title="<?php echo _t('Height corresponds to number of comments'); ?>"><?php echo _t('height:'); ?> <strong class="unit"><?php echo _t( 'comments' ); ?></strong></a>
		&amp;
		<a href="<?php URL::out( 'display_page', array( 'slug' => 'archive' ) ); ?>" title="Color corresponds to number of posts">color: <strong class="unit">posts</strong></a>	
	</span>
	<?php echo $content->title; ?></h3>
	<ol class="items days pseudize">
	<?php foreach($content->days as $day) {
		?>
		<li class="item day"><a href="<?php echo URL::get('display_entries_by_date', array('year' => $day['date']->format('Y'), 'month' => $day['date']->format('m'), 'day' => $day['date']->format('d'))); ?>" class="up<?php echo $day['posts']; ?>">
			<span class="bar" style="height:<?php echo 5 + $day['comments'] * 3; ?>px"><?php echo sprintf( _t( '%s comments' ), $day['comments'] ); ?></span>
			<span class="day"><?php echo $day['date']->format('d'); ?></span>
		</a></li>
	<?php } ?>
	</ol>
</div>