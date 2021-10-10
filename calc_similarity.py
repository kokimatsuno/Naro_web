# coding: utf-8

import MeCab
import numpy as np
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from gensim.models.doc2vec import Doc2Vec
from sqlalchemy import create_engine
import pymysql


with open ("db_pass.txt", "r", encoding="utf-8") as f:
    dbpass = f.read()
    dbpass = dbpass.replace("\n", "")

engine = create_engine(f"mysql+pymysql://s19752km:{dbpass}@webdb.sfc.keio.ac.jp:3306/s19752km")

#とりあえず、genre=201の作品でテスト
sql = "select ncode, title, keyword, story from Naro_All_info where genre = 201"
df = pd.read_sql(sql,con = engine)

#対象文章をリスト化する
target_docs = df['story'].to_list()
ncode_list = df['ncode'].to_list()

#モデルの読み込み
#参照：https://yag-ays.github.io/project/pretrained_doc2vec_wikipedia/
model = Doc2Vec.load("./jawiki.doc2vec.dbow300d/jawiki.doc2vec.dbow300d.model")

#形態素解析
def mecab_sep(text):
    m = MeCab.Tagger("-Ochasen")
    m.parse('')       #https://shogo82148.github.io/blog/2015/12/20/mecab-in-python3-final/
    
    node = m.parseToNode(text)

    word_list = []

    while node:
        if node.feature.split(",")[0] == "名詞":
            word_list.append(node.surface)
        elif node.feature.split(",")[0] == "動詞":
            word_list.append(node.feature.split(",")[6])
        elif node.feature.split(",")[0] == "形容詞":
            word_list.append(node.feature.split(",")[6])
        elif node.feature.split(",")[0] == "形容動詞":
            word_list.append(node.feature.split(",")[6])
        
        node = node.next
    
    return word_list


#Doc2Vec　文章のベクトル化
def calc_vecs_d2v(docs):
    vecs = []
    for d in docs:
        vecs.append(model.infer_vector(mecab_sep(d)))

    return vecs


###関数の実行###
all_docs_vecs = calc_vecs_d2v(target_docs)

#類似度の計算　配列0番目を基準にして
similarity = cosine_similarity([all_docs_vecs[0]], all_docs_vecs[1:])


result_df = pd.DataFrame(list(zip(ncode_list, similarity)), columns= ['ncode', 'similarity'])