<?php 
/**
 * Class viewReadNewdocument .
 *
 * @author Jcbarreno <jcbarreno.b@gmail.com>
 * @version 1.0
 * @package 
 */
class ModelNewDocument extends ModelMaster{

	protected $prefix;
	protected $app;
	protected $verticalId;

	public function  __construct($app, $prefix)
	{
		$this->prefix = $prefix;
		$this->app 	= $app;
		$this->tabBlockContent 	= "{$this->prefix}BlockContent";
	}

	public function getSearch($data)
	{

		$colums = array();
		$filter = array();
		foreach ($data as $key => $value) {
			if(!empty($value['value'])){

				$colums[] = $value['name'];

				switch ($value['name']) {
					case 'id_BlockContent':
						$filter[] = $this->getIdBLockContent($value['value']);
					break;

					case 'id_typeFile':
						$filter[] = $this->getIdFile($value['value']);

					break;
					
					default:
						$filter[] = $value['value'];
					break;
				}

			}
		}
		$colums = array_filter($colums);
		$filter = array_filter($filter);

		#_pre($colums);
		#_pre($filter);
		#exit;

		$searchFilter = "SELECT * FROM dg_NewDocument WHERE ";
		for ($i=0; $i < count($colums); $i++) {

			switch ($colums[$i]) {
				case 'id_BlockContent':
					$searchFilter.= $colums[$i].' in('.$filter[$i].') AND ';
				break;

				case 'id_typeFile':
					$searchFilter.= $colums[$i].' in('.$filter[$i].') AND ';
				break;
				
				default:
					$searchFilter.= $colums[$i].' LIKE "%'.$filter[$i].'%" AND ';
				break;
			}

		}
		$searchFilter.=' status >= 0 GROUP BY id ORDER BY registerDate DESC';
		return $this->getDocument($searchFilter);

	}

	public function getIdBLockContent($option)
	{
		$result = array();
		$idBlock 	= $this->getList("SELECT id FROM dg_Vertical WHERE name LIKE '%{$option}%' ");
		$idBlock 	= $this->getList("SELECT id FROM dg_BlockContent WHERE id_Vertical = ".$idBlock->content[0]['id']);

		foreach ($idBlock->content as $key => $value) {
			$result[] = $value['id'];
		}
		return implode(',',$result);
	}

	public function getIdFile($string)
	{
		$result 	= array();
		$idFile 	= $this->getList("SELECT id FROM dg_typeFile WHERE name LIKE '%$string%'");

		foreach ($idFile->content as $key => $value) {
			$result[] = $value['id'];
		}
		return implode(',',$result);
	}




	public function editDocument($id)
	{

		$idBlock 	= $this->getList("SELECT id_BlockContent FROM dg_NewDocument WHERE id = {$id}");
		$idBlock 	= $idBlock->content[0]['id_BlockContent'];
		$listColum 	= "id,".implode(",", $this->getListColumsTable($idBlock));

		$typeVertical 	= $this->getList("SELECT id_Vertical FROM dg_BlockContent WHERE id = {$idBlock} ");
		$typeVertical 	= $typeVertical->content[0]['id_Vertical'];
		
		$getListContent = $this->getList("SELECT {$listColum} FROM dg_NewDocument WHERE id = {$id}");
		$params = '<form id="form-setting-display-update" class="row">';

		foreach ($getListContent->content[0] as $key => $display) {

			if($key == "id"){
				$params.= '<input id="'.$key.'" type="hidden" placeholder="" name="'.$key.'" value="'.$display.'"/>';
			}else{


				switch ($key) {
					case 'domain':
						$typeValidation = 'class="string"';
					break;
					
					default:
						$typeValidation = 'class="numeric"';
					break;
				}

				$labelName = ( ($typeVertical == 5) && ($key=="md") )? "vmProdId":$key;

				$params.= '<div class="block-input large-6 columns" >
						<label>'.strtoupper($labelName).':
							<input id="'.$key.'" type="text" placeholder="" name="'.$key.'" value="'.$display.'" '.$typeValidation.'/>
						</label>
					</div>';
			}
			
		}
		$params.= '<a href="#" class="button radius large-6 large-offset-3 columns" onclick="$(this).documentGenerator(\'_updateDocument\',{id:'.$id.'}); " >Update</a></form>';

		$subMneu = '<span onclick="$(this).documentGenerator(\'_getDocument\'); " >Back</span>';



		

		return array("setting" => $this->contentHtml5( $params , "EDIT DOCUMENT", $subMneu ) );
	}

