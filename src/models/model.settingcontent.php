<?php 
/**
 * Class Settingcontent .
 *
 * @author Jcbarreno <jcbarreno.b@gmail.com>
 * @version 1.0
 * @package 
 */
class ModelSettingcontent {

	protected $prefix;
	protected $app;

	public function  __construct($app, $prefix)
	{
		$this->prefix = $prefix;
		$this->app 	= $app;
		$this->tabSettingContent 	= "{$this->prefix}SettingContent";
	}

	private function readFile($id){

		$path = $this->getList("SELECT cssSourcePath FROM dg_BlockContent WHERE id = ".$id);
		$path = $path->content[0]['cssSourcePath'];
		$file = fopen ($path, "r");
		$cotent = fread($file, filesize($path));
		return $cotent;
	}

	public function viewCreateettingcontent($params=false)
	{
		$valueDisplayName 	= "";
		$inputIdDisplay 	= "";
		$valueDisplayLabel	= "";
		$idBtn 				= "save-btn";
		$nameBtn 			= "Save";
		$idForm				= "settingcontent-form-create";
		$getDisplayType		= $this->getDisplayType();
		$getBlockContentType= $this->getBlockContentType();
		$getSettingType		= $this->getSettingType();
		$blockContent 		= "";
		$valueSettingName 	= "";

		if($params!= false){
			$blockContent 		= $this->createStructureContent($params->content);
			$inputIdSetting 	= '<input type="hidden" value="'.$params->id.'" name="idValue" />';
			$idBtn 				= "update-btn";
			$nameBtn 			= "Update";
			$idForm				= "settingcontent-form-update";

			$getDisplayType		= $this->getDisplayType($params->idDisplayType);
			$valueSettingName 	= 'value="'.$params->name.'"';
			$getSettingType		= $this->getSettingType($params->type);
		}

		$response = '<ul class="pricing-table">
				<li class="title">New Setting Content</li>
				<li><div class="space-bar"></div></li>
				<li>
					<form id="'.$idForm.'" class="content-form-block">
						'.$inputIdSetting.'
						<div class="row">

							<div class="large-12 columns">
								<span>Display Type:</span>
								<select name="typedisplay">
								'.$getDisplayType.'
								</select>
							</div>

							<div class="large-12 columns">
								<span>Name:</span>
								<input id="settingname" type="text" placeholder="Setting Name" name="settingname" '.$valueSettingName.' >
							</div>

							<div class="large-12 columns">
								<span>Setting Type:</span>
								<select name="typesetting">
								'.$getSettingType.'
								</select>
							</div>

							<div class="large-12 columns">
								<span>Content:</span>
								'.$blockContent.'
							</div>

							

						</div>
					</form>
				</li>
				<li class="cta-button"><a id="'.$idBtn.'" class="button" href="#">'.$nameBtn.'</a></li>
				</ul>';
		return $response;
	}

	public function createStructureContent($content)
	{
		$params = json_decode($content);

		$inputParams = '<table>';
		#$inputParams.= '<td><label>Add</label><div>+</div></td>';
		foreach ($params as $key => $value) {
			$inputParams.= '<tr>';
			$inputParams.= '<td><label>'.$key.'</label></td>';
			$inputParams.= '<td><input name="'.$key.'" value="'.$value.'"></td>';
			$inputParams.= '</tr>';
		}
		$inputParams.= '</table>';
		return $inputParams;
	}

	private function getSettingType($option=false)
	{
		if(!$option){
			$htmlSelect = '<option value="1">Javascript</option>';
			$htmlSelect.= '<option value="2">HTML</option>';
		}else{
			if($option==1){
				$htmlSelect = '<option value="1">Javascript</option>';
				$htmlSelect.= '<option value="2">HTML</option>';
			}else{
				$htmlSelect = '<option value="2">HTML</option>';
				$htmlSelect.= '<option value="1">Javascript</option>';
			
			}
		}
		return $htmlSelect;
	}

