<?php 
/**
 * Class BlockContent .
 *
 * @author Jcbarreno <jcbarreno.b@gmail.com>
 * @version 1.0
 * @package 
 */
class ModelBlockcontent extends ModelMaster{

	protected $prefix;
	protected $app;

	public function  __construct($app, $prefix)
	{
		$this->prefix = $prefix;
		$this->app 	= $app;
		$this->tabBlockContent 	= "{$this->prefix}BlockContent";
	}

	public function getMasiveDocument()
	{
		$getListContent = $this->getList("SELECT * FROM dg_NewDocument WHERE status = 1");

		#_pre($getListContent);exit;

		foreach ($getListContent->content as $keyContent => $valueContent) {
			$paramsContent 					= new stdClass();
			$setting 					= new stdClass();
			
			$paramsContent->idBLockContent 	= $valueContent['id_BlockContent'];
			$paramsContent->name 			= $valueContent['name'];
			$paramsContent->id 				= $valueContent['id'];
			
			$setting->CAMPAIGN 				= $valueContent['campaign'];
			$setting->DISPLAYID 			= $valueContent['displayId'];
			$setting->PUBLISHERID 			= $valueContent['publisherId'];
			$setting->MD 					= $valueContent['md'];
			$setting->ZIPCODE 				= $valueContent['zipcode'];
			$setting->DOMAIN 				= $valueContent['domain'];
			$setting->CID 					= $valueContent['cid'];

			$settings 	= $this->addSettingToHtml($setting,$paramsContent->idBLockContent,$paramsContent->id);
			$date 		= date("Y-m-d H:i:s");
			
			$update 	= $this->update("UPDATE dg_NewDocument
									SET content = '".base64_encode( $this->app->escape($settings->content) )."',
									css = '".base64_encode( $this->app->escape($settings->css) )."',
									status = 1 WHERE id = ".$paramsContent->id." ", "dg_NewDocument");
		}

		$getListHtml = $this->getList("SELECT content, css, id_BlockContent FROM dg_NewDocument WHERE status = 1");
		$arrayResponse = array();
		foreach ($getListHtml->content as $key => $htmlContent) {
			
			#$html = '<!DOCTYPE html>';
			#$html.= '<html>';
			#$html.= '<head>';
			$html= '<style type="text/css">';
			$html.= base64_decode($htmlContent['css']);
			$html.= '</style>';
			#$html.= '</head>';
			#$html.= '<body>';
			$html.= html_entity_decode(base64_decode($htmlContent['content']));
			#$html.= '</body>';
			#$html.= '</html>';

			$arrayResponse[] = array("html" => $html, "nameDoc" => $this->asignNameFile($htmlContent['id_BlockContent'],true) );
		}

		return $arrayResponse;
	}



	private function readFile($id){

		$path = $this->getList("SELECT cssSourcePath FROM dg_BlockContent WHERE id = ".$id);
		$path = $path->content[0]['cssSourcePath'];
		$file = fopen ($path, "r");
		$cotent = fread($file, filesize($path));
		return $cotent;
	}

	public function viewCreateBlockcontent($params=false)
	{
		$valueDisplayName 	= "";
		$inputIdDisplay 	= "";
		$valueDisplayLabel	= "";
		$idBtn 				= "save-btn";
		$nameBtn 			= "Save";
		$idForm				= "blockcontent-form-create";
		$getDisplayType		= $this->getDisplayType();
		$getBlockContentType= $this->getBlockContentType();
		$textAreaContent 	= "";
		$textAreaCss		= "";

		if($params!= false){
			$textAreaContent 	= $params->content;
			$inputIdDisplay 	= '<input type="hidden" value="'.$params->id.'" name="idValue" />';
			$idBtn 				= "update-btn";
			$nameBtn 			= "UPDATE";
			$idForm				= "blockcontent-form-update";

			$getDisplayType		= $this->getDisplayType($params->idDisplayType);
			$getBlockContentType= $this->getBlockContentType($params->idBlockContentType);
			$textAreaCss		=$this->readFile($params->id);
		}

		$response = '<form id="'.$idForm.'" class="content-form-block-prev">
						'.$inputIdDisplay.'
						<div class="block-edit-content">
							<!--div class="large-12 columns">
								<span>Display Type:</span>
								<select name="typedisplay">
								'.$getDisplayType.'
								</select>
							</div-->

							<!--div class="large-12 columns">
								<span>Block Name:</span>
								<select name="blockname">
								'.$getBlockContentType.'
								</select>
							</div-->


							<div id="option-colapse" class="expand">
								<img width="25" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-arrows-out.svg" >
							</div>
							<div class="block-content">

								<div class="large-12">
									<span>HTML:</span>
									<textarea id="textareacontent" name="textareacontent" >'.base64_decode($textAreaContent).'</textarea>
								</div>

								<div class="large-12">
									<span>CSS:</span>
									<textarea id="textareastylesheet" name="textareastylesheet" >'.$textAreaCss.'</textarea>
								</div>
							</div>

						</div>
					</form>
					<div class="prev-content-box"></div>';

		$subMneu = '<span onclick="$(this).documentGenerator(\'_updateBlockContent\',{data:\'false\'}); " >BACK</span>';
		$subMneu.= '<span class="cta-button" ><a id="'.$idBtn.'" class="button" href="#">'.$nameBtn.'</a></span>';


		return $this->contentHtml5( $response , "EDIT TEMPLATE", $subMneu );
		#return $html;
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

		#$html = '<!DOCTYPE html>';
		#$html = '<html>';
		#$html = '<head>';
		#$html = '<style type="text/css">';
		#$html.= $cotentStyle;
		#$html.= '</style>';
		#$html.= '</head>';
		#$html.= '<body>';
		$html.= html_entity_decode(base64_decode($list->content[0]['content']));
		#$html.= '</body>';
		#$html.= '</html>';

		return array("htmlContent" => $html );

	}

	public function viewReadBlockcontent($params=false)
	{

		$query = "SELECT bc.id, bv.name AS blockVertical, v.name AS nameVertical, tf.name AS typeFile, bc.content,bc.cssSourcePath
				FROM dg_BlockContent AS bc

				INNER JOIN dg_blockVertical AS bv
				ON bv.id = bc.id_blockVertical

				INNER JOIN dg_Vertical AS v
				ON v.id = bc.id_vertical

				INNER JOIN dg_typeFile AS tf
				ON tf.id = bc.id_typeFile

				WHERE bc.status = 1

				ORDER BY nameVertical ASC
				#GROUP BY nameVertical 
				";

		$list = $this->getList($query);

		$subMneu = '<span>NEW*</span>';

		$html = '';

		if( !$list->status ){
			$html.= '<div class="space-bar"><h5 style="text-align:center;">'.$list->content.'</h5></div>';
		}else{
				$html.= '<table style=" width:80%" align="center">
						<thead>
							<tr>
								<th width="40%">Vertical Name</th>
								<th width="40%">Display</th>
								<th width="10%">REMOVE</th>
								<th width="10%">EDIT</th>
							</tr>
						</thead>
						<tbody>';


			foreach ($list->content as $key => $value) {
				$html.= '
						<tr class="block-option">
							<td>'.$value['nameVertical'].'</td>
							<td>'.$value['typeFile'].'</td>
							<td><img class="remove-blockcontent" id-blockcontent="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-trash.svg" ></td>
							<td><img class="update-blockcontent" id-blockcontent="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-widget.svg" ></td>							
						</tr>';
						#<td><img class="prev-blockcontent" id-blockcontent="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-photo.svg" ></td>
			}
			$html.='</tbody>
						</table>';
		}

		$html.= '';
		return $this->contentHtml5( $html , "TEMPLATE", $subMneu );
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

	public function viewUpdateUpdateBlockcontent($params)
	{

		$list = $this->getList("SELECT * FROM {$this->tabBlockContent} WHERE id = {$params}");
		$response = new stdClass();
		foreach ($list as $key => $value) {
			foreach ($value as $keyBC => $valueBC) {
				$response->id = $valueBC['id'];
				#$response->idDisplayType = $valueBC['id_DisplayType'];
				#$response->idBlockContentType = $valueBC['id_BlockContentType'];
				$response->content = $valueBC['content'];
				$response->status = $valueBC['status'];
			}
		}
		return array( "content" => $this->viewCreateBlockcontent($response) );
	}

	private function asignNameFile($params, $onlyName=false)
	{
		#var_dump($onlyName); exit;
		
		$query = "SELECT bv.name AS blockVertical,v.name AS nameVertical,tf.name AS nameFile

					FROM dg_BlockContent AS bc

					INNER JOIN dg_blockVertical AS bv
					ON bv.id = bc.id_blockVertical


					INNER JOIN dg_Vertical AS v
					ON v.id = bc.id_Vertical


					INNER JOIN dg_typeFile AS tf
					ON tf.id = bc.id_typeFile
					WHERE bc.id =".$params['idValue']." LIMIT 1";

		$list = $this->getList($query);

		#_pre($list);exit;
		$nameFile = trim("Style.".$list->content[0]['blockVertical'].".".$list->content[0]['nameVertical'].".".$list->content[0]['nameFile']);

		if($onlyName){
			$nameFile = trim("Document.".$list->content[0]['blockVertical'].".".$list->content[0]['nameVertical'].".".$list->content[0]['nameFile']);
			return $nameFile;
		}

		$nameFile = "templates".DS."home".DS."css".DS."styleDocument".DS."blockContent".DS.mb_strtoupper($nameFile, 'UTF-8').".css";
		#_pre($myFile);exit;
		$fh = fopen($nameFile, 'w+') or die("can't open file");
		$stringData = $params['textareastylesheet'];
		fwrite($fh, $stringData);
		fclose($fh);
		return $nameFile;

	}

	public function updateBlockUpdateBlockcontent($params=false)
	{

		$response = new stdClass();


		/*
		_pre($params['false']);
		exit;

		if($params['false']==''){
			$response->status = true;
			$response->content = $this->viewReadBlockContent();
			return $response;
		}
		*/


		
		$update = $this->update("UPDATE ".$this->tabBlockContent." 
									 SET content = '".base64_encode( $this->app->escape($params['textareacontent']) )."',
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
