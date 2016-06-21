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
  $user_id = get_current_user_id();
  if (is_user_logged_in() 
       && !isset($_POST["nome"]) 
       && !isset($_POST["municipio"]) 
       && !isset($_POST["uf"])
       && !isset($_POST["cpf"])
       && !isset($_POST["instituicao"])
       && !isset($_POST["primeira_radio"])
       && !isset($_POST["primeira"])
       && !isset($_POST["segunda"])
       && !isset($_POST["terceira"])
       && !isset($_POST["quarta"])
       && !isset($_POST["quinta"])
       && !isset($_POST["sexta"])
       && !isset(get_user_meta($user_id, '_user_nome')[0])
       && !isset(get_user_meta($user_id, '_user_municipio')[0])
       && !isset(get_user_meta($user_id, '_user_uf')[0])
       && !isset(get_user_meta($user_id, '_user_cpf')[0])
       && !isset(get_user_meta($user_id, '_user_instituicao')[0])
       && !isset(get_user_meta($user_id, '_user_primeira_radio')[0])
       && !isset(get_user_meta($user_id, '_user_primeira')[0])
       && !isset(get_user_meta($user_id, '_user_segunda')[0])
       && !isset(get_user_meta($user_id, '_user_terceira')[0])
       && !isset(get_user_meta($user_id, '_user_quarta')[0])
       && !isset(get_user_meta($user_id, '_user_quinta')[0])
       && !isset(get_user_meta($user_id, '_user_sexta')[0])
       || isset($_POST["editar"]) && is_user_logged_in()
      ){
    
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<p>';
    echo 'Nome * <br />';
    echo '<input type="text" name="nome" required="required" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( get_user_meta($user_id, '_user_nome')[0] ) ? esc_attr( get_user_meta($user_id, '_user_nome')[0] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'Municipio * <br />';
    echo '<input type="text" name="municipio" required="required" pattern="[a-zA-Z ]+" value="' . ( isset( get_user_meta($user_id, '_user_municipio')[0] ) ? esc_attr( get_user_meta($user_id, '_user_municipio')[0] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'UF * <br />';
    echo '<input type="text" required="required" name="uf" value="' . ( isset( get_user_meta($user_id, '_user_uf')[0]) ? esc_attr( get_user_meta($user_id, '_user_uf')[0] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'CPF * <br />';
    echo '<input type="text" required="required" name="cpf" pattern="([0-9]{2}[\.]?[0-9]{3}[\.]?[0-9]{3}[\/]?[0-9]{4}[-]?[0-9]{2})|([0-9]{3}[\.]?[0-9]{3}[\.]?[0-9]{3}[-]?[0-9]{2})" value="' . ( isset( get_user_meta($user_id, '_user_cpf')[0] ) ? esc_attr( get_user_meta($user_id, '_user_cpf')[0] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Instituição * <br />';
    echo '<input type="text" required="required" name="instituicao" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( get_user_meta($user_id, '_user_instituicao')[0] ) ? esc_attr( get_user_meta($user_id, '_user_instituicao')[0] ) : '' ) . '" size="40" />';
    echo '</p>';
    // 1
    echo ' A sociedade civil tomou iniciativas para promover os princípios e objetivos da Convenção local e internacionalmente? * <br/>';
    echo '<input type="radio" name="primeira_radio" value="concordo" ' . ( get_user_meta($user_id, '_user_primeira_radio')[0] === 'concordo' ?  'checked': '' ) . '>concordo<br>';
    echo '<input type="radio" name="primeira_radio" value="concordo_comentar" ' . ( get_user_meta($user_id, '_user_primeira_radio')[0] === 'concordo_comentar' ?  'checked': '' ) . '>concordo e quero comentar<br>';
    echo '<input type="radio" name="primeira_radio" value="discordo_comentar" ' . ( get_user_meta($user_id, '_user_primeira_radio')[0] === 'discordo_comentar' ?  'checked': '' ) . '>discordo e quero comentar<br>';
    echo '<textarea maxlength="2100" rows="10" cols="70" name="primeira" placeholder="Máximo de 2100 caracteres" >' . ( isset( get_user_meta($user_id, '_user_primeira')[0] ) ? esc_attr( get_user_meta($user_id, '_user_primeira')[0] ) : '' ) . '</textarea>';
    echo '</p>';
    // 2
    echo ' A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?) * <br/>';
    echo '<textarea maxlength="2100" required="required" rows="10" cols="70" name="segunda"  placeholder="Máximo de 2100 caracteres">' . ( isset( get_user_meta($user_id, '_user_segunda')[0] ) ? esc_attr( get_user_meta($user_id, '_user_segunda')[0] ) : '' ) . '</textarea>';
    echo '</p>';
    // 3
    echo 'A sociedade civil tomou iniciativas para contribuir para a realização de uma maior transparência e prestação de contas na governança cultural (Como?) * <br/>';
    echo '<textarea maxlength="2100" required="required" rows="10" cols="70" name="terceira"  placeholder="Máximo de 2100 caracteres">' . ( isset( get_user_meta($user_id, '_user_terceira')[0] ) ? esc_attr( get_user_meta($user_id, '_user_terceira')[0] ) : '' ) . '</textarea>';
    echo '</p>';
    // 4
    echo 'A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?) * <br/>';
    echo '<textarea maxlength="2100" required="required" rows="10" cols="70" name="quarta"  placeholder="Máximo de 2100 caracteres">' . ( isset( get_user_meta($user_id, '_user_quarta')[0] ) ? esc_attr( get_user_meta($user_id, '_user_quarta')[0] ) : '' ) . '</textarea>';
    echo '</p>';
    // 5
    echo 'A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?) * <br/>';
    echo '<textarea maxlength="2100" required="required" rows="10" cols="70" name="quinta"  placeholder="Máximo de 2100 caracteres">' . ( isset( get_user_meta($user_id, '_user_quinta')[0] ) ? esc_attr( get_user_meta($user_id, '_user_quinta')[0] ) : '' ) . '</textarea>';
    echo '</p>';
    // 6
    echo 'A sociedade civil tomou iniciativas para criar parcerias inovadoras com os setores públicos e privados e com a sociedade civil de outras regiões do mundo. (Como?) * <br/>';
    echo '<textarea maxlength="2100" required="required" rows="10" cols="70" name="sexta"  placeholder="Máximo de 2100 caracteres">' . ( isset( get_user_meta($user_id, '_user_sexta')[0] ) ? esc_attr( get_user_meta($user_id, '_user_sexta')[0] ) : '' ) . '</textarea>';
    echo '</p>';

    echo '<p><input type="submit" name="enviar" value="Enviar"/></p>';
    echo '</form>';
  }
  elseif(is_user_logged_in() 
      && isset($_POST["nome"]) 
      && isset($_POST["municipio"]) 
      && isset($_POST["uf"]) 
      && isset($_POST["cpf"]) 
      && isset($_POST["instituicao"]) 
      && isset($_POST["primeira"]) 
      && isset($_POST["segunda"]) 
      && isset($_POST["terceira"]) 
      && isset($_POST["quarta"]) 
      && isset($_POST["quinta"]) 
      && isset($_POST["sexta"])
    )
  {
    
    //delete_post_meta(et_current_user_id(),'_users_voto');
    $data = get_post_meta(get_current_user_id(), '_users_voto', true);

    if( $data != "" ) {
      if ( !in_array( get_current_user_id(), $data ) ) {
        $data[] = get_current_user_id();
      }
      $data = array_unique($data); // remove duplicates
      sort( $data ); // sort array
      update_post_meta(get_current_user_id(), '_users_voto', $data);
    }
    else {
      $data = array();
      $user = get_current_user_id();
      array_push($data, $user);
      update_post_meta(get_current_user_id(),'_users_voto' , $data);
    }

    update_user_meta( $user_id, '_user_nome', $_POST["nome"]);
    update_user_meta( $user_id, '_user_municipio', $_POST["municipio"]);
    update_user_meta( $user_id, '_user_uf', $_POST["uf"]);
    update_user_meta( $user_id, '_user_cpf', $_POST["cpf"]);
    update_user_meta( $user_id, '_user_instituicao', $_POST["instituicao"]);
    update_user_meta( $user_id, '_user_primeira_radio', $_POST["primeira_radio"]);
    update_user_meta( $user_id, '_user_primeira', $_POST["primeira"]);
    update_user_meta( $user_id, '_user_segunda', $_POST["segunda"]);
    update_user_meta( $user_id, '_user_terceira', $_POST["terceira"]);
    update_user_meta( $user_id, '_user_quarta', $_POST["quarta"]);
    update_user_meta( $user_id, '_user_quinta', $_POST["quinta"]);
    update_user_meta( $user_id, '_user_sexta', $_POST["sexta"]);

    echo "<h2>Voto inserido com sucesso!</h2><br>";
    echo "<strong>Seu nome: </strong><br>";
    echo get_user_meta($user_id, '_user_nome')[0];
    echo "<br>";
    echo "<strong>Seu Estado:</strong><br>";
    echo get_user_meta($user_id, '_user_uf')[0];
    echo "<br>";

    // 1
    echo '<strong> A sociedade civil tomou iniciativas para promover os princípios e objetivos da Convenção local e internacionalmente?</strong><br/>';
        echo get_user_meta($user_id, '_user_primeira_radio')[0];
    echo "<br>";
    echo get_user_meta($user_id, '_user_primeira')[0];
    echo "<br>";
    // 2
    echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_segunda')[0];
    echo "<br>";
    // 3
    echo '<strong>A sociedade civil tomou iniciativas para contribuir para a realização de uma maior transparência e prestação de contas na governança cultural (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_terceira')[0];
    echo "<br>";
    // 4
    echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_quarta')[0];
    echo "<br>";
    // 5
    echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_quinta')[0];
    echo "<br>";
    // 6
    echo '<strong>A sociedade civil tomou iniciativas para criar parcerias inovadoras com os setores públicos e privados e com a sociedade civil de outras regiões do mundo. (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_sexta')[0];
    echo "<br>";
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post"><input type="submit" name="editar" value="editar"></form>';
    $_POST = '';

  }
  elseif( is_user_logged_in()
       && isset(get_user_meta($user_id, '_user_nome')[0])
       && isset(get_user_meta($user_id, '_user_municipio')[0])
       && isset(get_user_meta($user_id, '_user_uf')[0])
       && isset(get_user_meta($user_id, '_user_cpf')[0])
       && isset(get_user_meta($user_id, '_user_instituicao')[0])
       && isset(get_user_meta($user_id, '_user_primeira_radio')[0])
       && isset(get_user_meta($user_id, '_user_primeira')[0])
       && isset(get_user_meta($user_id, '_user_segunda')[0])
       && isset(get_user_meta($user_id, '_user_terceira')[0])
       && isset(get_user_meta($user_id, '_user_quarta')[0])
       && isset(get_user_meta($user_id, '_user_quinta')[0])
       && isset(get_user_meta($user_id, '_user_sexta')[0])
    )
  {

    echo "Veja abaixo seu voto: <br>";
    
    echo "<strong>Seu nome: </strong><br>";
    echo get_user_meta($user_id, '_user_nome')[0];
    echo "<br>";
    echo "<strong>Seu Estado:</strong><br>";
    echo get_user_meta($user_id, '_user_uf')[0];
    echo "<br>";

    // 1
    echo '<strong> A sociedade civil tomou iniciativas para promover os princípios e objetivos da Convenção local e internacionalmente?</strong><br/>';
        echo get_user_meta($user_id, '_user_primeira_radio')[0];
    echo "<br>";
    echo get_user_meta($user_id, '_user_primeira')[0];
    echo "<br>";
    // 2
    echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_segunda')[0];
    echo "<br>";
    // 3
    echo '<strong>A sociedade civil tomou iniciativas para contribuir para a realização de uma maior transparência e prestação de contas na governança cultural (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_terceira')[0];
    echo "<br>";
    // 4
    echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_quarta')[0];
    echo "<br>";
    // 5
    echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_quinta')[0];
    echo "<br>";
    // 6
    echo '<strong>A sociedade civil tomou iniciativas para criar parcerias inovadoras com os setores públicos e privados e com a sociedade civil de outras regiões do mundo. (Como?)</strong><br/>';
    echo get_user_meta($user_id, '_user_sexta')[0];
    echo "<br>";

    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post"><input type="submit" name="editar" value="editar"></form>';

  }
  else
  {
    echo 'você precisa de estar logado para participar da consulta pública!';
  }

// quem já votou:

    $users = get_post_meta(get_the_ID(), "_users_voto", true);
  if ($users !== ""){
    foreach ($users as $user) {
      echo get_avatar($user);
      echo "<br>";

      echo "<strong>Nome: </strong><br>";
      echo get_user_meta($user, '_user_nome')[0];
      echo "<br>";
      echo "<strong>Estado:</strong><br>";
      echo get_user_meta($user, '_user_uf')[0];
      echo "<br>";

      // 1
      echo '<strong> A sociedade civil tomou iniciativas para promover os princípios e objetivos da Convenção local e internacionalmente?</strong><br/>';
          echo get_user_meta($user, '_user_primeira_radio')[0];
      echo "<br>";
      echo get_user_meta($user, '_user_primeira')[0];
      echo "<br>";
      // 2
      echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
      echo get_user_meta($user, '_user_segunda')[0];
      echo "<br>";
      // 3
      echo '<strong>A sociedade civil tomou iniciativas para contribuir para a realização de uma maior transparência e prestação de contas na governança cultural (Como?)</strong><br/>';
      echo get_user_meta($user, '_user_terceira')[0];
      echo "<br>";
      // 4
      echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
      echo get_user_meta($user, '_user_quarta')[0];
      echo "<br>";
      // 5
      echo '<strong>A sociedade civil tomou iniciativas para monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?)</strong><br/>';
      echo get_user_meta($user, '_user_quinta')[0];
      echo "<br>";
      // 6
      echo '<strong>A sociedade civil tomou iniciativas para criar parcerias inovadoras com os setores públicos e privados e com a sociedade civil de outras regiões do mundo. (Como?)</strong><br/>';
      echo get_user_meta($user, '_user_sexta')[0];
      echo "<br>";

    }
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
