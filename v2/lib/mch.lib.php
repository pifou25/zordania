<?php

function mch_make_infos(array $com_array)
{
	$total = [0,0,0];
	foreach($com_array as $value)
	{
		$total[1] += $value['mch_nb']; //ressoures au total contre ca
		$total[2] += $value['mch_prix']; //idem
	}
        $total[0] = round( $total[2] / $total[1], 2); 
	return ['total_ventes' => count($com_array), 'ventes' => $total];
}
