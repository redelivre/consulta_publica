<?php

if (have_posts())
{
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
	
	if(intval(get_query_var('consultas_print_csv', 0)))
	{
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.date('Ymd').'_consulta_publica_report.csv');
		
		fputcsv($output, $header, ';');
		
		while (have_posts())
		{
			the_post();
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
		}
	}
	elseif(intval(get_query_var('consultas_print_xls', 0)))
	{
		header('Pragma: public');
		header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
		header("Pragma: no-cache");
		header("Expires: 0");
		header('Content-Transfer-Encoding: none');
		header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
		header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
		header('Content-Disposition: attachment; filename='.date('Ymd').'_consulta_publica_report.xls');
		
		fputs($output, utf8_decode("
			<table>
			    <tr>"
		));
		foreach ($header as $head)
		{
			fputs($output, utf8_decode('<td>'.$head.'</td>')); 
		}
		fputs($output, utf8_decode("
			    </tr>"
		));
		while (have_posts())
		{
			the_post();
			$users_ids = get_post_meta(get_the_ID(), '_users_voto', true);
			
			foreach ($users_ids as $time => $user_id)
			{
			   fputs($output, '<tr>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_nome', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_cpf', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_email', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_telefone', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_municipio', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_uf', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_representatividade', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_instituicao', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_setorial_area', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_relatorio_radio', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_relatorio', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_primeira', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_segunda', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_terceira', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_quarta', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_quinta', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_sexta', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_desafio1', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_desafio2', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_solucao1', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_solucao2', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_atividade1', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_atividade2', true)).'</td>');
					fputs($output, '<td>'.utf8_decode(get_user_meta( $user_id, '_user_documentosapoio', true)).'</td>');
			   fputs($output, '</tr>');
			}
		}
		fputs($output, '</table>');
	}
	fclose($output);
}
die();