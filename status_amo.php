<?
echo "200";

include "function.php";

status_amo($_POST, $link);


function status_amo($post, $link){

	$lod_file = "status_amo.txt";
	
	write($lod_file, 'w', 'Начали');	
	//write_mass($lod_file, 'a', $post);
	
	//Зарос Пользователей amoCRM
	$users = search($link['users']); 
	//write_mass($lod_file, 'a', $users); 
	
	//Зарос сделки
	$lead = search($link['leads'].'?id='.$post['leads']['status'][0]['id']); 
	//write_mass($lod_file, 'a', $lead); 
	
	//Разбор доп полей
	foreach($lead['_embedded']['items'][0]['custom_fields'] as $vol)
		$lead_cf[$vol['id']] = array('value'=>$vol['values']['0']['value'], 'enum'=>$vol['values']['0']['enum']);
	
	//write_mass($lod_file, 'a', $lead_cf); 
	
	$create = false;
	//проверка есть ли ученик в hh
	$student_hh_clone =  post_send_hh($link['GetStudents'], array(
		'authkey'=>$link['key'],
		'term'=>$lead_cf['442133']['value'].' '.$lead_cf['442131']['value'],
	));

	if(empty($student_hh_clone['Students']))
		$create = true;

	if(isset($lead_cf['445685']['value']))
		$create = true;
	//Проверки данных для добавления ученика в HH
	
	$lead_cf['95347']['officeOrCompanyId'] = '1'; //филиал
	if($lead_cf['95347']['enum'] == '197865') //Толе би
		$lead_cf['95347']['officeOrCompanyId'] = '3';
	if($lead_cf['95347']['enum'] == '731707') //Мамыр
		$lead_cf['95347']['officeOrCompanyId'] = '2';
		
		
	$lead_cf['96029']['maturity'] = 'Начальные классы(0-3)';
	if($lead_cf['96029']['value'] > 3) //проверяем классы
		$lead_cf['96029']['maturity'] = 'Школьники (4-12кл)';
	
	
	//добавление ученика в HH
	$student_hh = post_send_hh($link['AddStudent'], array(
		'authkey'=>$link['key'],
		'firstName'=>$lead_cf['442131']['value'],
		'lastName'=>$lead_cf['442133']['value'],
		'gender'=>true,
		'officeOrCompanyId'=>$lead_cf['95347']['officeOrCompanyId'],
		'status'=>'Регистрация',
		'maturity'=>$lead_cf['96029']['maturity'],
	));
	
	//write($lod_file, 'a', $student_hh['ClientId']);
	
	if($student_hh['ClientId'] == "") return;
	
	
	//Заполенение доп полей
	$lead_cf['89165']['hh'] = 'Вечер';
	if($lead_cf['89165']['enum'] == '183695') //Утро
		$lead_cf['89165']['hh'] = 'Утро';
	
	write_mass($lod_file, 'a', post_send_hh($link['EditUserExtraFields'], array(
		'authkey'=>$link['key'],
		'studentClientId'=>$student_hh['ClientId'],
		'fields'=>array(
			array(
				'name'=>'КЛАСС',
				'value'=>$lead_cf['96029']['value'],
			),
			array(
				'name'=>'Отделение',
				'value'=>$lead_cf['446861']['value'], 
			),
			array(
				'name'=>'Время обучения',
				'value'=>$lead_cf['89165']['hh'],
			),
			array(
				'name'=>'id amoCRM',
				'value'=>$post['leads']['status'][0]['id'],
			),
			array(
				'name'=>'Есть в amoCRM',
				'value'=>'true',
			),
			array(
				'name'=>'Дата начала занятий',
				'value'=>date('Y-m-d', strtotime(substr($lead_cf['374505']['value'], 0, 10))),
			),
			array(
				'name'=>'Дата окончания занятий',
				'value'=>date('Y-m-d', strtotime(substr($lead_cf['89203']['value'], 0, 10))),
			),
			array(
				'name'=>'Ответсвенный amoCRM',
				'value'=>$users['_embedded']['users'][$lead['_embedded']['items'][0]['responsible_user_id']]['name'],
			),
			array(
				'name'=>'Математика тест',
				'value'=>$lead_cf['446851']['value']
			),
			array(
				'name'=>'Английский тест',
				'value'=>$lead_cf['446853']['value']
			),
			array(
				'name'=>'Русский тест',
				'value'=>$lead_cf['446855']['value']
			),
			array(
				'name'=>'Казахский тест',
				'value'=>$lead_cf['446857']['value']
			)
		),
	)));	
	
	
	
	if(!isset($lead['_embedded']['items'][0]['main_contact']['id'])) return;
	
	//Запрос из amoCRM контакт
	$main_contact = search($link['contacts'].'?id='.$lead['_embedded']['items'][0]['main_contact']['id']); 
	$main_contact = $main_contact['_embedded']['items'][0];
	//write_mass($lod_file, 'a', $main_contact); 
	
	foreach($main_contact['custom_fields'] as $vol)
		$main_contact_cf[$vol['id']] = array('value'=>$vol['values']['0']['value'], 'enum'=>$vol['values']['0']['enum']);
	//write_mass($lod_file, 'a', $main_contact_cf); 
	
	//Добавление родителя
	write_mass($lod_file, 'a', post_send_hh($link['EditAgentContacts'], array(
		'authkey'=>$link['key'],
		'studentClientId'=>$student_hh['ClientId'],
		'agents'=>array(array(
			'firstName'=>$main_contact['name'],
			'whoIs'=>'родитель',
			'mobile'=>$main_contact_cf['83341']['value'],
			'UseMobileBySystem'=>'false',
			'phone'=>$main_contact_cf['96091']['value'],
			'eMail'=>$main_contact_cf['83343']['value'],
			'UseEMailBySystem'=>'false',
			'IsCustomer'=>'true',
		)),
	)));
	
	//Запрос добавленного ученика из HH, достаём второй id для URL
	$students_array =  post_send_hh($link['GetStudents'], array(
		'authkey'=>$link['key'],
		'clientId'=>$student_hh['ClientId'],
	));
	//write_mass($lod_file, 'a', $students_array); 
	write_mass($lod_file, 'a', $lead_cf); 
	
	//обновление сделки в amoCRM
	$l_post['update']=array(
		array(
			'id'=>$post['leads']['status'][0]['id'],
			'updated_at'=>strtotime("+2 second"),
			'custom_fields'=>array(
				array('id'=>'446859','values'=>array(array('value'=>'https://'.DOMAINHH.'.t8s.ru/Profile/'.$students_array['Students'][0]['Id']))), //
				array('id'=>'445685','values'=>array(array('value'=>$students_array['Students'][0]['Id']))), //
			)
		)
	);

	
	write_mass($lod_file, 'a', post_send($link['leads'], $l_post));
	
	write($lod_file, 'a', 'Закончили');	

	return "200";
}



?>