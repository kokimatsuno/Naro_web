#!/usr/bin/env python
# coding: utf-8


import requests
import pandas as pd
import json
import time as tm
import datetime
import gzip
from tqdm import tqdm
tqdm.pandas()
from sqlalchemy import create_engine
import pymysql


interval = 2

now_day = datetime.datetime.now()

now_day = now_day.strftime("%Y_%m_%d")


api_url="https://api.syosetu.com/novelapi/api/"

# 接続DBファイルの指定
with open ("db_pass.txt", "r", encoding="utf-8") as f:
    dbpass = f.read()
    dbpass.replace("\n", "")

engine = create_engine(f"mysql+pymysql://s19752km:{dbpass}@webdb.sfc.keio.ac.jp:3306/s19752km")

def get_all_novel_info():
    df = pd.DataFrame()

    payload = {'out' : 'json', 'gzip':5, 'of':'n', 'lim':1}
    res = requests.get(api_url, params=payload).content
    r =  gzip.decompress(res).decode("utf-8")
    allcount = json.loads(r)[0]["allcount"]

    print("対象作品数　", allcount);

    all_queue_cnt = (allcount // 500) + 10

    #現在時刻を取得
    nowtime = datetime.datetime.now().timestamp()
    lastup = int(nowtime)

    #現在時刻をログファイルに保存・更新に備える
    f = open('db_create_time_log.txt', 'w')
    f.write(str(lastup))
    f.close


    for i in tqdm(range(all_queue_cnt)):
        payload = {'out': 'json','gzip':5,'lim':500,'lastup':"1073779200-"+str(lastup), 'of':"t-n-w-s-g-k-gf-gl-nt-e-ga-l-ti-gp-dp-wp-mp-yp-a-sa-ka-nu"}


        # なろうAPIにリクエスト
        cnt=0
        while cnt < 5:
            try:
                res = requests.get(api_url, params=payload, timeout=30).content
                break
            except:
                print("Connection Error")
                cnt = cnt + 1
                tm.sleep(120) #接続エラーの場合、120秒後に再リクエストする

        r =  gzip.decompress(res).decode("utf-8")

        # pandasのデータフレームに追加する処理
        df = pd.read_json(r)
        df = df.drop(0)


        last_general_lastup = df.iloc[-1]["general_lastup"]

        lastup = datetime.datetime.strptime(last_general_lastup, "%Y-%m-%d %H:%M:%S").timestamp()
        lastup = int(lastup)
        #取得間隔を空ける
        tm.sleep(interval)

        # allcount列を削除
        df = df.drop("allcount", axis=1)
        # 重複行を削除する
        #df.drop_duplicates(subset='ncode', inplace=True)
        df = df.reset_index(drop=True)

        #mysqlへデータを保存
        dump_to_mysql(df, i)



    #DB重複チェック
    print("start Duplication check in mysql")
    mysql_dupulicate_erase()
    print("Complete Duplication check")

    #データ件数をDBから取得
    con = engine.connect()
    result_data = con.execute("select count(*) from Naro_All_info ")
    #result_dataを可読状態に変換する
    for result in result_data:
        pass

    print("Complete saving all data to mysql\n取得件数　", result[0])

### mysqlに書き込む処理 ###
def dump_to_mysql(df, for_cnt):
    try:
        if for_cnt == 0:
            df.to_sql("Naro_All_info", con=engine, if_exists='replace', index=False, method='multi')
        else:
            df.to_sql("Naro_All_info", con=engine, if_exists='append', index=False, method='multi')
    except:     #セッションが切断されていた場合は、こちらで再接続
        engine = create_engine("mysql+pymysql://s19752km:a0zJdmjW@webdb.sfc.keio.ac.jp:3306/s19752km")
        if for_cnt == 0:
            df.to_sql("Naro_All_info", con=engine, if_exists='replace', index=False, method='multi')
        else:
            df.to_sql("Naro_All_info", con=engine, if_exists='append', index=False, method='multi')




#データベース上で、重複ファイルを排除
def mysql_dupulicate_erase():
    engine = create_engine("mysql+pymysql://s19752km:a0zJdmjW@webdb.sfc.keio.ac.jp:3306/s19752km")
    con = engine.connect()
    #重複検索のため、primary keyを生成
    con.execute("alter table Naro_All_info add id int not null primary key auto_increment first")

    #重複削除実行のための構文
    sql = "delete from Naro_All_info where id not in (select min_id from (select min(id) as min_id from Naro_All_info group by ncode, title, general_lastup) as tmp)"
    con.execute(sql)#実行
    #primary key破棄
    con.execute("alter table Naro_All_info drop column id")
    con.close()



#######　関数の実行を指定　##########
print("start",datetime.datetime.now())
get_all_novel_info()
print("end",datetime.datetime.now())