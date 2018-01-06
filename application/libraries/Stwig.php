<?php if ( !defined( 'BASEPATH' ) ) { exit( 'No direct script access allowed' ); }

class Stwig {

	private $_stwig;

	function __construct() {
		require_once ( APPPATH . 'libraries/Twig/Autoloader.php' );
		Twig_Autoloader::register();
		$loader_from_string = new Twig_Loader_String();
        $this->_stwig = new Twig_Environment( $loader_from_string );
	}

	function render( $template, $data = array() ) {
        return $this->_stwig->render( $template, $data );
	}

}