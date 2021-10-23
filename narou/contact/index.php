<?php
header("X-Content-Type-Options: nosniff");

//LINE NOTIFYとの連携・お問い合わせを受けたときにメッセージを送る
function post_message($message){
  define("LINE_API_URL", "https://notify-api.line.me/api/notify");
  $fp = fopen("/home/s19752km/public_html/narou/line_api.txt", "r");
  $line_api_pass = fgets($fp);
  $line_api_pass = str_replace(PHP_EOL, "", $line_api_pass);
  fclose($fp);
  define("LINE_API_TOKEN", $line_api_pass);
  $data = http_build_query(["message" => $message], "", "&");

  $options = [
      'http'=> [
          'method'=>'POST',
          'header'=>'Authorization: Bearer ' . LINE_API_TOKEN . "\r\n"
                  . "Content-Type: application/x-www-form-urlencoded\r\n",
          'content' => $data,
          ]
        ];
  $context = stream_context_create($options);
//送信する。1引数：url or fileを読み込む。レスポンスがjsonでくる。
  $resultJson = file_get_contents(LINE_API_URL, false, $context);
  $resultArray = json_decode($resultJson, true);
  if($resultArray['status'] != 200)  {
      return false;
  }
  return true;
}


  session_start();
  $mode = "input";
  $errmessage = array();
//戻るボタンが押されたとき
  if(isset($_POST["back"]) && $_POST["back"]){
    //pass
  }else if(isset($_POST["confirm"]) && $_POST["confirm"]){
//確認ボタンが押されたとき
    if(!$_POST["fullname"]){
      //名前入力欄のチェック
      $errmessage[] = "名前を入力してください。";
    }else if(mb_strlen($_POST["fullname"]) > 100){
      $errmessage[] = "名前は100文字以内にしてください";
    }
    $_SESSION["fullname"] = htmlspecialchars($_POST["fullname"], ENT_QUOTES, "utf-8");

    //email入力欄のチェック
    if($_POST["email"] != "" OR $_SESSION["email"] != ""){
      if(mb_strlen($_POST["email"])> 200){
        $errmessage[] = "Eメールは200文字以内にして下さい";
      }else if( !filter_var($_POST["email"], FILTER_VALIDETE_EMAIL) ){
        $errmessage[] = "メールアドレスが不正です";
      }
    }
    $_SESSION["email"] = htmlspecialchars($POST["email"], ENT_QUOTES, "utf-8");

    //本文入力欄のチェック
    if ( !$_POST["message"] ){
    $errmessage[] = "お問い合わせ内容を入力して下さい";
  } else if( mb_strlen($_POST["message"]) > 500 ){
    $errmessage = "お問い合わせ内容は500文字以内にして下さい";
  }
    $_SESSION["message"] = htmlspecialchars($_POST["message"], ENT_QUOTES);
    if($errmessage){
      $mode = "input";
    }else{
    $token = bin2hex(random_bytes(32));
    $_SESSION["token"] = $token;
    $mode = "confirm";
    }
}
//送信ボタンが押されたとき
  else if(isset($_POST["send"]) && $_POST["send"]){
    if(!$_POST["token"] || !$_SESSION["token"] || $_POST["token"] != $_SESSION["token"] ){
      $errmessage[] = "不正な処理が行われました";
      $_SESSION = array();
      $mode = "input";
    }else if( $_POST['token'] != $_SESSION['token'] ){
    $errmessage[] = '不正な処理が行われました';
    $_SESSION     = array();
    $mode         = 'input';
  }else{
    $message = "お問い合わせを受け付けました\r\n"
              ."お名前：". $_SESSION["fullname"]. "\r\n"
              ."email：" .$_SESSION["email"]. "\r\n"
              ."お問い合わせ内容：\r\n"
              .preg_replace("/\r\n|\r|\n/", "\r\n", $_SESSION["message"] );
    if($_SESSION["email"] != ""){
    mail($_SESSION["email"], "お問い合わせありがとうございます", $message);
    }
    //mail("s19752km@sfc.keio.ac.jp", "ホームページ問い合わせ", $message);
    post_message($message);
    $_SESSION = array();
    $mode = "send";
  }
}else{
    $SESSION = array();
  }
  ?>

<html>
<head>
   <meta charset="utf-8">
   <link rel="stylesheet" href="../css/base.css">
   <link rel="stylesheet" href="../css/contact_style.css">
   <meta name="google" content="nositelinkssearchbox">
   <meta name="viewport" content="width=device-width,initial-scale=1" >
   <title>Contact |「小説家になろう」類似検索</title>
   <!-- Global site tag (gtag.js) - Google Analytics -->
   <script async src="https://www.googletagmanager.com/gtag/js?id=UA-207399511-3"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-207399511-3');
      </script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-PH3N219QEH"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-PH3N219QEH');
      </script>
</head>

<body>
  <h1> <a href="https://web.sfc.keio.ac.jp/~s19752km/narou/">「小説家になろう」類似検索</a></h1>


<?php if($mode == "input"){ ?>
  <!--入力画面-->
  <?php
     if( $errmessage ){
       echo '<div style="color:red;">';
       echo implode('<br>', $errmessage );
       echo '</div>';
     }
   ?>
      <form action="./index.php" method="post" class="Form">
        <div class="Form-Item">
          <p class="Form-Item-Label">
            <span class="Form-Item-Label-Required">必須</span>名前（ニックネーム）</p>
            <input type="text" class="Form-Item-Input" name="fullname" value="<?php echo $_SESSION["fullname"] ?>" placeholder="お名前">
        </div>
        <div class="Form-Item">
          <p class="Form-Item-Label">Eメール</p>
          <input type="email" class="Form-Item-Input" name="email" value="<?php echo $_SESSION["email"] ?>" placeholder="E-mail">
        </div>
        <div class="Form-Item">
          <p class="Form-Item-Label">
            <span class="Form-Item-Label-Required">必須</span>お問い合わせ内容</p>
            <textarea class="Form-Item-Textarea" name="message" id="" cols="" rows=""><?php echo $_SESSION["message"] ?></textarea>
        </div>
        <input type="submit" name="confirm" value="確認画面へ" class="Form-Btn">
      </form>
<?php }else if($mode == "confirm"){ ?>
  <!--確認画面-->
  <h2>確認画面</h2>
  <form action="./index.php" class="Form" method="post">
  <input type="hidden" name="token" value="<?php echo $_SESSION["token"]; ?>">
  <div class="Form-Item">
    <p class="Form-Item-Label">名前</p>
    <span class="Form-Item-Input"><?php echo $_SESSION["fullname"] ?></span>
  </div>
  <div class="Form-Item">
    <p class="Form-Item-Label">Eメール</p>
    <span class="Form-Item-Input"><?php echo $_SESSION["email"] ?></span>
  </div>
  <div class="Form-Item">
    <p class="Form-Item-Label">本文 </p>
    <span class="Form-Item-Input"><?php echo nl2br($_SESSION["message"]) ?></span>
  </div>
    <input type="submit" name="back" class="Form-Btn" value="修正する" />
    <input type="submit" name="send" class="Form-Btn" value="送信する" />
  </form>
<?php }else{?>
  <!-- 完了画面-->
  送信しました。お問い合わせありがとうございました。
<?php } ?>
