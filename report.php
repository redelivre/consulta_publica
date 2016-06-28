<?php

/**
 * Based on http://www.foxrunsoftware.net/articles/wordpress/add-custom-bulk-action/
*
*/

class ConsultaPublicaReport
{
	public function __construct()
	{
		//Bulk actions

		add_action('admin_print_scripts', array( $this, 'admin_scripts'));

		add_action('load-edit.php',         array( $this, 'bulk_action'));
		//add_action('admin_notices',         array( $this, 'admin_notices'));
		
		add_filter( 'manage_posts_columns' , array($this, 'manage_posts_columns' ), 1000, 2);
		add_action( 'manage_consultas_posts_custom_column' , array($this, 'display_posts_print'), 10, 2 );
	}

	/**
	 * add Bulk Action to post list
	 */
	function admin_scripts()
	{
		global $post_type;

		$currentScreen = get_current_screen();

		if( $currentScreen->id == 'edit-consultas' && ( $post_type == 'consultas' ) )
		{
			wp_enqueue_script('ConsultaPublicaReport', plugin_dir_url(__FILE__)."/js/admin.js", array('jquery'), '1.0', true);
			wp_localize_script('ConsultaPublicaReport', 'ConsultaPublicaReport', array('actions' =>
				array(
					//'print' => array( 'label' => __('Print', 'wp-side-comments') ),
					'export' => array( 'label' => __('CSV report', 'consulta_publica') ),
					//'export_day' => array( 'label' => __('CSV by day', 'wp-side-comments') ),
					//'export_user' => array( 'label' => __('CSV by user', 'wp-side-comments') ),
				)
			));
		}
	}
		
		
	/**
	 * handle the Bulk Action
	 *
	 * Based on the post http://wordpress.stackexchange.com/questions/29822/custom-bulk-action
	 */
	function bulk_action()
	{
		global $typenow;
		$post_type = $typenow;

		if($post_type == 'consultas')
		{
			// get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
			$action = $wp_list_table->current_action();
				
			$allowed_actions = array('print', "export", 'export_day', 'export_user');
			if(!in_array($action, $allowed_actions)) return;
				
			// security check
			//check_admin_referer('bulk-consultas');
				
			// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
			if(isset($_REQUEST['post'])) {
				$post_ids = array_map('intval', $_REQUEST['post']);
			}
				
			if(empty($post_ids)) return;
				
			global $wp_query;
				
			switch($action)
			{
				case 'export':
					$wp_query = new \WP_Query( array(
						'post__in' => $post_ids,
						'orderby' => 'title',
						'order' => 'ASC',
						'post_type' => $post_type,
						'consultas_print_csv' => 2,
					));
					break;
				break;
				/*case 'export_day':
					$wp_query = new \WP_Query( array(
					'post__in' => $post_ids,
					'orderby' => 'title',
					'order' => 'ASC',
					'post_type' => $post_type,
					'wp_side_comments_print_csv' => 2,
					));
					break;
				case 'export_user':
					$wp_query = new \WP_Query( array(
					'post__in' => $post_ids,
					'orderby' => 'title',
					'order' => 'ASC',
					'post_type' => $post_type,
					'wp_side_comments_print_csv' => 3,
					));
					break;
				case 'print':
					$wp_query = new \WP_Query( array(
					'post__in' => $post_ids,
					'orderby' => 'title',
					'order' => 'ASC',
					'post_type' => $post_type,
					));
					break;*/
				default: return;
			}
				
			include(plugin_dir_path(__FILE__) .'/report_print.php');
			exit();
		}
	}
	
	function manage_posts_columns($columns, $post_type)
	{
		if($post_type == 'consultas')
		{
			return array_merge( $columns,
				array( 'consulta_publica_report' => __( 'Report', 'consulta_publica' ) ) );
		}
		return $columns;
	}
	
	
	function display_posts_print( $column, $post_id )
	{
		if ($column == 'consulta_publica_report'){
			//echo '<a href="" target="_blank" title="'.__('Imprimir textos com comentários por parágrafo.','consulta_publica').'" ><span class="consulta_publica-icon-print-1" onclick="" ></span></a>';
			echo '<a href="" target="_blank" title="'.__('Exportar CSV','consulta_publica').'" ><span class="consulta-publica-icon-grid" ></span></a>';
			//echo '<a href="" target="_blank" title="'.__('Exportar CSV com número de comentários por dia','consulta_publica').'" ><span class="consulta_publica-icon-calendar-alt" ></span></a>';
			//echo '<a href="" target="_blank" title="'.__('Exportar CSV com número de comentários por usuário','consulta_publica').'" ><span class="consulta_publica-icon-user-pair" ></span></a>';
		}
	}

}

$ConsultaPublicaReport = new ConsultaPublicaReport();