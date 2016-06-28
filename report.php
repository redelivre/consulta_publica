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
					'export' => array( 'label' => __('Exportar relatório (CSV)', 'consulta_publica') ),
					'export_xls' => array( 'label' => __('Exportar relatório (Excel)', 'consulta_publica') ),
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
				
			$allowed_actions = array("export", 'export_xls');
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
						'consultas_print_csv' => 1,
					));
					break;
				break;
				case 'export_xls':
					$wp_query = new \WP_Query( array(
						'post__in' => $post_ids,
						'orderby' => 'title',
						'order' => 'ASC',
						'post_type' => $post_type,
						'consultas_print_xls' => 1,
					));
					break;
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
				array( 'consulta_publica_report' => __( 'Relatório', 'consulta_publica' ) ) );
		}
		return $columns;
	}
	
	
	function display_posts_print( $column, $post_id )
	{
		if ($column == 'consulta_publica_report')
		{
			$url_base = 'edit.php?s=&post_status=all&post_type=consultas&action2=-1';
			
			echo '<a href="'.admin_url($url_base."&action=export&post%5B%5D=".$post_id).'" target="_blank" title="'.__('Exportar em formato de tabela separada por ponto e vírgula (CSV)','consulta_publica').'" ><span class="consulta-publica-icon-grid" >'.__('Exportar CSV','consulta_publica').'</span></a>';
			echo '<br/><a href="'.admin_url($url_base."&action=export_xls&post%5B%5D=".$post_id).'" target="_blank" title="'.__('Exportar em formato Microsoft Excel (XLS)','consulta_publica').'" ><span class="consulta-publica-icon-xls" >'.__('Exportar XLS','consulta_publica').'</span></a>';
		}
	}

}

$ConsultaPublicaReport = new ConsultaPublicaReport();