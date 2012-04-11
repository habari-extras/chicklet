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
			'statistics' => _t( 'Statistics' )
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
	
	public static function get_stat( $type, $month = NULL )
	{
		$str= 'chicklet_stat_' . $type;
		if($month != NULL) {
			$str.= '_' . $month;
		}
			
		if(Cache::has($str)) {
			return Cache::get($str);
		}
				
		switch($type) {
			case 'entries':
			case 'posts':
				$params= array('content_type' => array( Post::type('entry'), Post::type('link')), 'nolimit' => TRUE);
				$stat= count(Posts::get($params));
				break;
			case 'subscribers':
				$stat = self::fetch();
				
				break;
			case 'comments':
				$stat= Comments::count_total(Comment::STATUS_APPROVED);
				break;
			case 'tags':
				$stat= count(Tags::vocabulary()->get_tree());
				break;
			default:
				$stat= 0;
				break;
		}
		
		Cache::set($str, $stat);
		return $stat;
	}
	
	// function action_add_template_vars( $theme )
	// {
	// 	// $count = $this->fetch();
	// 	// $theme->subscribers = $count;
	// }
	// 
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
	 * Handle activity sparkline block configuration
	 *
	 */
	public function action_block_form_activity_sparkline( $form, $block )
	{
		// $form is already assigned to a FormUI instance
		$form->append( 'text', 'sparkline_days', $block, _t( 'Days' ) );
		// No need to append a submit button as there is always a default form
		// No need to return values from an action hook
	}
	
	/**
	 * Activity Sparkline
	 *
	 * Handle activity sparkline block output
	 *
	 * @param Block $block The block instance to be configured
	 * @param Theme $theme The active theme
	 */
	public function action_block_content_activity_sparkline( $block, $theme )
	{
		// Number of days to show; make this configurable
		$n_days = $block->field_load( 'sparkline_days' );
		// 
		$i = 0;
		$days = array();
		while($i < $n_days) {
			$days[] = HabariDateTime::date_create()->modify('-' . $i . ' days');
			$i++;
		}
		$days= array_reverse($days);
		
		// Utils::debug( $days );
		
		$day_stats = array();		
		foreach($days as $day) {
			// $posts = $theme->get_posts();
			$posts = Posts::get( array('year' => $day->format('Y'), 'month' => $day->format('m'), 'day' => $day->format('d'), 'limit' => 5 ) );
			$posts = count($posts);
			// $posts = 90;
			$comments = Comments::get(array('year' => $day->format('Y'), 'month' => $day->format('m'), 'day' => $day->format('d'), 'status' => Comment::status('approved'), 'nolimit' => true));
			$comments = count($comments);
			// $comments = 5;
			if($posts > 0) {
				$posts = 5;
			}
			$day_stats[] = array(
				'posts' => $posts,
				'comments' => $comments,
				'date' => $day
			);
		// 
		}
		
		$block->days = $day_stats;
	}
	
	/**
	 * Statistics
	 *
	 * Handle statistics block configuration
	 *
	 */
	public function action_block_form_statistics( $form, $block )
	{
		// $form is already assigned to a FormUI instance
		// $form->append( 'text', 'sparkline_days', $block, _t( 'Days' ) );
		// No need to append a submit button as there is always a default form
		// No need to return values from an action hook
	}
	
	/**
	 * Statistics
	 *
	 * Handle statistics block output
	 *
	 * @param Block $block The block instance to be configured
	 * @param Theme $theme The active theme
	 */
	public function action_block_content_statistics( $block, $theme )
	{		
		$block->subscribers = self::get_stat('subscribers');
		$block->posts = self::get_stat('posts');
		$block->comments = self::get_stat('comments');
		$block->tags = self::get_stat('tags');
	}
}
?>
