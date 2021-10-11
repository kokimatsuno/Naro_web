<?php
header('Content-Type: text/html; charset=utf-8');

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
  $GLOBALS['result_cnt'] = $stmt->fetch(PDO::FETCH_ASSOC);
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
  $stmt = $pdo->prepare("select genre, similarity from Naro_All_info where ncode = :ncode");
  $stmt->bindValue(":ncode", $_GET['ncode']);
  $stmt->execute();
  $result_tmp = $stmt->fetch(); 

  $table_name = "Naro_similarity_{$result_tmp['genre']}";

  $stmt = $pdo->prepare("select title, ncode, story, writer from {$table_name} order by abs({$result_tmp['similarity']}) offset 1 ASCE limit 50");
  $stmt->execute();
  $result = $stmt->fetchAll();

  //DB切断
  $stmt = null;
  $pdo = null;
  return $result;
}

?>
