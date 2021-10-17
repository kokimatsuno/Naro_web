//narou/func.js

$(function() {
  $(".readmore_area").hide();                 //続きを読む部分を始めは隠しておく。

  $(".readmore_btn").click(function(){        //続きを読むを押したとき
    return showMore(this);
  });

  $(".readless_btn").click(function(){        //小さくするを押したとき
    return readless(this);
  });

  $("form").submit(function(){                //検索ボタンをおしたとき
    return check_search_form();
  })

  $("#select_search_type").change(function(){        //検索タイププルダウンが変更されたとき
    change_search_placeholder(this);
  })
});

//「続きを読む」を押したときの動作
function showMore(btn){
  var targetId = btn.getAttribute("href");          // 表示対象のid名をhref属性値から得る
  $(targetId).show();
  $(btn.parentNode).hide();
  return false;
}

//「少なくする」を押したときの動作
function readless(btn){
  var targetId = btn.getAttribute("href");
  $(btn.parentNode).hide();
  $(targetId).show();
  return false;
}

//テキストボックスが、空白であればfalseを返す。
function check_search_form(){
  //テキストボックスの中身を取得
  var text_val = $("#search_text").val();
  //前後の空白を削除
  text_val = $.trim(text_val);
  //テキスト空白判定
  if(text_val == ""){
    return false;
  }
}


//sortプルダウンが変更された場合に、並び替えを行う。
//urlパラメータの操作は、通常jsのほうが簡単
function change_order(){
  const selectElement = document.querySelector('#js-sort');
  var url = new URL(window.location.href);

  selectElement.addEventListener('change', (event) => {
    var value = event.target.value;
    if( url.searchParams.get('sort') ) {
      var params = url.searchParams;
      params.delete('sort');
      history.replaceState('', url.pathname);
    }

    url.searchParams.append('sort', value);
    var paramstr = url.searchParams;
    location.href = location.origin+location.pathname+"?"+paramstr;
  })
}

//sortプルダウンの選択状態を維持する
function keep_selected(sort_parm_js){
  $(`#js-sort option[value=${sort_parm_js}]`).prop('selected', true);
}

//search_typeプルダウンの選択状態を維持する。
function keep_selected_search_type(selected_parm_js){
  $(`#select_search_type option[value=${selected_parm_js}]`).prop('selected', true);
}

//search_typeプルダウンが変更された際に、テキストエリアのplaceholderを変更
function change_search_placeholder(){
  var select_val = $("#select_search_type").val();
  if(select_val == "title"){
    var placeholder_txt = "タイトル検索";
  }else if(select_val == "keyword"){
    var placeholder_txt = "キーワード検索";
  }else if(select_val == "ncode"){
    var placeholder_txt = "Nコード検索"
  }
  $("#search_text").prop("placeholder", placeholder_txt);
}
