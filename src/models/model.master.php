<?php 
/**
 * Class master .
 *
 * @author Jcbarreno <jcbarreno.b@gmail.com>
 * @version 1.0
 * @package 
 */
class ModelMaster {

	protected $prefix;
	protected $app;

	public function  __construct($app, $prefix) {
		$this->prefix = $prefix;
		$this->app 	= $app;
	}



	protected function contentHtml5($content="",$title="",$subMenu="")
	{
		$html5 ='<header>
					<div class="page-width">
						<a href="#" rel="home" class="top-logo" title="Katch" role="banner">
							<img src="'.$this->app['source'].'home/images/white-katch-logo.svg" alt="Katch">
						</a>
						<nav class="menu">
							<ul class="top-menu">
								<li class="menu-item">
									<a href="#">'.$subMenu.'</a>
								</li>
							</ul>
						</nav>
					</div>
				</header>';
		$html5.='<div class="document medium-1">
					
						<section class="main-content">
							<div class="header-title">
							<h1>'.$title.'</h1>
							</div>
							'.$content.'
						</section>
				</div>';
		$html5.='<footer>
						<p class="copyright">
							<a href="http://katch.com/">Katch</a> © '.date("Y").'
						</p>
		</footer>';
		return $html5;
	}

	private function instaceClass($class,$file)
	{
		$nameClass 		= $class;
		require_once PATH_SRC.DS.'models'.DS.'model.'.$file.'.php';
		return new $nameClass($this->app,$this->prefix);
	}

	public function debug()
	{
		$instance 		= $this->instaceClass("ModelBlockcontent","blockcontent");
		return $instance->prevBlockBlockcontent(4);

	}
	
	public function getSearch($data)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->getSearch($data);
	}

	public function updateDocument($params)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->updateDocument($params);
	}

	public function getVerticalParent()
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->getVerticalParent();
	}

	public function generateDocument($option=false,$id=false,$listDocument=false)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->generateDocument($option,$id,$listDocument);		
	}

	public function createDocument($id,$params,$fileId)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->createDocument($id,$params,$fileId);		
	}

	public function editDocument($id=false)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->editDocument($id);		
	}

	public function downloadDocument($id=false)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->downloadDocument($id);		
	}

	public function downloadDocumentDebug($id)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->downloadDocumentDebug($id);
	}

	public function getParamsDisplay($id,$parentId)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->getParamsDisplay($id,$parentId);
	}

	public function getDocument($option="read",$id=false)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		switch ($option) {
			case 'create':
				return $instance->getCreateDocument($id);
			break;
			
			case 'read':
			default:
				return $instance->getDocument();
			break;
		}
		
	}

	public function getDisplay($id,$parentId){
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->getDisplay($id,$parentId);
	}

	public function newdocument($data)
	{
		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->registerCreateNewdocument($data);
	}

	public function getNewdocument($id){

		$instance 		= $this->instaceClass("ModelNewDocument","newdocument");
		return $instance->getCreateNewdocument($id);
	}

	public function crud($entity=false, $type=false, $crud=false, $params=false)
	{
		
		$response		= "";
		$nameClass 		= "Model".ucfirst($entity);
		require_once PATH_SRC.DS.'models'.DS.'model.'.$entity.'.php';
		
		$instance 		= new $nameClass($this->app,$this->prefix);
		$nameFunction 	= $type.ucfirst($crud).ucfirst($entity);#view,Register
		
		#_pre($nameFunction);exit;
		if( $type == "create" || $type == "update" || $type == "register" ){
			$arrayParams = array();
			parse_str($params, $arrayParams);
		}else{
			$arrayParams = $params;
		}

		if( !(method_exists($nameClass,$nameFunction))  )
		{
			return "No existe el metodo.";
		}

		switch ($entity) {
			case 'vertical':
				return $instance->$nameFunction($arrayParams);
			break;
			
			case 'display':
				return $instance->$nameFunction($arrayParams);
			break;

			case 'blockcontent':
				return $instance->$nameFunction($arrayParams);
			break;

			case 'settingcontent':
				return $instance->$nameFunction($arrayParams);
			break;

			case 'newdocument':
				return $instance->$nameFunction($arrayParams);
			break;

			default:
				return "Metodo no encontrado";
			break;
		}
	}

	public function validateSession($user,$password)
	{
		$response 	= new stdClass();
		#$password 	= md5($password);	
		$table 		= "{$this->prefix}acl_user";
		try{
			$query 		= 'SELECT id FROM '.$table.' WHERE userName = "'.$user.'" AND password = "'.$password.'" ';
			$user  		= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
	
			if( !empty($user['id']) ):
				#inicia sesión
				@session_name("login_usuario");
				@session_start();

				#registrar inicio de sesion
				$_SESSION["authenticated_user"]	= true; #asignar que el usuario se autentico
				$_SESSION["lastaccess_user"]	= date("Y-n-j H:i:s"); #definir la fecha y hora de inicio de sesión en formato aaaa-mm-dd hh:mm:ss

				$response->status = true;
				$response->message 	= "Ok";
				return $response;

			endif;

			$response->status = false;
			$response->message 	= "El usuario o contraseña no son validos.";
			return $response;

		}catch(Exception $e){
			$response->status = false;
			$response->message 	= "Ocurrio un error";
			return $response;
		}
	}

public function validateSessionActive()
	{


		#inicia sesión
		@session_name("login_usuario");
		@session_start();
		$response 			= new stdClass();
		$response->redirect = FALSE;

		#validar que el usuario esta logueado
		if ( !(@$_SESSION["authenticated_user"]) ) {

			#el usuario NO inicio sesion
			$response->redirect = FALSE;

		} else {
			#el usuario inicio sesion
			$fechaGuardada 			= $_SESSION["lastaccess_user"];
			$ahora 					= date("Y-n-j H:i:s");
			$tiempo_transcurrido 	= (strtotime($ahora)-strtotime($fechaGuardada));

			#comparar el tiempo transcurrido 
			if($tiempo_transcurrido >= 600) {

				#si el tiempo es mayo del indicado como tiempo de vida de la session
				session_destroy(); #destruir la sesión y se redirecciona a lagin
				$response->redirect = FALSE;
				#sino, se actualiza la fecha de la session

			}else {

				#actualizar tiempo de session
				$_SESSION["lastaccess_user"] = $ahora;
				$response->redirect 	= TRUE;
			}
		}
		return $response;
	}





}
?>