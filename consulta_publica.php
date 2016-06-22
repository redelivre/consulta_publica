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
  $cabecalho_etapa1 = '<strong>Etapa 1 - Sobre o Relatório Quadrienal (2012-2015) da Convenção da Diversidade Cultural da Unesco:</strong><br>';
  $cabecalho_etapa2 = '<strong>Etapa 2 - A sociedade civil tomou iniciativas para:</strong><br>';
  $cabecalho_primeira = '<strong>Promover os princípios e objetivos da Convenção local e internacionalmente. (Como?) *</strong> <br/>';
  $cabecalho_segunda = '<strong>Levar as preocupações dos cidadãos, associações e empresas às autoridades públicas, incluindo as de grupos vulneráveis (Como?) *</strong> <br/>';
  $cabecalho_terceira = '<strong>Contribui para a realização de uma maior transparência e prestação de contas na governança cultural (Como?) *</strong> <br/>';
  $cabecalho_quarta = '<strong>Monitorar a política e o programa de implementação de medidas para proteger e promover a diversidade das expressões culturais (Como?) *</strong> <br/>';
  $cabecalho_quinta = '<strong>Criar capacidades nas áreas ligadas à Convenção e que recolhem dados. (Como?) *</strong> <br/>';
  $cabecalho_sexta = '<strong>Criar parcerias inovadoras com os setores públicos e privados e com a sociedade civil de outras regiões do mundo. (Como?) *</strong> <br/>';
  $cabecalho_desafio = '<strong>Desafios encontrados ou previstos na implementação da Convenção: </strong><br>';
  $cabecalho_solucao = '<strong>Soluções encontradas e previstas:</strong> <br>';
  $cabecalho_atividade = '<strong>Atividades planejadas para os próximos 4 anos na implementação da Convenção (liste-as):</strong> <br>';

  if (is_user_logged_in() 
       && !isset($_POST["primeira"])
       && get_user_meta($user_id, '_user_primeira', true) == ''
       || isset($_POST["editar"]) && is_user_logged_in()
      ){
    
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post" id="commentform" enctype="multipart/form-data">';
    echo '<p>';
    echo 'Nome * <br />';
    echo '<input type="text" name="nome" required value="' . ( get_user_meta($user_id, '_user_nome', true) != '' ? esc_attr( get_user_meta($user_id, '_user_nome', true) ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'CPF * <br />';
    echo '<input type="text" required name="cpf" pattern="([0-9]{2}[\.]?[0-9]{3}[\.]?[0-9]{3}[\/]?[0-9]{4}[-]?[0-9]{2})|([0-9]{3}[\.]?[0-9]{3}[\.]?[0-9]{3}[-]?[0-9]{2})" value="' . ( get_user_meta($user_id, '_user_cpf', true) != '' ? esc_attr( get_user_meta($user_id, '_user_cpf', true) ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'E-mail * <br />';
    echo '<input type="email" required name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" value="' . ( get_user_meta($user_id, '_user_email', true) != '' ? esc_attr( get_user_meta($user_id, '_user_email', true) ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'Telefone * <br />';
    echo '<input type="text" required name="telefone" maxlength="15" value="' . ( get_user_meta($user_id, '_user_telefone', true) != '' ? esc_attr( get_user_meta($user_id, '_user_telefone', true) ) : '' ) . '" size="40" />';
    echo '</p>';
    echo ' Cidade * <br />';
    echo '<input type="text" name="municipio" required value="' . ( get_user_meta($user_id, '_user_municipio', true) != '' ? esc_attr( get_user_meta($user_id, '_user_municipio', true) ) : '' ) . '" size="40" />';
    echo '</p>';
    echo 'UF * <br />';
    echo '<input type="text" required name="uf" value="' . ( get_user_meta($user_id, '_user_uf', true) != '' ? esc_attr( get_user_meta($user_id, '_user_uf', true) ) : '' ) . '" size="40" />';
    echo '</p>';

    echo '<p>';
    // representatividade

    $representatividade = get_user_meta($user_id, '_user_representatividade', true);

    echo 'Representatividade: * <br>';
    echo '<input type="radio" id="plenaria" onclick="showInstituicao()" name="representatividade" value="Plenária" ' . ( $representatividade === 'Plenária' ?  'checked': '' ) . ' required>Plenária ';
    echo '<input type="text" id="instituicao" ' . ( $representatividade === 'Plenária' ?  '':'style="visibility:hidden"' ) . ' placeholder="Digite aqui a sua instituição" name="instituicao" value="' . ( get_user_meta($user_id, '_user_instituicao', true) != '' ? esc_attr( get_user_meta($user_id, '_user_instituicao', true) ) : '' ) . '" size="40" /><br>';
    echo '<input type="radio" id="setorial" onclick="showArea()" name="representatividade" value="Setorial" ' . ( $representatividade === 'setorial' ?  'checked': '' ) . '>Setorial ';
    echo '<input type="text" id="setorial_area" ' . ( $representatividade === 'setorial' ?  '':'style="visibility:hidden"' ) . ' name="setorial_area" placeholder="Digite aqui a sua área" value="' . ( get_user_meta($user_id, '_user_setorial_area', true) != '' ? esc_attr( get_user_meta($user_id, '_user_setorial_area', true) ) : '' ) . '" size="40" /><br>';
    echo '<input type="radio" onclick="hideAll()" name="representatividade" value="Sociedade" ' . ( $representatividade === 'Sociedade' ?  'checked': '' ) . '>Sociedade Civil<br>';

    // relatório
    echo '<h4>'.$cabecalho_etapa1.'</h4>';
    echo '<br>';
    echo '<a href="/wp-content/plugins/consulta_publica/files/RELATÓRIO_QUADRIENAL_UNESCO.pdf" target="_blank">Acesse o relatório aqui</a><br>';
    echo '<br>';
    $relatorio_radio = get_user_meta($user_id, '_user_relatorio_radio', true);

    echo '<input type="radio" onclick="hideEditor()" name="relatorio_radio" value="Concordo com o relatório apresentado" ' . ( $relatorio_radio === 'Concordo com o relatório apresentado' ?  'checked': '' ) . ' required>Concordo com o relatório apresentado<br>';
    echo '<input type="radio" onclick="showEditor()" name="relatorio_radio" value="Concordo mas Gostaria de Comentar" ' . ( $relatorio_radio === 'Concordo mas Gostaria de Comentar' ?  'checked': '' ) . '>Concordo mas Gostaria de Comentar  <br>';
    echo '<input type="radio" onclick="showEditor" name="relatorio_radio" value="Não concordo" ' . ( $relatorio_radio === 'Não concordo' ?  'checked': '' ) . '>Não concordo<br>';    
    echo '<textarea id="editor_etapa1" ' . ( $relatorio_radio === 'Concordo com o relatório apresentado' ?  'style="visibility:hidden"': '' ) . 'maxlength="2100" rows="10" cols="70" name="relatorio" placeholder="Máximo de 2100 caracteres" >' . ( get_user_meta($user_id, '_user_relatorio', true) !== null ? esc_attr( get_user_meta($user_id, '_user_relatorio', true) ) : '' ) . '</textarea>';
    // cabeçalho
    echo '<br>';
    echo '<h4>'.$cabecalho_etapa2.'</h4>';

    // 1 ok

    echo $cabecalho_primeira;
    echo '<textarea maxlength="2100" rows="10" cols="70" name="primeira" placeholder="Máximo de 2100 caracteres" >' . ( get_user_meta($user_id, '_user_primeira', true) != '' ? esc_attr( get_user_meta($user_id, '_user_primeira', true) ) : '' ) . '</textarea>';
    echo '</p>';
    // 2

    echo $cabecalho_segunda;
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="segunda"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_segunda', true) != '' ? esc_attr( get_user_meta($user_id, '_user_segunda', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // 3
    
    echo $cabecalho_terceira;
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="terceira"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_terceira', true) != '' ? esc_attr( get_user_meta($user_id, '_user_terceira', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // 4
    
    echo $cabecalho_quarta;
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="quarta"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_quarta', true) != '' ? esc_attr( get_user_meta($user_id, '_user_quarta', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // 5
    
    echo $cabecalho_quinta;
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="quinta"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_quinta', true) != '' ? esc_attr( get_user_meta($user_id, '_user_quinta', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // 6
    
    echo $cabecalho_sexta;
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="sexta"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_sexta', true) != '' ? esc_attr( get_user_meta($user_id, '_user_sexta', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // desafio 

    echo $cabecalho_desafio;

    // desafio 1

    echo "Desafio 1 <br>";
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="desafio1"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_desafio1', true) != '' ? esc_attr( get_user_meta($user_id, '_user_desafio1', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // desafio 2
    
    echo "Desafio 2 <br>";
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="desafio2"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_desafio2', true) != '' ? esc_attr( get_user_meta($user_id, '_user_desafio2', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // solucao

    echo $cabecalho_solucao;

    // solucao 1
    
    echo "Solução 1 <br>";
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="solucao1"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_solucao1', true) != '' ? esc_attr( get_user_meta($user_id, '_user_solucao1', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // solucao 2
    
    echo "Solução 2 <br>";
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="solucao2"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_solucao2', true) != '' ? esc_attr( get_user_meta($user_id, '_user_solucao2', true) ) : '' ) . '</textarea>';
    echo '</p>';

    // atividade

    echo $cabecalho_atividade;

    // atividade 1
    
    echo "Atividade 1 <br>";
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="atividade1"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_atividade1', true) != '' ? esc_attr( get_user_meta($user_id, '_user_atividade1', true) ) : '' ) . '</textarea>';
    echo '</p>';
    
    // atividade 2
    
    echo "Atividade 2 <br>";
    echo '<textarea maxlength="2100" required rows="10" cols="70" name="atividade2"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_atividade2', true) != '' ? esc_attr( get_user_meta($user_id, '_user_atividade2', true) ) : '' ) . '</textarea>';
    echo '</p>';

    ?>
    <div class="attach">
    	<div class="att-block">
    		<label for="att" class="att-item-label">
    			<div class="att-item-title"><strong><?php _e('Documentos de apoio fornecidos pela Sociedade Civil (upload de pdf ou descrição)'); ?></strong>
     			</div><br/>
    		</label>
    		<input type="file" name="att" id="att"
    			value="<?php ?>"
    			class="file-upload"
    		>
        </div>
    </div><br><?php
    echo '<textarea maxlength="2100" rows="10" cols="70" name="documentosapoio"  placeholder="Máximo de 2100 caracteres">' . ( get_user_meta($user_id, '_user_documentosapoio', true) != '' ? esc_attr( get_user_meta($user_id, '_user_documentosapoio', true) ) : '' ) . '</textarea>';
    echo '</p>';
    // botao de enviar

    echo '<p><input class="et_pb_button  et_pb_button_0 et_pb_module et_pb_bg_layout_light" type="submit" name="enviar" value="Enviar"/></p>';
    echo '</form>';
  }
  elseif(is_user_logged_in() 
      && isset($_POST["nome"]) 
      && isset($_POST["cpf"]) 
      && isset($_POST["email"])
      && isset($_POST["telefone"])
      && isset($_POST["municipio"]) 
      && isset($_POST["uf"]) 
      && isset($_POST["representatividade"]) 
      && isset($_POST["relatorio_radio"])
      && isset($_POST["relatorio"])
      && isset($_POST["setorial_area"])
      && isset($_POST["instituicao"]) 
      && isset($_POST["primeira"]) 
      && isset($_POST["segunda"]) 
      && isset($_POST["terceira"]) 
      && isset($_POST["quarta"]) 
      && isset($_POST["quinta"]) 
      && isset($_POST["sexta"])
      && isset($_POST["desafio1"])
      && isset($_POST["desafio2"])
      && isset($_POST["solucao1"])
      && isset($_POST["solucao2"])
      && isset($_POST["atividade1"])
      && isset($_POST["atividade2"])
    )
  {
    
    // insere os id's dos usuários em um array, com isso sabemos qual foi a opnião do usuário
    $data = get_post_meta(get_the_ID(), '_users_voto', true);
    if( $data != "" ) {
      if ( !in_array( get_current_user_id(), $data ) ) {
        $data[] = get_current_user_id();
      }
      $data = array_unique($data); // remove duplicates
      sort( $data ); // sort array
      update_post_meta(get_the_ID(), '_users_voto', $data);
    }
    else {
      $data = array();
      $user = get_current_user_id();
      array_push($data, $user);
      update_post_meta(get_the_ID(),'_users_voto' , $data);
    }

    // atualiza nos campos meta do usuário o seu voto
    // informações do usuário
    update_user_meta( $user_id, '_user_nome', $_POST["nome"]);
    update_user_meta( $user_id, '_user_cpf', $_POST["cpf"]);
    update_user_meta( $user_id, '_user_email', $_POST["email"]);
    update_user_meta( $user_id, '_user_telefone', $_POST["telefone"]);
    update_user_meta( $user_id, '_user_municipio', $_POST["municipio"]);
    update_user_meta( $user_id, '_user_uf', $_POST["uf"]);
    update_user_meta( $user_id, '_user_representatividade', $_POST["representatividade"]);
    update_user_meta( $user_id, '_user_instituicao', $_POST["instituicao"]);
    update_user_meta( $user_id, '_user_setorial_area', $_POST["setorial_area"]);
    // informações de voto do usuário
    update_user_meta( $user_id, '_user_relatorio_radio', $_POST["relatorio_radio"]);
    update_user_meta( $user_id, '_user_relatorio', $_POST["relatorio"]);
    update_user_meta( $user_id, '_user_primeira', $_POST["primeira"]);
    update_user_meta( $user_id, '_user_segunda', $_POST["segunda"]);
    update_user_meta( $user_id, '_user_terceira', $_POST["terceira"]);
    update_user_meta( $user_id, '_user_quarta', $_POST["quarta"]);
    update_user_meta( $user_id, '_user_quinta', $_POST["quinta"]);
    update_user_meta( $user_id, '_user_sexta', $_POST["sexta"]);
    update_user_meta( $user_id, '_user_desafio1', $_POST["desafio1"]);
    update_user_meta( $user_id, '_user_desafio2', $_POST["desafio2"]);
    update_user_meta( $user_id, '_user_solucao1', $_POST["solucao1"]);
    update_user_meta( $user_id, '_user_solucao2', $_POST["solucao2"]);
    update_user_meta( $user_id, '_user_atividade1', $_POST["atividade1"]);
    update_user_meta( $user_id, '_user_atividade2', $_POST["atividade2"]);
    update_user_meta( $user_id, '_user_documentosapoio', $_POST["documentosapoio"]);
    
    $attach_id = array();
    $attach = array();
    $message = array(); //TODO error parser
    $notice = false;
     
    $has_att = true;
     
    if ($_FILES)
    {
    	if (!function_exists('wp_generate_attachment_metadata')){
    		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    	}
    	foreach ($_FILES as $file => $array)
    	{
    		if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK && $_FILES[$file]['error'] !== UPLOAD_ERR_NO_FILE )
    		{
    			switch($file)
    			{
    				case 'att':
    				default:
    					$message[] = __('Erro ao registrar anexo');
    					$has_att = false;
    					break;
    			}
    			 
    			$notice = true;
    		}
    		elseif( $_FILES[$file]['error'] == UPLOAD_ERR_OK )
    		{
    			$attach_id[$file] = media_handle_upload( $file, 0 );
    			$attach[$file] = wp_get_attachment_url($attach_id[$file]);
    		}
    	}
    }
    
    // can we have more attachment in future
    foreach ($attach_id as $key => $value)
    {
    	//and if you want to set that image as Post  then use:
    	if($key == 'att' && $has_att)
    	{
    		if( ! update_user_meta($user_id,'_user_att1', $attach_id[$key]))
    		{
    			$message[] = __('Erro ao gravar anexo', 'pontosdecultura');
    			$notice = true;
    		}
    	}
    }

    echo "<h2>Voto inserido com sucesso!</h2><br>";

    consulta_respostas($user_id, 
        $cabecalho_etapa1,
        $cabecalho_etapa2,
        $cabecalho_primeira,
        $cabecalho_segunda,
        $cabecalho_terceira,
        $cabecalho_quarta,
        $cabecalho_quinta,
        $cabecalho_sexta,
        $cabecalho_desafio,
        $cabecalho_solucao,
        $cabecalho_atividade
      );
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post"><input class="et_pb_button  et_pb_button_0 et_pb_module et_pb_bg_layout_light" type="submit" name="editar" value="editar"></form>';
    $_POST = '';

  }
  elseif( is_user_logged_in()
       && get_user_meta($user_id, '_user_primeira', true) != ''
    )
  {
	echo '<div class="seu-voto">';
    echo "<h1>Veja abaixo seu voto: </h1><br>";
    echo "<div class='content_respostas'>";
    echo "<div class='avatar-resposta'>";
    echo get_avatar($user_id);
    echo '</div>';
    consulta_respostas($user_id, 
        $cabecalho_etapa1,
        $cabecalho_etapa2,
        $cabecalho_primeira,
        $cabecalho_segunda,
        $cabecalho_terceira,
        $cabecalho_quarta,
        $cabecalho_quinta,
        $cabecalho_sexta,
        $cabecalho_desafio,
        $cabecalho_solucao,
        $cabecalho_atividade
      );
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post"><input class="et_pb_button  et_pb_button_0 et_pb_module et_pb_bg_layout_light" type="submit" name="editar" value="editar"></form>';
    echo '</div>'; // End seu-voto;
  }
  else
  {
    echo 'você precisa de estar logado para participar da consulta pública! ';
    echo '<a href=' . wp_login_url( get_permalink() ) . ' title="Login">Fazer Login!</a><br>';
    echo "<div class='clear'></div>";	
  }

// quem já votou:
  $users = get_post_meta(get_the_ID(), "_users_voto", true);
  if ($users !== ""){
  	echo '<div class="quem-votou">';
  	echo "<br/><h1>Quem Votou: </h1><br>";
    foreach ($users as $user) {
      echo "<div class='content_respostas'>";
      echo "<div class='avatar-resposta'>";
      echo get_avatar($user);
      echo "</div>";

    consulta_respostas($user, 
        $cabecalho_etapa1,
        $cabecalho_etapa2,
        $cabecalho_primeira,
        $cabecalho_segunda,
        $cabecalho_terceira,
        $cabecalho_quarta,
        $cabecalho_quinta,
        $cabecalho_sexta,
        $cabecalho_desafio,
        $cabecalho_solucao,
        $cabecalho_atividade
      );
    }
    echo '</div>'; // End quem-votou
  }


}

function consulta_respostas($user_id, 
    $cabecalho_etapa1,
    $cabecalho_etapa2,
    $cabecalho_primeira, 
    $cabecalho_segunda, 
    $cabecalho_terceira, 
    $cabecalho_quarta, 
    $cabecalho_quinta, 
    $cabecalho_sexta,
    $cabecalho_desafio,
    $cabecalho_solucao,
    $cabecalho_atividade
  )
{
    //dados do usuário
    echo "<div class='dados-resposta'>";
    echo "<strong>Nome: </strong><br>";
    echo get_user_meta($user_id, '_user_nome', true);
    echo "<br>";
    echo "<strong>Estado:</strong><br>";
    echo get_user_meta($user_id, '_user_uf', true);
    echo "</div>";

    // etapa 1 - relatorio
    echo '<div class="clear"></div>';
    echo "<h4>".$cabecalho_etapa1."</h4>";
    echo "<br>";
    echo "<strong>".get_user_meta($user_id, '_user_relatorio_radio', true)."</strong>";
    echo '<br>';
    echo "<br>";
    echo get_user_meta($user_id, '_user_relatorio', true);
    echo '<br>';
    echo "<br>";

    // etapa 2 - questionario
    echo "<h4>".$cabecalho_etapa2."</h4>";
    echo "<br>";
    // echo $cabecalho_etapa2

    // 1
    
    echo $cabecalho_primeira;
    echo get_user_meta($user_id, '_user_primeira', true);
    echo "<br>";
    echo "<br>";
    
    // 2
    echo $cabecalho_segunda;
    echo get_user_meta($user_id, '_user_segunda', true);
    echo "<br>";
    echo "<br>";
    // 3
    
    echo $cabecalho_terceira;
    echo get_user_meta($user_id, '_user_terceira', true);
    echo "<br>";
    echo "<br>";
    
    // 4
    
    echo $cabecalho_quarta;
    echo get_user_meta($user_id, '_user_quarta', true);
    echo "<br>";
    echo "<br>";
    
    // 5
    
    echo $cabecalho_quinta;
    echo get_user_meta($user_id, '_user_quinta', true);
    echo "<br>";
    echo "<br>";

    // 6
    
    echo $cabecalho_sexta;
    echo get_user_meta($user_id, '_user_sexta', true);
    echo "<br>";
    echo "<br>";

    // desafio

    echo $cabecalho_desafio;
    echo "<br>";
    // desafio 1
    
    echo '<strong>Desafio 1</strong><br>';
    echo get_user_meta($user_id, '_user_desafio1', true);
    echo "<br>";
    echo "<br>";

    // desafio 2
    
    echo "<strong>Desafio 2</strong><br>";
    echo get_user_meta($user_id, '_user_desafio2', true);
    echo "<br>";
    echo "<br>";

    // solucao

    echo $cabecalho_solucao;
    echo "<br>";

    // solucao 1

    echo "<strong>Solução 1</strong><br>";
    echo get_user_meta($user_id, '_user_solucao1', true);
    echo "<br>";
    echo "<br>";

    // solucao 2
    
    echo "<strong>Solução 2</strong><br>";
    echo get_user_meta($user_id, '_user_solucao2', true);
    echo "<br>";
    echo "<br>";

    // atividade

    echo $cabecalho_atividade;
    echo "<br>";

    // atividade 1

    echo "<strong>Atividade 1</strong><br>";
    echo get_user_meta($user_id, '_user_atividade1', true);
    echo "<br>";
    echo "<br>";

    // atividade 2

    echo "<strong>Atividade 2</strong><br>";
    echo get_user_meta($user_id, '_user_atividade2', true);
    echo "<br>";
    echo "<br>";
    
    $att_id = get_user_meta($user_id, '_user_att1', true);
    if($att_id)
    {
	    echo '<br/><strong>Documentos Anexados:</strong><br/>';
           echo "<br>";
	    echo wp_get_attachment_link($att_id).'<br/>';
	    echo get_user_meta($user_id, '_user_documentosapoio', true);
    }
    echo '<br><br>';
    echo '</div><!-- content_respostas -->';
}

//add_shortcode('perguntas', 'consultas_html_form_code');

// style and script 

function consultas_enqueue_style() {
       wp_enqueue_style( 'style-core', "/wp-content/plugins/consulta_publica/".'css/style.css', false ); 
}

function consultas_enqueue_script() {
       wp_enqueue_script( 'field-forms', "/wp-content/plugins/consulta_publica/".'js/forms.js', false );
}

add_action( 'wp_enqueue_scripts', 'consultas_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'consultas_enqueue_script' );


function consultas_insert_get_metas()
{
  update_option( 'consultas_metas' , get_metas() );
}

register_activation_hook( __FILE__ , 'consultas_insert_get_metas' );
require_once dirname(__FILE__)."/options.php"; 

?>