	public function updateDocument($params)
	{
		$response = new stdClass();
		$inputSetting = array();
		foreach ($params as $key => $input) {
			$inputSetting[$input['name']] = $input['value'];
		}

		$emptyArray = array();
		foreach ($inputSetting as $key => $value) {
			if(empty($value)){
				$emptyArray[] = $key;
			}
		}

		if( COUNT($emptyArray) > 0 ){
			return array("status" => false, "emptyValues" => $emptyArray, "message" => "All fields are mandatory" );
		}

		$date = date("Y-m-d H:i:s");
		

		$idBlock 	= $this->getList("SELECT id_BlockContent FROM dg_NewDocument WHERE id = ".$inputSetting['id']);
		$idBlock 	= $idBlock->content[0]['id_BlockContent'];
		$listColum = $this->getListColumsTable($idBlock);
		
		$queryUpdateDinamic = "UPDATE dg_NewDocument SET ";

		foreach ($listColum as $key => $value) {
			$queryUpdateDinamic.= $value.' = "'.$inputSetting[$value].'"';
			$queryUpdateDinamic.= ', ';
		}
		$queryUpdateDinamic.= "lastChange = '".$date."' WHERE id = ".$inputSetting['id'];

		$update 	= $this->update($queryUpdateDinamic, "dg_NewDocument");
		#_pre($update);exit;
		$this->generateDocument(false,$inputSetting['id']);

		if($update->status){
			$response->status = true;
			$response->uri = $_SERVER['HTTP_HOST'].$this->app['url_generator']->generate('document');
		}else{
			$response->status = false;
			$response->message = "An error has occurred, please try again later.";
		}
		return $response;

	}

	public function createDocument($id,$params,$fileId)
	{

		
		$typeDocument 	= $this->getList("SELECT id_typeFile FROM dg_BlockContent WHERE id = ".$id);
		

		switch ($typeDocument->content[0]['id_typeFile']) {
			case 4:
				$typeValidation = "alphanumeric";
			break;
			
			default:
				$typeValidation = "numeric";
			break;
		}


		

		$response 		= new stdClass();
		$inputSetting 	= array();
		$date 			= date("Y-m-d H:i:s");

		foreach ($params as $key => $input) {
			$inputSetting[$input['name']] = $input['value'];
		}

		#validar que todos los vacios
		$emptyArray = array();
		foreach ($inputSetting as $key => $value) {
			if( $value == "" ){
				$emptyArray[] = $key;
			}
		}


		#validar tipo de campos
		$typeError = array();
		foreach ($inputSetting as $key => $value) {
			if($typeValidation == 'numeric' && $key != "domain" ){
				
				if( !is_numeric($value) ){
					$typeError[] = $key;	
				}
			}
			
		}
		#_pre($typeError);
		#exit;


		#obtener nombre de columnas para el query
		$columnsName = array();
		foreach ($inputSetting as $key => $columns) {
			$columnsName[]=$key;
		}

		#obtener los valores que se ingresaran en las columnas
		$columnsValuesDinamic = array();
		foreach ($inputSetting as $key => $columns) {
			$columnsValuesDinamic[] = $this->app->escape("'".$columns."'");
		}

		$valueDefault 	= array($this->app->escape($id),"'".$this->app->escape($date)."'",$this->app->escape($fileId));
		$columsQuery 	= "id_BlockContent,registerDate,id_typeFile,".implode(",", $columnsName);
		$rowsValue 		= array_merge($valueDefault,$columnsValuesDinamic);
		$rowsValue 		= implode(",", $rowsValue);

		if( COUNT($emptyArray) > 0 ){
			return array("status" => false, "emptyValues" => $emptyArray, "message" => "All fields are mandatory." );
		}

		if( COUNT($typeError) > 0 ){
			return array("status" => false, "emptyValues" => $typeError, "message" => "Error in the field type." );
		}

		$query = "INSERT INTO dg_NewDocument ({$columsQuery}) ";
		$query.= "values ({$rowsValue})";

		#_pre($query);exit;
		$register = $this->insert($query,"dg_NewDocument");
		$this->generateDocument(false,$register->id);

		if($register->status){
			$response->status = true;
			$response->uri = $_SERVER['HTTP_HOST'].$this->app['url_generator']->generate('document');
		}else{
			$response->status = false;
			$response->message = "An error has occurred, please try again later.";
		}
		return $response;
	}

	private function getListColumsTable($id)
	{
		$typeFile 			= $this->getList("SELECT id_Vertical, id_typeFile FROM dg_BlockContent WHERE id = {$id}");
		$vertical 			= $typeFile->content[0]['id_Vertical'];
		$file 				= $typeFile->content[0]['id_typeFile'];
		$this->verticalId 	= $vertical;

		switch ($vertical) {
			case '1':
			case '2':
			case '3':
			case '4':

				switch ($file) {
					case '1':
					case '2':
					case '3':
						$arrayInput = array("campaign","displayId","publisherId","md","domain");
					break;
					
					case '4':
						$arrayInput = array("campaign","publisherId","md");
					break;

					default:
						$arrayInput = array();
					break;
				}

			break;
			
			case '5':

				switch ($file) {
					default:
						$arrayInput = array("campaign","displayId","publisherId","md","domain");
					break;
				}

			break;

			default:
			break;
		}

		return $arrayInput;
	}

