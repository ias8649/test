<?php
require_once('./settings.php');

require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

function checkServiceAccountCredentialsFile($pathToCredentials)
{
    // service account creds
    return file_exists($pathToCredentials) ? $pathToCredentials : false;
}

try {
    $db = new \PDO(DB_DNS, DB_USER, DB_PASSWORD, []);
} catch (Exception $e) {
    echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
    die();
}

$created = 0;
$deleted = 0;
$date = date('Y-m-d');

try {
    $statment = $db->prepare('SELECT * FROM users WHERE deleted_at = ?;');
    $statment->execute([$date]);
    $deleted = sizeof($statment->fetchAll());
    
    $statment = $db->prepare('SELECT * FROM users WHERE created_at = ?;');
    $statment->execute([$date]);
    $created = sizeof($statment->fetchAll());
    
} catch (Exception $e) {
    echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
    return;
}

$client = new Google_Client();

if ($credentials_file = checkServiceAccountCredentialsFile(CREDENTIAL)) {
    // set the location manually
    $client->setAuthConfig($credentials_file);
} elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
    // use the application default credentials
    $client->useApplicationDefaultCredentials();
} else {
    echo "missing Service Account Details";
    return;
}

$client->setApplicationName("test-project-users-flow");
$client->setScopes(['https://www.googleapis.com/auth/spreadsheets']);
$service = new Google_Service_Sheets($client);

$response = $service->spreadsheets_values->get(SPREADSHEETID, 'A:C', ['majorDimension' => 'COLUMNS']);

if (!is_null($response->values[0])) {
    $date_row = array_search($date, $response->values[0]); 
} else {
    $date_row = 1;
}

$rows = sizeof($response->values[0]);

$values = [[$date, $created, $deleted]];
$body    = new Google_Service_Sheets_ValueRange(['values' => $values]);
$options = array('valueInputOption' => 'RAW');

if ($date_row === false) {
    $range = 'A' . ($rows + 1) . ':C' . ($rows + 1);
} else {
    $range = 'A' . ($date_row + 1) . ':C' . ($date_row + 1);
}
$service->spreadsheets_values->update(SPREADSHEETID, $range, $body, $options);
