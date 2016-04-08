<?php

require 'vendor/autoload.php';

//code modified from https://github.com/ibmjstart/wp-bluemix-objectstorage/blob/master/classes/swift.php
$vcap = getenv("VCAP_SERVICES");
$data = json_decode($vcap, true);
$creds = $data['Object-Storage']['0']['credentials'];
$auth_url = $creds['auth_url'] . '/v3'; //keystone v3
$region = $creds['region'];
$userId = $creds['userId'];
$password = $creds['password'];
$projectId = $creds['projectId'];
$openstack = new OpenStack\OpenStack([
			    'authUrl' => $auth_url,
			    'region'  => $region,
			    'user'    => [
			        'id'       => $userId,
			        'password' => $password
			    ],
			    'scope'   => [
			    	'project' => [
			    		'id' => $projectId
			    	]
			    ]
			]);

$container = $openstack->objectStoreV1()
                       ->getContainer('php-uploader');

echo 'Here is some more debugging info:';
print_r($_FILES);

$uploaddir = '/var/www/uploads/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

echo '<pre>';
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}

//found on http://stackoverflow.com/questions/16888722/get-content-of-file-uploaded-by-user-before-saving
$fileContent = file_get_contents($_FILES["file"]["tmp_name"]);

$options = [
    'name'    => $_FILES['file']['name'],
    'content' => $fileContent
];

echo "<p>" . $options['name'] . "</p>";
echo "<p>" . $options['content'] . "</p>";

$container->createObject($options);

print "</pre>";

//found on http://stackoverflow.com/questions/14810399/php-form-redirect
header( 'Location: https://php-uploader.mybluemix.net' ) ;
?>