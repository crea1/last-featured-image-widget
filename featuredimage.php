<?php
/**
 * Plugin Name: Last featured Image Widget
 * Plugin URI: http://something.com/widget
 * Description: A widget that shows the featured image of the most recent post in a selected category
 * Version: 0.1
 * Author: Marius H. Kristensen
 * Author URI: http://www.suiram.org
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'lastfeatimg_load_widgets' );

/**
 * Register our widget.
 * 'LastFeatImg_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function lastfeatimg_load_widgets() {
	register_widget( 'LastFeatImg_Widget' );
}

/**
 * Example Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class LastFeatImg_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function LastFeatImg_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget to display latest featured image in a category', 'example') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'lastfeatimg-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'lastfeatimg-widget', __('Last Featured Image', 'example'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$imageHeight = $instance['imageHeight'];
		$imageWidth = $instance['imageWidth'];
		$the_category = $instance['cat'];
		$linkingMode = $instance['linkTo'];		
		if (!isset($instance['txtCustomUrl']))
		{
		$customURL = '#';
		}	else {
			$customURL = $instance['txtCustomUrl'];
		}
		// Query to run and fetch latest post of a category
		$my_query = new WP_Query('cat='.$the_category.'&showposts=1'); 
		
		if ($imageHeight < 1 || $imageWidth < 1) {
			$imageHeight = 50;
			$imageWidth  = 50;
		}

		/* Other variables */
		$plugin_dir_path = plugin_dir_url( __FILE__ );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;
		
		//echo 'Kategorien som er valgt: ' . $the_category;
		while ($my_query->have_posts()) : $my_query->the_post(); 
			
			// Populate link for either article or category
			if ( strcmp("category",trim($linkingMode)) == 0 ) {
				$link = get_category_link( $the_category );
			} elseif ( strcmp("customUrl",trim($linkingMode)) == 0 ) {
				$link = $customURL;
			} else {
				$link = get_permalink();
			}
			
			echo '<div class="featImageWidget">';
			echo '<a class="featImageWidgetImage" href="';
			echo $link;
			echo '" rel="bookmark">';			
			the_post_thumbnail(array($imageWidth,$imageHeight));
			echo '</a>';
			echo '<div class="featImageWidgetTitle">';
			echo '<a class="featImageWidget" href="';
			echo $link;
			echo '" rel="bookmark">';
			the_title();
			echo '</a></div></div>';
		endwhile;
		

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['imageWidth'] = strip_tags( $new_instance['imageWidth'] );
		$instance['imageHeight'] = strip_tags( $new_instance['imageHeight'] );
		$instance['cat'] = strip_tags( $new_instance['cat']);		
		$instance['linkTo'] = strip_tags( $new_instance['linkTo']);	
		$instance['txtCustomUrl'] = strip_tags( $new_instance['txtCustomUrl']);
		
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Featured image', 'example') );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>"  />
		</p>
		
		<!-- Category dropdown -->
		<p>
			<label><?php _e('Category'); ?></label>
			<?php wp_dropdown_categories( array( 'name' => $this->get_field_name("cat"), 'selected' => $instance["cat"] ) ); ?>
		</p>
		
		<!-- Image size -->
		<p>
			<label for="<?php echo $this->get_field_id('imageWidth'); ?>">
				<?php _e('Image width:'); ?>
				<input id="<?php echo $this->get_field_id('imageWidth'); ?>" name="<?php echo $this->get_field_name('imageWidth'); ?>" type="text" value="<?php echo $instance['imageWidth']; ?>" size="3" /> pixels
			</label>
		</p>
		<p>	
			<label for="<?php echo $this->get_field_id('imageHeight'); ?>">
				<?php _e('Image height:'); ?>
				<input id="<?php echo $this->get_field_id('imageHeight'); ?>" name="<?php echo $this->get_field_name('imageHeight'); ?>" type="text" value="<?php echo $instance['imageHeight']; ?>" size="3" /> pixels
			</label>
		</p>			
		
		<!-- Image link option -->
		<p>	
			<label for="<?php echo $this->get_field_id('linkTo'); ?>">
				<span><?php _e('Link to:'); ?></span>
				<input type="radio" name="<?php echo $this->get_field_name('linkTo'); ?>" value="article"<?php checked( 'article' == $instance['linkTo'] ); ?> />Article 
				<input type="radio" name="<?php echo $this->get_field_name('linkTo'); ?>" value="category"<?php checked( 'category' == $instance['linkTo'] ); ?> />Category
				<input type="radio" name="<?php echo $this->get_field_name('linkTo'); ?>" value="customUrl"<?php checked( 'customUrl' == $instance['linkTo'] ); ?> />URL				
				<input id="<?php echo $this->get_field_id('txtCustomUrl'); ?>" name="<?php echo $this->get_field_name('txtCustomUrl'); ?>" type="text" value="<?php echo $instance['txtCustomUrl']; ?>" />
			</label>
		</p>				
		

	<?php
	}
}

?>