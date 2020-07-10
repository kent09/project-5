<?php
$post_flds = array(
    'FuseUser'					=> 11,
    'FuseKey' 					=> "aykgfwwyrseevljrfens",
    'TemplateID'			 	=> "mGQWs2uj2YhYMMTGNbcZxb",
    'RecipientFirstName'		=> "Ajay ",
    'RecipientLastName'			=> "Verma",
    'RecipientEmail'			=> "ajay@outoftheboxsolutions.com.au",
    "Client.FirstName" 			=> "Ajay",
    'Client.LastName'			=> "Verma",
    'Client.Email'				=> "ajay@outoftheboxsolutions.com.au",
    'Client.Company'			=> "Ajay Company",
    'OOTB.FirstName'			=> "Test",
    'OOTB.LastName'				=> "User",
    'OOTB.Email'				=> "test@gmail.com",
    'OOTB.Company'				=> "OOTB Compnay",
    'CompanyName'  				=> 'test company',
    'Address' 					=> 'test address',
    'Position' 					=> 'test position',
    'Date' 			            =>  '2017-06-06',
    'SenderFirstName'           => 'Test',
    'SenderLastName'            => 'User',
    'SenderEmail'               => "test@gmail.com"
    
);

//$url = "http://localhost/fusedtools/public/tools/panda";
$url = "http://fuseddocs.com/tools/panda";
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, count($post_flds));
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_flds);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response 				= curl_exec($ch);

curl_close($ch);
$info=curl_getinfo($ch);

echo "<pre>"; print_r($response); die();
