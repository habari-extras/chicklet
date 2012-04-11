<?php
class Chicklet extends Plugin
{
	
	private $allblocks = array();

	/**
	 * When the plugin is initialized, register the block templates and set up supporting data.
	 */
	function action_init()
	{

		$this->allblocks = array(
			'activity_sparkline' => _t( 'Activity Sparkline' ),
		);

		foreach ( array_keys( $this->allblocks ) as $blockname ) {
			$this->add_template( "block.$blockname", dirname( __FILE__ ) . "/block.$blockname.php" );
		}

		// Handle backwards compatability
		if(!is_array(Options::get('chicklet__feedname'))) {
			Options::set('chicklet__feedname', array(Options::get('chicklet__feedname')));
		}
	}
	
	/**
	 * Add available blocks to the list of possible block types.
	 *
	 * @param array $block_list an Associative array of the internal names and display names of blocks
	 *
	 * @return array The modified $block_list array
	 */
	public function filter_block_list( $block_list )
	{
		$allblocks = $this->allblocks;
		foreach ( $allblocks as $blockname => $nicename ) {
			$block_list[ $blockname ] = $nicename;
		}
		return $block_list;
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$customvalue = $ui->append( 'textmulti', 'feedname', 'chicklet__feedname', _t('Feed Addresses:') );
					$customvalue = $ui->append( 'submit', 'submit', _t('Save') );
					$ui->out();
					break;
			}
		}
	}
	
	function action_add_template_vars( $theme )
	{
		$count = $this->fetch();
		$theme->subscribers = $count;
	}
	
	static public function fetch() {
		if(Cache::get('chickler_subscribercount') == NULL) {
			$count= 0;
			
			foreach(Options::get('chicklet__feedname') as $feed) {
				$url = "https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=" . $feed ;
				$remote = RemoteRequest::get_contents($url);

				@$xml = simplexml_load_string($remote);
				
				if($xml == false) {
					return 0;
				} else {
					$count = $count + intval($xml->feed->entry['circulation']);
				}
			}
						
			Cache::set('chickler_subscribercount', $count);
		} else {
			$count = Cache::get('chickler_subscribercount');
		}
		
		return $count;
	}
	
	/**
	 * Activity Sparkline
	 *
	 * Handle activity sparkline block output
	 *
	 * @param Block $block The block instance to be configured
	 * @param Theme $theme The active theme
	 */
	public function action_block_content_recent_comments( $block, $theme )
	{
		// Number of days to show; make this configurable
		$days = 20;
		
		$i = 0;
		$days = array();
		while($i < $days) {
			$days[] = time() - $i*86400;
			$i++;
		}
		$days= array_reverse($days);
		
		foreach($days as $day) {
			$posts = $theme->get_posts(array('year' => date('Y', $day), 'month' => date('m', $day), 'day' => date('d', $day), 'nolimit' => true));
			$posts = count($posts);
			$comments = Comments::get(array('year' => date('Y', $day), 'month' => date('m', $day), 'day' => date('d', $day), 'status' => Comment::status('approved'), 'nolimit' => true));
			$comments = count($comments);
			if($posts > 0) {
				$posts = 5;
		}
		
		$block->recent_comments = $valid_comments;
	}
}
?>
