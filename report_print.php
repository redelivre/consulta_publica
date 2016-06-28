<?php

if (have_posts())
{
	the_post();
	
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.date('Ymd').'_consulta_publica_report.csv');
	
	$output = fopen('php://output', 'w');

	$header = array(
		'nome',
		'cpf',
		'email',
		'telefone',
		'municipio',
		'uf',
		'representatividade',
		'instituicao',
		'setorial_area',
		'relatorio_radio',
		'relatorio',
		'primeira',
		'segunda',
		'terceira',
		'quarta',
		'quinta',
		'sexta',
		'desafio1',
		'desafio2',
		'solucao1',
		'solucao2',
		'atividade1',
		'atividade2',
		'documentosapoio'
	);
	fputcsv($output, $header, ';');
	$users_ids = get_post_meta(get_the_ID(), '_users_voto', true);
	
	foreach ($users_ids as $time => $user_id)
	{
		$index = 0;
		$row = array();
		$row[] = get_user_meta( $user_id, '_user_nome', true);
		$row[] = get_user_meta( $user_id, '_user_cpf', true);
		$row[] = get_user_meta( $user_id, '_user_email', true);
		$row[] = get_user_meta( $user_id, '_user_telefone', true);
		$row[] = get_user_meta( $user_id, '_user_municipio', true);
		$row[] = get_user_meta( $user_id, '_user_uf', true);
		$row[] = get_user_meta( $user_id, '_user_representatividade', true);
		$row[] = get_user_meta( $user_id, '_user_instituicao', true);
		$row[] = get_user_meta( $user_id, '_user_setorial_area', true);
		$row[] = get_user_meta( $user_id, '_user_relatorio_radio', true);
		$row[] = get_user_meta( $user_id, '_user_relatorio', true);
		$row[] = get_user_meta( $user_id, '_user_primeira', true);
		$row[] = get_user_meta( $user_id, '_user_segunda', true);
		$row[] = get_user_meta( $user_id, '_user_terceira', true);
		$row[] = get_user_meta( $user_id, '_user_quarta', true);
		$row[] = get_user_meta( $user_id, '_user_quinta', true);
		$row[] = get_user_meta( $user_id, '_user_sexta', true);
		$row[] = get_user_meta( $user_id, '_user_desafio1', true);
		$row[] = get_user_meta( $user_id, '_user_desafio2', true);
		$row[] = get_user_meta( $user_id, '_user_solucao1', true);
		$row[] = get_user_meta( $user_id, '_user_solucao2', true);
		$row[] = get_user_meta( $user_id, '_user_atividade1', true);
		$row[] = get_user_meta( $user_id, '_user_atividade2', true);
		$row[] = get_user_meta( $user_id, '_user_documentosapoio', true);
		fputcsv($output , $row, ';');
	}
	fclose($output);
}
die();