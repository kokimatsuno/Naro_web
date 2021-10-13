<?php

//ページング機能を搭載して、検索結果を表示する。
function paging_view($result){
    if($_GET['search_type'] == "similar"){
      $paging_url = "https://web.sfc.keio.ac.jp/~s19752km/narou/similar.php?search_type=similar&ncode={$_GET['ncode']}&title={$_GET['title']}&page_id=";
      define('MAX',10); // 1ページの記事の表示数
    }else{
      $paging_url = "https://web.sfc.keio.ac.jp/~s19752km/narou/search.php?search_type={$_GET['search_type']}&q={$_GET['q']}&submit={$_GET['submit']}&sort={$_GET['sort']}&page_id=";
      define('MAX',20); // 1ページの記事の表示数
    }
    $max_page = ceil($GLOBALS["result_cnt"] / MAX);  //トータルページ数

    if(!isset($_GET['page_id'])){   //page_id：urlパラメータにある現在のページ数
      $now = 1;     //設定されていない場合は1ページ目とする。
    }else{
      $now = $_GET['page_id'];
    }

    search_list($result);
    echo "<div id=\"class_all\">";
    //ページリンク
    if($now > 1){
    echo "<a class=\"paging\" href=\"{$paging_url}".($now-1). "\">前へ</a>";
  }

    for($i = 1; $i <= $max_page; $i++){
      if ($i == $now){
        echo "<b id=\"paging_now\">".$now."  </b>";
    } else if($i <= ($now+2) AND $i >= ($now - 2)){
        echo "<a class=\"paging\" href=\"{$paging_url}{$i}\">[{$i}]&nbsp;</a>";

    }else if ($i == 1 && $now >=5){
        echo "<a class=\"paging\" href=\"{$paging_url}{$i}\">[{$i}]</a>
        <span id=\"paging_after_1\">･･･</span>";
    }else if($i == 1 && $now ==4){
        echo "<a class=\"paging\" href=\"{$paging_url}{$i}\">[{$i}]</a>";
    }else if ($i == $max_page && ($now <= $max_page - 4)){
        echo "<span id=\"paging_before_last\">･･･</span>
        <a class=\"paging\" href=\"{$paging_url}{$i}\">[{$i}]</a>";
    }else if ($i == $max_page && ($now == $max_page - 3)){
        echo "<a class=\"paging\"　href=\"{$paging_url}{$i}\">[{$i}]</a>";
    }
    }
    if($now < $max_page){
      echo "<a class=\"paging\" href=\"{$paging_url}".($now+1)."\">次へ</a>";
    }
    echo "</div>";
}

//検索結果を表示・ページングなし
function search_list($search_db){
  $max_cnt = 150; //あらすじ最大表示文字数（半角）
  foreach($search_db as $key => $value){    //foreach $key：配列の連番・$value：配列の中身（今回は配列）
    echo "<div class=\"search_list_box\">";
    //タイトルを表示する。
    echo "<h3><a href=\"https://web.sfc.keio.ac.jp/~s19752km/narou/eachnovel/?ncode={$value['ncode']}\">{$value['title']}</a></h3>";
    //作者
    echo "<span class=\"list_writer\">作者：{$value['writer']}<br></span>";

    //あらすじを整形・表示
    echo "<span class=\"story\">";
    str_replace(["\r\n", "\r", "\n"], '', $value['story']);      //改行・空白行削除
    //はじめに表示しておく分
    $story_tmp = mb_strimwidth($value['story'], 0, $max_cnt, "");
    //「続きを読む」を押してから表示される分
    $story_after_readmore[$key] = str_replace($story_tmp, "", $value['story']);
    str_replace(["\r\n", "\r", "\n"], '', $story_after_readmore[$key]);
    if(strlen($value['story']) > $max_cnt){
      echo "<span id='id_readmore_before{$key}' class=\"readmore_before{$key}\">{$story_tmp}<br>
            <a href=\"#readmore{$key}\" class=readmore_btn>>続きを読む</a></span>";
      echo "<span id=\"readmore{$key}\" class='readmore_area'>{$value['story']}<br>
            <a href='#id_readmore_before{$key}' class='readless_btn'><小さくする</a></span>";

    }else{
      echo $story_tmp."<br>";
    }
    $value['title'] = htmlspecialchars($value['title'], ENT_QUOTES, "utf-8");
    echo "<div class='list_similar_btn'><a href='https://web.sfc.keio.ac.jp/~s19752km/narou/similar.php?search_type=similar&ncode={$value['ncode']}&title={$value['title']}'>類似検索</a></div>";
    echo "</span>";
    echo "</div>";
  }
}


//複数文字を区切りとして分割する。
function ex_explode($word_array, $str) {
    $q_array = array();

    //分割文字ごとにforeach
    foreach ($word_array as $key => $value1){
      if($key === 0){
        $q_array = explode($value1, $str);
        continue;
      }else{
        //文字列の配列を分割
        foreach ($q_array as $key => $value2) {
            $q_array_tmp = explode($value1, $value2);
            if($key == 0) $q_array = $q_array_tmp;
            else $q_array = array_merge($q_array, $q_array_tmp);
        }
    }
  }
    return $q_array;
}

?>
