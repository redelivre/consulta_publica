<?php
/**
 * @package Consulta Publica
 * @version 0.0
 */
/*
   Plugin Name: Consulta Publica
   Plugin URI: http://github.com/redelivre/consulta_publica
   Description: Plugin for manage consulta publica
   Author: Maurilio Atila
   Version: 0.0
   Author URI: https://twitter.com/cabelotaina
 */

defined('ABSPATH') or die('No script kiddies please!');
define( 'CONSULTA_PUBLICA_PATH', plugin_dir_path( __FILE__ ) );


add_action('init', 'create_consulta');
function create_consulta()
{
  register_post_type('consultas',
      array(
        'labels' => array(
          'name' => __('Consultas', 'consultas'),
          'singular_name' => __('Consulta', 'consultas'),
          'add_new_item' => __('Adicionar Nova Consulta Pública', 'consultas'),
          'edit_item' => __('Editar Consulta Pública', 'consultas'),
          'all_items' => __('Todos as Consultas Públicas', 'consultas'),
          'update_item' => __('Atualizar Consulta Pública', 'consultas'),
          'search_items' => __('Buscar Consultas', 'consultas'),
          'menu_name' => __('Consultas', 'consultas'),
          'not_found' => __('Não Encontrado', 'consultas'),
          'not_found_in_trash' => __('Não Encontrado na lixeira', 'consultas'),
          'description' => __('Conjunto de Consultas Públicas', 'consultas')
          ),
        'public' => true,
        'rewrite' => array(
          'with_front' => false,
          'slug' => 'consultas'
          ),
        'menu_icon' => 'dashicons-admin-users',
        )
          );
}


function get_metas()
{
  return array(
      array ( 'label' => 'Inicio da Consulta Publica', 'slug'=>'data_inicio' ,'info' => 'Inicio não informado', 'html' => array ('tag'=> 'input', 'type' => 'date' ) ),
      array ( 'label' => 'Final da Consulta Publica', 'slug'=>'data_final' ,'info' => 'Final não informado', 'html' => array ('tag'=> 'input', 'type' => 'date' ) ),
            ); 
}



function consultas_the_meta($post)
{
  if( !is_object($post) ) return;
  $post = $post->queried_object;
  if (isset($post->post_type) && $post->post_type!="consultas") return; 
  if (isset($post->post_type) && $post->post_type=="consultas") 
  {
    ?>


      <ul class="post-meta">
      <?php 
      $metas = get_option('consultas_metas');
    $user = wp_get_current_user();
    foreach($metas as $meta)
    {
      if ( !in_array( 'administrator', (array) $user->roles ) &&  in_array( $meta['slug'] , array( 'email', 'phone' , 'celular', 'address' ) ) ) {
        continue;
      }
      if ($meta['html']['tag'] == "select") {

        foreach ($meta['html']['options'] as $option) {
          if ($option['value'] == (int)get_post_meta( $post->ID, $meta['slug'] , true) )
          {
            $content = $option['content'];
          }
        }


        ?>
          <li><span class="post-meta-key"><?php echo $meta['label']; ?>: </span><?php echo $content; ?></li>
          <?php
          continue;
      }
      ?>
        <li><span class="post-meta-key"><?php echo $meta['label']; ?>: </span><?php print_r(get_post_meta( $post->ID, $meta['slug'] , true)); ?></li>


        <?php       } ?>
        </ul>
        <?php
        echo consultas_html_form_code();
  }
}

add_action("loop_end", "consultas_the_meta");

function consultas_change_post_placeholder($title)
{
  $screen = get_current_screen();
  if ('consultas' == $screen->post_type) {
    $title = 'Insira o nome da consulta pública';
  }
  return $title;
}

add_filter('enter_title_here', 'consultas_change_post_placeholder');


function add_consultas_to_query($query)
{
  if (is_home() && $query->is_main_query())
    $query->set('post_type', array('post', 'page', 'profiles'));
  return $query;
}
add_action('pre_get_posts', 'add_consultas_to_query');

