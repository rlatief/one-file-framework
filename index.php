<?php




$ast = new ast;

$ast->inDevelopment( true );

$ast->conf['sitename']        = 'Site Name';
$ast->conf['version']         = '0.0.1';
$ast->conf['domain']          = 'dev.randomlog.org';
$ast->conf['sitefolder']      = '/folder';
$ast->conf['defaultmodule']   = 'home';
$ast->conf['time']            = time();

$ast->conf['db->prefix']      = '';
$ast->conf['db->hostname']    = '';
$ast->conf['db->username']    = '';
$ast->conf['db->password']    = '';
$ast->conf['db->name']        = '';

$ast->conf['site']            = '//' . $ast->conf['domain'] . $ast->conf['sitefolder'];
$ast->conf['path']            = dirname( __FILE__ );
$ast->conf['cache']           = $ast->conf['site'] . '/cache';
$ast->conf['cachepath']       = $ast->conf['path'] . '/cache';

$ast->httpsRedirect( true );
$ast->filestructureCheck( true );

$ast->load();




class ast
{




   public $pagetitle = '';




   public function __construct()
   {

   }




   public function filestructureCheck( $docheck = true )
   {
      if ( $docheck )
      {

         $folders = array( 'cache', 'common', 'modules', 'sections', 'modules/403', 'modules/404' );

         foreach( $folders as $folder )
         {
            if ( !is_dir( $this->conf['path'] . '/' . $folder ) ) mkdir( $this->conf['path'] . '/' . $folder, 0755 );
         }
         // TODO: CHECK FOLDER PERMISSION
         if ( !file_exists( $this->conf['path'] . '/.htaccess' ) )
         {
            $content = 'RewriteEngine On' . "\n\n" . 'RewriteCond %{REQUEST_FILENAME} !-d' . "\n" . 'RewriteCond %{REQUEST_FILENAME} !-f' . "\n" . 'RewriteRule ^(.+)$ index.php [QSA,L]' . "\n\n" . '<Files ".err">' . "\n" . 'Order Allow,Deny' . "\n" . 'Deny from all' . "\n" . '</Files>';
            $fh = fopen( $this->conf['path'] . '/.htaccess', 'w' );
            fwrite( $fh, $content );
            fclose( $fh );
         }
         // TODO: CHMOD THESE FILES
         if ( !file_exists( $this->conf['path'] . '/sections/genheader.php' ) )
         {
            $content = '<!DOCTYPE html>' . "\n" . '<html lang="en" data-version="0.0.1">' . "\n" . '<head>' . "\n" . '<meta charset="utf-8">' . "\n" . '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n" . '<meta name="theme-color" content="#000000">' . "\n" . '<title><?php echo $this->pageTitle(); ?></title>' . "\n" . '<link href="<?php echo $this->conf[\'site\'] . \'/common/sitewide.css?v=\' . $this->conf[\'version\']; ?>" rel="stylesheet">' . "\n" . '<?php $this->loadCSS(); ?>' . "\n" . '<script src="<?php echo $this->conf[\'site\'] . \'/common/sitewide.js?v=\' . $this->conf[\'version\']; ?>"></script>' . "\n" . '<?php $this->loadJS(); ?>' . "\n" . '</head>' . "\n" . '<body>' . "\n\n\n\n\n";
            $fh = fopen( $this->conf['path'] . '/sections/genheader.php', 'w' );
            fwrite( $fh, $content );
            fclose( $fh );
         }

         if ( !file_exists( $this->conf['path'] . '/sections/genfooter.php' ) )
         {
            $content = "\n\n\n\n" . '<footer></footer>' . "\n\n\n\n\n" . '</body>' . "\n" . '</html>';
            $fh = fopen( $this->conf['path'] . '/sections/genfooter.php', 'w' );
            fwrite( $fh, $content );
            fclose( $fh );
         }

      }
   }




   private function modulepath()
   {
      return $this->conf['path'] . '/modules/' . $this->urlchunks[0];
   }




   private function moduleurl()
   {
      return $this->conf['site'] . '/modules/' . $this->urlchunks[0];
   }




