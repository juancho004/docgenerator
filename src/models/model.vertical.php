<?php 
/**
 * Class Vertical .
 *
 * @author Jcbarreno <jcbarreno.b@gmail.com>
 * @version 1.0
 * @package 
 */
class ModelVertical {

	protected $prefix;
	protected $app;

	public function  __construct($app, $prefix)
	{
		$this->prefix = $prefix;
		$this->app 	= $app;
		$this->tabVertical 	= "{$this->prefix}Vertical";
	}

	public function viewCreateVertical($params=false)
	{
		$valueVerticalName 	= "";
		$inputIdVertical 	= "";
		$idBtn 				= "save-btn";
		$nameBtn 			= "Save";
		$idForm				= "vertical-form-create";

		if($params!= false){
			$valueVerticalName 	= 'value="'.$params->name.'"';
			$inputIdVertical 	= '<input type="hidden" value="'.$params->id.'" name="idValue" />';
			$idBtn 				= "update-btn";
			$nameBtn 			= "Update";
			$idForm				= "vertical-form-update";
		}

		$response = '<ul class="pricing-table">
				<li class="title">New Vertical</li>
				<li><div class="space-bar"></div></li>
				<li>
					<form id="'.$idForm.'">
						'.$inputIdVertical.'
						<div class="row">
							<div class="large-12 columns">
								<span>Vertical Name:</span>
								<input id="verticalname" type="text" placeholder="vertical Name" name="verticalname" '.$valueVerticalName.' >
							</div>
						</div>
					</form>
				</li>
				<li class="cta-button"><a id="'.$idBtn.'" class="button" href="#">'.$nameBtn.'</a></li>
				</ul>';
		return $response;
	}

	public function viewReadVertical($params=false)
	{

		$list = $this->getList("SELECT * FROM {$this->tabVertical} WHERE status = 1");
		$html = '<ul class="pricing-table ">
				<li class="title">List Vertical</li>
				<li><div class="space-bar"></div></li>
				<li><div class="new-producto btn-new-item" onclick="newProducto()">Nuevo</div></li>';


		if( !$list->status ){
			$html.= '<li><div class="space-bar"><h5 style="text-align:center;">'.$list->content.'</h5></div></li>';
		}else{
				$html.= '<li><center><table style=" width:80%">
						<thead>
							<tr>
								<th width="70%">Vertical Name</th>
								<th width="15%" colspan="2">Option</th>
							</tr>
						</thead>
						<tbody>';


			foreach ($list->content as $key => $value) {
				$html.= '
						<tr class="block-option">
							<td>'.$value['name'].'</td>
							<td><img class="remove-vertical" id-vertical="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-trash.svg" ></td>
							<td><img class="update-vertical" id-vertical="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-pencil.svg" ></td>
						</tr>';
			}
			$html.='</tbody>
						</table></center></li>';
		}

		$html.= '</ul>';
		return $html;
	}

	public function registerCreateVertical($params)
	{
		$query = "insert into {$this->tabVertical} (name) ";
		$query.= "values ('".$this->app->escape($params['verticalname'])."')";
		return $this->insert($query,$this->tabVertical);
	}

	public function viewUpdateUpdateVertical($params)
	{
		#_pre($params);
		#exit;
		$list = $this->getList("SELECT * FROM {$this->tabVertical} WHERE id = {$params}");
		$response = new stdClass();
		foreach ($list as $key => $value) {
			foreach ($value as $keyVertical => $valueVertical) {
				$response->id = $valueVertical['id'];
				$response->name = $valueVertical['name'];
			}
		}
		return array( "content" => $this->viewCreateVertical($response) );
	}

	public function updateUpdateVertical($params)
	{
		$response = new stdClass();
		$update = $this->update("UPDATE ".$this->tabVertical." SET name = '".$this->app->escape($params['verticalname'])."'  WHERE id = ".$params['idValue']." ", $this->tabVertical);

		if( $update->status ){
			$response->status = true;
			$response->content = $this->viewReadVertical();
		}else{
			$response->status = false;
			$response->message = "No se pudo actualizar el registro";
		}
		return $response;
	}


	private function getList($query)
	{
		$response = new stdClass();
		try{
			$list = $this->app['dbs']['mysql_silex']->fetchAll($query);
			$response->status 	= (count($list) > 0 )? TRUE:FALSE;
			$response->content 	= (!$response->status)? "No se encontraron datos registrados":$list;
			return $response;
		}catch(Exception $e){
			$response->status 	= FALSE;
			$response->content 	= "Error #02: ERROR EN CONSULTA.";
			return $e->getMessage();
		}
	}

	private function insert($query,$table)
	{
		$response = new stdClass();
		try{
			$this->app['dbs']['mysql_silex']->executeQuery($query);
			$id_tab 				= $this->app['db']->lastInsertId('id');
			$response->status 	= TRUE;
			$response->message 	= "Registro creado exitosamente";
			#$response->id 		= $id_tab;
			return $response;
		}catch(Exception $e){
			$response->status 	= FALSE;
			$response->message 	= "Error #01: No se pudo insertar en en la tabla {$table}.";
			return $e->getMessage();
		}
	}

	private function update($query,$table)
	{

		$response = new stdClass();
		try{
			$resp = (boolean)$this->app['dbs']['mysql_silex']->executeQuery($query);
			$response->status 	= TRUE;
			$response->message 	= "Registro actualizado exitosamente";
			#$response->id 		= $id_tab;
			return $response;
		}catch(Exception $e){
			$response->status 	= FALSE;
			$response->message 	= "Error #0: No se pudo actualizar en en la tabla {$table}.";
			return $e->getMessage();
		}
	}

	

}
?>