add_action('admin_menu', 'consultas_meta_box');
add_action('save_post', 'save_consultas_meta_box', 10, 2);

function consultas_meta_box()
{
  add_meta_box('consultas-meta-box', 'Informações Complementares', 'display_consultas_meta_box', 'consultas', 'normal', 'high');

}

function display_consultas_meta_box($object, $box)
{ 
  $metas = get_option('consultas_metas');

  foreach($metas as $meta)
  {

    if ($meta['html']['tag'] == 'select')
    {
      ?>
        <p>
        <label for="<?php echo $meta['slug'] ?>"><?php echo $meta['label'] ?>:</label>
        <br>
        <select name="<?php echo $meta['slug'] ?>">
        <?php
        setlocale(LC_ALL, "en_US.utf8");
      foreach ($meta['html']['options'] as $option) {
        $content = iconv("utf-8", "ascii//TRANSLIT", $option['content']);
        //var_dump($option);
        $value_option = (string) $option['value'];
        $value_meta = (string) get_post_meta($object->ID, $meta['slug'] , true);
        ?>
          <option value="<?php echo $option['value'] ?>" <?php echo ($value_meta == $value_option) ? 'selected="selected"' : ''; ?> ><?php echo ucwords(strtolower($content)) ?></option>
          <?php
      }
      ?>
        </select>
        </p>
        <?php

    }
    else if ( $meta['html']['tag'] == 'input' )
    {
      ?>
        <p>
        <label for="<?php echo $meta['slug'] ?>"><?php echo $meta['label'] ?></label>
        <br>
        <input type="<?php echo $meta['html']['type'] ?>" name="<?php echo $meta['slug'] ?>" id="<?php echo $meta['slug'] ?>" style="width:50%"
        value="<?php echo esc_html(get_post_meta($object->ID, $meta['slug'] , true), 1); ?>">
        </p>
        <?php
    }
    else if ( $meta['html']['tag'] == 'textarea' )
    {
      ?>
        <p>
        <label for="<?php echo $meta['slug'] ?>"><?php echo $meta['label'] ?></label>
        <br/>
        <textarea name="<?php echo $meta['slug'] ?>" rows="<?php echo $meta['html']['rows']; ?>" cols="<?php echo $meta['html']['cols']; ?>" id="<?php echo $meta['slug'] ?>" style="width:50%" ><?php echo esc_html(get_post_meta($object->ID, $meta['slug'] , true), 1); ?></textarea>
        </p>
        <?php
    }
  } 
  ?>
    <input type="hidden" name="my_meta_box_nonce"
    value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>"/>

    <?php }

function save_consultas_meta_box($post_id, $post)
{
  if (!current_user_can('edit_post', $post_id))
    return;

  $metas = get_option('consultas_metas');
  foreach ( $metas as $meta)
  {
    if (isset($_POST[$meta['slug']])) {
      $meta_striped = stripslashes($_POST[$meta['slug']]);

      if ($meta_striped && '' == get_post_meta($post_id, $meta['slug'], true))
        add_post_meta($post_id, $meta['slug'], $meta_striped, true);

      elseif ($meta_striped != get_post_meta($post_id, $meta['slug'], true))
        update_post_meta($post_id, $meta['slug'], $meta_striped);

      elseif ('' == $meta_striped && get_post_meta($post_id, $meta['slug'], true))
        delete_post_meta($post_id, $meta['slug'], get_post_meta($post_id, $meta['slug'], true));
    }
  }
}


add_filter('manage_consultas_posts_columns', 'consultas_filter_columns');

function consultas_filter_columns($columns)
{
  // this will add the column to the end of the array
  $metas = get_option('consultas_metas');
  $i = 0;
  foreach ( $metas as $meta)
  {
    if ( $i === 5) break;
    $columns[$meta['slug']] = $meta['label'];
    $i++;
  }
  return $columns;
}

add_action('manage_posts_custom_column', 'consultas_action_custom_columns_content', 10, 2);

