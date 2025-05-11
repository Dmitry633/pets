<?php

declare(strict_types = 1);
global $dateToScreen;
function getLastFile (string $dirPath):int {
  $listFiles = [];
  $filesAsDate = [];
  $arrDates = [];

  foreach(scandir($dirPath) as $file) {//перебор всех файлов в директории
      if(is_dir($file)){//исключение из перечня дирректорий (точек)
          continue;
      }
      $listFiles[]=$file;
  }
  
  foreach($listFiles as $fileAsDate) {

    $trimmed = trim($fileAsDate, " .xlsx");
    $filesAsDate[] = $trimmed;
  }

  foreach ($filesAsDate as $date){ //перебор обрезанных от .xlsx наименований файла

$arrDate = date_parse_from_format("d.m.Y", $date);

if (is_bool($arrDate['year'])) {$arrDate['year'] = 25;}

$arrDates[] = mktime(0,0,0, $arrDate['month'], $arrDate['day'], $arrDate['year']);
   }
$lastFile = current(array_keys($arrDates, max($arrDates)));//определение ключа с максимальным значением
global $dateToScreen;
$dateToScreen[] = date ('d.m.y',max($arrDates));

return $lastFile;
}


function getBalanceFiles(string $dirPath): array {// добавим в качестве аргумента путь до файла, чтобы наша ф-ция имела меньше зависимостей и это позволит нам использовать ее в т ч вне данной директории
   
    $files = [];
    foreach(scandir($dirPath) as $file) {//перебор всех файлов в директории
        if(is_dir($file)){//исключение из перечня дирректорий (точек)
            continue;
        }
        $files[]=$dirPath . $file;
    }

    return $files;
    
}

function getDemandFile (string $dirPath): string{
  $files = [];
    foreach(scandir($dirPath) as $file) {//перебор всех файлов в директории
        if(is_dir($file)){//исключение из перечня дирректорий (точек)
            continue;
        }
        $files[]=$dirPath . $file;
    }

  return $files[0];
    
}

function getArticleArr (string $fileName): array { 
    if(! file_exists($fileName)){//проверка на Е файла
        trigger_error('File'.$filename.'doesn`t exist', E_USER_ERROR);//вызов ошибки
    }
    
$z=new ZipArchive();
$z->open($fileName);

$str_values=array();

// Прочитать строковые значения
if ($fp=$z->getStream('xl/sharedStrings.xml')) {
  $data='';
  while (!feof($fp)) {
    $data.=fread($fp, 1024);

  }
  fclose($fp);
 
  $xml=simplexml_load_string($data);
 
  if (isset($xml->si) && count($xml->si)) {
    foreach ($xml->si as $data) {
      $data=(array)$data;
      $str_values[]=$data['t'];
    }
  }
}
 
$xls_values=array();

// Прочитать значения из первой страницы документа
if ($fp=$z->getStream('xl/worksheets/sheet1.xml')) {
    $data='';
    while (!feof($fp)) {
      $data.=fread($fp, 1024);
    }
    fclose($fp);
   
    $xml=simplexml_load_string($data);
   
    if (isset($xml->sheetData)) {
      $sheetData=(array)($xml->sheetData);
      
      if (isset($sheetData['row']) && count($sheetData['row'])>0) {
        foreach($sheetData['row'] as $row) {

          $row=(array)$row;
   
          // Особый случай для одноколоночной страницы
          if (!is_array($row['c'])) {
            $row['c']=array($row['c']);
          }
   
          foreach ($row['c'] as $col) {
            $col=(array)$col;
            // Столбец и колонка
            preg_match('/([A-Z]+)(\d+)/',$col['@attributes']['r'],$matches);

            // Строка из списка
            if (isset($col['@attributes']['t'])
              && $col['@attributes']['t']=='s'
              && isset($str_values[$col['v']]))
            {

              $xls_values[$matches[2]][$matches[1]]=$str_values[$col['v']];
            }
            // Непосредственное значение
            elseif (isset($col['v'])) {
              $xls_values[$matches[2]][$matches[1]]=$col['v'];
            }
          }
        }
      }
    }
  }
   
  $z->close();
   
$articles = $xls_values;
    return $articles;

}


