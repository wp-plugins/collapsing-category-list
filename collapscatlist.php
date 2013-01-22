<?php
/*
Plugin Name: Collapsing category list
Plugin URI: http://www.interadictos.es/category/proyectos-personales/plugins-wordpress/
Description: Filter for collapsing the categories list
Version: 0.0.4
Author: José Miguel Gil Córdoba
Author URI: http://josemiguel.nom.es
License: GPLv2 or later
*/
define( 'PLUGIN_NAME', 'collapsing-category-list' );


/**
 * Class Walker_Category_Modify
 * Class modify from Walker_Category
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
          extract( $args );

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

          if ( 1 != $args['has_children'] ){
            $image_children = '<img src="'. plugin_dir_url( __FILE__ ) .'/images/nothing.gif" width="9px" height="9px" />';
          } else {
            if ( !empty($current_category) ) {
              $_current_category = get_term( $current_category, $category->taxonomy );
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

/**
 * Make a page options
 */
/*function collapscatlist_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}


	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}

function collapscatlist_menu() {
	add_plugins_page( 'Collapsing Category List Options',
                'Colapsing Category List',
                'manage_options',
                'collapscatlist_options',
                'collapscatlist_options' );
}

add_action( 'admin_menu', 'collapscatlist_menu' );*/
?>