function consultas_action_custom_columns_content($column_id, $post_id)
{
  //run a switch statement for all of the custom columns created
  $metas = get_option('consulta_metas');
  $i = 0;
  foreach ( $metas as $meta)
  {
    if ( $i === 5) break;
    if ( $column_id ==  $meta['slug'] )
      echo ($value = get_post_meta($post_id, $meta['slug'], true)) ? $value : $meta['info'];
    $i++;
  }
}


// pages and search system
function consultas_rewrite_add_var( $vars ) {
  $vars[] = 'busca';
  return $vars;
}
add_filter( 'query_vars', 'consultas_rewrite_add_var' );

// Create the rewrites
function consultas_rewrite_rule() {
  add_rewrite_tag( '%busca%', '([^&]+)' );
  add_rewrite_rule(
      '^busca',
      'index.php?busca',
      'top'
      );
}
add_action('init','consultas_rewrite_rule');

// Catch the URL and redirect it to a template file
function consultas_rewrite_catch() {
  global $wp_query;
  if ( array_key_exists( 'busca', $wp_query->query_vars ) ) {
    include ( CONSULTAS_PUBLICAS_PATH . 'profiles_list.php');
    exit;
  }
}
add_action( 'template_redirect', 'consultas_rewrite_catch' );

//options page
add_action( 'admin_menu', 'consultas_custom_admin_menu' );

function consultas_custom_admin_menu() {
  add_options_page(
      'Configurações das Consultas Públicas',
      'Configurações das Consultas Públicas',
      'manage_options',
      'consultas',
      'consultass_options_page'
      );

}

function consultas_html_form_code() {

  if (is_user_logged_in()){

    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<p>';
    echo 'Nome * <br />';
    echo '<input type="text" name="nome" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["nome"] ) ? esc_attr( $_POST["nome"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'Municipio * <br />';
    echo '<input type="text" name="municipio" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["municipio"] ) ? esc_attr( $_POST["municipio"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'UF * <br />';
    echo '<input type="text" name="uf" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["uf"] ) ? esc_attr( $_POST["uf"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'CPF * <br />';
    echo '<input type="text" name="cpf" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cpf"] ) ? esc_attr( $_POST["cpf"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Instituição * <br />';
    echo '<input type="text" name="instituicao" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["instituicao"] ) ? esc_attr( $_POST["instituicao"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo ' A sociedade civil tomou iniciativas para promover os princípios e objetivos da Convenção local e internacionalmente? * <br/>';
    echo '<input type="radio" name="primeira" value="primeira_concordo" >concordo<br>';
    echo '<input type="radio" name="primeira" value="primeira_concordo_comentar">concordo e quero comentar<br>';
    echo '<input type="radio" name="primeira" value="primeira_discordo_comentar" >discordo e quero comentar<br>';
    echo '<textarea rows="10" cols="70" name="cf-message">' . ( isset( $_POST["primeira"] ) ? esc_attr( $_POST["primeira"] ) : '' ) . '</textarea>';
    echo '</p>';
    echo 'A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)* <br/>';
    echo '<textarea rows="10" cols="70" name="cf-message">' . ( isset( $_POST["segunda"] ) ? esc_attr( $_POST["segunda"] ) : '' ) . '</textarea>';
    echo '</p>';
    echo 'A sociedade civil tomou iniciativas para contribuir para a realização de uma maior transparência e prestação de contas na governança cultural (Como?) <br/>';
    echo '<textarea rows="10" cols="70" name="cf-message">' . ( isset( $_POST["terceira"] ) ? esc_attr( $_POST["terceira"] ) : '' ) . '</textarea>';
    echo '</p>';
    echo '<p><input type="submit" name="enviar" value="Enviar"/></p>';
    echo '</form>';
  }
else
  {
   echo 'você precisa de estar logado para participar da consulta pública!';
  }
}
add_shortcode('perguntas', 'consultas_html_form_code');
function consultas_insert_get_metas()
{
  update_option( 'consultas_metas' , get_metas() );
}

register_activation_hook( __FILE__ , 'consultas_insert_get_metas' );
require_once dirname(__FILE__)."/options.php"; 

?>