	public function getParamsDisplay($id,$parentId)
	{

		$arrayInput = $this->getListColumsTable($id);
		$idFile 	= $this->getList("SELECT id_typeFile FROM dg_BlockContent WHERE id = {$id}");
		$idFile 	= $idFile->content[0]['id_typeFile'];
		$params 	= '<form id="form-setting-display" class="row" >';

		for ($i=0; $i < COUNT($arrayInput); $i++) {

			switch ($arrayInput[$i]) {
				case 'domain':
					$typeValidation = 'class="string"';
				break;
				
				default:
					$typeValidation = 'class="numeric"';
				break;
			}

			$labelName = ( ($this->verticalId == 5) && ($arrayInput[$i]=="md") )? "vmProdId":$arrayInput[$i];

			$params.= '<div class="block-input large-6 columns" >
						<label>'.strtoupper($labelName).':
							<input id="'.$arrayInput[$i].'" type="text" placeholder="" name="'.$arrayInput[$i].'"  '.$typeValidation.' />
						</label>
					</div>';
		}

		$params.= '<a href="#" class="button radius large-6 large-offset-3 columns" onclick="$(this).documentGenerator(\'_createDocument\',{id:'.$id.',file:'.$idFile.'}); " >Save</a></form>';
		$subMneu = '<span onclick="$(this).documentGenerator(\'_verticalFile\',{id:'.$parentId.',parentId:'.$parentId.'}); " >Back</span>';

		return array("setting" => $this->contentHtml5( $params , "DOCUMENT", $subMneu ));
	}

	public function getDisplay($id,$parentId){

		$query = 'SELECT bc.id, tf.name
					FROM dg_BlockContent AS bc
					INNER JOIN dg_typeFile AS tf
					ON tf.id = bc.id_typeFile
					WHERE bc.id_Vertical = '.$id;
		$objectDisplay = $this->getList($query);
		
		$listDisplay = '<div class="row menu-document" >';
		foreach ($objectDisplay->content as $key => $display) {
			$listDisplay.='<div class="small-6 large-6 columns item-block" display="'.$display['id'].'" onclick="$(this).documentGenerator(\'_createDisplay\',{id:'.$display['id'].',parentId:'.$parentId.'}); " >
			<div class="image-display"><img src="'.$this->app['source'].'home/foundation-icons/svgs/fi-page-pdf.svg" ></div>
			<div class="title-display"><span>'.$display['name'].'</span></div>
			</div>';
		}
		$listDisplay.= '</div>';


		$subMneu = '<span onclick="$(this).documentGenerator(\'_viewCreateDocument\',{id:'.$parentId.'}); " >Back</span>';
		return array("listDisplay" => $this->contentHtml5( $listDisplay , "DISPLAY", $subMneu ) );

	}

