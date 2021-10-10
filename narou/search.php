<link rel="stylesheet" href="css/base.css">

<?php
//narou/search.php
header("X-Content-Type-Options: nosniff");
require("function/db_operation.php");
require("function/other_func.php");

//エスケープ処理
$_GET['q'] = htmlspecialchars($_GET['q'], ENT_QUOTES, "utf-8");
$_GET['sort'] = htmlspecialchars($_GET['sort'], ENT_QUOTES, "utf-8");
$_GET['submit'] = htmlspecialchars($_GET['submit'], ENT_QUOTES, "utf-8");
$_GET['search_type'] = htmlspecialchars($_GET['search_type'], ENT_QUOTES, "utf-8");

?>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" >
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="function/func.js"></script>
    <title>「小説家になろう」検索サイト</title>
  </head>

  <body>
    <div class="footerFixed">
      <header>
        <h1> <a href="https://web.sfc.keio.ac.jp/~s19752km/narou/">「小説家になろう」検索サイト（非公式）</a></h1>
        <p id="title_annotation">※本サービスは株式会社ヒナプロジェクトが提供するものではありません</p>
        <nav>
          <ul id="nav">
            <li><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/">Home</a></li>
            <li><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/contact/">Contact</a></li>
            <li><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/about/">About</a></li>
          </ul>
        </nav>
      </header>



      <!--検索フォーム-->
    <form class ="search_container" action="https://web.sfc.keio.ac.jp/~s19752km/narou/search.php" method="get">
      <!--searchtypeを選択するプルダウン-->
      <select name='search_type' id="select_search_type">
        <option value="title">タイトル検索</opeion>
        <option value="keyword">キーワード検索</option>
      </select>
      <input id="search_text" placeholder="タイトル検索" type="text" name="q" value = "<?php echo $_GET['q'];?>">
      <input type="submit" name="submit" value="検索">
    </form>

<?php

if(empty($_GET['sort']) OR $_GET['sort'] === ""){
  $_GET['sort'] = "novelupdated_at";
}

$result = search_part($pdo);
$result_cnt_local = $GLOBALS['result_cnt'];
$result_cnt_local = number_format($result_cnt_local);  //検索結果数
echo "<span id=\"db_cnt\">該当件数：{$result_cnt_local}件</span>";

//並び替えプルダウンの作成
//プルダウンを作るための連想配列
$pulldown_array = [
  "novelupdated_at" => "新着順",
  "length" => "文字数",
  "genre" =>"ジャンル",
  "global_point" =>"総合評価ポイント",
  "daily_point" => "日間ポイント",
  "weekly_point" => "週間ポイント",
  "monthly_point" => "月間ポイント",
  "yearly_point" => "年間ポイント"
];
//プルダウン作成のための配列整形
foreach($pulldown_array as $key => $value){
$pulldown_array .= "<option value='". $key."'>". $value. "</option>";
}
?>

    <select name='sort', id="js-sort">
    <?php echo $pulldown_array;?>
    </select>
<script>
  change_order();
  var sort_parm_js="<?php echo $_GET['sort'];?>"
  keep_selected(sort_parm_js);
  var selected_parm_js="<?php echo $_GET['search_type'];?>"
  keep_selected_search_type(selected_parm_js);
</script>

  <div class="contents">
<?php
//検索結果のページング機能・検索結果を表示
paging_view($result);

?>
    </div>
    <footer>
      <br>小説家になろう」はヒナプロジェクトの登録商標です。<br>
      本サービスは株式会社ヒナプロジェクトが提供するものではありません
    </footer>
    </div>
  </body>
</html>