   private function loadJS() // TODO: Load from other modules
   {
      if ( isset( $this->headerJS ) ) echo '<script>' . $this->headerJS . '</script>' . "\n";
      $path = $this->conf['path'] . '/modules/' . $this->urlchunks[0] . '/functions.js';
      if ( file_exists( $path ) )
      {
         $hash = md5( $path . filemtime( $path ) );
         if ( !file_exists( $this->conf['cachepath'] . '/' . $hash . '.js' ) )
         {
         $fh = fopen( $this->conf['cachepath'] . '/' . $hash . '.js', 'w' );
         fwrite( $fh, file_get_contents( $path ) );
         fclose( $fh );
         }
         echo '<script src="' . $this->conf['cache'] . '/' . $hash . '.js"></script>' . "\n";
      }
   }




   private function loadCSS() // TODO: Load from other modules
   {
      if ( isset( $this->headerCSS ) ) echo '<style>' . $this->headerCSS . '</style>' . "\n";
      $path = $this->conf['path'] . '/modules/' . $this->urlchunks[0] . '/style.css';
      if ( file_exists( $path ) )
      {
         $hash = md5( $path . filemtime( $path ) );
         if ( !file_exists( $this->conf['cachepath'] . '/' . $hash . '.css' ) )
         {
            $fh = fopen( $this->conf['cachepath'] . '/' . $hash . '.css', 'w' );
            fwrite( $fh, file_get_contents( $path ) );
            fclose( $fh );
         }
         echo '<link rel="stylesheet" href="' . $this->conf['cache'] . '/' . $hash . '.css' . '">' . "\n";
      }
   }




   private function pageTitle()
   {
      $pagetitle = ( $this->pagetitle )? $this->pagetitle: ucfirst( $this->urlchunks[0] );
      return $pagetitle . ' - ' . $this->conf['sitename'];
   }




   private function loadSection( $section, $attrs = array() )
   {
      foreach( $attrs as $attr => $val ) $$attr = $val;
      if ( !file_exists( $this->conf['path'] . '/sections/' . $section . '.php' ) ) trigger_error( 'Unknown section requested', E_USER_ERROR );
      require $this->conf['path'] . '/sections/' . $section . '.php';
   }




   private function loadModule( $module )
   {
      if ( file_exists( $this->conf['path'] . '/modules/' . $module . '/header.php' ) || file_exists( $this->conf['path'] . '/modules/' . $module . '/output.php' ) )
      {
         if ( file_exists( $this->conf['path'] . '/modules/' . $module . '/header.php' ) ) require( $this->conf['path'] . '/modules/' . $module . '/header.php' );
         if ( !isset( $this->maxurlchunks ) ) $this->maxurlchunks = 1;
         if ( count( $this->urlchunks ) > $this->maxurlchunks ) trigger_error( 'Chunks exceeded: ' . $_SERVER['REQUEST_URI'], E_USER_ERROR );
         if ( file_exists( $this->conf['path'] . '/modules/' . $module . '/output.php' ) ) require( $this->conf['path'] . '/modules/' . $module . '/output.php' );
      }
      else
      {
         if ( !file_exists( $this->conf['path'] . '/modules/404/output.php' ) )
         {
            trigger_error( 'No 404 module: ' . $_SERVER['REQUEST_URI'], E_USER_ERROR );
         }
         else
         {
            $this->loadModule( '404' );
            trigger_error( '404: ' . $_SERVER['REQUEST_URI'], E_USER_ERROR );
         }
      }
   }




   public function load()
   {

      $request = parse_url( str_replace( $this->conf['sitefolder'], '', $_SERVER['REQUEST_URI'] ) );
      $this->urlchunks = explode( '/', substr( $request['path'], 1 ) );
      foreach( $this->urlchunks as $k => $v ) { if ( $v == '' ) unset( $this->urlchunks[$k] ); }
      $this->urlchunks = array_values( $this->urlchunks );
      if ( empty( $this->urlchunks[0] ) ) $this->urlchunks[0] = $this->conf['defaultmodule'];

      $this->loadModule( $this->urlchunks[0] );

   }




   public function httpsRedirect( $httpsredirect = true )
   {
      if( $httpsredirect && @$_SERVER['HTTPS'] != 'on' )
      {
         header( 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
         exit();
      }
   }




   public function inDevelopment( $indevelopment = false )
   {

      if ( $indevelopment )
      {
         error_reporting( E_ALL );
         ini_set( 'display_errors', true );
         ini_set( 'log_errors', true );
         ini_set( 'error_log', '.err' );

      }
      else
      {
         error_reporting( 0 );
         ini_set( 'display_errors', false );
         ini_set( 'log_errors', true );
         ini_set( 'error_log', '.err' );
      }

   }




}




//~ echo '<pre>' . print_r( $ast, true ) . '</pre>';