/*
function makeDigits() {//попробовать в качестве аргумета $item['C']
  echo "до if makeDigits:" . "\n";
  var_dump ($item['C']);

  if ((!preg_match('/[^\d]/',(string)$item['C']))) {
    $item['C'] = strval($item['C']);

    echo "внутри только числового случая makeDigits:" . "\n";
    var_dump ($item['C']);
    //str_pad();
    $requestList[$item['C']] = $item['D'];

  } else {
    echo "внутри НЕ только числового случая makeDigits:" . "\n";
    var_dump ($item['C']);

    $requestList[$item['C']] = $item['D'];
  }
}
*/
$safficientListBy = [];
$safficientListAfar = [];
$requestList = [];

function getVendor (array $getedArr) {
  global $requestList;
  
  foreach ($getedArr as $item) {
    if ($item['C'] == 'Наименование')
      continue;
    
    if ((!preg_match('/[^\d]/',(string)$item['B']))) {
      // $item['C'] = strval($item['C']);
      $item['B'] = str_pad((string)$item['B'], (mb_strlen((string)$item['B'])+1), '0', STR_PAD_LEFT);
    }
    
    match (1) {
      (preg_match('/(?i)(iek)|(?i)(oni)/',$item['C'])) => $demandIEK[] = $item,// print 'эту позицию необходимо искать в остатках ИЭК' . "\n",
      (preg_match('/(?i)(ekf)/',$item['C'])) =>  $demandEKF[] = $item,// print 'эту позицию необходимо искать в остатках ЭКФ'. "\n",
      (preg_match('/(?i)(dek)|(?i)(systeme)/',$item['C'])) =>  $demandSE[] = $item,
      (preg_match('/(?i)(tdm)/',$item['C'])) =>  $demandTDM[] = $item,
      default => $requestList[(string)$item['B']] = $item['D'], //помесить сюда функцию, которя в качестве ключа представляет строку вместо числа
      // default => makeDigits(),
      //((!preg_match('/[^\d]/',(string)$item['C']))) => str_pad((string)$item['C'], (mb_strlen((string)$item['C'])+1), '0', STR_PAD_LEFT);
    };
    
    if(is_null($demandIEK))$demandIEK = [];
    if(is_null($demandEKF))$demandEKF = [];
    if(is_null($demandSE))$demandSE = [];
    if(is_null($demandTDM))$demandTDM = [];
    
  }
  // echo 'Список к запросу:'. "\n";
  //   var_dump ($requestList);
  //изменить колонки в соответсвии с АО
  return [$demandIEK, $demandEKF, $demandSE,$demandTDM];
}


function makeBalancesIEK(array $balancesList): array {
  foreach($balancesList as $item){
    $storageIEK[]=array ($item['A'], ((int)(str_replace(' ', '',$item['C'])) + (int)(str_replace(' ', '',$item['D'])) + (int)(str_replace(' ', '',$item['F']))), (str_replace(' ', '',$item['E'])));
  }
  return $storageIEK;
}

function makeBalancesEKF(array $balancesList): array {
  foreach($balancesList as $item){
    if(is_null($item['D'])) $item['D'] = 0;
    $storageEKF[]=array ($item['A'], $item['F'], $item['D']);
  }
  return $storageEKF;
}

function makeBalancesSE(array $balancesList): array {
  foreach($balancesList as $item){
    if ($item['A'] == 'Лобня') continue;
    $storageSE[]=array ($item['B'], $item['E']);
  }

  return $storageSE;
}

function makeBalancesTDM(array $balancesList): array {
  foreach($balancesList as $item){
    $storageTDM[]=array ($item['A'], $item['D']);
  }
  return $storageTDM;
}


