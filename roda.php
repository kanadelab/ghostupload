<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<title>�S�[�X�g�A�b�v���[�_�i���j</title>
</head>
<body>
<p>
<?php
$id_pass_limit = 20;
$admin_pass = 'kanadesan';
$pass_dir = "pass/";
$data_dir = "data/";
$nar_dir = "nar/";
$file_size_limit = 1024 * 1024 * 5;

 main()
 
  ?>
</p>
</body>
</html>

<?php





function main(){

	global $id_pass_limit, $admin_pass, $pass_dir, $data_dir, $nar_dir, $file_size_limit;

	if( base_error_check() != true ){
		//��{�G���[�`�F�b�N�ŃG���[
		return;
	}

	$id = $_POST['id'];
	$pass = $_POST['password'];
	
	if( $_POST['formtype'] == 'signup' ){
		//�T�C���A�b�v ���[�h
		if( !file_exists($pass_dir) ){
			mkdir($pass_dir);
		}
		chmod( $pass_dir.$id, 0600);	//�`����Ȃ����߂̃p�[�~�b�V����
		
		$passFile = $pass_dir.$id.'.txt';
		if( file_exists($passFile) ){
			//�o�^�ς�
			echo $id.'�@���Ɏg�p����Ă���ID�ł��B�g�p���邱�Ƃ͏o���܂���B';
			return;
		}
		//�o�^�������Ȃ�
		touch( $passFile);
		chmod( $passFile, 0600);	//�O���猩���Ȃ����߂̃p�[�~�b�V����
		file_put_contents ( $passFile, $pass );	//�J�L�R�~
		echo $id.'�@�o�^���܂����B';
	}
	else if( $_POST['formtype'] == 'upload'){
		//�A�b�v���[�h ���[�h
		if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
			try {
				if( filesize() > $file_size_limit ){
					echo '�t�@�C���T�C�Y���傫�����܂��B';
					return;
				}
			
				if( id_pass_check($id, $pass ) != true ){
					//id�p�X���[�h�G���[
					return;
				}
				
				if(!iszip($_FILES["upfile"]["tmp_name"])){
					echo "�t�@�C���̌`����nar/zip�ł͂���܂���B";
					return;
				}
				
				if( file_exists($data_dir.$id.'/') ){
					unlinkRecursive($data_dir.$id.'/', true);
				}
				//�f�B���N�g���̍쐬
				mkdir($nar_dir);
				mkdir($data_dir);
				mkdir($data_dir.$id.'/');
				
				unzip( $_FILES["upfile"]["tmp_name"], $data_dir.$id."/");
				move_uploaded_file($_FILES["upfile"]["tmp_name"], $nar_dir.$id.'.nar');
				chmod($nar_dir.$id.'.nar', 0206);
				echo "�A�b�v���[�h�����I";
				}
			catch (Exception $e) {
				    echo '�G���[: ',  $e->getMessage(), "\n";
			}
		}
		else {
			echo "�t�@�C�����I������Ă��܂���B";
		}	
	}
	else if( $_POST['formtype'] == 'delete' ){

				if( id_pass_check($id, $pass ) != true ){
					//id�p�X���[�h�G���[
					return;
				}
				
				if( file_exists($data_dir.$id.'/') ){
					unlinkRecursive($data_dir.$id.'/', true);
				}
				
				unlink( $pass_dir.$id.".txt");
				unlink( $nar_dir.$id.".nar");
				echo "�폜���܂����B";
	}

	echo '<br><a href="index.html">�߂�</a>';

}

//��{�G���[�`�F�b�N
function base_error_check()
{

	global $id_pass_limit, $admin_pass, $pass_dir, $data_dir, $nar_dir, $file_size_limit;	

	if( !isset( $_POST['formtype'] ) ){
		echo "�t�H�[�� �G���[�ł��B";
		return false;
	}

	if( $_POST['admin'] != $admin_pass )
	{
	echo $_POST['admin'].'/'.$admin_pass;
		echo '�Ǘ��p�X�G���[�ł��B';
		return false;
	}

	if( !isset($_POST['id']) || !isset($_POST['password']) ){
		echo "ID�E�p�X���[�h�����͂���Ă��܂���B";
		return false;
	}

	if( strlen($_POST['id']) > $id_pass_limit || strlen($_POST['password']) > $id_pass_limit){
		echo "ID�E�p�X���[�h��".$id_pass_limit."�����܂łł��B";
		return false;
	}
	
	if( strlen($_POST['id']) < 2 || strlen($_POST['password']) < 2){
		echo "ID�E�p�X���[�h�͂Q�����ȏ�͓��͂��Ă��������B";
		return false;
	}

	if( !ctype_alnum($_POST['id'])  || !ctype_alnum($_POST['password']) ){
		echo "ID�E�p�X���[�h�ɂ͉p�����݂̂��g�p�ł��܂��B";
		return false;
	}
	return true;
}

function id_pass_check( $id, $pass )
{
	global $id_pass_limit, $admin_pass, $pass_dir, $data_dir, $nar_dir, $file_size_limit;
	
	//�p�X���[�h�ƍ�����
	if (! ($fp = fopen (  $pass_dir.$id.".txt", "r" ))) {
		echo "ID���o�^����Ă��܂���B";
		return false;
	}
	//�p�X���[�h�𓾂�
	$savedPass = fgets($fp);
	fclose($fp);
	if( $pass != $savedPass ){
		//�p�X���[�h�ƍ��G���[
		echo "ID�ƃp�X���[�h�̑g�ݍ��킹������������܂���B";
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

		for ($i = 0; $i < $zip->numFiles; $i++) { // $zip->numFiles �̓t�@�C����
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

//���؂肵���R�[�h:
//http://onlineconsultant.jp/pukiwiki/?PHP%20�f�B���N�g�����̃t�@�C���A�T�u�f�B���N�g�����ꊇ����
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