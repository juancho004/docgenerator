<?php 
/**
 * Class Display .
 *
 * @author Jcbarreno <jcbarreno.b@gmail.com>
 * @version 1.0
 * @package 
 */
class ModelDisplay {

	protected $prefix;
	protected $app;

	public function  __construct($app, $prefix)
	{
		$this->prefix = $prefix;
		$this->app 	= $app;
		$this->tabDisplay 	= "{$this->prefix}DisplayType";
	}

	public function viewCreateDisplay($params=false)
	{
		$valueDisplayName 	= "";
		$inputIdDisplay 	= "";
		$valueDisplayLabel	= "";
		$idBtn 				= "save-btn";
		$nameBtn 			= "Save";
		$idForm				= "display-form-create";

		if($params!= false){
			$valueDisplayName 	= 'value="'.$params->name.'"';
			$valueDisplayLabel 	= 'value="'.$params->label.'"';

			$inputIdDisplay 	= '<input type="hidden" value="'.$params->id.'" name="idValue" />';
			$idBtn 				= "update-btn";
			$nameBtn 			= "Update";
			$idForm				= "display-form-update";

			#_pre($params);exit;
		}

		$response = '<ul class="pricing-table">
				<li class="title">New Display</li>
				<li><div class="space-bar"></div></li>
				<li>
					<form id="'.$idForm.'">
						'.$inputIdDisplay.'
						<div class="row">

							<div class="large-12 columns">
								<span>Vertical Type:</span>
								<select name="typevertical">
								'.$this->getTypeVertical($params->multiVertical).'
								</select>
							</div>

							<div class="large-12 columns">
								<span>Vertical:</span>
								<select name="vertical">
								'.$this->getVertical($params->idVertical).'
								</select>
							</div>

							<div class="large-12 columns">
								<span>Provider:</span>
								<select name="provider">
								'.$this->getProvider($params->idProvider).'
								</select>
							</div>

							<div class="large-12 columns">
								<span>Name:</span>
								<input id="displayname" type="text" placeholder="display Name" name="displayname" '.$valueDisplayName.' >
							</div>
							
							<div class="large-12 columns">
								<span>Label:</span>
								<input id="displaylabel" type="text" placeholder="display Labek" name="displaylabel" '.$valueDisplayLabel.' >
							</div>

						</div>
					</form>
				</li>
				<li class="cta-button"><a id="'.$idBtn.'" class="button" href="#">'.$nameBtn.'</a></li>
				</ul>';
		return $response;
	}

	private function getTypeVertical($option=0)
	{

		switch ($option) {
			case '1':
				$htmlTypeVertical = '<option value="1">Multi Vertical</option>
									<option value="0">Vertical</option>';
			break;
			
			default:
				$htmlTypeVertical = '<option value="0">Vertical</option>
									<option value="1">Multi Vertical</option>';
			break;
		}

		return $htmlTypeVertical;
	}

	private function getVertical($id=false)
	{
		if(!$id){
			$list = $this->getList("SELECT * FROM dg_Vertical WHERE status = 1");
			$htmlVertical = "";
			foreach ($list->content as $key => $vertical) {
				$htmlVertical.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
			}
		}else{

			$list = $this->getList("SELECT * FROM dg_Vertical WHERE status = 1 AND id = {$id}");
			$htmlVertical = "";
			foreach ($list->content as $key => $vertical) {
				$htmlVertical.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
			}

			$list = $this->getList("SELECT * FROM dg_Vertical WHERE status = 1 AND id <> {$id}");
			foreach ($list->content as $key => $vertical) {
				$htmlVertical.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
			}
			$htmlVertical.= "";
		}

		return $htmlVertical;
	}

	private function getProvider($id=false)
	{
		if(!$id){
			
			$list = $this->getList("SELECT * FROM dg_Provider");
			$htmlProvider = "";
			foreach ($list->content as $key => $vertical) {
				$htmlProvider.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
			}
			$htmlProvider.= "";

		}else{

			$list = $this->getList("SELECT * FROM dg_Provider WHERE id = {$id}");
			$htmlProvider = "";
			foreach ($list->content as $key => $vertical) {
				$htmlProvider.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
			}

			$list = $this->getList("SELECT * FROM dg_Provider WHERE id <> {$id}");
			foreach ($list->content as $key => $vertical) {
				$htmlProvider.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
			}

			$htmlProvider.= "";
		}

		return $htmlProvider;
	}

