<?php
header("X-Content-Type-Options: nosniff");
header('Content-Type: text/html; charset=utf-8');

require("./function/db_operation.php");
require("./function/other_func.php");

ob_start();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
     <meta name="viewport" content="width=device-width,initial-scale=1" >
     <link rel="stylesheet" href="./css/base.css">
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <title>「小説家になろう」類似検索結果</title>
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
      <!-- 検索フォーム -->
      <form class ="search_container" action="https://web.sfc.keio.ac.jp/~s19752km/narou/search.php" method="get">
      <select name='search_type' id="select_search_type">
        <option value="title">タイトル検索</opeion>
        <option value="keyword">キーワード検索</option>
      </select>
      <input id="search_text" placeholder="タイトル検索" type="text" name="q">
      <input type="submit" name="submit" value="検索">
      </form>
      
      <div class="contents">
      <script src="function/func.js"></script>
        <h2>類似検索結果</h2>
        <h3>検索対象：<?php 
        flush();
        ob_flush();
        $result_target = ncode_search_title($pdo);
        echo "<a href=\"https://web.sfc.keio.ac.jp/~s19752km/narou/eachnovel/?ncode={$result_target['ncode']}\">{$result_target['title']}</a>";
        ?></h3>
        <?php
        $result = search_similar($pdo);
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