	public function getCreateDocument($id=false)
	{
		$subMneu = '<span onclick="$(this).documentGenerator(\'_viewVerticalParent\'); " >Back</span>';
		$listVertical = $this->getList("SELECT DISTINCT(v.name ), v.id
										FROM dg_BlockContent AS bc
										INNER JOIN dg_Vertical AS v
										ON v.id = bc.id_Vertical
										WHERE bc.id_VerticalParent = {$id}");

		if(!$listVertical->status){
			$menu = '<div class="row menu-document">
			<div class="small-6 large-6 columns item-block">'.$listVertical->content.'</div>
			</div>';
		}else{
			$menu = '<div class="row menu-document">';
			foreach ($listVertical->content as $key => $verticalMenu) {
				$menu.='<div class="small-6 large-6 columns item-block" vertical="'.$verticalMenu['id'].'" onclick="$(this).documentGenerator(\'_verticalFile\',{id:'.$verticalMenu['id'].',parentId:'.$id.'}); " >
				<div class="image-vertical"><img src="'.$this->app['source'].'home/foundation-icons/svgs/fi-page-multiple.svg" /></div>
				<div class="title-vertical"><span>'.$verticalMenu['name'].'</span></div>

				</div>';
			}
			$menu.= '</div>';
	
		}

		

		

		return $this->contentHtml5( $menu , "SUB VERTICAL", $subMneu );
	}

	public function getVerticalParent()
	{
		$listVertical = $this->getList("SELECT DISTINCT(vp.id), vp.name 
										FROM dg_VerticalParent AS vp
										INNER JOIN dg_BlockContent AS bc
										ON vp.id = bc.id_VerticalParent");

		$menu = '<div class="row menu-document">';
		foreach ($listVertical->content as $key => $verticalMenu) {
			$menu.='<div class="small-4 large-4 columns item-block" vertical="'.$verticalMenu['id'].'" onclick="$(this).documentGenerator(\'_viewCreateDocument\',{id:'.$verticalMenu['id'].'}); " >
			<div class="image-vertical"><img src="'.$this->app['source'].'home/foundation-icons/svgs/fi-page-multiple.svg" /></div>
			<div class="title-vertical"><span>'.$verticalMenu['name'].'</span></div>

			</div>';
		}
		$menu.= '</div>';

		$subMneu = '<span onclick="$(this).documentGenerator(\'_getDocument\'); " >Back</span>';

		return $this->contentHtml5( $menu , "VERTICAL", $subMneu );
	}

	public function generateDocument($option=false,$id=false,$listDocument=false)
	{	

		if( (empty($listDocument)) && ($id=='false') ){
			$response->status 	= false;
			$response->message 	= "Select at least one document.";
			return $response;
		}

		$response = new stdClass();
		$listDocumentId = array();
		if(!$listDocument){
			$condition = "id = {$id}";	
		}else{
			foreach ($listDocument as $key => $listData) {
				$listDocumentId[]=$listData['value'];
			}
			$condition = implode(",", $listDocumentId);
			$condition = ' id IN ('.$condition.') ';

		}
		
		$getListContent = $this->getList("SELECT * FROM dg_NewDocument WHERE {$condition}");

		#_pre($getListContent);exit;

		#$getListContent = $this->getList("SELECT * FROM dg_NewDocument WHERE {$condition} ");


		if(!$getListContent->status){
			$response->status 	= false;
			$response->message 	= "All documents were already generated.";
			return $response;
		}
		

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
			$setting->PRODUCTID 			= $valueContent['md'];
			#$setting->ZIPCODE 				= $valueContent['zipcode'];
			$setting->DOMAIN 				= $valueContent['domain'];
			#$setting->CID 					= $valueContent['cid'];

			$settings 	= $this->addSettingToHtml($setting,$paramsContent->idBLockContent,$paramsContent->id);
			$date 		= date("Y-m-d H:i:s");
			
			$update 	= $this->update("UPDATE dg_NewDocument
									SET content = '".base64_encode( $this->app->escape($settings->content) )."',
									css = '".base64_encode( $this->app->escape($settings->css) )."',
									status = 1 WHERE id = ".$paramsContent->id." ", "dg_NewDocument");
		}

		$response->status 	= true;
		$response->message 	= "The document was generated successfully.";
		$response->content 	= $this->getDocument();

		return $response;
	}

	private function addSettingToHtml($settings,$idBlock,$idUpdate)
	{
		$response 			= new stdClass();		
		$getHtml 			= $this->getList("SELECT content, cssSourcePath FROM dg_BlockContent WHERE status = 1 AND id = {$idBlock}");
		#_pre($getHtml);exit;
		$response->content 	= base64_decode($getHtml->content[0]['content']);
		$path 				= $getHtml->content[0]['cssSourcePath'];
		$file 				= fopen ($path, "r");
		$response->css 		= fread($file, filesize($path));

		foreach ($settings as $key => $valueSettings) {
			$response->content = str_replace("{{{$key}}}", $valueSettings, $response->content);
		}

		return $response;
	}


	public function getDocument($queryString=null)
	{

		if($queryString == null ){
			$query = "SELECT * FROM dg_NewDocument GROUP BY id ORDER BY status, registerDate ASC";
		}else{
			$query = $queryString;
		}

		$getListContent = $this->getList($query);
		$document = array();
		
		$subMneu = '<span onclick="$(this).documentGenerator(\'_viewVerticalParent\'); " >NEW</span>';

		$html = '';
		$rowContent = '';

/*
<thead>
<tr>
<th ><center style="line-height: 40px;"><span>SELECT ALL</span> <input id="selecctall"  type="checkbox" ></center></th>
<th width="10%" id="id_BlockContent" class="colum-search">
<span id="t-v" class="show-tool-tip" >VERTICAL</span>
<input class="id_BlockContent input-search hidden-input-search" type="text" name="id_BlockContent" value="">
</th>
<th width="10%" id="id_typeFile" class="colum-search" >
<span id="t-d" class="show-tool-tip" >DISPLAY</span>
<input class="id_typeFile input-search hidden-input-search" type="text" name="id_typeFile" value="">
</th>
<th width="10%" id="campaign" class="colum-search" >
<span id="t-c" class="show-tool-tip" >CAMPAIGN</span>
<input class="campaign input-search hidden-input-search" type="text" name="campaign" value="">
</th>
<th width="10%" id="publisherId" class="colum-search" >
<span id="t-p" class="show-tool-tip" >PUBLISHERID</span>
<input class="publisherId input-search hidden-input-search" type="text" name="publisherId" value="">
</th>
<th width="10%" id="displayId" class="colum-search" >
<span id="t-di" class="show-tool-tip" >DISPLAYID</span>
<input class="displayId input-search hidden-input-search" type="text" name="displayId" value="">
</th>
<th class="tour-status" >STATUS</th>
<th class="tour-edit" >EDIT</th>
<th class="tour-download" >DOWNLOAD</th>
</tr>
</thead>
*/


			if( !$getListContent->status ){
				$html.= '<h5 style="text-align:center;">'.$getListContent->content.'</h5>';
			}else{
				foreach ($getListContent->content as $key => $listContent) {
					$references = $this->getReferencesDocument($listContent['id_BlockContent']);
					$document[$key]['id'] 			= $listContent['id'];
					#$document[$key]['blockVertical']= $references->blockVertical;
					$document[$key]['nameVertical'] = $references->nameVertical;
					$document[$key]['nameFile'] 	= $references->nameFile;
					$document[$key]['campaign'] 	= $listContent['campaign'];
					$document[$key]['publisherId'] 	= $listContent['publisherId'];
					$document[$key]['displayId'] 	= $listContent['displayId'];
					$document[$key]['status'] 		= $listContent['status'];
				}
					$html.= '<div class="nav-bar"><p id="generate-selected-document" class="" >Generate selected documents</p></div>
							<form id="filter-form" >
							<table id="table-document-list" style=" width:100%" align="center">

							<thead>
								<tr>
									<th style="width: 130px !important;" ><center style="line-height: 40px;"><span>SELECT ALL</span> <input id="selecctall"  type="checkbox" ></center></th>
									<th ><center style="line-height: 40px;"><span>VERTICAL</span></center></th>
									<th ><center style="line-height: 40px;"><span>DISPLAY</span></center></th>
									<th ><center style="line-height: 40px;"><span>CAMPAIGN</span></center></th>
									<th ><center style="line-height: 40px;"><span>PUBLISHERID</span></center></th>
									<th ><center style="line-height: 40px;"><span>DISPLAYID</span></center></th>
									<th class="tour-status" >STATUS</th>
									<th class="tour-edit" >EDIT</th>
									<th class="tour-download" >DOWNLOAD</th>
								</tr>
							</thead>
							
							<tfoot class="header-search">
								<tr>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tfoot>


							<tbody id="data-document">';

			
			foreach ($document as $key => $doc) {
				
				switch ($doc['status']) {
					case 0:
					case '0':
						$status = '<span data-tooltip data-options="hover_delay: 50;" class="has-tip" title="Click to generate document" ><img style="cursor:pointer" class="generate-doc" id-doc="'.$doc['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-clock.svg" ></span>';
						$download = '<img class="not-complete-doc" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-key.svg" >';
					break;
					
					default:
						$status = '<img src="'.$this->app['source'].'home/foundation-icons/svgs/fi-checkbox.svg" >';
						$download = '<a href="'.$this->app['url_generator']->generate('document', array("option" => "download", "id" => $doc['id'])) .'"><img class="download-doc" id-doc="'.$doc['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-download.svg" ></a>';
					break;
				}

				if($queryString == null ){
					$html.= '
							<tr id="item-'.$key.'" class="block-option" >
								<td><center><input class="document-item" type="checkbox" name="generate[]" value="'.$doc['id'].'"></center></td>
								<td><center>'.$doc['nameVertical'].'</center></td>
								<td><center>'.$doc['nameFile'].'</center></td>
								<td><center>'.$doc['campaign'].'</center></td>
								<td><center>'.$doc['publisherId'].'</center></td>
								<td><center>'.$doc['displayId'].'</center></td>
								<td><center>'.$status.'</center></td>
								<td><center><img class="update-doc" id-doc="'.$doc['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-widget.svg" ></center></td>
								<td><center>'.$download.'</center></td>
							</tr>';
				}else{
					$rowContent.= '
							<tr id="item-'.$key.'" class="block-option" >
								<td><center><input class="document-item" type="checkbox" name="generate[]" value="'.$doc['id'].'"></center></td>
								<td><center>'.$doc['nameVertical'].'</center></td>
								<td><center>'.$doc['nameFile'].'</center></td>
								<td><center>'.$doc['campaign'].'</center></td>
								<td><center>'.$doc['publisherId'].'</center></td>
								<td><center>'.$doc['displayId'].'</center></td>
								<td><center>'.$status.'</center></td>
								<td><center><img class="update-doc" id-doc="'.$doc['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-widget.svg" ></center></td>
								<td><center>'.$download.'</center></td>
							</tr>';
				}
			}
			$html.='</tbody>
						</table></form>';
		}
			$html.= $this->getPaginator();

		if($queryString != null ){
			return $rowContent;
		}

		return $this->contentHtml5( $html , "DOCUMENT", $subMneu );
	}

	public function downloadDocument($id)
	{
		$arrayResponse = array();
		$getHtml = $this->getList("SELECT content, css, id_BlockContent FROM dg_NewDocument WHERE status = 1 AND id = {$id}");

		foreach ($getHtml->content as $key => $htmlContent) {
			$references = $this->getReferencesDocument($htmlContent['id_BlockContent']);
			$nameDoc 	= strtoupper(str_replace(" ", "", trim($references->publisherId."_".$references->domain."_".$references->campaign."_".$references->nameFile."_".$references->nameVertical) ));
			$html = '<style type="text/css">';
			$html.= base64_decode($htmlContent['css']);
			$html.= '</style>';
			$html.= html_entity_decode(base64_decode($htmlContent['content']));
			$arrayResponse = array("html" => $html, "nameDoc" => $nameDoc );
		}

		return $arrayResponse;

	}

	public function downloadDocumentDebug($id)
	{
		return $this->prevBlockBlockcontent($id);
	}

	public function getReferencesDocument($id)
	{
		$response = new stdClass();
		$query = "SELECT nd.publisherId, nd.campaign, bv.name AS blockVertical,v.name AS nameVertical,tf.name AS nameFile, nd.domain

			FROM dg_BlockContent AS bc

			INNER JOIN dg_blockVertical AS bv
			ON bv.id = bc.id_blockVertical

			INNER JOIN dg_Vertical AS v
			ON v.id = bc.id_Vertical

			INNER JOIN dg_typeFile AS tf
			ON tf.id = bc.id_typeFile

			INNER JOIN dg_NewDocument AS nd
			ON nd.id_BlockContent = bc.id

			WHERE bc.id =".$id;
		
			$referencesName = $this->getList($query);

			

		foreach ($referencesName->content as $key => $references) {
			$response->blockVertical 	= $references['blockVertical'];
			$response->nameVertical 	= $references['nameVertical'];
			$response->nameFile 		= $references['nameFile'];
			$response->publisherId 		= $references['publisherId'];
			$response->campaign 		= $references['campaign'];
			$response->domain 		= $references['domain'];
		}
		return $response;

	}

	public function registerCreateNewdocument($data)
	{
		$params = array();

		foreach ($data['params'] as $key => $value) {
			$params[$value['name']] = $value['value'];
		}

		$setting 			= new stdClass();
		$setting->campaign 	= $params['campaign'];
		$setting->cid 		= $params['cid'];
		$setting->displayId = $params['displayId'];
		$setting->publisherId 	= $params['publisherId'];
		$setting->md 		= $params['md'];
		$date 				= date("Y-m-d H:i:s");
		$typeVertical 		= ($params['typeVertical'] == 0)? "Vertical":"MultiVertical";
		$nameVertical 		= $this->getList("SELECT name FROM dg_Vertical WHERE id = ".$params['nameVertical']);
		$nameVertical 		= $nameVertical->content[0]['name'];
		$nameDisplay 		= $this->getList("SELECT name FROM dg_DisplayType WHERE id = ".$params['nameDisplay']);
		$nameDisplay 		= $nameDisplay->content[0]['name'];
		$nameCampaign 		= $params['campaign'];
		$nameDocument 		= $typeVertical.$nameVertical.$nameDisplay.$nameCampaign.md5($date);


		$query = "INSERT INTO dg_NewDocument (name,registerDate,id_DisplayType) ";
		$query.= "values (
			'".$this->app->escape($nameDocument)."',
			'".$this->app->escape($date)."',
			".$this->app->escape($params['nameDisplay'])."
			)";
		$response = $this->insert($query,"dg_NewDocument");
	
		#$header = $this->getList("SELECT content, cssSourcePath FROM dg_BlockContent WHERE id_DisplayType = {$params['nameDisplay']} AND id_BlockContentType = 1 ");
		#$footer = $this->getList("SELECT content, cssSourcePath FROM dg_BlockContent WHERE id_DisplayType = {$params['nameDisplay']} AND id_BlockContentType = 2 ");
		$content = $this->getList("SELECT content, cssSourcePath FROM dg_BlockContent WHERE id_DisplayType = {$params['nameDisplay']} AND id_BlockContentType = 3 ");

		#$this->registerDetailNewDocument($response->id,1,$header->content[0]['cssSourcePath'],base64_encode($header->content[0]['content']) );
		#$this->registerDetailNewDocument($response->id,2,$footer->content[0]['cssSourcePath'],base64_encode($footer->content[0]['content']) );
		$this->registerDetailNewDocument($response->id,3,$content->content[0]['cssSourcePath'],$this->settingParamsTemplate(base64_decode($content->content[0]['content']),$setting) );

		return $response->id;
		
		#return $this->createHtmlContent($header->content[0],$content->content[0],$footer->content[0]);
	}

	private function settingParamsTemplate($content,$setting)
	{
		/*
		{{DISPLAYID}}
		{{PUBLISHERID}}
		{{CAMPAIGN}}
		{{MD}}
		{{CID}}
		*/
		$response = str_replace("{{DISPLAYID}}", $setting->displayId, $content);
		$response = str_replace("{{PUBLISHERID}}", $setting->publisherId, $response);
		$response = str_replace("{{CAMPAIGN}}", $setting->campaign, $response);
		$response = str_replace("{{MD}}", $setting->md, $response);
		$response = str_replace("{{CID}}", $setting->cid, $response);
		return base64_encode($response);
	}

	private function registerDetailNewDocument($idNewDocument,$idBlockType,$cssFile,$content)
	{

		$css 		= fread( (fopen ($cssFile, "r")), filesize($cssFile));
		$date  = date("Y-m-d H:i:s");
		$query = "INSERT INTO dg_NewDocumentDetail (id_NewDocument,id_BlockContent,css,content,registerDate) ";
		$query.= "values (
			'".$this->app->escape($idNewDocument)."',
			'".$this->app->escape($idBlockType)."',
			'".base64_encode($this->app->escape($css))."',
			'".$this->app->escape($content)."',
			'".$this->app->escape($date)."'
			)";

		$response = $this->insert($query,"dg_NewDocument");
	}

	public function getCreateNewdocument($id)
	{
		$response = array();
		$list = $this->getList("SELECT id_BlockContent, css, content FROM dg_NewDocumentDetail WHERE id_NewDocument = {$id} ");

		foreach ($list->content as $key => $value) {

			
			#foreach ($valueData as $key => $value) {			
				switch ($value['id_BlockContent']) {
					case '1':
						$response['header']['css'] = $value['css'];
						$response['header']['content'] = $value['content'];
					break;
					
					case '2':
						$response['footer']['css'] = $value['css'];
						$response['footer']['content'] = $value['content'];
					break;
					
					case '3':
						$response['content']['css'] = $value['css'];
						$response['content']['content'] = $value['content'];
					break;
					
					default:
					break;
				}
			#}
		}

		#_pre($list);exit;

		$html = '<!DOCTYPE html>';
		$html.= '<html>';
		$html.= '<head>';
		#$html.= '<link rel="stylesheet" href="/documentatio_generator/web/templates/home/css/foundation.css" />';
		$html.= '<style type="text/css">';
		#$html.= base64_decode($response['header']['css']);
		#$html.= base64_decode($response['footer']['css']);
		$html.= base64_decode($response['content']['css']);
		$html.= '</style>';
		$html.= '</head>';
		$html.= '<body>';
		#$html.= base64_decode(html_entity_decode($response['header']['content']) );
		$html.= base64_decode(html_entity_decode($response['content']['content']) );
		#$html.= base64_decode(html_entity_decode($response['footer']['content']) );
		$html.= '</body>';
		$html.= '</html>';

		return $html;
	}


	public function createHtmlContent($header,$content,$footer)
	{

		$headerContent 	= $header['content'];
		$headerCss 		= fread( (fopen ($header['cssSourcePath'], "r")), filesize($header['cssSourcePath']));

		$contentContent = $content['content'];
		$contentCss 		= fread( (fopen ($content['cssSourcePath'], "r")), filesize($content['cssSourcePath']));

		$footerContent 	= $footer['content'];
		$footerCss 		= fread( (fopen ($footer['cssSourcePath'], "r")), filesize($footer['cssSourcePath']));

		$html = '<!DOCTYPE html>';
		$html.= '<html>';
		$html.= '<head>';
		$html.= '<style type="text/css">';
		$html.= $headerCss;
		$html.= $contentCss;
		$html.= $footerCss;
		$html.= '</style>';
		$html.= '</head>';
		$html.= '<body>';
		$html.= html_entity_decode($headerContent);
		$html.= html_entity_decode($contentContent);
		$html.= html_entity_decode($footerContent);
		$html.= '</body>';
		$html.= '</html>';

		return $html;

	}


	public function displayGetNewdocument($params)
	{
		$data = explode(",", $params);
		$multiVertical 	= $data[0];
		$typeVertical 	= $data[1];
		$response = new stdClass();
		
		$list = $this->getList("SELECT * FROM dg_DisplayType WHERE id_Vertical = {$typeVertical} AND multiVertical = {$multiVertical}");
		
		$htmlDislayType = '<option value="">Selecciona una opcion</option>';
		foreach ($list->content as $key => $display) {
			$htmlDislayType.= '<option value="'.$display['id'].'">'.$display['name'].'</option>';
		}
		if($list->status){
			$response->response = $htmlDislayType;
		}else{
			$response->response = '<option value="">No hay documentos asociados</option>';
		}

		$response->response = '<span>Display Name:</span>
								<select  id="name-display"  name="nameDisplay">
								'.$response->response.'
								</select>';
		return $response;
	}

	public function blockcontentGetNewdocument($params)
	{
		$data 		= explode(",", $params);
		$idDisplay 	= $data[2];
		$query 		= "SELECT * FROM dg_SettingContent WHERE id_DisplayType = {$idDisplay} LIMIT 1";
		$list 		= $this->getList($query);
		$setting 	= $this->createStructureContent( $list->content[0]['comment'] );

		return array("table" => $setting );
	}

	public function createStructureContent($content)
	{
		$params 		= json_decode($content);
		$inputParams 	= '<table>';
		#$inputParams.= '<td><input name="" value=""></td>';
		foreach ($params as $key => $value) {
			$inputParams.= '<tr>';
			$inputParams.= '<td><label>'.$key.'</label></td>';
			$inputParams.= '<td><input name="'.$key.'" value="'.$value.'"></td>';
			$inputParams.= '</tr>';
		}
		$inputParams.= '</table>';
		return $inputParams;
	}

	private function readFile($id){

		$path = $this->getList("SELECT cssSourcePath FROM dg_BlockContent WHERE id = ".$id);
		$path = $path->content[0]['cssSourcePath'];
		$file = fopen ($path, "r");
		$cotent = fread($file, filesize($path));
		return $cotent;
	}

	public function viewCreateNewdocument($params=false)
	{

		if(!$params){
			$response = '<ul class="pricing-table">
					<li class="title">New Document</li>
					<li><div class="space-bar"></div></li>
					<li  class="dinamic-content"  >
						<form id="new-document" >
							<div class="row">
								
								<div class="large-12 columns">
									<span>Vertical Type:</span>
									<select id="type-vertical" name="typeVertical">
									'.$this->getVerticalType().'
									</select>
								</div>

								<div class="large-12 columns">
									<span>Vertical Name:</span>
									<select  id="name-vertical"  name="nameVertical">
									'.$this->getVertical().'
									</select>
								</div>

								<div id="block-name-display" class="large-12 columns">
								</div>

								<div id="block-setting" class="large-12 columns">
								</div>

							</div>
						</form>
					</li>
					<li class="cta-button"><a id="save-new-document" class="button" href="#">Save</a></li>
					</ul>';
		}

		return $response;
	}

	private function getVerticalType()
	{
		$htmlSelect = '<option value="0">Vertical</option>';
		$htmlSelect.= '<option value="1">Multi Vertical</option>';
		return $htmlSelect;
	}
	private function getVertical()
	{
		$list = $this->getList("SELECT * FROM dg_Vertical WHERE status = 1");
		$htmlVertical = "";
		$htmlVertical.= '<option value="">Selecciona una opcion</option>';
		foreach ($list->content as $key => $vertical) {
			$htmlVertical.= '<option value="'.$vertical['id'].'">'.$vertical['name'].'</option>';
		}

		return $htmlVertical;
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

		$html = '<style type="text/css">';
		$html.= $cotentStyle;
		$html.= '</style>';
		$html.= html_entity_decode( base64_decode($list->content[0]['content']) );
		#_pre($html);exit;
		return array("htmlContent" => $html );

	}

	public function viewReadNewdocument($params=false)
	{

		$query = "SELECT bc.id, dt.name AS nameDisplay, bct.name AS nameBlockContentType, bc.content, bc.cssSourcePath, bc.registerDate, bc.lastChange
					FROM dg_BlockContent AS bc
					INNER JOIN dg_DisplayType AS dt
					ON dt.id = bc.id_DisplayType
					INNER JOIN  dg_BlockContentType AS bct
					ON bct.id = bc.id_BlockContentType
					WHERE bc.status = 1";

		$list = $this->getList($query);

		#_pre($list);exit;

		$html = '<ul class="pricing-table ">
				<li class="title">List Display</li>
				<li><div class="space-bar"></div></li>
				<li><div class="new-blockcontent btn-new-item" onclick="newProducto()">NEW</div></li>';

		if( !$list->status ){
			$html.= '<li class="dinamic-content" ><div class="space-bar"><h5 style="text-align:center;">'.$list->content.'</h5></div></li>';
		}else{
				$html.= '<li class="dinamic-content" ><center><table style=" width:80%">
						<thead>
							<tr>
								<th width="20%">Display</th>
								<th width="20%">Block Name</th>
								<th width="20%">Register Date</th>
								<th width="20%">Last Change</th>
								<th width="20%" colspan="3">Option</th>
							</tr>
						</thead>
						<tbody>';


			foreach ($list->content as $key => $value) {
				$html.= '
						<tr class="block-option">
							<td>'.$value['nameDisplay'].'</td>
							<td>'.$value['nameBlockContentType'].'</td>
							<td>'.$value['registerDate'].'</td>
							<td>'.$value['lastChange'].'</td>
							<td><img class="remove-blockcontent" id-blockcontent="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-trash.svg" ></td>
							<td><img class="update-blockcontent" id-blockcontent="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-pencil.svg" ></td>
							<td><img class="prev-blockcontent" id-blockcontent="'.$value['id'].'" src="'.$this->app['source'].'home/foundation-icons/svgs/fi-photo.svg" ></td>
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

	public function viewUpdateUpdateBlockcontent($params)
	{

		$list = $this->getList("SELECT * FROM {$this->tabBlockContent} WHERE id = {$params}");
		$response = new stdClass();
		foreach ($list as $key => $value) {
			foreach ($value as $keyBC => $valueBC) {
				$response->id = $valueBC['id'];
				$response->idDisplayType = $valueBC['id_DisplayType'];
				$response->idBlockContentType = $valueBC['id_BlockContentType'];
				$response->content = $valueBC['content'];
				$response->status = $valueBC['status'];
			}
		}
		return array( "content" => $this->viewCreateBlockcontent($response) );
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
			$response->content 	= (!$response->status)? "Not found search results.":$list;
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
			$response->id 		= $id_tab;
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
