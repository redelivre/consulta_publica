<?php

if (have_posts())
{
	the_post();
	
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.date('Ymd').'_consulta_publica_report.csv');
	
	$output = fopen('php://output', 'w');

	$header = array(__('Usuário', 'consulta_publica'), __('Número de cometários', 'consulta_publica'));
	fputcsv($output, $header, ';');
	$users = get_users();
	foreach ($users as $key => $value)
	{
		$row = array(
			$key,
			$value['count'],
		);
		$index = 0;
		foreach ($header as $col)
		{
			if($index < 2)
			{
				$index++;
				continue;
			}
			if(array_key_exists($col, $value['metas']))
			{
				$row[] = $value['metas'][$col][0];
			}
			else
			{
				$row[] = '';
			}
		}
		fputcsv($output , $row, ';');
	}
	//fclose($output);
}
die();