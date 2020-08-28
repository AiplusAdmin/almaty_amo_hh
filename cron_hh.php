<?
echo "200";

include __DIR__  .  DIRECTORY_SEPARATOR ."function.php";

cron_hh($link);


function cron_hh($link){

	$lod_file = __DIR__  .  DIRECTORY_SEPARATOR ."cron_hh.txt";
	
	write($lod_file, 'w', 'Начали');

	$student_array = post_send_hh($link['GetStudents'], array(
		'authkey'=>$link['key'],
		'extraFieldName'=>'Есть в amoCRM',
		'extraFieldValue'=>'true',
	));

	$i=0;
	foreach($student_array['Students'] as $Student){
		
		$i++;
		
		foreach($Student['ExtraFields'] as $Fields)
			$ExtraFields[$Fields['Name']] = $Fields['Value'];
			
		$l_post['update'][]=array(
			'id'=>$ExtraFields['id amoCRM'],
			'updated_at'=>strtotime("+2 second"),
			'custom_fields'=>array(
				array('id'=>'642911','values'=>array(array('value'=>$Student['Status']))), 
				//array('id'=>'650975','values'=>array(array('value'=>'https://'.DOMAINHH.'.t8s.ru/Profile/'.$Student['Id']))), 
				//array('id'=>'650197','values'=>array(array('value'=>$Student['ClientId']))), 
				//array('id'=>'650971','values'=>array(array('value'=>str_replace('T00:00:00.0000000', '', $ExtraFields['Дата окончания занятий'])))), 
			)
		);
		
		if($i>100){
			$i=0;
			write_mass($lod_file, 'a', post_send($link['leads'], $l_post));
			unset($l_post);
			sleep(1);
		}
		
		unset($ExtraFields);
			
	}
	
	if($i!=0)
		write_mass($lod_file, 'a', post_send($link['leads'], $l_post));
	
	
	write($lod_file, 'a', 'Закончили');	

	return "200";

}



?>