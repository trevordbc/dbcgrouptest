<?php
class Elementor_Podcast_Player_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'podcast-player-widget';
	}

	public function get_title() {
		return 'Single Podcast Category';
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
		global $wp_roles;
		$roles = $wp_roles->get_names();
		unset($roles['administrator']);

		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__('Podcast Player', 'podcast-player-widget'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'rssfeedtitle',
			[
				'label' => __( 'Podcast Category Title', 'podcast-player-widget' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'url',
			[
				'label' => __( 'Podcast RSS URL', 'podcast-player-widget' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'protectedcontent',
			[
				'label' => __( 'Protected Content?', 'podcast-player-widget' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Default', 'podcast-player-widget' ),
					'yes' => esc_html__( 'Yes', 'podcast-player-widget' ),
					'no' => esc_html__( 'No', 'podcast-player-widget' ),
				],
				'default' => 'no',
			]
		);

		$this->add_control(
			'allowed_roles',
			[
				'label' => __( 'Allowed User Roles', 'podcast-player-widget' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $roles,
				'multiple' => true,
				'condition' => [
					'protectedcontent' => 'yes',
				],
			]
		);

		$this->add_control(
			'podcastamount',
			[
				'label' => __('Number of Podcasts', 'podcast-player-widget'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$rss_feed_title = $settings['rssfeedtitle'];
		$rss_feed_url = $settings['url'];
		$podcast_limit = $settings['podcastamount'];
		$podcast_elements = '';

		// Get the first item's title and URL
		$first_item_title = !empty($rss_items) ? esc_html($rss_items[0]->get_title()) : '';
		$first_item_duration = !empty($rss_items) ? esc_html($rss_items[0]->get_enclosure()->get_duration()) : '';
		$first_item_url = !empty($rss_items) ? esc_url($rss_items[0]->get_enclosure()->get_link()) : '';

		?>
		<style>
			#audio-player {
				width: 100%;
				background: #EFEFEF;
				padding: 10px;
				padding-bottom: 25px;
				border-radius: 15px;
				text-align: center;
				margin-top: 10px;
				margin-bottom: 15px;
			}
			#audio-player button, button#load-more {
				color: #FFF;
				background: linear-gradient(128deg, rgba(255,0,0,1) 0%, rgba(223,0,70,1) 47%, rgba(255,160,0,1) 100%); 
			}
			button#load-more {
				margin-top: 10px;
			}
			#audio-player button i {
				color: #FFF;
			}
			#seekbar::-webkit-slider-runnable-track {
			  background: linear-gradient(128deg, rgba(255,0,0,1) 0%, rgba(223,0,70,1) 47%, rgba(255,160,0,1) 100%);
			}
			#seekbar::-moz-range-track {
			  background: linear-gradient(128deg, rgba(255,0,0,1) 0%, rgba(223,0,70,1) 47%, rgba(255,160,0,1) 100%);
			}
			.seekbar-container {
			  position: relative;
			}

			.seekbar-tooltip {
			  position: absolute;
			  background-color: #333;
			  color: #fff;
			  padding: 3px 5px;
			  border-radius: 3px;
			  font-size: 12px;
			  white-space: nowrap;
			  display: block;
			  top: 43px;
			}

			#seekbar {
			  position: relative;
			  z-index: 1;
			  background: #000;
			  width: 100%;
			  --seekbar-value: 0;
			  padding: 5px;
			  border-radius: 10px;
			  margin-top: 10px;
			}
			#currentTime, #duration {
				line-height: 25px;
			}
			.podcast-element-container {
			  display: flex;
			  justify-content: space-between;
			  align-items: center;
			  padding: 10px;
			  font-size: 14px;
			  background: #EFEFEF;
			  border-radius: 10px;
			  margin: 10px 0px 0px;
			}
			.podcast-element.active::after {
				display: block;
				content: "Now Playing";
				margin: 0px 15px 0px;
				padding: 2px 4px;
				background: linear-gradient(128deg, rgba(255,0,0,1) 0%, rgba(223,0,70,1) 47%, rgba(255,160,0,1) 100%);
				font-size: 10px;
				letter-spacing: 2px;
				text-transform: uppercase;
				font-weight: 900;
				color: #fff;
				border-radius: 0px 0px 5px 5px;
				border: 1px solid #E73F47;
				border-top: 0px;
			}
		</style>
		<div class="container">
			<div id="audio-player" data-podcast-rss-url="<?php echo esc_attr($rss_feed_url); ?>">
				<div id="currentTitle" style="text-align: center; font-size: 14px;"></div>
				<div id="duration" style="text-align: center; font-size: 12px;"></div>
				<audio id="player" data-audio-limit="<?php echo esc_attr($podcast_limit); ?>" src="<?php echo $first_item_url; ?>"></audio>
				<div id="controls">
					<button id="play" class="control"><i class="fas fa-play"></i></button>
					<button id="pause" class="control"><i class="fas fa-pause"></i></button>
					<button id="stop" class="control"><i class="fas fa-stop"></i></button>
				</div>
				<div id="seeker" class="seekbar-container">
				  <input type="range" id="seekbar" value="0" step="1" min="0">
				  <div class="seekbar-tooltip" id="seekbar-tooltip">00:00</div>
				</div>

			</div>

			<div class="podcast-player-elements"></div>
			<button id="load-more" data-rss-feed-url="<?php echo esc_attr($rss_feed_url); ?>" data-podcast-limit="<?php echo esc_attr($podcast_limit); ?>">Load More</button>
		</div>
		<?php
	}
}