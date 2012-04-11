<div id="module-stats" class="module stats">
	<h3><?php echo $content->title; ?><span class="icon stats">Stats</span></h3>
	<a class="stat subscribers" href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">
		<strong class="number"><?php echo $content->subscribers; ?></strong>
		<span class="unit"><?php echo _n( 'Reader', 'Readers', $content->subscribers ); ?></span>
	</a>
	<a class="stat entries" href="<?php URL::out( 'display_page', array( 'slug' => 'archive' ) ); ?>">
		<strong class="number"><?php echo $content->posts; ?></strong>
		<span class="unit"><?php echo _n( 'Post', 'Posts', $content->posts ); ?></span>
	</a>
	<a class="stat comments" href="<?php URL::out( 'display_page', array( 'slug' => 'comments' ) ); ?>">
		<strong class="number"><?php echo $content->comments; ?></strong>
		<span class="unit"><?php echo _n( 'Comment', 'Comments', $content->comments ); ?></span>
	</a>
	<a class="stat tags" href="<?php URL::out( 'display_page', array( 'slug' => 'tags' ) ); ?>">
		<strong class="number"><?php echo $content->tags; ?></strong>
		<span class="unit"><?php echo _n( 'Tag', 'Tags', $content->tags ); ?></span>
	</a>
</div>