	public function viewReadDisplay($params=false)
	{
		$query = "SELECT dt.id, v.name as vertical, p.name as provider, dt.label, dt.multiVertical 
					FROM dg_DisplayType as dt
					INNER JOIN dg_Vertical as v
					ON v.id = dt.id_Vertical
					INNER JOIN dg_Provider as p
					ON p.id = dt.id_Provider
					WHERE dt.status = 1";

		$list = $this->getList($query);

		#_pre($list);

		$html = '<ul class="pricing-table ">
				<li class="title">List Display</li>
				<li><div class="space-bar"></div></li>
				<li><div class="new-display btn-new-item" onclick="newProducto()">Nuevo</div></li>';

		if( !$list->status ){
			$html.= '<li><div class="space-bar"><h5 style="text-align:center;">'.$list->content.'</h5></div></li>';
		}else{
				$html.= '<li><center><table style=" width:80%">
						<thead>
							<tr>
								<th width="20%">Provider</th>
								<th width="20%">Vertical</th>
								<th width="20%">Document Name</th>
								<th width="20%">Vertical Type</th>
								<th width="20%" colspan="2">Option</th>
							</tr>
						</thead>
						<tbody>';


			foreach ($list->content as $key => $value) {
				$html.= '
						<tr class="block-option">
							<td>'.$value['provider'].'</td>
							<td>'.$value['vertical'].'</td>
							<td>'.$value['label'].'</td>
							<td>'.(($value['multiVertical'] == 0)? "Vertial":"Multi Vertical" ).'</td>
							<td><img class="remove-display" id-display="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-trash.svg" ></td>
							<td><img class="update-display" id-display="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-pencil.svg" ></td>
						</tr>';
			}
			$html.='</tbody>
						</table></center></li>';
		}

		$html.= '</ul>';
		return $html;
	}

	public function registerCreateDisplay($params)
	{

		$query = "insert into dg_DisplayType (id_Vertical,id_Provider,multiVertical,name,label) ";
		$query.= "values (
			'".$this->app->escape($params['vertical'])."',
			'".$this->app->escape($params['provider'])."',
			'".$this->app->escape($params['typevertical'])."',
			'".$this->app->escape($params['displayname'])."',
			'".$this->app->escape($params['displaylabel'])."'
			)";
		return $this->insert($query,"dg_DisplayType");
	}

	public function viewUpdateUpdateDisplay($params)
	{

		$list = $this->getList("SELECT * FROM {$this->tabDisplay} WHERE id = {$params}");
		$response = new stdClass();
		foreach ($list as $key => $value) {
			foreach ($value as $keyVertical => $valueVertical) {
				$response->id = $valueVertical['id'];
				$response->idVertical = $valueVertical['id_Vertical'];
				$response->idProvider = $valueVertical['id_Provider'];
				$response->multiVertical = $valueVertical['multiVertical'];
				$response->name = $valueVertical['name'];
				$response->label = $valueVertical['label'];
				$response->status = $valueVertical['status'];
			}
		}
		return array( "content" => $this->viewCreateDisplay($response) );
	}

	public function updateUpdateDisplay($params)
	{

		$response = new stdClass();
		$update = $this->update("UPDATE ".$this->tabDisplay." 
									SET multiVertical = ".$this->app->escape($params['typevertical']).",
									 id_Vertical = ".$this->app->escape($params['vertical']).",
									 id_Provider = ".$this->app->escape($params['provider']).",
									 name = '".$this->app->escape($params['displayname'])."',
									 label = '".$this->app->escape($params['displaylabel'])."' 
									WHERE id = ".$params['idValue']." ", $this->tabDisplay);

		if( $update->status ){
			$response->status = true;
			$response->content = $this->viewReadDisplay();
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
