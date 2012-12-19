<?php
/*
Plugin Name: Text Blocks
Plugin URI: http://halgatewood.com/text-blocks
Description: Blocks of content that can be used throughout the site in theme templates and widgets.
Author: Hal Gatewood
Author URI: http://www.halgatewood.com
Version: 1.0
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/



// ADDS
add_action( 'init', 'create_text_block_type' );
add_filter( 'post_updated_messages', 'codex_text_block_updated_messages' );
add_action( 'admin_head', 'textblocks_css' );
add_filter( 'manage_edit-text-blocks_columns', 'textblocks_columns' );
add_action( 'manage_text-blocks_posts_custom_column', 'textblocks_add_columns' );
add_action( 'widgets_init', create_function('', 'return register_widget("TextBlocksWidget");') );
add_shortcode( 'text-blocks', 'text_blocks_shortcode');


// CUSTOM POST TYPE
function create_text_block_type() 
{
  	$labels = array(
				    'name' 					=> __('Text Blocks', 'text-blocks'),
				    'singular_name' 		=> __('Text Block', 'text-blocks'),
				    'add_new' 				=> __('Add New', 'text-blocks'),
				    'add_new_item' 			=> __('Add New Block', 'text-blocks'),
				    'edit_item' 			=> __('Edit Text Block', 'text-blocks'),
				    'new_item' 				=> __('New Block', 'text-blocks'),
				    'all_items' 			=> __('All Text Blocks', 'text-blocks'),
				    'view_item' 			=> __('View Block', 'text-blocks'),
				    'search_items' 			=> __('Search Text Blocks', 'text-blocks'),
				    'not_found' 			=> __('No blocks found', 'text-blocks'),
				    'not_found_in_trash' 	=> __('No blocks found in Trash', 'text-blocks'), 
				    'parent_item_colon' 	=> '',
				    'menu_name' 			=> __('Text Blocks')
  					);
						
	$args = array(
					'labels' 				=> $labels,
					'public' 				=> false,
					'publicly_queryable' 	=> true,
					'show_ui' 				=> true, 
					'show_in_menu' 			=> true, 
					'query_var' 			=> true,
					'rewrite' 				=> array('with_front' => false),
					'capability_type' 		=> 'post',
					'has_archive' 			=> false,
					'hierarchical' 			=> false,
					'menu_position' 		=> 26.4,
					'exclude_from_search' 	=> true,
					'supports' 				=> array( 'title', 'editor' )
					);
					
	register_post_type( 'text-blocks', $args );
}

// CUSTOM POST TYPE MESSAGES
function codex_text_block_updated_messages( $messages ) 
{
	global $post, $post_ID;

	$messages['text-blocks'] = array(
							0 => '',
							1 => sprintf( __('Text Block updated. <a href="%s">View Widget</a>'), esc_url( get_permalink($post_ID) ) ),
							2 => __('Custom field updated.'),
							3 => __('Custom field deleted.'),
							4 => __('Text Block updated.'),
							5 => isset($_GET['revision']) ? sprintf( __('Block restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
							6 => sprintf( __('Block published. <a href="%s">View Block</a>'), esc_url( get_permalink($post_ID) ) ),
							7 => __('Block saved.'),
							8 => sprintf( __('Block submitted. <a target="_blank" href="%s">Preview Block</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
							9 => sprintf( __('Block scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Widget</a>'),
							  		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
							10 => sprintf( __('Block draft updated. <a target="_blank" href="%s">Preview Widget</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
							);
	return $messages;
}


// ADMIN: WIDGET ICONS
function textblocks_css()
{
	$icon 		= plugins_url( 'text-blocks' ) . "/menu-icon.png";
	$icon_32 	= plugins_url( 'text-blocks' ) . "/icon-32.png";
	
	echo "
		<style> 
			#menu-posts-text-blocks .wp-menu-image { background: url({$icon}) no-repeat 6px -26px !important; }
			#menu-posts-text-blocks.wp-has-current-submenu .wp-menu-image { background-position:6px 6px!important; }
			.icon32-posts-text-blocks { background: url({$icon_32}) no-repeat 0 0 !important; }
		</style>
	";	
}

// CUSTOM COLUMNS
function textblocks_columns( $columns ) 
{
	return array(
		'cb'       	=> '<input type="checkbox" />',
		'title'    	=> __( 'Title', 'text-blocks' ),
		'shortcode'	=> __( 'Shortcode', 'text-blocks' ),
		'text'     	=> __( 'Text', 'text-blocks' ),
		'date'     	=> __( 'Date', 'text-blocks' )
	);
}

// CUSTOM COLUMN DATA
function textblocks_add_columns( $column )
{
	global $post;
	$edit_link = get_edit_post_link( $post->ID );

	if ( $column == 'text' ) echo strip_tags($post->post_content);	
 	if(	$column == "shortcode") echo "[text-blocks id={$post->ID}]";	
}


// TEXT BLOCK WIDGET
class TextBlocksWidget extends WP_Widget 
{
	function TextBlocksWidget() { parent::WP_Widget(false, $name = 'Text Blocks Widget'); }

    function widget($args, $instance) 
    {	
        extract( $args );
        $title 		= isset($instance['title']) ? $instance['title'] : false;
        $id 		= (int) $instance['id'];
        $block 		= get_post( $id );
        ?>
          <?php echo $before_widget; ?>
              <?php if ( $title ) echo $before_title . $title . $after_title; ?>
				<ul class="text-blocks">
					<li class="text-block"><?php echo $block->post_content; ?></li>
				</ul>
          <?php echo $after_widget; ?>
        <?php
    }
 
    function update($new_instance, $old_instance) 
    {		
		$instance = $old_instance;
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['id'] 		= strip_tags($new_instance['id']);
        return $instance;
    }
 
    function form($instance) 
    {	
        $title = isset($instance['title']) ? esc_attr($instance['title']) : "";
        $selected_block = isset($instance['id']) ? esc_attr($instance['id']) : 0;
        
        $blocks = get_posts( array('post_type' => 'text-blocks', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ));
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Text Block:'); ?></label> 
          <select class="widefat" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>">
          	<?php foreach($blocks as $block) { ?>
          	<option value="<?php echo $block->ID; ?>"><?php echo $block->post_title; ?></option>
          	<?php } ?>
          </select>
		</p>
        <?php 
    }
}

// SHOW TEXT BLOCK (needs one parameter $id)
function show_text_block($id)
{
	$content = apply_filters( 'the_content', get_post_field('post_content', $id) );
	return apply_filters( 'text_blocks_shortcode_html', $content);
}

// SHORT CODE
function text_blocks_shortcode($atts)
{
	$id = isset($atts['id']) ? (int) $atts['id'] : false;
	if($id) { return show_text_block($id); }
	else { return false; }
}


?>