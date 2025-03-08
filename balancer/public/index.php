<?php

declare(strict_types = 1);

$root = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR;
// echo $root;

define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app'. DIRECTORY_SEPARATOR);
define('IEK', $root . 'mydomain.ru' . DIRECTORY_SEPARATOR .'I'. DIRECTORY_SEPARATOR . 'II' . DIRECTORY_SEPARATOR . 'IEK'. DIRECTORY_SEPARATOR);
define('EKF', $root . 'mydomain.ru' . DIRECTORY_SEPARATOR .'I'. DIRECTORY_SEPARATOR . 'II' . DIRECTORY_SEPARATOR . 'EKF'. DIRECTORY_SEPARATOR);
define('SE', $root . 'mydomain.ru' . DIRECTORY_SEPARATOR .'I'. DIRECTORY_SEPARATOR . 'II' . DIRECTORY_SEPARATOR . 'Систэм Электрик (Шнейдер)'. DIRECTORY_SEPARATOR . 'Шнейдер остатки'. DIRECTORY_SEPARATOR . 'Екатеринбург'. DIRECTORY_SEPARATOR);
define('TDM', $root . 'mydomain.ru' . DIRECTORY_SEPARATOR .'I'. DIRECTORY_SEPARATOR . 'II' . DIRECTORY_SEPARATOR . 'ТДМ'. DIRECTORY_SEPARATOR . 'МСК'. DIRECTORY_SEPARATOR);


define('FILES_PATH', $root . 'mydomain.ru' . DIRECTORY_SEPARATOR. 'II' . DIRECTORY_SEPARATOR);
define('DEMAND', $root . 'mydomain.ru' . DIRECTORY_SEPARATOR. 'demand' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', '..' .DIRECTORY_SEPARATOR. 'views' . DIRECTORY_SEPARATOR);

require APP_PATH . 'App.php';

$demandFile = getdemandFile(DEMAND);

$demandArr = getArticleArr($demandFile);

$filesIEK = getBalanceFiles(IEK);
$lastFileIEK = getLastFile(IEK);
$filesEKF = getBalanceFiles(EKF);
$lastFileEKF = getLastFile(EKF);
$filesSE = getBalanceFiles(SE);
$lastFileSE = getLastFile(SE);

$filesTDM = getBalanceFiles(TDM);
$lastFileTDM = getLastFile(TDM);



$balancesArrIEK = getArticleArr($filesIEK[$lastFileIEK]);
$balancesArrEKF = getArticleArr($filesEKF[$lastFileEKF]);
$balancesArrSE = getArticleArr($filesSE[$lastFileSE]);
$balancesArrTDM = getArticleArr($filesTDM[$lastFileTDM]);

$storageIEK = makeBalancesIEK($balancesArrIEK);

$storageEKF = makeBalancesEKF($balancesArrEKF);

$storageSE = makeBalancesSE($balancesArrSE);


$storageTDM = makeBalancesTDM($balancesArrTDM);


$vendorsDemandArr = getVendor($demandArr);// [взять наименование из demandArr и по нему определиитьь вендор]

getMatches($storageIEK, $vendorsDemandArr[0]);
getMatches($storageEKF, $vendorsDemandArr[1]);
getMatches($storageSE, $vendorsDemandArr[2]);

getMatches($storageTDM, $vendorsDemandArr[3]);

require VIEWS_PATH . 'balances.php';

echo 'ОБРАБОТКА ЗАВЕРШЕНА'."\n";
