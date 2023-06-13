<?php
class Elementor_Podcast_Player_Widget_Advanced extends \Elementor\Widget_Base {

	public function get_name() {
		return 'podcast-player-widget-advanced';
	}

	public function get_title() {
		return 'RSS Display';
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'podcast', 'podcasts', 'gallery', 'library' ];
	}

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Podcast Player', 'podcast-player-widget-advanced' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'ressfeedtitle',
			[
				'label' => __( 'Podcast Category Title', 'podcast-player-widget-advanced' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text'
			]
		);
		$this->add_control(
			'podcastamount',
			[
				'label' => __( 'Amount Of Podcasts', 'podcast-player-widget-advanced' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 30,
				'step' => 1,
				'default' => 6
			]
		);
		$this->add_control(
			'url',
			[
				'label' => __( 'Podcast RSS URL', 'podcast-player-widget-advanced' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'url',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		
		$settings = $this->get_settings_for_display();
		include_once( ABSPATH . WPINC . '/feed.php' );
		$rss = fetch_feed( $settings['url'] );
		if ( ! is_wp_error( $rss ) ) :
		$maxitems = $rss->get_item_quantity( $settings['podcastamount'] );
		$rss_items = $rss->get_items( 0, $maxitems );
		endif;
		?>
<div id="dbc-single-podcast-holder">
	    <audio src="" controlsList="nodownload" class="default-podcast-player" preload="true"></audio>
		<?php if ( $maxitems == 0 ) : ?>
		<p style="color: red;">No Podcasts To Display</p>
		<?php else: ?>
		<?php //var_dump($rss) ?>
		<?php foreach( $rss_items as $item ) : ?>

		<div data-podcast-container="" class="dbc-single-podcast">
			<div class="dbc-single-podcast-spacer">
				<?php if ($enclosure = $item->get_enclosure()) : $podcastmp = $enclosure->get_link(); endif; ?>
				<div class="dbc-single-podcast-background-image">
					<div class="podcast-title">
						<h2 class="podcast-title"><span class="podcast-title-spacer"><?php echo $item->get_title(); ?></span></h2>
					</div>
				</div>
				<div class="podcast-player">
					<div class="pp-actions">
						<audio src="<?php $podcastmp; ?>" preload="true" controlsList="nodownload"></audio>
						<a class="gradient-button play-button" id="play" data-podcast-url="<?php echo $podcastmp; ?>">
							<i class='fa fa-play-circle-o'> Play</i>
							<i class="fa fa-pause-circle-o hidden"> Pause</i>
						</a>
					</div>
					<div class="pp-seeker">
						<input type="range" id="seek" class="seek-bar" value="0" max=""/>
					</div>
					<div class="pp-audio">
						<a href="" class="gradient-button audio-button" id="audio"><i class="fa fa-volume-up" aria-hidden="true"></i>
</a>
					</div>
					<div style="clear: both;"></div>
					<span class="gradient-button total-duration"><small>00:00</small></span>
				</div>
			</div>
		</div>

		<?php endforeach; ?>
		<?php endif; ?>
</div>
		<?php
	}
}