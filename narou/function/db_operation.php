<?php
//エスケープ処理
$_GET['q'] = htmlspecialchars($_GET['q'], ENT_QUOTES, "utf-8");
$_GET['sort'] = htmlspecialchars($_GET['sort'], ENT_QUOTES, "utf-8");
$_GET['submit'] = htmlspecialchars($_GET['submit'], ENT_QUOTES, "utf-8");
$_GET['search_type'] = htmlspecialchars($_GET['search_type'], ENT_QUOTES, "utf-8");
$_GET['ncode'] = htmlspecialchars($_GET['ncode'], ENT_QUOTES, "utf-8");


//DB接続
$host = 'webdb.sfc.keio.ac.jp';
$db   = 's19752km';
$user = 's19752km';
$fp = fopen("/home/s19752km/public_html/narou/db_pass.txt", "r");
$pass = fgets($fp);
$pass = str_replace(PHP_EOL, "", $pass);
fclose($fp);
$charset = 'utf8';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try{
  $pdo = new PDO($dsn, $user, $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   //連想配列（辞書型）で受け取る
        ]);
} catch (\PDOException $e) {    //DB接続エラー時の処理
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    exit($e->getMessage());
}


//URLパラメータが書き換えられたときの処理
if($_GET['sort'] != "length" && $_GET['sort'] != "genre" && $_GET['sort'] != "global_point"
    && $_GET['sort'] != "daily_point" && $_GET['sort'] != "weekly_point" && $_GET['sort'] != "monthly_point" && $_GET['sort'] != "yearly_point"){
  $_GET['sort'] = "novelupdated_at";
}


//////DB検索/////

//ncode完全一致・詳細データ取得
function ncode_search_detail_comp($pdo){
  $stmt = $pdo->prepare("select * from Naro_All_info where ncode = :ncode");
  $stmt->bindValue(':ncode', $_GET['ncode']);
  $stmt->execute();

  $result = $stmt->fetch();     //fetchOneではないので注意

  //DB切断
  $stmt = null;
  $pdo = null;
  return $result;
}

//ncodeからタイトル・ncodeを取得する
function ncode_search_title($pdo){
  $stmt = $pdo->prepare("select title, ncode from Naro_All_info where ncode = :ncode");
  $stmt->bindValue(":ncode", $_GET['ncode']);
  $stmt->execute();
  $result = $stmt->fetch();
  //この関数を使うページでは、まだDBを使うので、$pdoはnullにしない
  return $result;
}

//部分一致検索（タイトル　OR キーワード）
function search_part($pdo){
  //空白でAND検索
  $q_array = ex_explode(array(" ", "　", "AND"), $_GET["q"]);
  //空の要素を排除（第２引数の関数をcallbackして、falseであればその要素を削除
  $q_array = array_filter($q_array, "strlen");
  $q_array_cnt = count($q_array);
  //キーワード検索の際のセンテンスを用意
  if($_GET['search_type'] == "keyword"){
    for($i=0; $i<$q_array_cnt; $i++){
      if($i ==0){
        $like_sentence = "keyword like ?";
      }else{
        $like_sentence = "{$like_sentence} AND keyword like ? ";
      }
    }
  }else{
  //タイトル検索のときのセンテンスを用意
  for($i=0; $i<$q_array_cnt; $i++){
    if($i ==0){
      $like_sentence = "title like ? ";
    }else{
      $like_sentence = "{$like_sentence}AND title like ? ";
        } 
    }
  }
  if($_GET['page_id'] == "" || empty($_GET['page_id'])){
  $_GET['page_id'] = (int)1;
  }
  $offset_par = ((int)($_GET['page_id']) - 1) * 20;
  //該当件数検索
  $stmt = $pdo->prepare("select count(*) from Naro_All_info where ${like_sentence}");
  for($i=0; $i<$q_array_cnt; $i++){
    $stmt->bindValue(($i+1), "%{$q_array[$i]}%");
  }
  $stmt->execute();
  $GLOBALS['result_cnt'] = $stmt->fetch();
  $GLOBALS['result_cnt'] = (int)$GLOBALS['result_cnt']['count(*)'];
  //表示分検索
  $stmt = $pdo->prepare("select title, ncode, story, writer from Naro_All_info where ${like_sentence} order by {$_GET['sort']} desc limit 20 offset ${offset_par}");
  for($i=0; $i<$q_array_cnt; $i++){
    $stmt->bindValue(($i+1), "%{$q_array[$i]}%");
  }
  $stmt->execute();

  $result = $stmt->fetchAll();
  //DB切断
  $stmt = null;
  $pdo = null;
  return $result;
}

//ncodeから類似検索をかける
function search_similar($pdo){
  //ジャンル番号を取得し、参照テーブルを特定
  $stmt = $pdo->prepare("select genre from Naro_All_info where ncode = :ncode");
  $stmt->bindValue(":ncode", $_GET['ncode']);
  $stmt->execute();
  $result_tmp = $stmt->fetch(); 
  $table_name = "Naro_similarity_{$result_tmp['genre']}";

  //対象ncodeのsimilarityを取得
  $stmt = $pdo->prepare("select similarity from {$table_name} where ncode = :ncode");
  $stmt->bindValue(":ncode", $_GET['ncode']);
  $stmt->execute();
  $result_tmp = $stmt->fetch();
  $similarity = $result_tmp['similarity'];

  $ip_num = ip2long($_SERVER['REMOTE_ADDR']);

  $tmp_table_name = "similarity_tmp_{$ip_num}";

  if($_GET['page_id'] == "" || empty($_GET['page_id'])){
    $_GET['page_id'] = (int)1;
    }
  $offset_par = ((int)($_GET['page_id']) - 1) * 20;
  $GLOBALS['result_cnt'] = (int)60;

  //類似検索
  //対象similarity前後60件を抜き出し、一時テーブルへ保存
  $stmt = $pdo->prepare("create temporary table {$tmp_table_name} as (select ncode,  similarity from {$table_name} where similarity < {$similarity} limit 60) union (select ncode,  similarity from {$table_name} where similarity > {$similarity} limit 60) order by abs(similarity - {$similarity}) limit 60");
  $stmt->execute();

  $stmt = $pdo->prepare("alter table {$tmp_table_name} add id int not null primary key auto_increment first");
  $stmt->execute();
  
  $stmt = $pdo->prepare("select {$tmp_table_name}.ncode, Naro_All_info.writer, Naro_All_info.title, Naro_All_info.story from {$tmp_table_name} inner join Naro_All_info on {$tmp_table_name}.ncode = Naro_All_info.ncode order by {$tmp_table_name}.id limit 20 offset {$offset_par}");
  $stmt->execute();  
  $result = $stmt->fetchAll();

  //DB切断
  $stmt = null;
  $pdo = null;
  return $result;
}

//https://qiita.com/Hiraku/items/71873bf31e503eb1b4e1

?>


