<?php

get_header();

function the_content_max_charlength($charlength, $content) {
  $content = wp_strip_all_tags($content);
  $charlength++;

  if ( mb_strlen( $content ) > $charlength ) {
    $subex = mb_substr( $content, 0, $charlength - 5 );
    $exwords = explode( ' ', $subex );
    $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
    if ( $excut < 0 ) {
      echo mb_substr( $subex, 0, $excut );
    } else {
      echo $subex;
    }
    echo '[...]';
  } else {
    echo $content;
  }
}

$search = isset( $_POST['search'] ) ? $_POST['search'] : '';

$args = array(
    'post_type'   => 'consultas',
    'post_status' => 'publish',
    'meta_value'  =>  $search,
    'meta_compare' => 'LIKE',
    );

$query = new WP_Query( $args );
$profiles = $query->posts;

$fields = get_option( 'consutas_metas' );

?>



<form method="post" action="<?php site_url('?busca') ?>"  name="form">
<input type="text" name="search" placeholder="Insira qualquer informação da consulta" value="<?php echo $search; ?>"/>
<br>
<br>
<?php

foreach ($fields as $field) {
  if ($field['html']['tag'] == 'select' ) {
    echo '<select name="' . $field['slug'] . '">';
    foreach ($field['html']['options'] as $option) {
      var_dump($_POST[$field['slug']]);
      var_dump($option['value']) ;
      $selected = ($_POST[$field['slug']] == (string) $option['value']) ?"selected":"";
      echo '<option value="' . $option['value'] . '" ' . $selected . '>' . $option['content'] .'</option>';
    }
    echo '</select>';
  }
}
?>
<br>
<br>
<input type="submit" name="submit" id="submit" class="button button-primary" value="Search"  />
</form>
<br>

<?php



foreach( $profiles as $profile )
{ 
  echo '<h2><a href="' . $profile->guid . '">' . $profile->post_title . '</a><br></h2>';
  //echo $profile->post_content;
  echo the_content_max_charlength( 300, $profile->post_content);
  echo '<br>';
  $metas = array( 'email', 'phone' ,  ) ; 
  foreach( $metas as $meta )
  {
    $value = get_post_meta($profile->ID, $meta, true);
    if ( $value != '' )
    { 
      echo $value . '<br>';
    }

  }
  echo '<br>';
}
/* Restore original Post Data */
wp_reset_postdata();
wp_footer();

?>
</body>
</html> 

