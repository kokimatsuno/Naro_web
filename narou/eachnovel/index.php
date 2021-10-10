<link rel="stylesheet" href="../css/base.css">

<?php
/*narou/eachnovel/index.html*/
header("X-Content-Type-Options: nosniff");

require ('../function/db_operation.php');
//個別タイトルの詳細データを獲得
$result = ncode_search_detail_comp($pdo);
//あらすじを改行で区切って、配列に変換
$story_array = explode("\n", $result['story']);
//キーワードを空白で区切って、配列に変換
$keyword_array = explode(" ", $result['keyword']);
?>
<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
     <meta name="viewport" content="width=device-width,initial-scale=1" >
    <title><?echo $result['title'];?></title>
  </head>

  <body>
    <div class="footerFixed">
      <header>
        <h1><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/">「小説家になろう」検索サイト</a></h1>
        <p id="title_annotation">※本サービスは株式会社ヒナプロジェクトが提供するものではありません</p>
        <nav>
          <ul id="nav">
            <li><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/">Home</a></li>
            <li><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/contact/">Contact</a></li>
            <li><a href="https://web.sfc.keio.ac.jp/~s19752km/narou/about/">About</a></li>
          </ul>
        </nav>
      </header>
      <div class="contents">
        <h2 class="each_title"><?php echo $result['title'];?><br>
        <small><small>（作者：<?php echo $result['writer'];?>　/　ncode：<?php echo $result['ncode'];?>）</small></small>
        </h2>
        <div class="each_detail">
          <span class="read_novel_button">
            <?php /*セキュリティ対策のために rel=noopener noreferrerを設定しておく。
            （開いた先にwindow.opener.location = newURL があると、開いた先でこちらのurlが操作される）*/?>
          <a href="https://ncode.syosetu.com/<?php echo $result['ncode']?>" target="_blank" rel="noopener noreferrer">小説を読む。</a>
          </span>
          <small class="button_notice"><small>『小説家になろう』のサーバに移動します。</small></small>

        <h3>あらすじ
  <?php
      if($result['noveltype'] == 1){ echo "<span class=\"novel_type\">連載</span>";}
    else{ echo "<span class=\"novel_type\">短編</span>";}
  ?>
        </h3>
        <div id="story_box">
  <?php
      foreach($story_array as $value){
        echo $value."<br>";
      }?>
      <br>[キーワード：<?php
        foreach($keyword_array as $key => $value){
          echo "<a href=\"https://web.sfc.keio.ac.jp/~s19752km/narou/search.php?search_type=keyword&q=".$value."\">".$value."</a>&nbsp;&nbsp;";
        }?>]
<?
      switch($result['genre']){
        case 101: $genre = "恋愛・異世界"; break;
        case 102: $genre = "恋愛・現実世界"; break;
        case 201: $genre = "ファンタジー・ハイファンタジー"; break;
        case 202: $genre = "ファンタジー・ローファンタジー"; break;
        case 301: $genre = "文芸・純文学"; break;
        case 302: $genre = "文芸・ヒューマンドラマ"; break;
        case 303: $genre = "文芸・歴史"; break;
        case 304: $genre = "文芸・推理"; break;
        case 305: $genre = "文芸・ホラー"; break;
        case 306: $genre = "文芸・アクション"; break;
        case 307: $genre = "文芸・コメディー"; break;
        case 401: $genre = "SF・VRゲーム"; break;
        case 402: $genre = "SF・宇宙"; break;
        case 403: $genre = "SF・空想科学"; break;
        case 404: $genre = "SF・パニック"; break;
        case 9901:  $genre = "その他・童話"; break;
        case 9902:  $genre = "その他・詩"; break;
        case 9903:  $genre = "その他・エッセイ"; break;
        case 9999:  $genre = "その他・その他"; break;
        case 9801:  $genre = "ノンジャンル・ノンジャンル"; break;
      }
      echo "</div>";
      $length = number_format($result['length']);
  ?>
        <h3>掲載話数・文字数・想定読了時間</h3>
        掲載話数　　：<?php echo $result['general_all_no'];?>話<br>
        文字数　　　：<?php echo $length;?>字<br>
        想定読了時間：<?php echo $result['time'];?>分<br>
        <small><small>※1分あたり500字読むと想定</small></small>
        <h3>ジャンル</h3>
        <?php echo $genre;?>
        <h3>日時関連</h3>
        <table>
          <tr>
            <td>初回掲載日</td><td>
        <?php echo date("Y/ m/ d", strtotime($result['general_firstup']));?>
            </td>
          </tr>
          <tr>
            <td>最終掲載日</td><td>
        <?php echo date("Y/ m/ d", strtotime($result['general_lastup']));?>
            </td>
          </tr>
          <tr>
            <td>最終更新日</td><td>
        <?php echo date("Y/ m/ d", strtotime($result['novelupdated_at']));?>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <footer>
      <br>「小説家になろう」はヒナプロジェクトの登録商標です。<br>
      本サービスは株式会社ヒナプロジェクトが提供するものではありません
    </footer>
    </div>
  </body>

  </html>
