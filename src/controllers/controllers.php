<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->match('/', function () use ( $app) {
	$template 	= 'home.twig';
	$array 		= array( );
	return new Response(
		$app['twig']->render( $template, $array )
	);

})->method('GET|POST')->bind('home');


$app->match('/masive', function ($id) use ( $app ,$master ) {

	$getHtml = $master->getMasiveDocument();

	$html = html_entity_decode($getHtml[0]['html']);
	require_once PATH_SRC.DS.'PDF'.DS.'html2pdf.class.php';
	
	#_pre($html);exit;

	try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($html);
        $html2pdf->Output($getHtml[0]['nameDoc'].'.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
exit;

})->method('GET|POST')->value("id","");


$app->match('/debug', function () use ( $app ,$master ) {

	$getHtml = $master->downloadDocumentDebug(35);
	require_once PATH_SRC.DS.'PDF'.DS.'html2pdf.class.php';
	try
	{
		$html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 0);
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->writeHTML($getHtml['htmlContent']);
		$pdfName = "debug".'.pdf';
		$html2pdf->Output($pdfName, 'I');
		exit;
	}
	catch(HTML2PDF_exception $e) {
		echo $e;
		exit;
	}
	exit;
})->method('GET|POST');



$app->match('/document/{option}/{id}', function ($option,$id) use ( $app ,$master ) {

	$array = array();
	$template ="";

	switch ($option) {
		case "read":
		default:
			$template 	= 'newdocument.twig';
			$array 		= array( "table" => $master->getDocument() );
		break;

		case 'generate':
			return $app->json( $master->generateDocument($option,$id,$_POST['listDocument']) );
		break;

		case 'download':
			$getHtml = $master->downloadDocument($id);
			$html = html_entity_decode($getHtml['html']);
			require_once PATH_SRC.DS.'PDF'.DS.'html2pdf.class.php';
			try
			{
				$html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 0);
				$html2pdf->pdf->SetDisplayMode('fullpage');
				$html2pdf->writeHTML($html);
				$html2pdf->Output($getHtml['nameDoc'].'.pdf');
			}
			catch(HTML2PDF_exception $e) {
				echo $e;
			}
			exit;
			return $app->redirect($app['url_generator']->generate('document') );
		break;

		case "create":
			#$template 	= 'newdocument.twig';
			#$array 		= array( "table" => $master->getDocument($option) );
			return $app->json( array( "content" => $master->getDocument($option,$id) ) );
		break;

		case 'display':
			return $app->json( $master->getDisplay($id,$_POST['parent']) );
		break;

		case 'paramsdisplay':
			return $app->json( $master->getParamsDisplay($id,$_POST['parent']) );
		break;

		case 'createdocument':
			#_pre($_POST);exit;
			return $app->json( $master->createDocument($id,$_POST['params'],$_POST['file']) );
		break;

		case 'editdocument':
			return $app->json( $master->editDocument($id) );
		break;
		
		case 'updatedocument':
			return $app->json( $master->updateDocument($_POST['params']) );
		break;

		case 'getdocument':
			return $app->json( array( "content" => $master->getDocument() )  );
		break;

		case 'verticalparent':
			return $app->json( array( "content" => $master->getVerticalParent() )  );
		break;

		case 'search':
			return $app->json( array( "content" => $master->getSearch($_POST['data']) ) );
		break;

	}

	return new Response(
		$app['twig']->render( $template, $array )
	);

})->method('GET|POST')->value("id",false)->value("option","read")->bind('document');


$app->match('/preview', function () use ( $app ,$master ) {

	$htmlReturn = '<style>'.$_POST['html']['css'].'</style>';


	$contentHtml = str_replace('src="', 'src="../', $_POST['html']['html']);
	$contentHtml = str_replace("src='", "src='../", $contentHtml);

	$htmlReturn.= html_entity_decode( $contentHtml );
	$response = $htmlReturn;
	return $app->json( array("htmlPreview" => $response) );

})->method('GET|POST');

$app->match('/error', function () use ( $app) {
	$template 	= 'error.twig';
	$array 		= array( );
	return new Response(
		$app['twig']->render( $template, $array )
	);

})->method('GET|POST')->bind('error');

$app->match('/login', function () use ( $app, $master ) {

	$isActive = $master->validateSessionActive();
	if( !$isActive->redirect ){
		$template 	= 'login.twig';
		$array 		= array( );
		return new Response(
			$app['twig']->render( $template, $array )
		);
	}

	return $app->redirect('./sale' );
	
})->method('GET|POST');

$app->match('/{entity}/{type}/{crud}/{id}', function ($entity,$type,$crud,$id) use ( $app,$master) {

	$array = array();
	$template ="";
	switch ($entity) {
		/**
		VERTICAL
		*/
		case 'vertical':
			$template 	= 'vertical'.ucfirst($type).ucfirst($crud).'.twig';
			$array 		= array( "table" => $master->crud( $entity, $type, $crud, $id ));
		break;

		/**
		DISPLAY TYPE
		*/
		case 'display':
			$template 	= 'display'.ucfirst($type).ucfirst($crud).'.twig';
			#_pre($template);exit();
			$array 		= array( "table" => $master->crud( $entity, $type, $crud, $id ));
		break;

		/**
		BLOCK CONTENT
		*/
		case 'blockcontent':
			$template 	= 'blockcontent'.ucfirst($type).ucfirst($crud).'.twig';
			#_pre($template);exit();
			$array 		= array( "table" => $master->crud( $entity, $type, $crud, $id ));
		break;

		/**
		SETTING CONTENT
		*/
		case 'settingcontent':
			$template 	= 'settingcontent'.ucfirst($type).ucfirst($crud).'.twig';
			#_pre($template);exit();
			$array 		= array( "table" => $master->crud( $entity, $type, $crud, $id ));
		break;

		/**
		NEW DOCUMENT
		*/
		case 'newdocument':
			$template 	= 'newdocument'.ucfirst($type).ucfirst($crud).'.twig';
			#_pre($template);exit();
			$array 		= array( "table" => $master->crud( $entity, $type, $crud, $id ));
		break;

	}
	return new Response(
		$app['twig']->render( $template, $array )
	);
})->method('GET|POST')->value("entity","newDocument")->value("type","view")->value("crud","read")->value("id",false)->bind('view');


$app->match('/crud/{entity}/{type}/{crud}/{id}', function ($entity,$type,$crud,$id) use ( $app ,$master ) {

	#var_dump($entity, $type, $crud, $id);
	#exit;

	switch ($entity) {
		
		case 'vertical':
			return $app->json( $master->crud( $entity, $type, $crud, $id ) );
		break;
		
		case 'display':
			return $app->json( $master->crud( $entity, $type, $crud, $id ) );
		break;

		case 'blockcontent':

			if( !empty($_POST['info']) ){
				$id = array();
				parse_str($_POST['info'], $id);
				$type = "updateBlock";
			}
			return $app->json( $master->crud( $entity, $type, $crud, $id ) );
		break;
		
		case 'settingcontent':
			return $app->json( $master->crud( $entity, $type, $crud, $id ) );
		break;

		case 'newdocument':
			return $app->json( $master->crud( $entity, $type, $crud, $id ) );
		break;
	}

})->method('GET|POST')->value("action",false)->value("model",false);