	private function getDisplayType($id=false)
	{
		if(!$id){
			$list = $this->getList("SELECT * FROM dg_DisplayType WHERE status = 1");
			$htmlDislayType = "";
			foreach ($list->content as $key => $display) {
				$htmlDislayType.= '<option value="'.$display['id'].'">'.$display['name'].'</option>';
			}
		}else{

			$list = $this->getList("SELECT * FROM dg_DisplayType WHERE status = 1 AND id = {$id}");
			$htmlDislayType = "";
			foreach ($list->content as $key => $display) {
				$htmlDislayType.= '<option value="'.$display['id'].'">'.$display['name'].'</option>';
			}

			$list = $this->getList("SELECT * FROM dg_DisplayType WHERE status = 1 AND id <> {$id}");
			foreach ($list->content as $key => $display) {
				$htmlDislayType.= '<option value="'.$display['id'].'">'.$display['name'].'</option>';
			}
			$htmlDislayType.= "";
		}

		return $htmlDislayType;
	}

	private function getBlockContentType($id=false)
	{
		if(!$id){
			$list = $this->getList("SELECT * FROM dg_BlockContentType WHERE status = 1");
			$htmlBlockContentType = "";
			foreach ($list->content as $key => $blockCT) {
				$htmlBlockContentType.= '<option value="'.$blockCT['id'].'">'.$blockCT['name'].'</option>';
			}
		}else{

			$list = $this->getList("SELECT * FROM dg_BlockContentType WHERE status = 1 AND id = {$id}");
			$htmlBlockContentType = "";
			foreach ($list->content as $key => $blockCT) {
				$htmlBlockContentType.= '<option value="'.$blockCT['id'].'">'.$blockCT['name'].'</option>';
			}

			$list = $this->getList("SELECT * FROM dg_BlockContentType WHERE status = 1 AND id <> {$id}");
			foreach ($list->content as $key => $blockCT) {
				$htmlBlockContentType.= '<option value="'.$blockCT['id'].'">'.$blockCT['name'].'</option>';
			}
			$htmlBlockContentType.= "";
		}

		return $htmlBlockContentType;
	}

	public function prevBlockBlockcontent($id)
	{
		$list = $this->getList("SELECT content, cssSourcePath FROM dg_BlockContent WHERE id = ".$id);
		$file 			= fopen ($list->content[0]['cssSourcePath'], "r");
		$cotentStyle 	= fread($file, filesize($list->content[0]['cssSourcePath']));

		$html = '<!DOCTYPE html>';
		$html = '<html>';
		$html = '<head>';
		$html = '<style type="text/css">';
		$html.= $cotentStyle;
		$html.= '</style>';
		$html.= '</head>';
		$html.= '<body>';
		$html.= html_entity_decode($list->content[0]['content']);
		$html.= '</body>';
		$html.= '</html>';

		return array("htmlContent" => $html );

	}

