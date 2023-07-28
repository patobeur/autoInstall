<?php 
$viewPage = './php/views/v_index.html';
if (file_exists($viewPage)) {
	$finalView = file_get_contents($viewPage);
	$views = [
		"bloc" => [],
		"paths" => [
			"loginForm" => ['path'=>'./php/views/v_loginForm.html', 'target'=>'#LOGINFORMVIEW#'],
			"themeToggle" => ['path'=>'./php/views/v_themeToggle.html', 'target'=>'#NAVVIEW#']
		]
	];

	foreach ($views['paths'] as $key => $value) {
		if (file_exists($value['path'])) {
			$finalView = str_replace($value['target'], file_get_contents($value['path']), $finalView);
		}
	}
	print_r($finalView);
}