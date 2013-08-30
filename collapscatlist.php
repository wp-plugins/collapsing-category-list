<?php
/*
Plugin Name: Collapsing category list
Plugin URI: http://www.interadictos.es/category/proyectos-personales/plugins-wordpress/
Description: Filter for collapsing the categories list
Version: 0.0.6.1
Author: José Miguel Gil Córdoba
Author URI: http://josemiguel.nom.es
License: GPLv2 or later
*/
define( 'PLUGIN_NAME', 'collapsing-category-list' );

/**
 * Class Walker_Category_Modify
 * Modify class from Walker_Category
 */
class Walker_Category_Modify extends Walker_Category{

  /**
   * @see Walker::start_el()
   * @since 2.1.0
   *
   * @param string $output Passed by reference. Used to append additional content.
   * @param object $category Category data object.
   * @param int $depth Depth of category in reference to parents.
   * @param array $args
   */
  function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
          global $post;
          extract( $args );
          $current_categories = get_the_category($post->ID);

          if (is_array($current_categories)) {
            if (empty($current_category)) {
              $current_category = $current_categories[0];
            }
          }
          else {
            if (empty($current_category)) {
              $current_category = $current_categories;
            }
          }

          $cat_name = esc_attr( $category->name );
          $cat_name = apply_filters( 'list_cats', $cat_name, $category );
          $link     = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';
          if ( $use_desc_for_title == 0 || empty($category->description) )
                  $link .= 'title="' . esc_attr( sprintf( __( 'View all posts filed under %s' ), $cat_name ) ) . '"';
          else
                  $link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
          $link .= '>';
          $link .= $cat_name . '</a>';

          if ( !empty($feed_image) || !empty($feed) ) {
                  $link .= ' ';

                  if ( empty($feed_image) )
                          $link .= '(';

                  $link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $feed_type ) ) . '"';

                  if ( empty($feed) ) {
                          $alt = ' alt="' . sprintf( __( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
                  } else {
                          $title = ' title="' . $feed . '"';
                          $alt   = ' alt="' . $feed . '"';
                          $name  = $feed;
                          $link .= $title;
                  }

                  $link .= '>';

                  if ( empty($feed_image) )
                          $link .= $name;
                  else
                          $link .= "<img src='$feed_image'$alt$title" . ' />';

                  $link .= '</a>';

                  if ( empty($feed_image) )
                          $link .= ')';
          }

          if ( !empty($show_count) )
                  $link .= ' (' . intval( $category->count ) . ')';

          if ( 1 != $args['has_children'] || !$collaps_categories ){
            $image_children = '<img src="'. plugin_dir_url( __FILE__ ) .'/images/nothing.gif" width="9px" height="9px" />';
          } else {

            if ( !empty($current_category)) {
              if (is_object($current_category)) {
                $_current_category = $current_category;
              }
              else {
                $_current_category = get_term( $current_category, $category->taxonomy );
              }
              
              if ( $category->term_id == $_current_category->parent ) {
                $image_children  = '<a href="#" id="collapse">';
                $image_children .= '<img src="'. plugin_dir_url( __FILE__ ) .'/images/collapse.gif" width="9px" height="9px" />';
                $image_children .= '</a>';
              } else {
                $image_children  = '<a href="#" id="expand">';
                $image_children .= '<img src="'. plugin_dir_url( __FILE__ ) .'/images/expand.gif" width="9px" height="9px" />';
                $image_children .= '</a>';
              }
            } else {
              $image_children  = '<a href="#" id="expand">';
              $image_children .= '<img src="'. plugin_dir_url( __FILE__ ) .'/images/expand.gif" width="9px" height="9px" />';
              $image_children .= '</a>';
            }
          }

          if ( 'list' == $args['style'] ) {
                  $output .= "\t<li";
                  $class   = 'cat-item cat-item-' . $category->term_id;
                  if ( !empty($current_category) ) {
                          $_current_category = get_term( $current_category, $category->taxonomy );
                          if ( $category->term_id == $current_category )
                                  $class .= ' current-cat';
                          elseif ( $category->term_id == $_current_category->parent )
                                  $class .= ' current-cat-parent';
                  }
                  $output .= ' class="' . $class . '"';
                  $output .= ">$image_children $link\n";
          } else {
                  $output .= "\t$link<br />\n";
          }

  }
}

/**
 * Class WP_Widget_Collaps_Categories
 * Modify class WP_Widget_Categories
 */
class WP_Widget_Collaps_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_categories', 'description' => __( "A list or dropdown of categories" ) );
		parent::__construct('categories', __('Categories'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base);
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';
                $cc = ! empty ( $instance['collaps_categories'] ) ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'collaps_categories' => $cc);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category');
			wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories(apply_filters('widget_categories_args', $cat_args));
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
                $instance['collaps_categories'] = !empty($new_instance['collaps_categories']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
                $collaps_categories = isset( $instance['collaps_categories'] ) ? (bool) $instance['collaps_categories'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('collaps_categories'); ?>" name="<?php echo $this->get_field_name('collaps_categories'); ?>"<?php checked( $collaps_categories ); ?> />
		<label for="<?php echo $this->get_field_id('collaps_categories'); ?>"><?php _e( 'Collaps categories' ); ?></label></p>
<?php
	}
}

// Make a filter what modify the categories list
function my_filter_widget_categories( $args ) {
   $walker = new Walker_Category_Modify();
   $args   = array_merge( $args, array( 'walker' => $walker ) );
   return $args;
}
add_filter( 'widget_categories_args', 'my_filter_widget_categories' );

// Add the javascript file.
function my_init() {
  if ( !is_admin() ) {
    wp_enqueue_script( 'the_js', plugins_url( '/js/dropdown.js',__FILE__ ), array( 'jquery' ) );
  }
}
add_action( 'init', 'my_init' );

// Register the categories widget
function register_categories_widget() {
  unregister_widget('WP_Widget_Categories');
  register_widget('WP_Widget_Collaps_Categories');
}
add_action( 'widgets_init', 'register_categories_widget');

?>
