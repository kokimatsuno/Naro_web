<link rel="stylesheet" href="css/base.css">
<?php

header("X-Content-Type-Options: nosniff");

/*narou/index.html*/

require ('function/db_operation.php');
require ('function/other_func.php');
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
      <h2>新着小説</h2>
      <?
      //新着$num件をDBから取得
      $num = 30;
      $stmt = $pdo->query('select title, ncode, writer, story, keyword from Naro_All_info order by novelupdated_at desc limit '.$num);
      $new_novel = $stmt->fetchAll();
      //DB切断
      $stmt = null;
      $pdo = null;
      //取得データを表示する
      search_list($new_novel);
?>

   </div>
    <footer>
      <br>小説家になろう」はヒナプロジェクトの登録商標です。<br>
      本サービスは株式会社ヒナプロジェクトが提供するものではありません
    </footer>
    </div>
  </body>
</html>
