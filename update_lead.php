<?
echo "200";

include "function.php";

update_lead($_POST, $link);


function update_lead($lead, $link){

	$lod_file = "update_lead.txt";
	
	write($lod_file, 'w', 'Начали');	
	write_mass($lod_file, 'a', $lead);

	//Разбор доп полей
	foreach($lead['leads']['update'][0]['custom_fields'] as $vol){
		$lead_cf[$vol['id']] = array('value'=>$vol['values']['0']['value'], 'enum'=>$vol['values']['0']['enum']);
		if($vol['id'] == '650971') $lead_cf[$vol['id']] = array('value'=> date('Y-m-d', $vol['values']['0']), 'enum'=>$vol['values']['0']);
		if($vol['id'] == '27733') $lead_cf[$vol['id']] = array('value'=> date('Y-m-d', ($vol['values']['0']+10800)), 'enum'=>($vol['values']['0']+10800));//+3 часа
	}
		
	write_mass($lod_file, 'a', $lead_cf);
	
	//id hh не найдено в сделке
	if($lead_cf['650197']['value'] == "") return;
		
	//Дата окончания занятий равна проверочной дате	= выход
	if($lead_cf['650971']['value'] == $lead_cf['27733']['value']) return;



	//Зарос Пользователей amoCRM
	$users = search($link['users']); 
	
	
	//Добавить/Отредактировать/Удалить отдельное поле нельзя. Весь набор полей всегда отправляется полностью.
	//Если какое-то из полей не указано, то оно будет удалено у лида/ученика.
	//Редактируем доп поля (Обновляем дату в hh)
	$lead_cf['27691']['hh'] = 'Утро';
	if($lead_cf['27691']['enum'] == '38505') //Обед 14:30
		$lead_cf['27691']['hh'] = 'Вечер';
	
	write_mass($lod_file, 'a', post_send_hh($link['EditUserExtraFields'], array(
		'authkey'=>$link['key'],
		'studentClientId'=>$lead_cf['650197']['value'],
		'fields'=>array(
			array(
				'name'=>'КЛАСС',
				'value'=>$lead_cf['27673']['value'],
			),
			array(
				'name'=>'Отделение',
				'value'=>$lead_cf['27689']['value'], 
			),
			array(
				'name'=>'Время обучения',
				'value'=>$lead_cf['27691']['hh'],
			),
			array(
				'name'=>'Пол',
				'value'=>$lead_cf['650981']['value'],
			),
			array(
				'name'=>'id amoCRM',
				'value'=>$lead['leads']['update'][0]['id'],
			),
			array(
				'name'=>'Есть в amoCRM',
				'value'=>'true',
			),
			array(
				'name'=>'Дата окончания занятий',
				'value'=>$lead_cf['27733']['value'],
			),
			array(
				'name'=>'Ответсвенный amoCRM',
				'value'=>$users['_embedded']['users'][$lead['leads']['update'][0]['responsible_user_id']]['name'],
			),
		),
	)));	
	
	
	//обновление сделки в amoCRM
	$l_post['update']=array(
		array(
			'id'=>$lead['leads']['update'][0]['id'],
			'updated_at'=>strtotime("+1 second"),
			'custom_fields'=>array(
				array('id'=>'650971','values'=>array(array('value'=>$lead_cf['27733']['value']))), 
			)
		)
	);

	
	write_mass($lod_file, 'a', post_send($link['leads'], $l_post));
	
	
	write($lod_file, 'a', 'Закончили');	

	return "200";
}

?>