function getMatches (array $balances, array $demand): array {

  $storage = [];
  $item = [];
  $storageArticle = [];
  $itemArticle = [];

  foreach($balances as $subArr){
    $storage[]=array ($subArr[0], $subArr[1], $subArr[2]);
    $storageArticle[]=$subArr[0];
  }

  foreach($demand as $subArr){
    $item[]=array ($subArr['B'], $subArr['D']);
    $itemArticle[]=$subArr['B'];
  }
  
  $storageArticle = array_map('strtoupper', $storageArticle);
  $itemArticle = array_map('strtoupper', $itemArticle);
  $absentBalance = [];
  foreach(array_diff($itemArticle, $storageArticle) as $absentPos){
    // echo 'Позиция '. $absentPos. ' отсутсвтует в остатках'. "\n";
    $absentBalance[] = $absentPos;
  };
for($i = 0; $i<count($item); $i++){
  foreach($absentBalance as $absentPos){
    if(strcasecmp($item[$i][0], $absentPos) == 0){
      // echo "Требуется запросить позицию ". $item[$i][0] . " в количестве ". $item[$i][1]. "\n";
      $articleRequest[] = $item[$i][0];
      $quantityRequest[] = $item[$i][1];

    }
  }
}
    $articleSufficientBy= [];
    $quantitySufficientBy = [];
    $articleSufficientAfar= [];
    $quantitySufficientAfar = [];
  foreach ($item as $need) {

    for($i=1; $i<count($storage); $i++) {
    if ($need[0]== "Артикул") 
      continue;

    if (is_null($storage[$i][0])){
      $storage[$i][0] = 'z';
    }
    if ((preg_match('/\.\d\d\./',(string)$storage[$i][1]))) $storage[$i][1] = 0;
    if (strcasecmp($need[0], $storage[$i][0]) == 0) {
      
      if ($storage[$i][2] > $need[1]){
        // echo 'Позиции '. $need[0] . ' достаточно в НСК'. "\n";
        $articleSufficientBy[]= $need[0];
        $quantitySufficientBy[] = $need[1];

      }
      
      else if ($storage[$i][2] == $need[1]){
        $articleSufficientBy[]= $need[0];
        $quantitySufficientBy[] = $need[1];
      } 
       else if ($storage[$i][1] > $need[1]){
        $articleSufficientAfar[]= $need[0];
        $quantitySufficientAfar[] = $need[1];
        
      } 
      
      else if ($storage[$i][1] == $need[1]){
        $articleSufficientAfar[]= $need[0];
        $quantitySufficientAfar[] = $need[1];
      } 
      else if ($storage[$i][1]+ $storage[$i][2] > $need[1]){
        // echo 'Позиции '. $need[0] . ' достаточно на складах МСК и НСК вместе'. "\n";
        // echo 'Позиции '. $need[0] . ' на складах МСК и НСК числится '. $storage[$i][1] . ' + '. $storage[$i][2].  "\n";
        // var_dump ($storage[$i]);
        $articleSufficientAfar[]= $need[0];
        $quantitySufficientAfar[] = $need[1];
        
      } 
      else if ($storage[$i][1]+ $storage[$i][2] ==  $need[1]){
        // echo 'Позиции '. $need[0] . ' впритык на складах МСК и НСК вместе'. "\n";
        $articleSufficientAfar[]= $need[0];
        $quantitySufficientAfar[] = $need[1];
        
      } 
      else {
        // echo 'Позиции '. $need[0] . ' Не достаточно'. "\n";
        // echo 'Позиции '. $need[0] . ' на складах МСК числится '.$storage[$i][1]. "\n";
        $articleRequest[] = $need[0];
        $quantityRequest[] = $need[1];
        // $requestList[] = [$need[0],$need[1]];
      }
    }
    }
  }
  
  // static $requestList = [], $safficientListBy = [], $safficientListAfar = [];
  global $requestList, $safficientListBy, $safficientListAfar;
  // echo 'Предытоговый вывод списка к запросу:' ."\n";
  // var_dump($requestList);
  if (is_null($articleRequest)) $articleRequest[] = 'ошибочный артикул';
  if (is_null($quantityRequest)) $quantityRequest[] = '0';
  // echo ':' ."\n";

  $requestListTemp = array_combine($articleRequest,$quantityRequest);
  // echo 'Временный список к запросу:' ."\n";
  // print_r($requestListTemp);
  $safficientListByTemp = array_combine($articleSufficientBy,$quantitySufficientBy);
  $safficientListAfarTemp = array_combine($articleSufficientAfar,$quantitySufficientAfar);
  
  
  $requestList = array_merge($requestList,$requestListTemp);// ключи могут "потеряться" в результате преобразования массива с помощью функций array_*()
  // echo 'Итоговый список к запросу:' ."\n";
  // print_r($requestList);
  $safficientListBy = array_merge($safficientListBy,$safficientListByTemp);
  $safficientListAfar = array_merge($safficientListAfar,$safficientListAfarTemp);
  /*
  echo "\n";
  echo "Список к запросу: "."\n";
  print_r($requestList);
  echo "\n";
  echo "Позиции в налчии в пределах 5 р д поставки: "."\n";
  print_r($safficientListBy);
  echo "Позиции в налчии более чем 5 р д поставки: "."\n";
  print_r($safficientListAfar);
  */
  return [$requestList, $safficientListBy, $safficientListAfar];
}
 