	public function viewReadSettingcontent($params=false)
	{

		$query = "SELECT 	sc.id AS idSetting,
					CASE dt.multiVertical
						WHEN 1 then 'MultiVertical'
						WHEN 0 then 'Vertical'
					END AS 	multiVertical,
					v.name AS verticalName, dt.name AS displayType, sc.name AS settingName,
					CASE sc.type
						WHEN 1 then 'Javascript'
						WHEN 2 then 'Html/Table'
					END AS 	typeSetting
					FROM dg_SettingContent AS sc
					INNER JOIN dg_DisplayType AS dt
					ON dt.id = sc.id_DisplayType
					INNER JOIN dg_Vertical AS v
					ON v.id = dt.id_Vertical";

		$list = $this->getList($query);

		#_pre($list);exit;

		$html = '<ul class="pricing-table ">
				<li class="title">List Display</li>
				<li><div class="space-bar"></div></li>
				<li><div class="new-blockcontent btn-new-item" onclick="newProducto()">Nuevo</div></li>';

		if( !$list->status ){
			$html.= '<li><div class="space-bar"><h5 style="text-align:center;">'.$list->content.'</h5></div></li>';
		}else{
				$html.= '<li><center><table style=" width:80%">
						<thead>
							<tr>
								<th width="20%">Vertical Type</th>
								<th width="20%">Vertical Name</th>
								<th width="20%">Display Name</th>
								<th width="15%">Setting Name</th>
								<th width="10%">Setting Type</th>
								<th width="15%" colspan="3">Option</th>
							</tr>
						</thead>
						<tbody>';


			foreach ($list->content as $key => $value) {
				$html.= '
						<tr class="block-option">
							<td>'.$value['multiVertical'].'</td>
							<td>'.$value['verticalName'].'</td>
							<td>'.$value['displayType'].'</td>
							<td>'.$value['settingName'].'</td>
							<td>'.$value['typeSetting'].'</td>
							<td><img class="remove-settingcontent" id-settingcontent="'.$value['idSetting'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-trash.svg" ></td>
							<td><img class="update-settingcontent" id-settingcontent="'.$value['idSetting'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-pencil.svg" ></td>
							<td><img class="prev-settingcontent" id-settingcontent="'.$value['idSetting'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-photo.svg" ></td>
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

	public function viewUpdateUpdateSettingcontent($params)
	{
		$list = $this->getList("SELECT * FROM {$this->tabSettingContent} WHERE id = {$params}");
		#_pre($list);exit;
		$response = new stdClass();
		foreach ($list as $key => $value) {
			foreach ($value as $keySC => $valueSC) {
				$response->id = $valueSC['id'];
				$response->idDisplayType = $valueSC['id_DisplayType'];
				$response->name = $valueSC['name'];
				$response->content = $valueSC['comment'];
				$response->type = $valueSC['type'];
			}
		}
		return array( "content" => $this->viewCreateettingcontent($response) );
	}

	private function asignNameFile($params)
	{
		$query = "SELECT p.name, v.name, dt.label,
					CASE multiVertical
						WHEN 1 then 'MultiVertical'
						WHEN 0 then 'Vertical'
					END AS 	multiVertical

					FROM dg_DisplayType AS dt

					INNER JOIN dg_Vertical AS v
					ON v.id = dt.id_Vertical

					INNER JOIN dg_Provider AS p
					ON p.id = dt.id_Provider
					WHERE dt.id =".$params['idValue']." LIMIT 1";

		$list = $this->getList($query);
		$nameFile = trim("Style.".$list->content[0]['multiVertical'].".".$list->content[0]['name'].".".$list->content[0]['label']);
		$nameFile = "templates".DS."home".DS."css".DS."styleDocument".DS."blockContent".DS.mb_strtoupper($nameFile, 'UTF-8').".css";
		#_pre($myFile);exit;
		$fh = fopen($nameFile, 'w+') or die("can't open file");
		$stringData = $params['textareastylesheet'];
		fwrite($fh, $stringData);
		fclose($fh);
		return $nameFile;

	}

	public function updateUpdateBlockcontent($params)
	{

		
		$response = new stdClass();
		$update = $this->update("UPDATE ".$this->tabBlockContent." 
									SET id_DisplayType = ".$this->app->escape($params['typedisplay']).",
									 id_BlockContentType = ".$this->app->escape($params['blockname']).",
									 content = '".($this->app->escape($params['textareacontent']))."',
									 lastChange = '".date("Y-m-d H:i:s")."' 
									WHERE id = ".$params['idValue']." ", $this->tabBlockContent);

		if( $update->status ){
			$response->status = true;
			$response->content = $this->viewReadBlockContent();

			$pathFile = $this->asignNameFile($params);

			$this->update("UPDATE ".$this->tabBlockContent." 
									SET cssSourcePath = '".$this->app->escape($pathFile)."' 
									WHERE id = ".$params['idValue']." ", $this->tabBlockContent);
		}else{
			$response->status = false;
			$response->message = "No se pudo actualizar el registro.";
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
		#_pre($query);exit;
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
