<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<title>ゴーストアップローダ（仮）</title>
</head>
<body>
<p>
<?php

//設定です
$id_pass_limit = 20;
$admin_pass = 'kanadesan';
$pass_dir = "pass/";
$data_dir = "data/";
$nar_dir = "nar/";
$file_size_limit = 1024 * 1024 * 5;

//リザルト取得用
$upload_result = "";		//リザルトの説明
$upload_result_code = "";	//リザルトのコード success=成功 failed=失敗

 main();
 
 echo '<!--result: '.$upload_result.'-->';
 echo '<!--resultcode: '.$upload_result_code.'-->';
 
  ?>
</p>
</body>
</html>

<?php





function main(){

	global $id_pass_limit, $admin_pass, $pass_dir, $data_dir, $nar_dir, $file_size_limit;

	if( base_error_check() != true ){
		//基本エラーチェックでエラー
		return;
	}

	$id = $_POST['id'];
	$pass = $_POST['password'];
	
	if( $_POST['formtype'] == 'signup' ){
		//サインアップ モード
		if( !file_exists($pass_dir) ){
			mkdir($pass_dir);
		}
		chmod( $pass_dir.$id, 0600);	//覗かれないためのパーミッション
		
		$passFile = $pass_dir.$id.'.txt';
		if( file_exists($passFile) ){
			//登録済み
			set_result( $id.'　既に使用されているIDです。使用することは出来ません。', 'failed' );
			return;
		}
		//登録をおこなう
		touch( $passFile);
		chmod( $passFile, 0600);	//外から見られないためのパーミッション
		file_put_contents ( $passFile, $pass );	//カキコミ
		set_result( $id.'　登録しました。', 'success' );
	}
	else if( $_POST['formtype'] == 'upload'){
		//アップロード モード
		if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
			try {
				if( filesize() > $file_size_limit ){
					set_result( 'ファイルサイズが大きすぎます。', 'failed' );
					return;
				}
			
				if( id_pass_check($id, $pass ) != true ){
					//idパスワードエラー
					return;
				}
				
				if(!iszip($_FILES["upfile"]["tmp_name"])){
					set_result( 'ファイルの形式がnar/zipではありません。', 'failed' );
					return;
				}
				
				if( file_exists($data_dir.$id.'/') ){
					unlinkRecursive($data_dir.$id.'/', true);
				}
				//ディレクトリの作成
				mkdir($nar_dir);
				mkdir($data_dir);
				mkdir($data_dir.$id.'/');
				
				unzip( $_FILES["upfile"]["tmp_name"], $data_dir.$id."/");
				move_uploaded_file($_FILES["upfile"]["tmp_name"], $nar_dir.$id.'.nar');
				chmod($nar_dir.$id.'.nar', 0206);
				set_result( 'アップロードが完了しました。', 'success' );
				}
			catch (Exception $e) {
				set_result( 'エラー: ',  $e->getMessage(), 'failed' );
			}
		}
		else {
			set_result( 'ファイルが選択されていません。', 'failed' );
		}	
	}
	else if( $_POST['formtype'] == 'delete' ){

				if( id_pass_check($id, $pass ) != true ){
					//idパスワードエラー
					return;
				}
				
				if( file_exists($data_dir.$id.'/') ){
					unlinkRecursive($data_dir.$id.'/', true);
				}
				
				unlink( $pass_dir.$id.".txt");
				unlink( $nar_dir.$id.".nar");
				set_result( $id.'　削除しました。', 'success' );
				
	}

	echo '<br><a href="index.html">戻る</a>';

}

function set_result( $result_string, $result_code )
{
	global $upload_result, $upload_result_code;
	$upload_result = $result_string;
	$upload_result_code = $result_code;
	echo $result_string;
}

//基本エラーチェック
function base_error_check()
{

	global $id_pass_limit, $admin_pass, $pass_dir, $data_dir, $nar_dir, $file_size_limit;	

	if( !isset( $_POST['formtype'] ) ){
		set_result( 'フォーム エラーです。', 'failed' );
		return false;
	}

	if( $_POST['admin'] != $admin_pass )
	{
		set_result( '管理パスが正しくありません。', 'failed' );
		return false;
	}

	if( !isset($_POST['id']) || !isset($_POST['password']) ){
		set_result( 'ID・パスワードが入力されていません。', 'failed' );
		return false;
	}

	if( strlen($_POST['id']) > $id_pass_limit || strlen($_POST['password']) > $id_pass_limit){
		set_result( "ID・パスワードは".$id_pass_limit."文字までです。", 'failed' );
		return false;
	}
	
	if( strlen($_POST['id']) < 2 || strlen($_POST['password']) < 2){
		echo "ID・パスワードは２文字以上は入力してください。";
		set_result( 'ID・パスワードは２文字以上入力してください。', 'failed' );
		return false;
	}

	if( !ctype_alnum($_POST['id'])  || !ctype_alnum($_POST['password']) ){
		set_result( 'ID・パスワードには英数字のみが使用できます。', 'failed' );
		return false;
	}
	return true;
}

function id_pass_check( $id, $pass )
{
	global $id_pass_limit, $admin_pass, $pass_dir, $data_dir, $nar_dir, $file_size_limit;
	
	//パスワード照合処理
	if (! ($fp = fopen (  $pass_dir.$id.".txt", "r" ))) {
		set_result( 'IDが登録されていません。', 'failed' );
		return false;
	}
	//パスワードを得る
	$savedPass = fgets($fp);
	fclose($fp);
	if( $pass != $savedPass ){
		//パスワード照合エラー
		set_result( 'IDとパスワードの組み合わせが正しくありません。', 'failed' );
		return false;
	}
	
	return true;
}

function iszip ( $zip_path )
{
	$zip = new ZipArchive();
	if( $zip->open($zip_path) === true ){
		$zip->close();
		return true;
	}
	else{
		return false;
	}
}


function unzip( $zip_path, $dir_path )
{
	$zip = new ZipArchive();
	if( $zip->open($zip_path) === true ){

		for ($i = 0; $i < $zip->numFiles; $i++) { // $zip->numFiles はファイル数
			$name = $zip->getNameIndex($i);
			$destName = str_replace( '\\', '/', $name);
			//$zip->renameName($name, $destName);
			
			$path_parts = pathinfo($dir_path.$destName);
			$path = $path_parts['dirname'] . '/';
			mkdir($path, 0777, true );
			//echo  $path.'<br>';
			
			touch($dir_path.$destName);
			chmod($dir_path.$destName, 0206);
			$fp = fopen($dir_path.$destName, 'wb');
			fwrite( $fp, $zip->getFromName($name));
			fclose($fp);
		}
		//$zip->extractTo( $dir_path);
		$zip->close();
	}
	else{
		throw new Exception('It does not open a zip file');
	}
}

//お借りしたコード:
//http://onlineconsultant.jp/pukiwiki/?PHP%20ディレクトリ内のファイル、サブディレクトリを一括消去
function unlinkRecursive($dir, $deleteRootToo)
{
    if(!$dh = @opendir($dir))
    {
        return;
    }
    while (false !== ($obj = readdir($dh)))
    {
        if($obj == '.' || $obj == '..')
        {
            continue;
         }  
 
        if (!@unlink($dir . '/' . $obj))
        {
           unlinkRecursive($dir.'/'.$obj, true);
       }
   }
 
   closedir($dh);
  
   if ($deleteRootToo)
   {
       @rmdir($dir);
   }
  
   return;
}


?>